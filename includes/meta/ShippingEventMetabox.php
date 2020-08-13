<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Meta;

use WC_Shipping_Zones;
use \WCShippingEvent\Meta\LocalPickupDetailsMetabox;
use \WCShippingEvent\Cpt\ShippingEvent;
use \WCShippingEvent\Base\DateController;
use \WCShippingEvent\Base\ShippingEventController;

/**
 *
 */
class ShippingEventMetabox
{

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function init() {
    add_action( 'admin_menu', array( $this, 'add_shipping_event_meta_box' ) );
    add_action('save_post', array( $this, 'shipping_event_settings_save_postdata') );
  }

  function add_shipping_event_meta_box() {
  	$meta_box = array(
  		'id' => 'shipping_event_basic_settings',
  		'title' => 'Basic Settings',
  		'page' => 'shipping_event',
  		'context' => 'normal',
  		'priority' => 'default',
  		'autosave' => 'false'
  	);

    add_meta_box($meta_box['id'], $meta_box['title'], array( $this, 'shipping_event_basic_settings_output' ), $meta_box['page'], $meta_box['context'], $meta_box['priority']);

  	$meta_box = array(
  		'id' => 'shipping_event_methods_settings',
  		'title' => 'Shipping Methods',
  		'page' => 'shipping_event',
  		'context' => 'normal',
  		'priority' => 'default',
  		'autosave' => 'false'
  	);

    add_meta_box($meta_box['id'], $meta_box['title'], array( $this, 'shipping_event_methods_settings_output' ), $meta_box['page'], $meta_box['context'], $meta_box['priority']);

  	$meta_box = array(
  		'id' => 'shipping_event_products_settings',
  		'title' => 'Products',
  		'page' => 'shipping_event',
  		'context' => 'normal',
  		'priority' => 'default',
  		'autosave' => 'false'
  	);

    add_meta_box($meta_box['id'], $meta_box['title'], array( $this, 'shipping_event_products_settings_output' ), $meta_box['page'], $meta_box['context'], $meta_box['priority']);


  }

  function shipping_event_methods_settings_output( $post )
  {
    ?>
    <div id="shipping_event_method_list" class="wc-metaboxes-wrapper panel">

      <?php
        $shipping_zones = WC_Shipping_Zones::get_zones();
        $selected_shipping_methods= get_post_meta( $post->ID, 'selected_shipping_methods', true);

        foreach ( $shipping_zones as $zone ) {
          $zone_obj = WC_Shipping_Zones::get_zone($zone['zone_id']);
          ?><p><?php echo $zone['zone_name'] ?></p><?php

          $shipping_methods = $zone_obj->get_shipping_methods();
          $method_label = '';
          foreach ( $shipping_methods as $shipping_method ) {
            $shipping_method_enabled = "no";
            if( $shipping_method->method_title != $method_label ) {
              $method_label = $shipping_method->method_title;
              ?><p><?php echo $method_label ?></p><?php
            }
            if( !empty( $selected_shipping_methods ) && array_key_exists( $shipping_method->instance_id, $selected_shipping_methods ) ) {
              $shipping_method_enabled = $selected_shipping_methods[$shipping_method->instance_id]['enabled'];
            }
            ?>
            <p class="form-field">
              <input type="checkbox"
                name="<?php echo 'selected_shipping_methods[' . $shipping_method->instance_id . '][enabled]' ?>"
                value="yes"
                <?php checked( $shipping_method_enabled, "yes" ); ?>>
              <label><?php echo $shipping_method->title ?></label>
            </p><?php
          }
        }
      ?>
    </div><?php
  }

  function shipping_event_basic_settings_output( $post )
  {
    $post_id          = $post->ID;
    $shipping_event = ShippingEventController::get_instance()->get_shipping_event( $post );
    ?>

    <p class="form-field">
      <label><?php esc_html_e( 'Enabled', 'woocommerce-shipping-event' ); ?></label>
      <input
        type="checkbox"
        id="shipping_event_enabled"
        name="shipping_event_enabled"
        value="yes"
        <?php checked( $shipping_event->get_enabled(), true ); ?>
      />
    </p>

    <p class="form-field">
      <label><?php esc_html_e( 'Date of shipping/Delivery:', 'woocommerce-shipping-event' ); ?></label>
      <input
       type="date"
       id="shipping_event_date"
       name="shipping_event_date"
       value="<?php echo DateController::date_to_str( $shipping_event->get_shipping_date() ) ?>"
      />
    </p>

    <p class="form-field">
      <label><?php esc_html_e( 'Start accepting orders:', 'woocommerce-shipping-event' ); ?></label>
      <input
       type="date"
       id="shipping_event_start_orders_date"
       name="shipping_event_start_orders_date"
       value="<?php echo DateController::date_to_str( $shipping_event->get_begin_order_date() ) ?>"
      />
    </p>

    <p class="form-field">
      <label><?php esc_html_e( 'Accepting orders until:', 'woocommerce-shipping-event' ); ?></label>
      <input
        type="date"
        id="shipping_event_end_orders_date"
        name="shipping_event_end_orders_date"
        value="<?php echo DateController::date_to_str( $shipping_event->get_end_order_date() ) ?>"
      />
    </p>

    <p class="form-field">
      <label><?php esc_html_e( 'Disable Back Order for all products', 'woocommerce-shipping-event' ); ?></label>
      <input
        type="checkbox"
        id="shipping_event_disable_backorder"
        name="shipping_event_disable_backorder"
        value="yes"
        <?php checked( $shipping_event->get_disable_backorder(), true ); ?>
      />
    </p>


    <p class="form-field">
      <label><?php esc_html_e( 'Event Type:', 'woocommerce-shipping-event' ); ?></label>
      <?php
      echo(
        wp_dropdown_pages(
          array(
            'post_type'         => 'shipping_event_type',
            'name'              => 'shipping_event_type',
            'echo'              => 0,
            'show_option_none'  => __( '&mdash; Select &mdash;' ),
            'option_none_value' => '0',
            'selected'          => $shipping_event->get_event_type_id(),
          )
        )
      );
      ?>
    </p>
    <?php
  }

