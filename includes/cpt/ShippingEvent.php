<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Cpt;

use DateTime;
use WCShippingEvent\Base\DateController;
use WCShippingEvent\Base\ShippingEventController;
use WCShippingEvent\Base\SettingsController;

class ShippingEvent {

  private $id;

  private $title;

  private $enabled;

  /** @var WP_Post of ShippingEventType */
  private $event_type;

  private $shipping_date;

  private $begin_order_date;

  private $end_order_date;

  private $disable_backorder;

  private $shipping_methods;

  private $delivery_time_window;

  private $products;

  private $max_order_num;

  private $previous_shipping_event;

  private const META_KEYS = array (
    'begin_order_date' => 'shipping_event_start_orders_date',
    'shipping_date' => 'shipping_event_date',
    'end_order_date' => 'shipping_event_end_orders_date',
    'disable_backorder' => 'shipping_event_disable_backorder',
    'enabled' => 'shipping_event_enabled',
    'shipping_methods' => 'selected_shipping_methods',
    'products' => 'products',
    'event_type' => 'shipping_event_type',
    'delivery_time_window' => 'shipping_event_delivery_time_window',
    'max_order_num' => 'shipping_event_max_order_num',
    'previous_shipping_event' => 'shipping_event_previous_shipping_event'
  );

  private static $product_keys = array (
    'enabled' => 'enabled',
    'stock' => 'stock'
  );

  private static $shipping_method_keys = array (
    'enabled' => 'enabled',
    'min_order_value' => 'min_order_value'
  );

