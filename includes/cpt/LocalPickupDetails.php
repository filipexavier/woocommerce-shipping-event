<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Cpt;

class LocalPickupDetails {

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public static function get_selected_method( $local_pickup_details ) {
    if( is_null( $local_pickup_details ) ) return null;
    return get_post_meta( $local_pickup_details->ID, 'local_pickup_details_local_pickup', true );
  }
}
