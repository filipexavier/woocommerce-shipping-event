<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Cpt;

use DateTime;
use WCShippingEvent\Base\DateController;
use WCShippingEvent\Base\ShippingEventController;

class ShippingEvent {

  private $id;

  private $title;

  private $enabled;

  private $shipping_date;

  private $begin_order_date;

  private $end_order_date;

  private $shipping_methods;

  private static $meta_keys = array (
    'begin_order_date' => 'shipping_event_start_orders_date',
    'shipping_date' => 'shipping_event_date',
    'end_order_date' => 'shipping_event_end_orders_date',
    'enabled' => 'shipping_event_enabled',
    'shipping_methods' => 'selected_shipping_methods'
  );

  public function __construct( $shipping_event_id ) {
    $shipping_event_post = ShippingEventController::get_instance()->get_post_by_id( $shipping_event_id );
    $this->id = $shipping_event_id;
    $this->enabled = ShippingEventController::get_instance()->get_post_enabled( $shipping_event_id, ShippingEvent::get_meta_key( 'enabled' ) );
    $this->shipping_date = DateController::get_post_date( $shipping_event_id, ShippingEvent::get_meta_key( 'shipping_date' ) );
    $this->title = $shipping_event_post->post_title;
    $this->begin_order_date = DateController::get_post_date( $shipping_event_id, ShippingEvent::get_meta_key( 'begin_order_date' ) );
    $this->end_order_date = DateController::get_post_date( $shipping_event_id, ShippingEvent::get_meta_key( 'end_order_date' ) );
  }

  /**
   * Returns true if the shipping event is still not open for orders; false if is already opened or closed
   * @return boolean
  */
  public function open_order_pending() {
    if( !$this->get_enabled() ) return false;
    if( $this->get_shipping_date() < DateController::now() ) return false;
    if( $this->get_end_order_date() < DateController::now() ) return false;
    if( $this->get_begin_order_date() <= DateController::now() ) return false;

    return true;
  }

  public function orders_enabled() {
    if( !$this->get_enabled() ) return false;
    if( $this->get_shipping_date() < DateController::now() ) return false;
    if( $this->get_begin_order_date() > DateController::now() ) return false;
    if( $this->get_end_order_date() < DateController::now() ) return false;

    return true;
  }


  public function get_id() {
    return $this->id;
  }

  public function get_enabled() {
    return $this->enabled;
  }

  public function get_title() {
    return $this->title;
  }

  public function get_shipping_date() {
    return $this->shipping_date;
  }

  public function get_begin_order_date() {
    return $this->begin_order_date;
  }

  public function get_end_order_date() {
    return $this->end_order_date;
  }

  public static function get_meta_keys() {
    return $self::meta_keys;
  }

  public static function get_meta_key( $key ) {
    if( !array_key_exists( $key, self::$meta_keys ) ) return '';
    return self::$meta_keys[ $key ];
  }

}
