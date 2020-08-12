<?php
/**
 * @package WoocommerceShippingEvent
*/


namespace WCShippingEvent\Base;

use \WCShippingEvent\Init;

class SettingsController {

  private static $instance;

  public const OPTION_CODES = array(
    'wcse_choose_shipping_event_page',
    'wcse_shipping_event_page'
  );

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

	public function init() {
    add_action( 'admin_menu', array( $this, 'add_menus' ) );
    add_action( 'admin_init', array( $this, 'register_options' ) );
  }

  public function add_menus() {
    //Plugin general settings submenu - admin
    add_submenu_page(
        'edit.php?post_type=shipping_event',
        __('Settings', 'woocommerce_shipping_event' ),
        __('Settings', 'woocommerce_shipping_event' ),
        'manage_options',
        'shipping-event-settings',
        array( $this, 'settings_page_output' )
    );
  }


  /**
   * Options page callback
   */
  public function settings_page_output()
  {
      $this->options = get_option( 'woocommerce_shipping_event' );

      // wp_enqueue_media();

      echo '<div class="wrap">';

      printf( '<h1>%s</h1>', __( 'Shipping Event Plugin Settings', 'woocommerce_shipping_event' ) );

      echo '<form method="post" action="options.php">';

      settings_fields( 'shipping_event_settings_group' );

      do_settings_sections( 'shipping_event_settings_page' );

      submit_button();

      var_dump(is_page('shipping-event-settings'));
      var_dump($page_id);
      echo '</form></div>';
  }

  public function register_options() {
      $this->register_settings();
      //Main Setting session
      add_settings_section(
          'general_settings_section', // ID
          __( 'General Settings', 'woocommerce_shipping_event' ), // Title
          array( $this, 'general_settings_intro_output' ), // Callback
          'shipping_event_settings_page' // Page
          );

      $this->register_fields();
  }

  /**
   * Register and add settings
   */
  public function register_settings() {
      register_setting(
          'shipping_event_settings_group', // Option group
          'wcse_choose_shipping_event_page'  // Option name
      );
      register_setting(
          'shipping_event_settings_group', // Option group
          'wcse_shipping_event_page'  // Option name
      );
  }

  public function register_fields() {
      add_settings_field(
          'choose_shipping_event_page_id', // ID
          __('Choose Shipping Event Page', 'woocommerce_shipping_event' ), // Title
          array( $this, 'choose_shipping_event_page_field_output' ), // Callback
          'shipping_event_settings_page', // Page
          'general_settings_section' // Section
          );

      add_settings_field(
          'shipping_event_page_id', // ID
          __('Shipping Event Page', 'woocommerce_shipping_event' ), // Title
          array( $this, 'shipping_event_page_field_output' ), // Callback
          'shipping_event_settings_page', // Page
          'general_settings_section' // Section
          );
  }

  public function general_settings_intro_output() {}

  public function choose_shipping_event_page_field_output() {
    echo(
      wp_dropdown_pages(
        array(
          'name'              => 'wcse_choose_shipping_event_page',
          'echo'              => 0,
          'show_option_none'  => __( '&mdash; Select &mdash;' ),
          'option_none_value' => '0',
          'selected'          => get_option( 'wcse_choose_shipping_event_page' ),
        )
      )
    );
  }

  public function shipping_event_page_field_output() {
    echo(
      wp_dropdown_pages(
        array(
          'name'              => 'wcse_shipping_event_page',
          'echo'              => 0,
          'show_option_none'  => __( '&mdash; Select &mdash;' ),
          'option_none_value' => '0',
          'selected'          => get_option( 'wcse_shipping_event_page' ),
        )
      )
    );
  }

  /**
   * Return the page selected as choose shipping event page in the plugin settings. Or null if setting is void
   * @return WP_Post | null
  */
  public function get_shipping_event_page() {
    return get_post( get_option('wcse_shipping_event_page') );
  }

  public function get_shipping_event_page_url() {
    return get_permalink( $this->get_shipping_event_page() );
  }

  /**
   * Return the page selected as choose shipping event page in the plugin settings. Or null if setting is void
   * @return WP_Post | null
  */
  public function get_choose_event_page() {
    return get_post( get_option('wcse_choose_shipping_event_page') );
  }

  public function get_choose_event_page_url() {
    return get_permalink( $this->get_choose_event_page() );
  }

  /**
   * Return true if current page is the one selected as choose shipping event page in the plugin settings
   * @return bool
  */
  public function is_choose_event() {
    return is_page( $this->get_choose_event_page() );
  }

}
