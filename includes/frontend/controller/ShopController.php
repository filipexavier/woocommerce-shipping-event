<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Frontend\Controller;

use \WCShippingEvent\Cpt\ShippingEvent;

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
      add_action( 'wp', array( $this, 'select_user_shipping_event' ) );
      add_action( 'woocommerce_before_cart', array( $this, 'show_shipping_date_on_header' ), 40 );
      add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'show_shipping_date_on_header' ), 40 );
      add_filter( 'woocommerce_product_is_visible', array( $this, 'override_is_visible' ), 10, 2 );
      add_filter( 'woocommerce_is_purchasable', array($this, 'override_is_purchasable' ), 10, 2);
      add_filter( 'woocommerce_add_to_cart_validation', array($this, 'override_is_visible' ), 1, 2);
      add_filter( 'woocommerce_product_is_in_stock', array( $this, 'override_is_in_stock' ), 10, 2 );
      add_filter( 'woocommerce_product_get_stock_quantity' , array( $this, 'override_stock_quantity' ), 10, 2 );
      add_filter( 'woocommerce_product_get_manage_stock' , array( $this, 'override_manage_stock' ), 10, 2 );
    }
  }

  public function set_session_shipping_event() {
    if( !isset( WC()->session ) ){
      WC()->session = new $session_class();
      WC()->session->init();
    }

    $session_shipping_event_id = WC()->session->get('shipping_event');
    $shipping_event_id = null;

    if( array_key_exists( 'chosen_shipping_event_id', $_POST ) ) {
      $chosen_shipping_event_id = $_POST['chosen_shipping_event_id'];
      if( isset( $session_shipping_event_id ) ) {
        if( $chosen_shipping_event_id != $session_shipping_event_id ) {
          //TODO: REDIRECT TO CHOOSE SHIPPING EVENT AND REMOVE ALL FILTERS
          $shipping_event_id = $chosen_shipping_event_id;
        } else $shipping_event_id = $session_shipping_event_id;
      } else $shipping_event_id = $chosen_shipping_event_id;
    } else if( isset( $session_shipping_event_id ) ) $shipping_event_id = $session_shipping_event_id;

    if( isset( $shipping_event_id ) )
      $this->shipping_event = get_post($shipping_event_id);

  }

  public function get_session_shipping_event_product_list() {
    if( !isset( $this->shipping_event ) )
      $this->set_session_shipping_event();

    return $this->get_shipping_event_product_list( $this->shipping_event );
  }

  public function get_shipping_event_product_list( $shipping_event ) {
    if ( !isset( $shipping_event ) ) return null;

    $shipping_event_id = $shipping_event->ID;
    $shipping_event_enabled = get_post_meta( $shipping_event_id, 'shipping_event_enabled', true );
    if ( isset( $shipping_event_enabled ) && $shipping_event_enabled == "yes" ) {
      $shipping_event_products = get_post_meta( $shipping_event_id, 'products', true );

      if ( !isset( $shipping_event_products ) ) return null;
      return $shipping_event_products;
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
      $status = $this->get_shipping_event_product_data( $product_id );
      if( !isset( $status ) ) return false;
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
      $status = $this->get_shipping_event_product_data( $product->get_id() );
      if( !isset( $status ) ) return false;
    }
    return $is_purchasable;
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

  public function select_user_shipping_event()
  {
    //TODO: Confirm change if previously set

    $this->set_session_shipping_event();
    if ( isset( $this->shipping_event ) ) {
      $shipping_event_id = $this->shipping_event->ID;
      $user = wp_get_current_user();
      $user_id = is_user_logged_in() ? get_current_user_id() : 0;
      if ( isset( $user ) && isset( $user->ID ) && $user->ID != 0 ) {
        update_user_meta( $user->ID, 'shipping_event', $shipping_event_id);
      } else {
        WC()->session->set('shipping_event', $shipping_event_id);
      }
    }
  }

  public function show_shipping_date_on_header() {
    if( !isset( $this->shipping_event ) ) return;
    $date = ShippingEvent::get_shipping_date_alert( $this->shipping_event );
    if( !isset( $date ) ) return;
    wc_add_notice( __("Seu pedido será entregue no dia " .  $date ), 'notice');
  }


}
