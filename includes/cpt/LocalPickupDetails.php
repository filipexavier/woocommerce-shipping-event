<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Cpt;

class LocalPickupDetails {

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

	public function init() {
    add_action( 'init', array( $this, 'install_post_type' ), 0 );
  }

  public static function install_post_type() {

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
  		'menu_position'         => 5,
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

  public static function get_selected_method( $local_pickup_details ) {
    if( is_null( $local_pickup_details ) ) return null;
    return get_post_meta( $local_pickup_details->ID, 'local_pickup_details_local_pickup', true );
  }
}
