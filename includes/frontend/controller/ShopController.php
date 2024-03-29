<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Frontend\Controller;

use \WCShippingEvent\Cpt\ShippingEvent;
use \WCShippingEvent\Base\ShippingEventController;
use \WCShippingEvent\Base\SettingsController;
use \WCShippingEvent\Init;

class ShopController {

  private static $instance;

  public const CHOSEN_ARG_CODE = 'chosen_shipping_event_id';

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
      add_filter( 'woocommerce_product_get_stock_status' , array( $this, 'override_stock_status' ), 10, 2 );
      add_filter( 'woocommerce_product_backorders_allowed' , array( $this, 'override_allow_backorders' ), 10, 2 );
      add_filter( 'woocommerce_product_get_manage_stock' , array( $this, 'override_manage_stock' ), 10, 2 );

      //Maybe show popup in All pages of woocommerce
      add_action( 'woocommerce_before_cart', array($this, 'block_access_when_no_event'), 10 );
      add_action( 'woocommerce_before_single_product', array($this, 'block_access_when_no_event'), 10 );
      add_action( 'woocommerce_before_main_content', array($this, 'block_access_when_no_event'), 10 );
      add_action( 'woocommerce_before_checkout_form', array($this, 'block_access_when_no_event'), 10 );
    }
    add_filter( 'woocommerce_can_reduce_order_stock', array( $this, 'reduce_shipping_event_stock' ), 1, 2 );
    add_filter( 'woocommerce_can_restore_order_stock', array( $this, 'restore_shipping_event_stock' ), 1, 2 );
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
    if( array_key_exists( self::CHOSEN_ARG_CODE, $_GET ) &&
        !empty( $_GET[self::CHOSEN_ARG_CODE] ) &&
        $_GET[self::CHOSEN_ARG_CODE] != $shipping_event_id ) {
      $shipping_event_id = $_GET[self::CHOSEN_ARG_CODE];
    }

    //Set this shipping_event property
    if( !empty( $shipping_event_id ) &&
      ( empty( $this->shipping_event ) ||
        $shipping_event_id != $this->shipping_event->get_id() ) ) {
      $this->shipping_event = ShippingEventController::get_instance()->get_shipping_event( $shipping_event_id );
      WC()->session->set('shipping_event', $shipping_event_id);
    }

    //Check shipping_event valid
    if ( empty( $this->shipping_event ) || !$this->shipping_event->orders_enabled() ) {
      $this->shipping_event = ShippingEventController::get_instance()->single_opened_event();
      if( is_null( $this->shipping_event ) ) WC()->session->__unset('shipping_event');
      else WC()->session->set('shipping_event', $this->shipping_event->get_id());
    }
  }

  public function has_chosen_valid_shipping_event() {
    //Check shipping_event valid
    if (!($this->shipping_event && $this->shipping_event->orders_enabled())) {
      $this->set_session_shipping_event();
    }
    return $this->shipping_event && $this->shipping_event->orders_enabled();
  }

  public function redirect($url, $permanent = false)
  {
      if (headers_sent() === false)
      {
          header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
      }

      exit();
  }

  public function block_access_when_no_event() {
    if( $this->has_chosen_valid_shipping_event() ) return;
    //Don't show pop up on thess taxonomies pages
    if( SettingsController::get_instance()->check_tax_page() ) return;

    //Redirect to list of events
    $this->redirect( SettingsController::get_instance()->get_choose_event_page_url(), false);
  }

  public function choose_event_popup() {
    $ok_btn_target = $close_btn_target = SettingsController::get_instance()->get_choose_event_page_url();
    $title = __("Choose date", 'woocommerce-shipping-event' );
    $active = "true";
    $cancel_btn = "false";
    $msg = __( "Please choose a date before shopping. If you already chosen, it's probably not available anymore.", 'woocommerce-shipping-event' );
    $this->show_popup( $active, $title, $msg, $cancel_btn, $ok_btn_target, $close_btn_target );
  }

  public function show_popup( $active, $title, $msg, $cancel_btn, $ok_btn_target, $close_btn_target ) {
    include Init::get_instance()->plugin_path . 'includes/frontend/view/shipping_event_popup.php';
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

  public function override_is_in_stock( $not_out_of_stock, $product ) {
    if( $this->get_shipping_event() ) {
      return 'outofstock' !== $this->shipping_event->get_product_stock_status( $product->get_id() );
    }
    return $not_out_of_stock;
  }

  public function override_is_purchasable( $is_purchasable, $product ) {
    //respects if item is not visible by default settings of woocommerce
    if( !$is_purchasable || is_null( $this->get_shipping_event() ) ) return $is_purchasable;
    return $this->shipping_event->is_product_enabled( $product->get_id() );
  }

  /**
   * Changed copy from wc_restore_stock_levels() in wc-stock-functions.php (woocommerce plugin)
  */
  function mimic_default_restore_stock_behavior( $order, $shipping_event ) {
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
  		if ( !$item_stock_reduced || ! $product || ! $shipping_event->get_product_manage_stock( $product->get_id() ) ) {
  			continue;
  		}

  		$item_name = $product->get_formatted_name();
      //override default call to wc_update_product_stock()
  		$new_stock = $shipping_event->update_product_stock( $product, $item_stock_reduced, 'increase', true );
      error_log($item_name . " new stock - " . $new_stock);
  		if ( is_wp_error( $new_stock ) || !$new_stock ) {

  			/* translators: %s item name. */
  			$order->add_order_note( sprintf( __( 'Unable to restore stock for item %s.', 'woocommerce' ), $item_name ) . __( "Motive: " ) . $new_stock );
  			continue;
  		}

  		$item->delete_meta_data( '_reduced_stock' );
  		$item->save();

      $changes[] = $item_name . ' ' . ( $new_stock - $item_stock_reduced ) . '&rarr;' . $new_stock;
  	}

    if ( $changes ) {
  		$order->add_order_note( __( 'Stock levels increased:', 'woocommerce' ) . ' ' . implode( ', ', $changes ) );
  	}

  	do_action( 'woocommerce_restore_order_stock', $order );
  }

  function restore_shipping_event_stock( $can_restore_stock, $order ) {
    $shipping_event_id = get_post_meta( $order->get_id(), 'shipping_event', true );
    if( empty( $shipping_event_id ) ) return $can_restore_stock;
    $shipping_event = ShippingEventController::get_instance()->get_shipping_event( $shipping_event_id );
    if( is_null( $shipping_event ) ) return $can_restore_stock;

    $this->mimic_default_restore_stock_behavior( $order, $shipping_event );

    //Stops default restoring process
    return false;
  }

  /**
   * Changed copy from wc_reduce_stock_levels() in wc-stock-functions.php (woocommerce plugin)
  */
  function mimic_default_reduce_stock_behavior( $order, $shipping_event ) {
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
  		if ( $item_stock_reduced || ! $product || ! $shipping_event->get_product_manage_stock( $product->get_id() ) ) {
  			continue;
  		}

  		$qty       = $item->get_quantity();//Ignore original apply_filters
  		$item_name = $product->get_formatted_name();
      //override default call to wc_update_product_stock()
  		$new_stock = $shipping_event->update_product_stock( $product, $qty, 'reduce', true );
      error_log($item_name . " new stock - " . $new_stock);
  		if ( is_wp_error( $new_stock ) || !$new_stock ) {

  			/* translators: %s item name. */
  			$order->add_order_note( sprintf( __( 'Unable to reduce stock for item %s.', 'woocommerce' ), $item_name ) . __( "Motive: " ) . $new_stock );
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
    $shipping_event = $this->shipping_event;
    if( !$this->shipping_event ) {
      $shipping_event_id = get_post_meta( $order->get_id(), 'shipping_event', true );
      if( empty( $shipping_event_id ) ) return $can_restore_stock;
      $shipping_event = ShippingEventController::get_instance()->get_shipping_event( $shipping_event_id );
      if( is_null( $shipping_event ) ) return $can_restore_stock;
    }
    $this->mimic_default_reduce_stock_behavior( $order, $shipping_event );

    //Stops default reducing process
    return false;
  }

  public function override_allow_backorders( $allow_backorders, $product ) {
    if( $allow_backorders && $this->get_shipping_event() && $this->shipping_event->get_disable_backorder() )
      return false;

    return $allow_backorders;
  }

  public function override_stock_quantity( $stock_num, $product ) {
    if( $this->get_shipping_event() ) {
      $stock = $this->shipping_event->get_product_stock_quantity( $product->get_id() );
      if( $stock ) return $stock;
    }
    return $stock_num;
  }

  public function override_stock_status( $stock_status, $product ) {
    if( $this->get_shipping_event()
      && $stock_status == 'onbackorder'
      && $this->shipping_event->get_disable_backorder() )
      return 'outofstock';

    return $stock_status;
  }

  public function override_manage_stock( $manage_stock, $product ) {
    if( $this->shipping_event && $this->shipping_event->get_product_manage_stock( $product->get_id() ) )
      return true;
    return $manage_stock;
  }

  public function is_delivery_order( $order ) {
    $address = get_post_meta( $order->get_id(), 'local_pickup_details_address', true );
    if ( empty( $address ) ) return true;
    return false;
  }

  public function get_shipping_event_id() {
    if( $this->get_shipping_event() ) return $this->shipping_event->get_id();
    return null;
  }

  public function get_shipping_event() {
    if( is_null( $this->shipping_event ) ) $this->set_session_shipping_event();
    return $this->shipping_event;
  }

}
