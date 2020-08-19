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
      'name'                  => _x( 'Shipping Event Type', 'Post Type General Name', 'text_domain' ),
      'singular_name'         => _x( 'Shipping Event Type', 'Post Type Singular Name', 'text_domain' ),
      'menu_name'             => __( 'Shipping Event Types', 'text_domain' ),
      'name_admin_bar'        => __( 'Shipping Event Types', 'text_domain' ));
    $args = array(
      'label'                 => __( 'Shipping Event Types', 'text_domain' ),
      'description'           => __( 'General data type used by a group of shipping events', 'text_domain' ),
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
  		'name'                  => _x( 'Local Pickup Details', 'Post Type General Name', 'text_domain' ),
  		'singular_name'         => _x( 'Local Pickup Details', 'Post Type Singular Name', 'text_domain' ),
  		'menu_name'             => __( 'Local Pickup Details', 'text_domain' ),
  		'name_admin_bar'        => __( 'Local Pickup Details', 'text_domain' ));
  	$args = array(
  		'label'                 => __( 'Local Pickup Details', 'text_domain' ),
  		'description'           => __( 'Add address and pickup time to local pickup', 'text_domain' ),
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
      'name'                  => _x( 'Shipping Events', 'Post Type General Name', 'text_domain' ),
      'singular_name'         => _x( 'Shipping Event', 'Post Type Singular Name', 'text_domain' ),
      'menu_name'             => __( 'Shipping Events', 'text_domain' ),
      'name_admin_bar'        => __( 'Shipping Event', 'text_domain' )
    );
    $args = array(
      'label'                 => __( 'Shipping Event', 'text_domain' ),
      'description'           => __( 'Event of a shipping as local pickup or delivery with a preset date', 'text_domain' ),
      'labels'                => $labels,
      'supports'              => array( 'title', 'thumbnail', 'revisions' ),
      // 'taxonomies'            => array( 'category', 'post_tag' ),
      'hierarchical'          => false,
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
