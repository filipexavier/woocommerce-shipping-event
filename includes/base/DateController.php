<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Base;

use DateTime;

class DateController {


  public static function format_date( $date ) {
    if( is_string( $date ) ) $date = date_create( $date, wp_timezone() );
    return $date->format( 'd/m/Y' ) . " (" . self::day_of_week( $date ) . ")";
  }

  public static function str_to_date( $date ) {
    return date_create( $date, wp_timezone() );
  }

  public static function day_of_week( $date ) {
    return ucfirst( __( $date->format( 'l' ) ) );
  }

  public static function now() {
    return new DateTime( 'now', wp_timezone() );
  }

  public static function date_to_str( $date ) {
    if( is_null( $date ) ) return null;
    return $date->format('Y-m-d');
  }

  public static function add_days( $date, $days ) {
    return self::str_to_date( self::date_to_str( $date ) . ' + ' . $days . ' day' );
  }

  public static function get_post_date( $post_id, $post_meta_key ) {
    //Get date in UTC
    $strdate = get_post_meta( $post_id, $post_meta_key, true );
    if( empty( $strdate ) ) return null;
    //Convert to local time
    return date_create( get_date_from_gmt( $strdate ), wp_timezone() );
  }


}
