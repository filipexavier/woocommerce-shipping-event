<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Cpt;

class CustomPostTypeController {

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

	public function init() {
    add_action( 'init', array( $this, 'install_shipping_event_post_type' ), 0 );
    add_action( 'init', array( $this, 'install_shipping_event_type_post_type' ), 0 );
    add_action( 'init', array( $this, 'install_local_pickup_details_post_type' ), 0 );
    add_action( 'admin_menu', array( $this, 'add_menus' ) );
    add_action( 'init', array( $this, 'add_ux_builder_post_type' ) );
  }

	public function add_ux_builder_post_type() {
		add_ux_builder_post_type( 'shipping_event_type' );
	}

  public function add_menus() {

    //Shipping event type submenu - admin
    add_submenu_page(
        'edit.php?post_type=shipping_event',
        __( 'Event Types', 'woocommerce-shipping-event' ),
        __( 'Shipping Event Types', 'woocommerce-shipping-event' ),
        'manage_options',
        'edit.php?post_type=shipping_event_type'
    );
  }

  public function install_shipping_event_type_post_type() {

    $labels = array(
      'name'                  => __( 'Shipping Event Type', 'woocommerce-shipping-event' ),
      'singular_name'         => __( 'Shipping Event Type', 'woocommerce-shipping-event' ),
      'menu_name'             => __( 'Shipping Event Types', 'woocommerce-shipping-event' ),
      'name_admin_bar'        => __( 'Shipping Event Types', 'woocommerce-shipping-event' ));
    $args = array(
      'label'                 => __( 'Shipping Event Types', 'woocommerce-shipping-event' ),
      'description'           => __( 'General data type used by a group of shipping events', 'woocommerce-shipping-event' ),
      'labels'                => $labels,
      'supports'              => array( 'title', 'editor', 'thumbnail' ),
      'hierarchical'          => true,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => false,
      'menu_position'         => 12,
      'menu_icon'             => 'dashicons-store',
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => true,
      'can_export'            => true,
      'has_archive'           => false,
      'exclude_from_search'   => false,
      'rewrite'               => true,
      'publicly_queryable'    => true,
      'capability_type'       => 'page',
    );

    register_post_type( 'shipping_event_type', $args );

  }

  public static function install_local_pickup_details_post_type() {

    $labels = array(
  		'name'                  => __( 'Local Pickup Details', 'woocommerce-shipping-event' ),
  		'singular_name'         => __( 'Local Pickup Details', 'woocommerce-shipping-event' ),
  		'menu_name'             => __( 'Local Pickup Details', 'woocommerce-shipping-event' ),
  		'name_admin_bar'        => __( 'Local Pickup Details', 'woocommerce-shipping-event' ));
  	$args = array(
  		'label'                 => __( 'Local Pickup Details', 'woocommerce-shipping-event' ),
  		'description'           => __( 'Add address and pickup time to local pickup', 'woocommerce-shipping-event' ),
  		'labels'                => $labels,
  		'supports'              => array( 'title' ),
  		'hierarchical'          => false,
  		'public'                => true,
  		'show_ui'               => true,
  		'show_in_menu'          => true,
  		'menu_position'         => 12,
  		'menu_icon'             => 'dashicons-store',
  		'show_in_admin_bar'     => true,
  		'show_in_nav_menus'     => true,
  		'can_export'            => true,
  		'has_archive'           => true,
  		'exclude_from_search'   => true,
  		'publicly_queryable'    => true,
  		'capability_type'       => 'page',
  	);
  	register_post_type( 'local_pickup_details', $args );

  }

  public static function install_shipping_event_post_type() {

    $labels = array(
      'name'                  => __( 'Shipping Events', 'woocommerce-shipping-event' ),
      'singular_name'         => __( 'Shipping Event', 'woocommerce-shipping-event' ),
      'menu_name'             => __( 'Shipping Events', 'woocommerce-shipping-event' ),
      'name_admin_bar'        => __( 'Shipping Event', 'woocommerce-shipping-event' )
    );
    $args = array(
      'label'                 => __( 'Shipping Event', 'woocommerce-shipping-event' ),
      'description'           => __( 'Event of a shipping as local pickup or delivery with a preset date', 'woocommerce-shipping-event' ),
      'labels'                => $labels,
      'supports'              => array( 'title', 'thumbnail' ),
      // 'taxonomies'            => array( 'category', 'post_tag' ),
      'hierarchical'          => true,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 12,
      'menu_icon'             => 'dashicons-cart',
      // 'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => false,
      'rewrite'               => false,
      'can_export'            => true,
      'has_archive'           => true,
      'exclude_from_search'   => true,
      'publicly_queryable'    => true,
      'capability_type'       => 'page',
    );
    register_post_type( 'shipping_event', $args );

  }
}
