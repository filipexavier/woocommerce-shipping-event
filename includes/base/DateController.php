<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Base;

use DateTime;

class DateController {

  public static function set_locale() {
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Sao_Paulo');
  }

  public static function get_date_from_string( $strdate ) {
    return new DateTime( $strdate );
  }

  public static function format_week( $strdate ) {
    DateController::set_locale();
    $date = DateController::get_date_from_string( $strdate );
    return strftime('%d - %m (%A)', $date->getTimestamp() );
  }

  public static function now() {
    DateController::set_locale();
    return new DateTime('now');
  }
}
