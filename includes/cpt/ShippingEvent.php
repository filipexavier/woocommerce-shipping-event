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

  /** @var WP_Post of ShippingEventType */
  private $event_type;

  private $shipping_date;

  private $begin_order_date;

  private $end_order_date;

  private $disable_backorder;

  private $shipping_methods;

  private $products;

  private const META_KEYS = array (
    'begin_order_date' => 'shipping_event_start_orders_date',
    'shipping_date' => 'shipping_event_date',
    'end_order_date' => 'shipping_event_end_orders_date',
    'disable_backorder' => 'shipping_event_disable_backorder',
    'enabled' => 'shipping_event_enabled',
    'shipping_methods' => 'selected_shipping_methods',
    'products' => 'products',
    'event_type' => 'shipping_event_type'
  );

  private static $product_keys = array (
    'enabled' => 'enabled',
    'stock' => 'stock'
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


}
