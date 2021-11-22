<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Base;

use DateTime;
use WCShippingEvent\Base\DateController;
use WCShippingEvent\Cpt\ShippingEvent;
use WCShippingEvent\Frontend\Controller\ShopController;

class ShippingEventController {

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function is_a_shipping_event( $obj ) {
    return is_a( $obj, 'WCShippingEvent\Cpt\ShippingEvent' );
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
    if( is_null( $shipping_event_id ) ) return null;
    if( $this->is_a_shipping_event( $shipping_event_id ) ) return $shipping_event_id;
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
    return array_filter( $shipping_event_methods, array( $this, 'selected_method_filter' ) );
  }

  public function get_shipping_event_product_list( $shipping_event_id ) {
    if ( empty( $shipping_event_id ) ) return null;

    $shipping_event_products = get_post_meta( $shipping_event_id, 'products', true );
    if ( !isset( $shipping_event_products ) ) return null;
    return $shipping_event_products;
  }

  public function is_product_enabled( $product_data ) {
    return $this->is_item_enabled( $product_data );
  }

  public function is_item_enabled( $data ) {
    return ( array_key_exists( 'enabled', $data ) && $data['enabled'] == "yes" );
  }

  public function safe_data_access( $data_array, $data_key ) {
    if ( isset( $data_array )
      && array_key_exists( $data_key, $data_array )
      && isset( $data_array[$data_key] )
      && $data_array[$data_key] != '' )
      return $data_array[$data_key];

    return null;
  }

  public function safe_post_meta_access( $id, $data_key ) {
    if ( empty( $id ) ) return null;
    $meta_value = get_post_meta( $id, $data_key, true );
    if ( empty( $meta_value ) ) return null;
    return $meta_value;
  }

  public function shipping_methods_blocked() {
    $order_value = WC()->cart->subtotal;
    $shipping_event = ShopController::get_instance()->get_shipping_event();
    if( !$shipping_event ) return '';

    return array_filter( $shipping_event->get_shipping_methods(),
      function( $method ) use( $order_value ) {
        $min_value =
          $this->safe_data_access(
            $method, ShippingEvent::get_shipping_method_key( 'min_order_value' ) );
        if( !$min_value ) return false;
        return $order_value < $min_value;
      }
    );
  }

  public function posts_to_shipping_events( $posts ) {
    $shipping_event_list = array();
    foreach( $posts as $post ) {
      array_push( $shipping_event_list, $this->get_shipping_event( $post ) );
    }
    return $shipping_event_list;
  }

  /**
   * @param 'Array as key => value ( ShippingEvent or WP_Post )'
   * @return Array(ShippingEvent)
  */
  public function order_by_date( $posts ) {
    $shipping_event_list = $this-> posts_to_shipping_events( $posts );
    usort( $shipping_event_list, array( $this, 'event_date_comparator') );
    return $shipping_event_list;
  }

  public function event_date_comparator( $a, $b ) {
    $date_a = null;
    $date_b = null;
    if ( is_a( $a, 'WP_Post' ) ) {
      $date_a = DateController::get_post_date( $a->ID, ShippingEvent::get_meta_key( 'shipping_date' ) );
      $date_b = DateController::get_post_date( $b->ID, ShippingEvent::get_meta_key( 'shipping_date' ) );
    } else if( $this->is_a_shipping_event( $a ) ) {
      $date_a = $a->get_shipping_date();
      $orders_opened_a = $a->show_to_client();
      $date_b = $b->get_shipping_date();
      $orders_opened_b = $b->show_to_client();
    } else return 0;

    if( $orders_opened_a == $orders_opened_b ) return $this->basic_comparator( $date_a, $date_b );
    return $this->basic_comparator( $orders_opened_b, $orders_opened_a ); //invert because true > false, but opened shoud be above closed
  }

  public function basic_comparator( $a, $b ) {
    if( $a == $b ) return 0;
    return ( $a < $b ) ? -1 : 1;
  }

  public function shipping_method_comparator( $method_a, $method_b ) {
    $type_a = $method_a->method_title;
    $type_b = $method_b->method_title;
    $title_a = $method_a->title;
    $title_b = $method_b->title;
    if( $type_a == $type_b ) return $this->basic_comparator( $title_a, $title_b );
    return $this->basic_comparator( $type_a, $type_b );
  }

  public function shipping_zone_comparator( $zone_a, $zone_b ) {
    $name_a = $zone_a['zone_name'];
    $name_b = $zone_b['zone_name'];
    return $this->basic_comparator( $name_a, $name_b );
  }

  public function selected_method_filter( $method ) {
    return $this->is_item_enabled( $method );
  }

  public function get_orders_num( $shipping_event ) {
    global  $woocommerce , $wpdb;
    $ordersnum = $wpdb->get_results(
      "SELECT COUNT(distinct cpf.meta_value) as num
      FROM wp_posts as orders
      INNER JOIN wp_postmeta AS shipping_events
        ON shipping_events.meta_key = 'shipping_event'
        AND orders.ID = shipping_events.post_id
      INNER JOIN wp_posts AS shipping_event
        ON shipping_event.ID = shipping_events.meta_value
      LEFT JOIN wp_postmeta AS cpf
       	ON cpf.meta_key = '_billing_cpf' and orders.ID = cpf.post_id
      WHERE orders.post_type = 'shop_order'
        AND orders.post_status NOT IN ( 'wc-cancelled', 'wc-completed', 'wc-failed', 'wc-refunded' )
        AND shipping_event.ID = " . $shipping_event->get_id()
    );
    return $ordersnum[0]->num;
  }

  public function single_opened_event() {
    $shipping_event_list = $this->order_by_date( get_posts( array( 'post_type' => 'shipping_event' ) ) );
    foreach( $shipping_event_list as $shipping_event ) {
      //var_dump( $shipping_event->get_shipping_date());
      if( !$shipping_event->orders_enabled() ) continue;
      //echo "achei";
      return $shipping_event;
    }
    return null;
  }

}
