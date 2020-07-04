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

    <h4><?php esc_html_e( 'Shipping Event Settings', 'woocommerce-shipping-event' ); ?></h4>

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

    <div id="shipping_event_product_lis" class="wc-metaboxes-wrapper panel">
    	<div class="toolbar toolbar-top">
    		<span class="expand-close">
    			<a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
    		</span>
    		<select name="shipping_event_products" class="shipping_event_products">
    			<option value=""><?php esc_html_e( 'Custom product attribute', 'woocommerce' ); ?></option>
    			<?php
    			global $wc_product_attributes;

    			// Array of defined attribute taxonomies.

    			if ( ! empty( $product_list ) ) {
    				foreach ( $product_list as $product ) {
    					echo '<option value="' . esc_attr( $product->get_name() ) . '">' . esc_html( $product->get_name() ) . '</option>';
    				}
    			}
    			?>
    		</select>
    		<button type="button" class="button add_attribute"><?php esc_html_e( 'Add', 'woocommerce' ); ?></button>
    	</div>

    	<div class="shipping_event_products wc-metaboxes">
    		<!-- <?php
    		$i          = -1;

    		foreach ( $product_list as $product ) {
    			$i++;

    			//include 'html-product-attribute.php';
    		}
    		?> -->
    	</div>
    	<div class="toolbar">
    		<span class="expand-close">
    			<a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
    		</span>
    		<button type="button" class="button save_attributes button-primary"><?php esc_html_e( 'Save attributes', 'woocommerce' ); ?></button>
    	</div>
    </div>

    <div id="shipping_event_product_list" class="wc-metaboxes-wrapper panel">
        <div data-taxonomy="<?php echo esc_attr( $product->get_name() ); ?>" class="woocommerce_attribute wc-metabox taxonomy closed">
        	<div class="woocommerce_attribute_data wc-metabox-content">
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
                    $product_enabled = $product_event_data['enabled'];
                    $product_stock = $product_event_data['stock'];
                  }
                  // echo '<script language="javascript">';
                  // echo 'alert("stock: ' . $product_stock . 'id: ' . $product_id .'")';
                  // echo '</script>';
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

      if (array_key_exists('shipping_event_date', $_POST)) {
          update_post_meta(
              $post_id,
              'shipping_event_date',
              $_POST['shipping_event_date']
          );
      }

      if ( isset( $_POST['shipping_event_enabled'] )) {
        $enabled = $_POST['shipping_event_enabled'];
          update_post_meta(
              $post_id,
              'shipping_event_enabled',
              $enabled
          );
      }

      // $product_array = $_POST['products'];
      // $product_post_meta = array();
      //
      // foreach( $product_array as $key => $value ) {
      //   $stock = $value['stock'];
      //   echo $stock;
      //   $enabled = $value['enabled'];
      //   echo $enabled;
      //   if( $enabled == "yes" ){
      //     $product_post_meta[$key] = array(
      //       'stock' => 4,
      //       'enabled' => "yes"
      //     );
      //   }
      // }

      update_post_meta(
        $post_id,
        'products',
        $_POST['products']
      );

      var_dump($_POST['products']);

      // echo '<script language="javascript">';
      // echo 'alert("' . $post_id . '")';
      // echo '</script>';
      //debug();


  }

}
