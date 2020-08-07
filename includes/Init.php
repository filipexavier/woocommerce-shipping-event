<?php
/**
* @package WoocommerceShippingEvent
*/

namespace WCShippingEvent;

use \WCShippingEvent\Base\BaseController;
use \WCShippingEvent\Base\ShippingEventController;
use \WCShippingEvent\Cpt\LocalPickupDetails;
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
      add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
      ShippingEventController::get_instance()->init();
      LocalPickupDetails::get_instance()->init();
      MetaBoxesController::get_instance()->init();
      ShopController::get_instance()->init();
      ShortcodeController::get_instance()->init();
      CheckoutController::get_instance()->init();
  }

  public function setup() {
    $this->disable_hold_stock();
  }

  public function add_admin_pages() {
    add_menu_page('Meu menu', 'Blabla', 'manage_options', 'woocommerce_shipping_event', array( $this, 'admin_index' ), 'dashicons-store', 10 );
  }

  public function admin_index() {
    require_once $this->plugin_path . 'templates/admin/admin_menu.php';
  }

  public function enqueue_admin() {
    wp_enqueue_style( 'wcse_admin', $this->plugin_url . '/assets/css/wcse_admin.css', array(), rand(111,9999), 'all' );
  }

  public function enqueue() {
    if( is_checkout()  || is_cart() ) {
      wp_enqueue_style( 'wcse_shipping_methods', $this->plugin_url . '/assets/css/wcse_shipping_methods.css', array(), rand(111,9999), 'all' );
      wp_enqueue_script( 'wcse_shipping_methods', $this->plugin_url . '/assets/js/wcse_shipping_methods.js', array( 'jquery' ), rand(111,9999), 'all' );
    }
  }

  public function disable_hold_stock() {
    $hold_stock = get_option( 'woocommerce_hold_stock_minutes' );
    if ( $hold_stock !== '' ) {
      update_option( 'woocommerce_hold_stock_minutes', '' );
    }
  }

}
