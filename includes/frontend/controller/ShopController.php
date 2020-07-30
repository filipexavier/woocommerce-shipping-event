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
      add_filter( 'woocommerce_is_purchasable', array($this, 'override_is_purchasable' ), 10, 2);
      add_filter( 'woocommerce_add_to_cart_validation', array($this, 'override_is_visible' ), 1, 2);
      add_filter( 'woocommerce_product_is_in_stock', array( $this, 'override_is_in_stock' ), 10, 2 );
      add_filter( 'woocommerce_product_get_stock_quantity' , array( $this, 'override_stock_quantity' ), 10, 2 );
      add_filter( 'woocommerce_product_get_manage_stock' , array( $this, 'override_manage_stock' ), 10, 2 );
      add_filter( 'woocommerce_product_set_stock_quantity' , array( $this, 'override_set_stock' ), 10, 2 );
    }
  }

  public function set_session_shipping_event() {
    //Start keepping unlogged user data
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

  public function get_session_shipping_event_product_list() {
    return $this->get_shipping_event_product_list( $this->shipping_event );
  }

  public function get_session_shipping_event_method_list() {
    return $this->get_shipping_event_method_list( $this->shipping_event );
  }

  public function get_shipping_event_product_list( $shipping_event ) {
    if ( !empty( $shipping_event ) && $shipping_event->get_enabled() ) {
      $shipping_event_products = get_post_meta( $shipping_event->get_id(), 'products', true );
      if ( !isset( $shipping_event_products ) ) return null;
      return $shipping_event_products;
    }
    return null;
  }

  public function get_shipping_event_method_list( $shipping_event ) {
    if ( !empty( $shipping_event ) && $shipping_event->get_orders_enabled() ) {
      $shipping_event_methods = get_post_meta( $shipping_event->get_id(), 'selected_shipping_methods', true );

      if ( !isset( $shipping_event_methods ) ) return null;
      return $shipping_event_methods;
    }
    return null;
  }

  public function get_shipping_event_product_data( $product_id ) {
    $shipping_event_products = $this->get_session_shipping_event_product_list();
    if ( !isset( $shipping_event_products ) ) return null;
    if ( !array_key_exists( $product_id, $shipping_event_products ) ) return null;
    $product_data = $shipping_event_products[$product_id];

    if ( !array_key_exists( 'enabled', $product_data ) || $product_data['enabled'] != "yes" ) return null;
    return $product_data;
  }

  public function get_shipping_event_product_stock( $product_id ) {
    $product_data = $this->get_shipping_event_product_data( $product_id );
    if ( !isset( $product_data ) || !array_key_exists( 'stock', $product_data ) || $product_data['stock'] == '' ) return null;
    return $product_data['stock'];
  }

  public function get_shipping_event_product_stock_quantity( $product_id ) {
    return $this->get_shipping_event_product_stock( $product_id );
  }

  public function get_shipping_event_product_stock_status( $product_id ) {
    $stock = $this->get_shipping_event_product_stock( $product_id );
    if( isset( $stock ) ) {
      if( $stock > 0 ) {
        return true;
      } else {
        return false;
      }
    } else {
      return null;
    }
  }

  /**
   * @param bool $bool Return either true/false
   * @param int $id ID of the product
   * @return bool it return either true or false
   */
  public function override_is_visible($visible, $product_id) {
    if($visible) {
      $product_data = $this->get_shipping_event_product_data( $product_id );
      if( !isset( $product_data ) ) return false;
    }
    return $visible;
  }

  public function override_is_in_stock( $stock_status, $product ) {
    $status = $this->get_shipping_event_product_stock_status( $product->get_id() );
    if( isset( $status ) ) return $status;
    return $stock_status;
  }

  public function override_is_purchasable( $is_purchasable, $product ) {
    if( $is_purchasable ) {
      $product_data = $this->get_shipping_event_product_data( $product->get_id() );
      if( is_null( $product_data ) ) return false;
    }
    return $is_purchasable;
  }

  public function override_set_stock( $stock_num, $product ) {

  }

  public function override_stock_quantity( $stock_num, $product ) {
    $stock = $this->get_shipping_event_product_stock_quantity( $product->get_id() );
    if( isset( $stock ) ) return $stock;
    return $stock_num;
  }

  public function override_manage_stock( $manage_stock, $product ) {
    $stock = $this->get_shipping_event_product_stock( $product->get_id() );
    if( isset( $stock) ) $manage_stock = true;
    return $manage_stock;
  }

  public function get_shipping_event() {
    return $this->shipping_event;
  }

}
