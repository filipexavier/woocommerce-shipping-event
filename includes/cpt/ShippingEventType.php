<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Cpt;

use WCShippingEvent\Base\DateController;

class ShippingEventType {

  public const AVAILABLE_ARGS = array(
    '#TITLE#',
    '#EVENT_DATE#',
    '#BEGIN_ORDERS_DATE#',
    '#END_ORDERS_DATE#',
    '#SELECT_EVENT#'
  );

  public const DATE_ARGS = array(
    '[DAY]',
    '[MONTH]',
    '[YEAR]',
    '[YY]',
    '[DAY_OF_WEEK]',
    '[NAME_OF_MONTH]'
  );

  public const SHIPPING_ID_CODE = 'shipping_event_id_';

  public static function subst_args( $shipping_event, $original_text ) {
    $new_str_keys = array(
      $shipping_event->get_title(),
      self::customize_date_format( $shipping_event->get_shipping_date(), 'wcse_event_type_event_date_format' ),
      self::customize_date_format( $shipping_event->get_begin_order_date(), 'wcse_event_type_open_orders_format' ),
      self::customize_date_format( $shipping_event->get_end_order_date(), 'wcse_event_type_close_orders_format' ),
      self::SHIPPING_ID_CODE . $shipping_event->get_id()
    );

    return str_replace( self::AVAILABLE_ARGS, $new_str_keys, $original_text );
  }

  public static function customize_date_format( $date, $option_key ) {
    $format = get_option( $option_key );
    if( empty( $format ) ) return date_i18n( wc_date_format(), $date->getTimestamp() );
    return self::apply_args_date( $date, $format );
  }

  public static function apply_args_date( $date, $user_format ) {
    $new_str_keys = array(
      $date->format('d'),
      $date->format('m'),
      $date->format('Y'),
      $date->format('y'),
      DateController::day_of_week( $date ),
      __( $date->format('F') )
    );

    return str_replace( self::DATE_ARGS, $new_str_keys, $user_format );
  }

}
