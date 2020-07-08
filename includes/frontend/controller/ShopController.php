<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Frontend\Controller;

class ShopController {

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function init() {
    if( !is_admin() ) {
      add_action( 'woocommerce_before_main_content', array( $this, 'select_user_shipping_event' ) );
      add_filter('woocommerce_product_is_visible', array( $this, 'hide_products' ), 10, 2 );
      add_filter( 'woocommerce_product_is_in_stock', array( $this, 'override_is_in_stock' ), 10, 2 );
      add_filter( 'woocommerce_product_get_stock_quantity' , array( $this, 'override_stock_quantity' ), 10, 2 );
      add_filter( 'woocommerce_product_get_manage_stock' , array( $this, 'override_manage_stock' ), 10, 2 );
    }
  }

  public function get_session_shipping_event_id() {
    if( !isset( WC()->session ) ){
      WC()->session = new $session_class();
      WC()->session->init();
    }

    $shipping_event_id = WC()->session->get('shipping_event');
    $shipping_event_id = 796;
    return $shipping_event_id;
  }

  public function get_session_shipping_event_product_list() {
    return $this->get_shipping_event_product_list( $this->get_session_shipping_event_id() );
  }

  public function get_shipping_event_product_list( $shipping_event_id ) {
    $shipping_event_id = $this->get_session_shipping_event_id();
    if ( !isset( $shipping_event_id ) ) return null;

    $shipping_event = get_post($shipping_event_id);
    if ( !isset( $shipping_event ) ) return null;

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
  public function hide_products($visible, $product_id) {
    if($visible) {

      //Shipping event chosen by the user
      $shipping_event_id = $this->get_session_shipping_event_id();

      if ( !isset( $shipping_event_id ) ) return $visible;
      //TODO: REDIRECT TO CHOOSE SHIPPING EVENT

      $shipping_event = get_post($shipping_event_id);
      if ( !isset( $shipping_event ) ) return $visible;

      $shipping_event_enabled = get_post_meta( $shipping_event_id, 'shipping_event_enabled', true );
      if ( isset( $shipping_event_enabled ) && $shipping_event_enabled == "yes" ) {
        $shipping_event_products = get_post_meta( $shipping_event_id, 'products', true );

        if ( !array_key_exists( $product_id, $shipping_event_products ) ) return false;

        $product_data = $shipping_event_products[$product_id];
        if ( !array_key_exists( 'enabled', $product_data ) || $product_data['enabled'] != "yes" ) return false;

      } else {
        //TODO: Show message THAT TO GO BACK TO CHOOSE SHIPPING EVENT
        return false;
      }
    }
    return $visible;
  }

  public function override_is_in_stock( $stock_status, $product ) {
    $status = $this->get_shipping_event_product_stock_status( $product->get_id() );
    if( isset( $status ) ) return $status;
    return $stock_status;
  }

  public function override_stock_quantity( $stock_num, $product ) {
    $stock = $this->get_shipping_event_product_stock_quantity( $product->get_id() );
    if( isset( $stock ) ) return $stock;
    return $stock_num;
  }

  public function override_manage_stock( $manage_stock, $product ) {
    $stock = $this->get_shipping_event_product_stock( $product_id );
    if( isset( $stock) ) $manage_stock = true;
    return $manage_stock;
  }

  public function select_user_shipping_event()
  {
    $user = wp_get_current_user();
    $user_id = is_user_logged_in() ? get_current_user_id() : 0;
    if ( isset( $user ) && isset( $user->ID ) && $user->ID != 0 ) {
      update_user_meta( $user->ID, 'shipping_event', '796');
    } else {
      WC()->session->set('shipping_event', '796');
    }

  }


}
