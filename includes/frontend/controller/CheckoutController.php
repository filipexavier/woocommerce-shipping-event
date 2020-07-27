<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Frontend\Controller;

use \WCShippingEvent\Cpt\ShippingEvent;
use \WCShippingEvent\Frontend\Controller\ShopController;

class CheckoutController {

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function init() {
    if( !is_admin() ) {
      add_action( 'woocommerce_before_cart', array( $this, 'show_shipping_date_on_header' ), 1 );
      add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'show_shipping_date_on_header' ), 1 );
      add_filter( 'woocommerce_package_rates', array( $this, 'filter_shipping_methods' ) );
      add_action( 'woocommerce_after_shipping_rate' , array( $this, 'add_local_pickup_details' ), 10, 2 );
    }
  }

  public function show_shipping_date_on_header() {
    if( is_null( ShopController::get_instance()->get_shipping_event() ) ) return;
    $date = ShippingEvent::get_shipping_date_alert( ShopController::get_instance()->get_shipping_event() );
    if( !isset( $date ) ) return;
    wc_add_notice( __("Seu pedido será entregue no dia " .  $date ), 'notice');
  }

  public function filter_shipping_methods( $package_rates ) {
    $selected_shipping_methods = ShopController::get_instance()->get_session_shipping_event_method_list();
    if( !isset( $selected_shipping_methods ) ) {
      //TODO: Abort checkout
      return array();
    }
    $unset_list = array();
    foreach( $package_rates as $available_shipping_method ){
      $method_id = $available_shipping_method->instance_id;
      if( !array_key_exists( $method_id, $selected_shipping_methods ) ) {
        array_push( $unset_list, $available_shipping_method->id );
      }
    }

    foreach( $unset_list as $method_id ) {
      unset($package_rates[$method_id]);
    }
    return $package_rates;
  }

  public function add_local_pickup_details( $shipping_method, $index ) {
    ?>
    <div class="shipping_method_details" hidden>
      <span class="shipping_method_address">
        <?php echo get_post_meta( $shipping_method->instance_id, 'local_pickup_details_address', true ) ?>
      </span>
    </div>
    <?php
  }

}
