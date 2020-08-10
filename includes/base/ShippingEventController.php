<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Base;

use DateTime;
use WCShippingEvent\Base\DateController;
use WCShippingEvent\Cpt\ShippingEvent;

class ShippingEventController {

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
  		'name'                  => _x( 'Shipping Events', 'Post Type General Name', 'text_domain' ),
  		'singular_name'         => _x( 'Shipping Event', 'Post Type Singular Name', 'text_domain' ),
  		'menu_name'             => __( 'Shipping Events', 'text_domain' ),
  		'name_admin_bar'        => __( 'Shipping Event', 'text_domain' ),
  		'archives'              => __( 'Item Archives', 'text_domain' ),
  		'attributes'            => __( 'Item Attributes', 'text_domain' ),
  		'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
  		'all_items'             => __( 'All Items', 'text_domain' ),
  		'add_new_item'          => __( 'Add New Item', 'text_domain' ),
  		'add_new'               => __( 'Add New', 'text_domain' ),
  		'new_item'              => __( 'New Item', 'text_domain' ),
  		'edit_item'             => __( 'Edit Item', 'text_domain' ),
  		'update_item'           => __( 'Update Item', 'text_domain' ),
  		'view_item'             => __( 'View Item', 'text_domain' ),
  		'view_items'            => __( 'View Items', 'text_domain' ),
  		'search_items'          => __( 'Search Item', 'text_domain' ),
  		'not_found'             => __( 'Not found', 'text_domain' ),
  		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
  		'featured_image'        => __( 'Featured Image', 'text_domain' ),
  		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
  		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
  		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
  		'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
  		'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
  		'items_list'            => __( 'Items list', 'text_domain' ),
  		'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
  		'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
  	);
  	$args = array(
  		'label'                 => __( 'Shipping Event', 'text_domain' ),
  		'description'           => __( 'Event of a shipping as local pickup or delivery with a preset date', 'text_domain' ),
  		'labels'                => $labels,
  		'supports'              => array( 'title' ),
  		'taxonomies'            => array( 'category', 'post_tag' ),
  		'hierarchical'          => false,
  		'public'                => true,
  		'show_ui'               => true,
  		'show_in_menu'          => true,
  		'menu_position'         => 5,
  		'menu_icon'             => 'dashicons-cart',
  		'show_in_admin_bar'     => true,
  		'show_in_nav_menus'     => true,
  		'can_export'            => true,
  		'has_archive'           => true,
  		'exclude_from_search'   => true,
  		'publicly_queryable'    => true,
  		'capability_type'       => 'page',
  	);
  	register_post_type( 'shipping_event', $args );

  }

  /**
   * @param int post_id of the post type shipping event
   * @return ShippingEvent
  */
  public function get_shipping_event( $shipping_event_id ) {
    if( is_a( $shipping_event_id, 'ShippingEvent' ) ) return $shipping_event_id;
    $shipping_event_post = $this->get_post_by_id( $shipping_event_id );
    try {
      return new ShippingEvent( $shipping_event_post );
    } catch ( Exception $e ) {
      return null;
    }
  }

  /**
   * @param int post_id of the post type shipping event
   * @return WP_Post
  */
  public function get_post_by_id( $shipping_event_id ) {
    if( is_a( $shipping_event_id, 'WP_Post' ) ) return $shipping_event_id;
    $shipping_event_post = get_post( $shipping_event_id );
    if( $shipping_event_post->post_type == 'shipping_event' ) return $shipping_event_post;
    return null;
  }

  /**
   * @param 'Accepts post_id, post object or shipping_event object'
   * @return ShippingEvent
  */
  public function get_shipping_date( $shipping_event_id ) {
    if( is_a( $shipping_event_id, 'ShippingEvent' ) ) return $shipping_event_id;
    $shipping_event_post = null;
    if( is_a( $shipping_event_id, 'WP_Post' ) ) {
      $shipping_event_post = $shipping_event_id;
    } else {
      $shipping_event_post = $this->get_post_by_id($shipping_event_id);
    }

    if( $shipping_event_post ) return new ShippingEvent( $shipping_event_post );
    return null;
  }

  public function is_post_meta_enabled( $shipping_event_id, $meta_key ) {
    $shipping_event_enabled = get_post_meta( $shipping_event_id, $meta_key, true );
    if ( !empty( $shipping_event_enabled ) && $shipping_event_enabled == "yes" ) return true;
    return false;
  }

  public function get_shipping_event_shipping_methods_list( $shipping_event_id ) {
    if ( empty( $shipping_event_id ) ) return null;

    $shipping_event_methods = get_post_meta( $shipping_event_id, 'selected_shipping_methods', true );
    if ( !isset( $shipping_event_methods ) ) return null;
    return $shipping_event_methods;
  }

  public function get_shipping_event_product_list( $shipping_event_id ) {
    if ( empty( $shipping_event_id ) ) return null;

    $shipping_event_products = get_post_meta( $shipping_event_id, 'products', true );
    if ( !isset( $shipping_event_products ) ) return null;
    return $shipping_event_products;
  }

  public function is_product_enabled( $product_data ) {
    return ( array_key_exists( 'enabled', $product_data ) && $product_data['enabled'] == "yes" );
  }

  public function safe_data_access( $data_array, $data_key ) {
    if ( isset( $data_array )
      && array_key_exists( $data_key, $data_array )
      && isset( $data_array[$data_key] )
      && $data_array[$data_key] != '' )
      return $data_array[$data_key];

    return null;
  }

}
