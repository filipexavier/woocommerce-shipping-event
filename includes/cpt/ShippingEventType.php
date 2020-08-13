<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Cpt;

use WCShippingEvent\Base\DateController;

class ShippingEventType {

  public const EVENT_TAGS = array(
    '#TITLE#' => 'Use this tag anywhere and the user will see the title of each Shipping Event',
    '#EVENT_DATE#' => 'Use this tag anywhere and the user will see the delivery/pickup date of each Shipping Event',
    '#BEGIN_ORDERS_DATE#' => 'Use this tag anywhere and the user will see when the orders are opened for each Shipping Event',
    '#END_ORDERS_DATE#' => 'Use this tag anywhere and the user will see the last day before orders are closed for each Shipping Event',
    '#SELECT_EVENT#' => 'Put this tag in the link of a button where you want the user to select the Shipping Event and access the Store'
  );

  public const DATE_TAGS = array(
    '[DAY]',
    '[MONTH]',
    '[YEAR]',
    '[YY]',
    '[DAY_OF_WEEK]',
    '[NAME_OF_MONTH]'
  );

  public const SHIPPING_ID_CODE = 'shipping_event_id_';
  public const ORDERS_CLOSED_CODE = 'shipping_event_orders_closed';

  public static function get_event_tags() {
    return array_keys( self::EVENT_TAGS );
  }

  public static function translate_event_tags( $shipping_event, $original_text ) {
    $new_str_keys = array(
      $shipping_event->get_title(),
      self::customize_date_format( $shipping_event->get_shipping_date(), 'wcse_event_type_event_date_format' ),
      self::customize_date_format( $shipping_event->get_begin_order_date(), 'wcse_event_type_open_orders_format' ),
      self::customize_date_format( $shipping_event->get_end_order_date(), 'wcse_event_type_close_orders_format' ),
      self::handle_select_event_button_behavior( $shipping_event )
    );

    return str_replace( self::get_event_tags(), $new_str_keys, $original_text );
  }

  public static function handle_select_event_button_behavior( $shipping_event ) {
    if( $shipping_event->open_order_pending() ) return self::ORDERS_CLOSED_CODE;
    return self::SHIPPING_ID_CODE . $shipping_event->get_id();
  }

  public static function customize_date_format( $date, $option_key ) {
    $format = get_option( $option_key );
    if( empty( $format ) ) return date_i18n( wc_date_format(), $date->getTimestamp() );
    return self::translate_date_tags( $date, $format );
  }

  public static function translate_date_tags( $date, $user_format ) {
    $new_str_keys = array(
      $date->format('d'),
      $date->format('m'),
      $date->format('Y'),
      $date->format('y'),
      DateController::day_of_week( $date ),
      __( $date->format('F') )
    );

    return str_replace( self::DATE_TAGS, $new_str_keys, $user_format );
  }

}
