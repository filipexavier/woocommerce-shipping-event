<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Cpt;

use WCShippingEvent\Base\DateController;

class ShippingEventType {

  public const AVAILABLE_ARGS = array(
    '[TITLE]',
    '[EVENT_DATE]',
    '[BEGIN_ORDERS_DATE]',
    '[END_ORDERS_DATE]',
    '[SELECT_EVENT]'
  );

  public const SHIPPING_ID_CODE = 'shipping_event_id_';

  public static function subst_args( $shipping_event, $original_text ) {
    $new_str_keys = array(
      $shipping_event->get_title(),
      DateController::format_date( $shipping_event->get_shipping_date() ),
      DateController::format_date( $shipping_event->get_begin_order_date() ),
      DateController::format_date( $shipping_event->get_end_order_date() ),
      self::SHIPPING_ID_CODE . $shipping_event->get_id()
    );

    return str_replace( self::AVAILABLE_ARGS, $new_str_keys, $original_text );
  }

}
