<?php
/**
 * @package WoocommerceShippingEvent
*/


namespace WCShippingEvent\Base;

use \WCShippingEvent\Init;
use \WCShippingEvent\Base\DateController;
use \WCShippingEvent\Cpt\ShippingEventType;

class SettingsController {

  private static $instance;

  public const OPTION_CODES = array(
    'wcse_choose_shipping_event_page',
    'wcse_shipping_event_page',
    'wcse_shortcode_label_button_unavailable',
    'wcse_shortcode_target_button_unavailable',
    'wcse_event_type_event_date_format',
    'wcse_event_type_open_orders_format',
    'wcse_event_type_close_orders_format',
    'wcse_orders_limit_control',
    'wcse_near_limit_num'
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
        __('Settings', 'woocommerce-shipping-event' ),
        __('Settings', 'woocommerce-shipping-event' ),
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

      printf( '<h1>%s</h1>', __( 'Shipping Event Plugin Settings', 'woocommerce-shipping-event' ) );

      printf( '<h3>%s</h3>', __( 'Instructions:', 'woocommerce-shipping-event' ) );
      printf( __( 'With this plugin the user will have to choose a delivery/pickup date before accessing the shop/store page.', 'woocommerce-shipping-event' ) );
      printf( '<br />'. __( 'For this to work you will need to setup a few things:', 'woocommerce-shipping-event' ) );

      ?>
      <p>
        <li><?php echo __( 'Create a Shipping Event and setup the proper dates, products and shipping methods', 'woocommerce-shipping-event' ) ?></li>
        <li><?php echo __( 'Create a new page and a link for the user to select the date and start purchasing', 'woocommerce-shipping-event' ) ?></li>
        <li><?php echo __( 'Select that page in the setting bellow', 'woocommerce-shipping-event' ) ?></li>
        <li><?php echo __( 'Create a Shipping Event Type to define how the shipping event option will be shown in that page', 'woocommerce-shipping-event' ) ?>
          <p style="margin-left:20px">
            <dd>
              <?php
                echo __( 'You can use a few tags (exactly as described) in your Event Type to show information of the shipping event:', 'woocommerce-shipping-event' );
                foreach( ShippingEventType::EVENT_TAGS as $tag => $description ) {
                  printf( '<p><code>%s</code> - %s</p>', $tag, __( $description, 'woocommerce-shipping-event' ) );
                }
               ?>
            </dd>
          </p>
        </li>
        <li><?php echo __( 'Fill out the rest of the settings bellow', 'woocommerce-shipping-event' ) ?></li>
      </p>
      <br />

      <form method="post" action="options.php">

      <?php
      settings_fields( 'shipping_event_settings_group' );

      do_settings_sections( 'shipping_event_settings_page' );

      submit_button();

      echo '</form></div>';
  }

  public function register_options() {
      $this->register_settings();
      //Main Setting session
      add_settings_section(
          'general_settings_section', // ID
          __( 'General Settings', 'woocommerce-shipping-event' ), // Title
          array( $this, 'general_settings_intro_output' ), // Callback
          'shipping_event_settings_page' // Page
          );

      add_settings_section(
        'shortcode_settings_section', // ID
        __( 'Choose Delivery/Pickup Page (Shortcode)', 'woocommerce-shipping-event' ), // Title
        array( $this, 'shortcode_intro_output' ), // Callback
        'shipping_event_settings_page' // Page
      );

      add_settings_section(
          'date_format_section', // ID
          __( 'Date Format', 'woocommerce-shipping-event' ), // Title
          array( $this, 'date_format_intro_output' ), // Callback
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
          'wcse_ignore_popup_choose_event_page'  // Option name
      );
      register_setting(
          'shipping_event_settings_group', // Option group
          'wcse_shipping_event_page'  // Option name
      );
      register_setting(
          'shipping_event_settings_group', // Option group
          'wcse_shortcode_label_button_unavailable'  // Option name
      );
      register_setting(
          'shipping_event_settings_group', // Option group
          'wcse_shortcode_target_button_unavailable'  // Option name
      );
      register_setting(
          'shipping_event_settings_group', // Option group
          'wcse_event_type_event_date_format'  // Option name
      );
      register_setting(
          'shipping_event_settings_group', // Option group
          'wcse_event_type_open_orders_format'  // Option name
      );
      register_setting(
          'shipping_event_settings_group', // Option group
          'wcse_event_type_close_orders_format'  // Option name
      );

      register_setting(
          'shipping_event_settings_group', // Option group
          'wcse_orders_limit_control'  // Option name
      );

      register_setting(
          'shipping_event_settings_group', // Option group
          'wcse_near_limit_num'  // Option name
      );
  }

