<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Meta;

/**
 *
 */
class MetaBoxesController
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
    add_action('save_post', array( $this, 'wporg_save_postdata') );
    // code...
  }

  function add_shipping_event_meta_box() {
  	$prefix = '';

  	$meta_box = array(
  		'id' => 'shipping_event',
  		'title' => 'Settings',
  		'page' => 'shipping_event',
  		'context' => 'normal',
  		'priority' => 'default',
  		'autosave' => 'false'
  	);

    add_meta_box($meta_box['id'], $meta_box['title'], array( $this, 'output' ), $meta_box['page'], $meta_box['context'], $meta_box['priority']);
  }

  function output( $post )
  {
    $post_id          = $post->ID;
    $event_enabled    = get_post_meta($post_id, 'shipping_event_enabled', true );
    $date             = get_post_meta($post_id, 'shipping_event_date', true );
    $start_orders_date = get_post_meta($post_id, 'shipping_event_start_orders_date', true );
    $end_orders_date  = get_post_meta($post_id, 'shipping_event_end_orders_date', true );
		$product_list     = wc_get_products('');
    ?>

    <p class="form-field">
      <label><?php esc_html_e( 'Enabled', 'woocommerce-shipping-event' ); ?></label>
      <input
        type="checkbox"
        id="shipping_event_enabled"
        name="shipping_event_enabled"
        value="yes"
        <?php checked( $event_enabled, "yes" ); ?>
      />
    </p>

    <p class="form-field">
      <label><?php esc_html_e( 'Date of shipping/Delivery:', 'woocommerce-shipping-event' ); ?></label>
      <input
       type="date"
       id="shipping_event_date"
       name="shipping_event_date"
       value="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( $date ) ) ); ?>"
      />
    </p>

    <p class="form-field">
      <label><?php esc_html_e( 'Start accepting orders:', 'woocommerce-shipping-event' ); ?></label>
      <input
       type="date"
       id="shipping_event_start_orders_date"
       name="shipping_event_start_orders_date"
       value="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( $start_orders_date ) ) ); ?>"
      />
    </p>

    <p class="form-field">
      <label><?php esc_html_e( 'Accepting orders until:', 'woocommerce-shipping-event' ); ?></label>
      <input
        type="date"
        id="shipping_event_end_orders_date"
        name="shipping_event_end_orders_date"
        value="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( $end_orders_date ) ) ); ?>"
      />
    </p>

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
                if( isset( $products_data ) && array_key_exists( $product_id, $products_data ) ){
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

  function wporg_save_postdata($post_id)
  {
      if (array_key_exists('shipping_event_end_orders_date', $_POST)) {
          update_post_meta(
              $post_id,
              'shipping_event_end_orders_date',
              $_POST['shipping_event_end_orders_date']
          );
      }

      if (array_key_exists('shipping_event_start_orders_date', $_POST)) {
          update_post_meta(
              $post_id,
              'shipping_event_start_orders_date',
              $_POST['shipping_event_start_orders_date']
          );
      }

      if (array_key_exists( 'shipping_event_date', $_POST ) ) {
          update_post_meta(
              $post_id,
              'shipping_event_date',
              $_POST['shipping_event_date']
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

      if (array_key_exists( 'products', $_POST ) ) {
        update_post_meta(
          $post_id,
          'products',
          $_POST['products']
        );
      }

  }

}