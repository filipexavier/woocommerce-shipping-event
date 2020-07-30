<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Base;

use DateTime;

class DateController {


  public static function format_date( $date ) {
    return $date->format( 'd/m/Y' ) . " (" . ucfirst( __( $date->format( 'l' ) ) ) . ")";
  }

  public static function now() {
    return new DateTime( 'now', wp_timezone() );
  }

  public static function date_to_str( $date ) {
    return $date->format('Y-m-d');
  }

  public static function get_post_date( $post_id, $post_meta_key ) {
    //Get date in UTC
    $strdate = get_post_meta( $post_id, $post_meta_key, true );
    if( empty( $strdate ) ) return null;
    //Convert to local time
    return date_create( get_date_from_gmt( $strdate ), wp_timezone() );
  }
}