  public function register_fields() {
      add_settings_field(
          'choose_shipping_event_page_id', // ID
          __('Choose Shipping Event Page', 'woocommerce-shipping-event' ), // Title
          array( $this, 'choose_shipping_event_page_field_output' ), // Callback
          'shipping_event_settings_page', // Page
          'general_settings_section' // Section
          );

      add_settings_field(
          'ignore_popup_choose_event_page_id', // ID
          __('Block Popup on these taxonomy pages', 'woocommerce-shipping-event' ), // Title
          array( $this, 'ignore_popup_choose_event_page_field_output' ), // Callback
          'shipping_event_settings_page', // Page
          'general_settings_section' // Section
          );

      add_settings_field(
        'shipping_event_page_id', // ID
        __('Shipping Event Page', 'woocommerce-shipping-event' ), // Title
        array( $this, 'shipping_event_page_field_output' ), // Callback
        'shipping_event_settings_page', // Page
        'shortcode_settings_section' // Section
      );
      add_settings_field(
        'shipping_event_page_id', // ID
        __('Shipping Event Page', 'woocommerce-shipping-event' ), // Title
        array( $this, 'shipping_event_page_field_output' ), // Callback
        'shipping_event_settings_page', // Page
        'shortcode_settings_section' // Section
      );

      add_settings_field(
        'shortcode_label_button_unavailable', // ID
        __('Label - orders unavailable', 'woocommerce-shipping-event' ), // Title
        array( $this, 'shortcode_label_button_unavailable_field_output' ), // Callback
        'shipping_event_settings_page', // Page
        'shortcode_settings_section' // Section
      );

      add_settings_field(
        'shortcode_target_button_unavailable', // ID
        __('Target - orders unavailable', 'woocommerce-shipping-event' ), // Title
        array( $this, 'shortcode_target_button_unavailable_field_output' ), // Callback
        'shipping_event_settings_page', // Page
        'shortcode_settings_section' // Section
      );

      add_settings_field(
          'event_type_event_date_format', // ID
          __('Event Date Format', 'woocommerce-shipping-event' ), // Title
          array( $this, 'type_event_date_format_field_output' ), // Callback
          'shipping_event_settings_page', // Page
          'date_format_section' // Section
          );

      add_settings_field(
          'event_type_begin_orders_format', // ID
          __('Open Orders Date Format', 'woocommerce-shipping-event' ), // Title
          array( $this, 'type_open_orders_format_field_output' ), // Callback
          'shipping_event_settings_page', // Page
          'date_format_section' // Section
          );

      add_settings_field(
          'event_type_close_orders_format', // ID
          __('Close Orders Date Format', 'woocommerce-shipping-event' ), // Title
          array( $this, 'type_close_orders_format_field_output' ), // Callback
          'shipping_event_settings_page', // Page
          'date_format_section' // Section
          );

      add_settings_field(
          'orders_limit_control', // ID
          __('Enable orders limit control', 'woocommerce-shipping-event' ), // Title
          array( $this, 'orders_limit_control_output' ), // Callback
          'shipping_event_settings_page', // Page
          'general_settings_section' // Section
          );

      add_settings_field(
          'near_limit_num', // ID
          __('Near limit reached number', 'woocommerce-shipping-event' ), // Title
          array( $this, 'near_limit_num_output' ), // Callback
          'shipping_event_settings_page', // Page
          'general_settings_section' // Section
          );
  }

  public function general_settings_intro_output() {

  }

  public function shortcode_intro_output() {

  }

  public function date_format_intro_output() {
    echo __( 'Use the following expressions/codes to represent the way you want to show each date on your shipping event list. The date will appear as you set here each time you use it in the shipping event type. ','woocommerce_shipping_event' );
    echo __( 'Example: <code>[DAY_OF_WEEK], [DAY]/[MONTH]</code> will become ', 'woocommerce-shipping-event' ) . '<code>' . ShippingEventType::translate_date_tags( DateController::now(),'[DAY_OF_WEEK], [DAY]/[MONTH]' ) . '</code><br /><br />';
    echo __('Expressions available: ', 'woocommerce-shipping-event' ) . '<code>' . implode( ", ", ShippingEventType::DATE_TAGS ) . '</code>';
  }

  public function orders_limit_control_output() {
    $format = get_option( 'wcse_orders_limit_control' );
    ?>
      <input
       type="checkbox"
       id="near_orders_limit_control"
       name="wcse_orders_limit_control"
       <?php checked( $format, 'on' ); ?>
      />
    <?php
  }

  public function near_limit_num_output() {
    $format = get_option( 'wcse_near_limit_num' );
    ?>
      <input
       type="number"
       id="near_limit_num"
       name="wcse_near_limit_num"
       value="<?php echo $format ?>"
      />
    <?php
  }

