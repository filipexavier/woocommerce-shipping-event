<?php
/**
* @package WoocommerceShippingEvent
*/

namespace WCShippingEvent;

use \WCShippingEvent\Base\BaseController;
use \WCShippingEvent\Cpt\ShippingEvent;
use \WCShippingEvent\Meta\MetaBoxesController;

final class Init extends BaseController {

  private static $instance;

  // function __construct() {
  //   $base_controller = BaseController->get_instance;
  // }

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function register_services() {
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
      add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
      ShippingEvent::get_instance()->init();
      MetaBoxesController::get_instance()->init();
  }

  public function add_admin_pages() {
    add_menu_page('Meu menu', 'Blabla', 'manage_options', 'woocommerce_shipping_event', array( $this, 'admin_index' ), 'dashicons-store', 10 );
  }

  public function admin_index() {
    require_once $this->plugin_path . 'templates/admin/admin_menu.php';
  }
  //

  public function add_product() {
    return "aaa";
  }

  public function enqueue() {
    wp_enqueue_style( 'mypluginstyle', $this->plugin_url . '/assets/css/wpdpd.min.css' );
    wp_enqueue_style( 'wcse_admin_style', $this->plugin_url . '/assets/css/wc_admin.css' );
    wp_enqueue_script( 'mypluginscript', $this->plugin_url . '/assets/js/shipping_event_products.js' );
		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . '.js', array( 'jquery' ), '2.70', true );
    add_action( 'wp_ajax_woocommerce_shipping_event_add_product', array( __CLASS__, 'add_product' ) );

    $params = array(
      'remove_item_notice'            => '',
      'i18n_select_items'             => __( 'Please select some items.', 'woocommerce' ),
      'i18n_do_refund'                => __( 'Are you sure you wish to process this refund? This action cannot be undone.', 'woocommerce' ),
      'i18n_delete_refund'            => __( 'Are you sure you wish to delete this refund? This action cannot be undone.', 'woocommerce' ),
      'i18n_delete_tax'               => __( 'Are you sure you wish to delete this tax column? This action cannot be undone.', 'woocommerce' ),
      'remove_item_meta'              => __( 'Remove this item meta?', 'woocommerce' ),
      'remove_attribute'              => __( 'Remove this attribute?', 'woocommerce' ),
      'name_label'                    => __( 'Name', 'woocommerce' ),
      'remove_label'                  => __( 'Remove', 'woocommerce' ),
      'click_to_toggle'               => __( 'Click to toggle', 'woocommerce' ),
      'values_label'                  => __( 'Value(s)', 'woocommerce' ),
      'text_attribute_tip'            => __( 'Enter some text, or some attributes by pipe (|) separating values.', 'woocommerce' ),
      'visible_label'                 => __( 'Visible on the product page', 'woocommerce' ),
      'used_for_variations_label'     => __( 'Used for variations', 'woocommerce' ),
      'new_attribute_prompt'          => __( 'Enter a name for the new attribute term:', 'woocommerce' ),
      'calc_totals'                   => __( 'Recalculate totals? This will calculate taxes based on the customers country (or the store base country) and update totals.', 'woocommerce' ),
      'copy_billing'                  => __( 'Copy billing information to shipping information? This will remove any currently entered shipping information.', 'woocommerce' ),
      'load_billing'                  => __( "Load the customer's billing information? This will remove any currently entered billing information.", 'woocommerce' ),
      'load_shipping'                 => __( "Load the customer's shipping information? This will remove any currently entered shipping information.", 'woocommerce' ),
      'featured_label'                => __( 'Featured', 'woocommerce' ),
      'prices_include_tax'            => esc_attr( get_option( 'woocommerce_prices_include_tax' ) ),
      'tax_based_on'                  => esc_attr( get_option( 'woocommerce_tax_based_on' ) ),
      'round_at_subtotal'             => esc_attr( get_option( 'woocommerce_tax_round_at_subtotal' ) ),
      'no_customer_selected'          => __( 'No customer selected', 'woocommerce' ),
      'plugin_url'                    => WC()->plugin_url(),
      'ajax_url'                      => admin_url( 'admin-ajax.php' ),
      'order_item_nonce'              => wp_create_nonce( 'order-item' ),
      'add_attribute_nonce'           => wp_create_nonce( 'add-attribute' ),
      'save_attributes_nonce'         => wp_create_nonce( 'save-attributes' ),
      'calc_totals_nonce'             => wp_create_nonce( 'calc-totals' ),
      'get_customer_details_nonce'    => wp_create_nonce( 'get-customer-details' ),
      'search_products_nonce'         => wp_create_nonce( 'search-products' ),
      'grant_access_nonce'            => wp_create_nonce( 'grant-access' ),
      'revoke_access_nonce'           => wp_create_nonce( 'revoke-access' ),
      'add_order_note_nonce'          => wp_create_nonce( 'add-order-note' ),
      'delete_order_note_nonce'       => wp_create_nonce( 'delete-order-note' ),
      'calendar_image'                => WC()->plugin_url() . '/assets/images/calendar.png',
      'post_id'                       => '',
      'currency_format_decimal_sep'   => esc_attr( wc_get_price_decimal_separator() ),
      'currency_format_thousand_sep'  => esc_attr( wc_get_price_thousand_separator() ),
      'currency_format'               => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ), // For accounting JS.
      'rounding_precision'            => wc_get_rounding_precision(),
      'tax_rounding_mode'             => wc_get_tax_rounding_mode(),
      'product_types'                 => array_unique( array_merge( array( 'simple', 'grouped', 'variable', 'external' ), array_keys( wc_get_product_types() ) ) ),
      'i18n_download_permission_fail' => __( 'Could not grant access - the user may already have permission for this file or billing email is not set. Ensure the billing email is set, and the order has been saved.', 'woocommerce' ),
      'i18n_permission_revoke'        => __( 'Are you sure you want to revoke access to this download?', 'woocommerce' ),
      'i18n_tax_rate_already_exists'  => __( 'You cannot add the same tax rate twice!', 'woocommerce' ),
      'i18n_delete_note'              => __( 'Are you sure you wish to delete this note? This action cannot be undone.', 'woocommerce' ),
      'i18n_apply_coupon'             => __( 'Enter a coupon code to apply. Discounts are applied to line totals, before taxes.', 'woocommerce' ),
      'i18n_add_fee'                  => __( 'Enter a fixed amount or percentage to apply as a fee.', 'woocommerce' ),
    );

    wp_localize_script( 'mypluginscript', 'woocommerce_admin_meta_boxes', $params );
  }

  // function register() {

  // }
  //
  //
  // function generate_post_types(){
  //   echo("3");
  //   add_action( 'init', array( 'Wpdpd_Shipping_Event', 'init' ) );
  // }
  //

}
