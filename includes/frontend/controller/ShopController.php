<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Frontend\Controller;

use \WCShippingEvent\Cpt\ShippingEvent;
use \WCShippingEvent\Base\ShippingEventController;

class ShopController {

  private static $instance;

  /** @var ShippingEvent */
  private $shipping_event;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function init() {
    if( !is_admin() ) {
      add_action( 'woocommerce_init', array( $this, 'set_session_shipping_event' ) );
      add_filter( 'woocommerce_product_is_visible', array( $this, 'override_is_visible' ), 10, 2 );
      add_filter( 'woocommerce_is_purchasable', array($this, 'override_is_purchasable' ), 10, 2 );
      add_filter( 'woocommerce_add_to_cart_validation', array($this, 'override_is_visible' ), 1, 2 );
      add_filter( 'woocommerce_product_is_in_stock', array( $this, 'override_is_in_stock' ), 10, 2 );
      add_filter( 'woocommerce_product_get_stock_quantity' , array( $this, 'override_stock_quantity' ), 10, 2 );
      add_filter( 'woocommerce_product_get_manage_stock' , array( $this, 'override_manage_stock' ), 10, 2 );
      add_filter( 'woocommerce_can_reduce_order_stock', array( $this, 'reduce_shipping_event_stock' ), 1, 2 );
    }
  }

  public function set_session_shipping_event() {
    if( !WC()->session ) return;

    if ( !WC()->session->has_session() ) {
      WC()->session->set_customer_session_cookie( true );
    }

    $shipping_event_id = null;
    if( !empty( $this->shipping_event ) ) $shipping_event_id = $this->shipping_event->ID;
    else $shipping_event_id = WC()->session->get('shipping_event');

    //Change Chosen Shipping Event
    if( array_key_exists( 'chosen_shipping_event_id', $_POST ) &&
        !empty( $_POST['chosen_shipping_event_id'] ) &&
        $_POST['chosen_shipping_event_id'] != $shipping_event_id )
        //TODO: REDIRECT TO CHOOSE SHIPPING EVENT AND REMOVE ALL FILTERS
      $shipping_event_id = $_POST['chosen_shipping_event_id'];

    //Set this shipping_event property
    if( !empty( $shipping_event_id ) &&
      ( empty( $this->shipping_event ) ||
        $shipping_event_id != $this->shipping_event->get_id() ) ) {
      $this->shipping_event = new ShippingEvent( $shipping_event_id );
      WC()->session->set('shipping_event', $shipping_event_id);
    }

    //Check shipping_event valid
    if ( empty( $this->shipping_event ) || !$this->shipping_event->orders_enabled() ) {
      $this->shipping_event = null;
      WC()->session->__unset('shipping_event');
      //TODO: REDIRECT TO CHOOSE SHIPPING EVENT AND REMOVE ALL FILTERS
    }
  }

  /**
   * @param bool $bool Return either true/false
   * @param int $id ID of the product
   * @return bool it return either true or false
   */
  public function override_is_visible( $visible, $product_id ) {
    //respects if item is not visible by default settings of woocommerce
    if( !$visible || is_null( $this->get_shipping_event() ) ) return $visible;
    return $this->shipping_event->is_product_enabled( $product_id );
  }

  public function override_is_in_stock( $stock_status, $product ) {
    if( $this->get_shipping_event() ) {
      $in_stock = $this->shipping_event->is_product_in_stock( $product->get_id() );
      //Ignore if null because it means that stock is not set, so lets consider default behavior of woocommerce
      if( !is_null( $in_stock ) ) return $in_stock;
    }
    return $stock_status;
  }

  public function override_is_purchasable( $is_purchasable, $product ) {
    //respects if item is not visible by default settings of woocommerce
    if( !$is_purchasable || is_null( $this->get_shipping_event() ) ) return $is_purchasable;
    return $this->shipping_event->is_product_enabled( $product->get_id() );
  }

  /**
   * Changed copy from wc_reduce_stock_levels() in wc-stock-functions.php (woocommerce plugin)
  */
  function mimic_default_reduce_stock_behavior( $order ) {
    $changes = array();

  	// Loop over all items.
  	foreach ( $order->get_items() as $item ) {
  		if ( ! $item->is_type( 'line_item' ) ) {
  			continue;
  		}

  		// Only reduce stock once for each item.
  		$product            = $item->get_product();
  		$item_stock_reduced = $item->get_meta( '_reduced_stock', true );

      //Ignore if the product is managing stock. Shipping Event overrides this setting
  		if ( $item_stock_reduced || ! $product ) {
  			continue;
  		}

  		$qty       = $item->get_quantity();//Ignore original apply_filters
  		$item_name = $product->get_formatted_name();
      //override default call to wc_update_product_stock()
  		$new_stock = $this->shipping_event->update_product_stock( $product, $qty, 'reduce', true );
      error_log($item_name . " new stock - " . $new_stock);
  		if ( is_wp_error( $new_stock ) || !$new_stock ) {

  			/* translators: %s item name. */
  			$order->add_order_note( sprintf( __( 'Unable to reduce stock for item %s.', 'woocommerce' ), $item_name ) . "Motive: " . $new_stock );
  			continue;
  		}

  		$item->add_meta_data( '_reduced_stock', $qty, true );
  		$item->save();

  		$changes[] = array(
  			'product' => $product,
  			'from'    => $new_stock + $qty,
  			'to'      => $new_stock,
  		);
  	}

  	wc_trigger_stock_change_notifications( $order, $changes );

  	do_action( 'woocommerce_reduce_order_stock', $order );
  }

  function reduce_shipping_event_stock( $can_reduce_stock, $order ) {
    if( !$this->shipping_event ) return $can_reduce_stock;
    $this->mimic_default_reduce_stock_behavior( $order );

    //Stops default reducing process
    return false;
  }

  public function override_stock_quantity( $stock_num, $product ) {
    if( $this->get_shipping_event() ) {
      $stock = $this->shipping_event->get_product_stock_quantity( $product->get_id() );
      if( $stock ) return $stock;
    }
    return $stock_num;
  }

  public function override_manage_stock( $manage_stock, $product ) {
    if( $this->shipping_event && $this->shipping_event->get_product_manage_stock( $product->get_id() ) )
      return true;
    return $manage_stock;
  }

  public function get_shipping_event() {
    if( is_null( $this->shipping_event ) ) $this->set_session_shipping_event();
    return $this->shipping_event;
  }

}