  public function __construct( $post ) {
    $shipping_event_post = null;
    if( is_a( $post, 'WP_Post' ) ) {
      $shipping_event_post = $post;
    } else {//in case argument is a post id
      $shipping_event_post = ShippingEventController::get_instance()->get_post_by_id( $post );
    }

    $shipping_event_id = $shipping_event_post->ID;
    $this->id = $shipping_event_id;
    $this->enabled = ShippingEventController::get_instance()->is_post_meta_enabled( $shipping_event_id, ShippingEvent::get_meta_key( 'enabled' ) );
    $this->event_type = get_post( get_post_meta( $shipping_event_id, ShippingEvent::get_meta_key( 'event_type' ), true ) );
    $this->disable_backorder = ShippingEventController::get_instance()->is_post_meta_enabled( $shipping_event_id, ShippingEvent::get_meta_key( 'disable_backorder' ) );
    $this->shipping_date = DateController::get_post_date( $shipping_event_id, ShippingEvent::get_meta_key( 'shipping_date' ) );
    $this->title = $shipping_event_post->post_title;
    $this->begin_order_date = DateController::get_post_date( $shipping_event_id, ShippingEvent::get_meta_key( 'begin_order_date' ) );
    $this->end_order_date = DateController::get_post_date( $shipping_event_id, ShippingEvent::get_meta_key( 'end_order_date' ) );
    $this->products = ShippingEventController::get_instance()->get_shipping_event_product_list( $shipping_event_id );
    $this->shipping_methods = ShippingEventController::get_instance()->get_shipping_event_shipping_methods_list( $shipping_event_id );
    $this->delivery_time_window = get_post_meta( $shipping_event_id, ShippingEvent::get_meta_key( 'delivery_time_window' ), true );
    $this->max_order_num = get_post_meta( $shipping_event_id, ShippingEvent::get_meta_key( 'max_order_num' ), true );
    $this->previous_shipping_event_id = ShippingEventController::get_instance()->safe_post_meta_access(
      $shipping_event_id,
      ShippingEvent::get_meta_key( 'previous_shipping_event' )
    );
    $this->previous_shipping_event = ShippingEventController::get_instance()->get_shipping_event(
      $this->previous_shipping_event_id
    );
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

  /**
   * Returns true if the shipping event has closed for orders; false if is opened or before order period
   * @return boolean
  */
  public function after_order_period() {
    if( !$this->get_enabled() ) return false;
    if( $this->get_end_order_date() < DateController::now() ) return true;

    return false;
  }

  /**
   * Returns true if the shipping event should be shown in the shortcode list; Show orders ended but as closed
   * @return boolean
  */
  public function show_to_client() {
    if( !$this->get_enabled() ) return false;
    if( $this->get_shipping_date() < DateController::now() ) return false;

    return true;
  }

  public function orders_enabled() {
    if( !$this->get_enabled() ) return false;
    if( $this->get_shipping_date() < DateController::now() ) return false;
    if( $this->get_begin_order_date() > DateController::now() ) return false;
    if( DateController::add_days( $this->get_end_order_date(), 1 ) < DateController::now() ) return false;

    return true;
  }


  public function get_id() {
    return $this->id;
  }

  public function get_enabled() {
    return $this->enabled;
  }

  public function get_event_type() {
    return $this->event_type;
  }

  public function get_event_type_id() {
    if( is_null( $this->event_type ) ) return 0;
    return $this->event_type->ID;
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

  public function get_disable_backorder() {
    return $this->disable_backorder;
  }

  public function set_disable_backorder( $disable_backorder ) {
    return $this->disable_backorder = $disable_backorder ? true : false;
  }

  public function get_products() {
    return $this->products;
  }

  public function save_products() {
    return update_post_meta( $this->id, ShippingEvent::get_meta_key( 'products' ), $this->products );
  }

  public function set_products( $products, $save ) {
    $this->products = $products;
    if( !$save ) return true;
    return $this->save_products();
  }

  public function get_shipping_methods() {
    return $this->shipping_methods;
  }

  public function get_delivery_time_window() {
    return $this->delivery_time_window;
  }

  public function get_max_order_num() {
    return $this->max_order_num;
  }

  public function get_previous_shipping_event() {
    return $this->previous_shipping_event;
  }

  public function get_previous_shipping_event_id() {
    return $this->previous_shipping_event_id;
  }

  public function get_shipping_method_data( $method_id ) {
    return ShippingEventController::get_instance()->safe_data_access(
        $this->shipping_methods, $method_id );
  }

  public function is_shipping_method_selected( $method_id ) {
    return array_key_exists( $method_id, $this->shipping_methods );
  }

  public function get_shipping_method_min_order_value( $method_id ) {
    return ShippingEventController::get_instance()->safe_data_access(
        $this->get_shipping_method_data( $method_id ), $this->get_shipping_method_key( 'min_order_value' ) );
  }

  public static function get_meta_keys() {
    return $self::META_KEYS;
  }

  public static function get_meta_key( $key ) {
    if( !array_key_exists( $key, self::META_KEYS ) ) return '';
    return self::META_KEYS[ $key ];
  }

  public static function get_product_key( $key ) {
    if( !array_key_exists( $key, self::$product_keys ) ) return '';
    return self::$product_keys[ $key ];
  }

  public static function get_shipping_method_key( $key ) {
    if( !array_key_exists( $key, self::$shipping_method_keys ) ) return '';
    return self::$shipping_method_keys[ $key ];
  }

  public function is_product_enabled( $product_id ) {
    return !empty( $this->get_product_data( $product_id ) );
  }

  public function get_product_data( $product_id ) {
    if ( !isset( $this->products ) ) return null;
    if ( !array_key_exists( $product_id, $this->products ) ) return null;
    $product_data = $this->products[$product_id];

    if ( !ShippingEventController::get_instance()->is_product_enabled( $product_data ) ) return null;
    return $product_data;
  }

  /**
   * @return bool if shipping_event is set to override stock to this product
  */
  public function get_product_manage_stock( $product_id ) {
    return !is_null( $this->get_product_stock_quantity( $product_id ) );
  }

  /**
   * @return int Stock quantity for this product, if product is enabled in this shipping Event
   * Returns null if product is not enabled or stock quantity is not set
  */
  public function get_product_stock_quantity( $product_id ) {
    $product_data = $this->get_product_data( $product_id ); //Already considers is enabled
    return ShippingEventController::get_instance()->safe_data_access( $product_data, $this->get_product_key( 'stock' ) );
  }

  /**
   * @return string stock status ['onbackorder', 'instock', 'outofstock']
   * Returns outofstock if product is not enabled,stock quantity is not set or 0. And returns onbackorder if p
  */
  public function get_product_stock_status( $product_id ) {
    $product = wc_get_product( $product_id );
    $stock = $this->get_product_stock_quantity( $product_id );//Already considers is enabled
    if( is_null( $stock ) || !is_numeric( $stock )
      || ( $product->get_stock_status() == 'onbackorder' && $stock <= 0 && !$this->get_disable_backorder() ) )
      return $product->get_stock_status();

    return $stock > 0 ? 'instock' : 'outofstock';
  }

  /**
   * @return bool if product stock quantity is > 0
   * Returns null if product is not enabled or stock quantity is not set
  */
  public function is_product_in_stock( $product_id ) {
    $stock = $this->get_product_stock_quantity( $product_id );//Already considers is enabled
    if( is_null( $stock ) || !is_numeric( $stock ) ) return null;
    return $stock > 0;
  }

  public function update_product_data( $product_id, $meta_key, $meta_value ) {
    if( !array_key_exists( $product_id, $this->products ) ) return false;
    $this->products[$product_id][$meta_key] = $meta_value;
    return true;
  }

  public function update_product_stock( $product, $qty, $action, $save ) {
    if( $action != 'reduce' && $action != 'increase' ) return false;

    $multiplier = 1;
    if( $action == 'reduce')  $multiplier = -1;

    $current_amount = $this->get_product_stock_quantity( $product->get_id() );
    $new_qty = $current_amount + ( $multiplier * $qty );

    if( !$this->update_product_data( $product->get_id(), $this->get_product_key( 'stock' ), $new_qty ) ) return false;
    if( $save && !$this->save_products() ) return false;
    return $new_qty;
  }

  public function get_orders_num() {
    return ShippingEventController::get_instance()->get_orders_num( $this );
  }

  public function get_orders_limit_left() {
    return is_null( $this->get_max_order_num() ) ? 0 : $this->get_max_order_num() - $this->get_orders_num();
  }

  public function max_orders_reached() {
    return SettingsController::get_instance()->orders_limit_control_enabled() && $this->get_max_order_num() <= $this->get_orders_num();
  }

  public function near_limit_num() {
    return !$this->max_orders_reached() && $this->get_orders_limit_left() <= SettingsController::get_instance()->get_near_orders_limit_num();
  }
}