  function shipping_event_products_settings_output( $post )
  {
    $post_id          = $post->ID;
    $product_list     = wc_get_products('');

    ?>
    <div id="shipping_event_product_list" class="wc-metaboxes-wrapper panel">
      	<div class="woocommerce_attribute wc-metabox woocommerce_attribute_data wc-metabox-content">
      		<table class="wp-list-table widefat fixed striped">
            <thead>
              <tr>
                <td id="cb" class="manage-column column-cb check-column">                  </td>
                <th id="product_name" scope="column" class="manage-column column-title column-primary sortable desc">
                  <label><?php esc_html_e( 'Product Name', 'woocommerce-shipping-event' ); ?></label>
                </th>
                <th id="stock" scope="column" class="manage-column column-title column-primary sortable desc">
                  <label><?php esc_html_e( 'Stock', 'woocommerce-shipping-event' ); ?></label>
                </th>
              </tr>
            </thead>
      			<tbody>
              <?php
              $products_data = get_post_meta($post_id, 'products', true);

              foreach ( $product_list as $product ) {
                $product_id = $product->get_id();
                $product_enabled = "no";
                $product_stock = '';
                if( !empty( $products_data ) && array_key_exists( $product_id, $products_data ) ){
                  $product_event_data = $products_data[$product_id];
                  if( array_key_exists( 'enabled', $product_event_data ) ){
                    $product_enabled = $product_event_data['enabled'];
                  }
                  if( array_key_exists( 'stock', $product_event_data ) ){
                    $product_stock = $product_event_data['stock'];
                  }
                }
                ?>
        				<tr>
                  <th scope="row" class="check-column">
                    <input
                      type="checkbox"
                      id="shipping_event_enabled"
                      name="<?php echo 'products[' . $product_id . '][enabled]' ?>"
                      value="yes"
                      <?php checked( $product_enabled, "yes" ); ?>
                    />
                  </td>
        					<td class="attribute_name">
                    <h3><strong class="attribute_name"><?php echo wc_attribute_label( $product->get_name() ); ?></strong></h3>
                  </td>
                  <td>
                    <input
                      type="text"
                      id="shipping_event_product_stock"
                      name="<?php echo 'products[' . $product_id . '][stock]' ?>"
                      value="<?php echo $product_stock ?>"
                      width="10px"
                    />
                  </td>
                </tr>
              <?php
            } ?>
            </tbody>
          </table>
        </div>
    </div> <?php
  }

  function shipping_event_settings_save_postdata($post_id)
  {
      if (array_key_exists('shipping_event_end_orders_date', $_POST)) {
          update_post_meta(
              $post_id,
              'shipping_event_end_orders_date',
              get_gmt_from_date( $_POST['shipping_event_end_orders_date'] )
          );
      }

      if (array_key_exists('shipping_event_start_orders_date', $_POST)) {
          update_post_meta(
              $post_id,
              'shipping_event_start_orders_date',
              get_gmt_from_date( $_POST['shipping_event_start_orders_date'] )
          );
      }

      if (array_key_exists( 'shipping_event_date', $_POST ) ) {
          update_post_meta(
              $post_id,
              'shipping_event_date',
              get_gmt_from_date( $_POST['shipping_event_date'] )
          );
      }

      if (array_key_exists( 'shipping_event_type', $_POST ) ) {
          update_post_meta(
              $post_id,
              'shipping_event_type',
              $_POST['shipping_event_type']
          );
      }

      if ( array_key_exists( 'shipping_event_enabled', $_POST ) ) {
        update_post_meta(
          $post_id,
          'shipping_event_enabled',
          $_POST['shipping_event_enabled']
        );
      } else {
        delete_post_meta(
          $post_id,
          'shipping_event_enabled');
      }

      if ( array_key_exists( 'shipping_event_disable_backorder', $_POST ) ) {
        update_post_meta(
          $post_id,
          'shipping_event_disable_backorder',
          $_POST['shipping_event_disable_backorder']
        );
      } else {
        delete_post_meta(
          $post_id,
          'shipping_event_disable_backorder');
      }

      //Save products
      if (array_key_exists( 'products', $_POST ) ) {
        update_post_meta(
          $post_id,
          'products',
          $_POST['products']
        );
      }

      //Save shipping methods
      if (array_key_exists( 'selected_shipping_methods', $_POST ) ) {
        update_post_meta(
          $post_id,
          'selected_shipping_methods',
          $_POST['selected_shipping_methods']
        );
      }

  }

}