  public function type_event_date_format_field_output() {
    $format = get_option( 'wcse_event_type_event_date_format' );
    ?>
      <input
       type="text"
       id="event_type_event_date_format"
       name="wcse_event_type_event_date_format"
       size="50"
       placeholder="[DAY]/[MONTH] ([DAY_OF_WEEK])"
       value="<?php echo $format ?>"
      />
    <?php
    echo '<strong>' . __('Simulation: ', 'woocommerce-shipping-event' ) . '</strong>' . ShippingEventType::translate_date_tags( DateController::now(), $format ? $format : '[DAY]/[MONTH] ([DAY_OF_WEEK])' );
  }

  public function shortcode_label_button_unavailable_field_output() {
    ?>
      <input
       type="text"
       id="shortcode_label_button_unavailable"
       name="wcse_shortcode_label_button_unavailable"
       size="30"
       placeholder="<?php echo __( 'Shop' ) ?>"
       value="<?php echo get_option( 'wcse_shortcode_label_button_unavailable' ) ?>"
      />
      <p class="description">
        <?php echo __( 'This label/text will be shown inside the button configured to select the delivery/pickup date (#SELECT_EVENT#) for the events which the orders and not opened yet.', 'woocommerce-shipping-event' ) ?>
      </p>
    <?php
  }

  public function shortcode_target_button_unavailable_field_output() {
    echo(
      wp_dropdown_pages(
        array(
          'name'              => 'wcse_shortcode_target_button_unavailable',
          'echo'              => 0,
          'show_option_none'  => __( '&mdash; Select &mdash;' ),
          'option_none_value' => '0',
          'selected'          => get_option( 'wcse_shortcode_target_button_unavailable' ),
        )
      )
    );
    ?>
      <p class="description">
      <?php echo __( 'Select the page you want the users to be redirect to when they click the button configured to select the delivery/pickup date (#SELECT_EVENT#) for the events which the orders and not opened yet. If not selected, the button will be disabled', 'woocommerce-shipping-event' ) ?>
      </p>
    <?php
  }

  public function type_open_orders_format_field_output() {
    $format = get_option( 'wcse_event_type_open_orders_format' );
    ?>
      <input
       type="text"
       id="event_type_begin_orders_format"
       name="wcse_event_type_open_orders_format"
       size="50"
       placeholder="[DAY]/[MONTH] ([DAY_OF_WEEK])"
       value="<?php echo $format ?>"
      />
    <?php
    echo '<strong>' . __('Simulation: ', 'woocommerce-shipping-event' ) . '</strong>' . ShippingEventType::translate_date_tags( DateController::now(), $format ? $format : '[DAY]/[MONTH] ([DAY_OF_WEEK])' );
  }

  public function type_close_orders_format_field_output() {
    $format = get_option( 'wcse_event_type_close_orders_format' );
    ?>
      <input
       type="text"
       id="event_type_close_orders_format"
       name="wcse_event_type_close_orders_format"
       size="50"
       placeholder="[DAY]/[MONTH] ([DAY_OF_WEEK])"
       value="<?php echo $format ?>"
      />
    <?php
    echo '<strong>' . __('Simulation: ', 'woocommerce-shipping-event' ) . '</strong>' . ShippingEventType::translate_date_tags( DateController::now(), $format ? $format : '[DAY]/[MONTH] ([DAY_OF_WEEK])' );
  }

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

  public function ignore_popup_choose_event_page_field_output() {
    $text = get_option( 'wcse_ignore_popup_choose_event_page' );
    ?>
      <input
       type="text"
       id="wcse_ignore_popup_choose_event_page"
       name="wcse_ignore_popup_choose_event_page"
       size="50"
       value="<?php echo $text ?>"
      />
      <p class="description">
        <?php echo __( 'Code of default or custom categories/tags/taxonomies separated by commas (no spaces between).', 'woocommerce-shipping-event' ) ?>
      </p>
    <?php
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

  public function get_ignore_popup_choose_event_page() {
    return explode(',', get_option('wcse_ignore_popup_choose_event_page') );
  }

  public function check_tax_page() {
    foreach ( $this->get_ignore_popup_choose_event_page() as $tax_id ) {
      if( is_tax($tax_id) ) return true;
    }
    return false;
  }

  public function get_near_orders_limit_num() {
    return get_option( 'wcse_near_limit_num' );
  }

  public function orders_limit_control_enabled() {
    return get_option( 'wcse_orders_limit_control' ) == 'on';
  }

  public function get_shipping_event_page_url() {
    return get_permalink( $this->get_shipping_event_page() );
  }

  public function get_unavailable_target_page() {
    $page = get_option( 'wcse_shortcode_target_button_unavailable' );
    if( $page ) return get_post( $page );
    return '';
  }

  public function get_unavailable_target_page_url() {
    $page = $this->get_unavailable_target_page();
    if( $page ) return get_permalink( $this->get_unavailable_target_page() );
    return '';
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
