<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Base;

use DateTime;
use WCShippingEvent\Base\DateController;
use WCShippingEvent\Cpt\ShippingEvent;

class ShippingEventController {

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * @param int post_id of the post type shipping event
   * @return WP_Post
  */
  public function get_post_by_id( $shipping_event_id ) {
    if( is_a( $shipping_event_id, 'WP_Post' ) ) return $shipping_event_id;
    $shipping_event_post = get_post( $shipping_event_id );
    if( $shipping_event_post->post_type == 'shipping_event' ) return $shipping_event_post;
    return null;
  }

  /**
   * @param 'Accepts post_id, post object or shipping_event object'
   * @return ShippingEvent
  */
  public function get_shipping_event( $shipping_event_id ) {
    if( is_a( $shipping_event_id, 'ShippingEvent' ) ) return $shipping_event_id;
    $shipping_event_post = null;
    if( is_a( $shipping_event_id, 'WP_Post' ) ) {
      $shipping_event_post = $shipping_event_id;
    } else {
      $shipping_event_post = $this->get_post_by_id($shipping_event_id);
    }
    if( $shipping_event_post && $shipping_event_post->post_type == 'shipping_event' ) {
      try {
        return new ShippingEvent( $shipping_event_post );
      } catch ( Exception $e ) {
        return null;
      }
    }
    return null;
  }

  public function is_post_meta_enabled( $shipping_event_id, $meta_key ) {
    $shipping_event_enabled = get_post_meta( $shipping_event_id, $meta_key, true );
    if ( !empty( $shipping_event_enabled ) && $shipping_event_enabled == "yes" ) return true;
    return false;
  }

  public function get_shipping_event_shipping_methods_list( $shipping_event_id ) {
    if ( empty( $shipping_event_id ) ) return null;

    $shipping_event_methods = get_post_meta( $shipping_event_id, 'selected_shipping_methods', true );
    if ( !isset( $shipping_event_methods ) ) return null;
    return $shipping_event_methods;
  }

  public function get_shipping_event_product_list( $shipping_event_id ) {
    if ( empty( $shipping_event_id ) ) return null;

    $shipping_event_products = get_post_meta( $shipping_event_id, 'products', true );
    if ( !isset( $shipping_event_products ) ) return null;
    return $shipping_event_products;
  }

  public function is_product_enabled( $product_data ) {
    return ( array_key_exists( 'enabled', $product_data ) && $product_data['enabled'] == "yes" );
  }

  public function safe_data_access( $data_array, $data_key ) {
    if ( isset( $data_array )
      && array_key_exists( $data_key, $data_array )
      && isset( $data_array[$data_key] )
      && $data_array[$data_key] != '' )
      return $data_array[$data_key];

    return null;
  }

}
