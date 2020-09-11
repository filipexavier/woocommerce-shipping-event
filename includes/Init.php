<?php
/**
* @package WoocommerceShippingEvent
*/

namespace WCShippingEvent;

use \WCShippingEvent\Base\BaseController;
use \WCShippingEvent\Base\ShippingEventController;
use \WCShippingEvent\Base\SettingsController;
use \WCShippingEvent\Cpt\CustomPostTypeController;
use \WCShippingEvent\Cpt\ShippingEventType;
use \WCShippingEvent\Meta\MetaBoxesController;
use \WCShippingEvent\Frontend\Controller\ShopController;
use \WCShippingEvent\Frontend\Controller\ShortcodeController;
use \WCShippingEvent\Frontend\Controller\CheckoutController;

final class Init extends BaseController {

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function register_services() {
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
      add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
      add_action( 'init', array( $this, 'add_localize_files' ) );
      CustomPostTypeController::get_instance()->init();
      ShopController::get_instance()->init();
      ShortcodeController::get_instance()->init();
      CheckoutController::get_instance()->init();
      SettingsController::get_instance()->init();

      if( is_admin() ) {
        MetaBoxesController::get_instance()->init();
      }
  }

  function add_localize_files() {
    load_plugin_textdomain( 'woocommerce-shipping-event', FALSE, 'woocommerce-shipping-event/languages' );
}


  public function setup() {
    $this->disable_hold_stock();
  }

  public function enqueue_admin() {
    wp_enqueue_style( 'wcse_admin', $this->plugin_url . '/assets/css/wcse_admin.css', array(), rand(111,9999), 'all' );
    wp_enqueue_script( 'wcse_admin', $this->plugin_url . '/assets/js/wcse_admin.js', array( 'jquery' ), rand(111,9999), 'all' );
  }

  public function enqueue() {
    if( is_checkout() || is_cart() ) {
      wp_enqueue_style( 'wcse_shipping_methods', $this->plugin_url . '/assets/css/wcse_shipping_methods.css', array(), rand(111,9999), 'all' );
      wp_enqueue_script( 'wcse_shipping_methods', $this->plugin_url . '/assets/js/wcse_shipping_methods.js', array( 'jquery' ), rand(111,9999), 'all' );
      $data = array(
        'blocked_shipping_methods' => ShippingEventController::get_instance()->shipping_methods_blocked(),
        'blocked_shipping_alert' => __( 'Available for purchases from', 'woocommerce-shipping-event' ),
        'currency_format' => get_woocommerce_currency(),
        'locale' => get_user_locale()
      );
      wp_localize_script( 'wcse_shipping_methods', 'php_vars', $data );
    }

    if( is_checkout() || is_cart() || is_woocommerce() ) {
      wp_enqueue_style( 'wcse_popup', $this->plugin_url . '/assets/css/wcse_popup.css', array(), rand(111,9999), 'all' );
    }

    if( SettingsController::get_instance()->is_choose_event() ) {
      wp_enqueue_style( 'wcse_popup', $this->plugin_url . '/assets/css/wcse_popup.css', array(), rand(111,9999), 'all' );

      wp_enqueue_script( 'wcse_choose_shipping_event', $this->plugin_url . '/assets/js/wcse_choose_shipping_event.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog' ), rand(111,9999), 'all' );
      $data = array(
          'chosen_event_param' => ShopController::CHOSEN_ARG_CODE,
          'shipping_id_code' => ShippingEventType::SHIPPING_ID_CODE,
          'orders_closed_code' => ShippingEventType::ORDERS_CLOSED_CODE,
          'button_target' => SettingsController::get_instance()->get_shipping_event_page_url(),
          'orders_closed_button_target' => SettingsController::get_instance()->get_unavailable_target_page_url(),
          'orders_closed_button_label' => get_option( 'wcse_shortcode_label_button_unavailable' )
      );
      wp_localize_script( 'wcse_choose_shipping_event', 'php_vars', $data );
    }
  }

  public function disable_hold_stock() {
    $hold_stock = get_option( 'woocommerce_hold_stock_minutes' );
    if ( $hold_stock !== '' ) {
      update_option( 'woocommerce_hold_stock_minutes', '' );
    }
  }

}
