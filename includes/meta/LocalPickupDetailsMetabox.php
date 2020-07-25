<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Meta;

use WC_Shipping_Zones;
use \WCShippingEvent\Cpt\LocalPickupDetails;

/**
 *
 */
class LocalPickupDetailsMetabox
{

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function init() {
    add_action( 'admin_menu', array( $this, 'add_address_field' ) );
    add_action('save_post', array( $this, 'local_pickup_details_settings_save_postdata') );
    add_filter( 'wp_insert_post_data' , array( $this, 'modify_post_title' ), '99', 1 );
  }

  public function add_address_field() {
    $meta_box = array(
      'id' => 'local_pickup_details_basic_settings',
      'title' => 'Basic Settings',
      'page' => 'local_pickup_details',
      'context' => 'normal',
      'priority' => 'default',
      'autosave' => 'false'
    );

    add_meta_box($meta_box['id'], $meta_box['title'], array( $this, 'local_pickup_details_basic_settings_output' ), $meta_box['page'], $meta_box['context'], $meta_box['priority']);

  }

  public function local_pickup_details_basic_settings_output( $post ) {
    $address = get_post_meta( $post->ID, 'local_pickup_details_address', true );
    $pickup_selected_id = get_post_meta( $post->ID, 'local_pickup_details_local_pickup', true );
    $other_local_pickups = get_posts( array( 'post_type' => 'local_pickup_details', 'exclude' => $post->ID ) );
    $other_methods_selected = array_map( 'WCShippingEvent\Cpt\LocalPickupDetails::get_selected_method', $other_local_pickups );
    ?>

    <p class="form-field">
      <label><?php esc_html_e( 'Local pickup', 'woocommerce-shipping-event' ); ?></label>
      <select name="local_pickup_details_local_pickup">
        <?php
        $shipping_zones = WC_Shipping_Zones::get_zones();

        foreach ( $shipping_zones as $zone ) {
          $zone_obj = WC_Shipping_Zones::get_zone($zone['zone_id']);
          $shipping_methods = $zone_obj->get_shipping_methods();
          $method_label = '';
          ?><option value="">Selecione...</option><?php
          foreach ( $shipping_methods as $shipping_method ) {
            if( $shipping_method->id != 'local_pickup' ) continue;
            if( in_array( $shipping_method->instance_id, $other_methods_selected ) ) continue;
            $method_label = $shipping_method->title;
            ?>
            <option value="<?php echo $shipping_method->instance_id ?>"
              <?php selected( $pickup_selected_id, $shipping_method->instance_id ) ?>>
              <?php echo $method_label . " (" . $zone['zone_name'] . ")" ?>
            </option><?php
          }
        }
        ?>
      </select>
    </p>

    <p class="form-field">
      <label><?php esc_html_e( 'Address', 'woocommerce-shipping-event' ); ?></label>
      <input
        type="text"
        id="local_pickup_details_address"
        name="local_pickup_details_address"
        value="<?php echo $address ?>"
      />
    </p>
    <?php
  }

  public function local_pickup_details_settings_save_postdata( $post_id ) {

    if (array_key_exists('local_pickup_details_address', $_POST)) {
        update_post_meta(
            $post_id,
            'local_pickup_details_address',
            $_POST['local_pickup_details_address']
        );
    }

    if (array_key_exists('local_pickup_details_local_pickup', $_POST)) {
        update_post_meta(
            $post_id,
            'local_pickup_details_local_pickup',
            $_POST['local_pickup_details_local_pickup']
        );
        update_post_meta(
            $_POST['local_pickup_details_local_pickup'],
            'local_pickup_details_address',
            $_POST['local_pickup_details_address']
        );

    }
  }

  function modify_post_title( $data ) {
    if($data['post_type'] == 'local_pickup_details' && isset($_POST['local_pickup_details_local_pickup'])) {
      $shipping_zones = WC_Shipping_Zones::get_zones();
      foreach ( $shipping_zones as $zone ) {
        $zone_obj = WC_Shipping_Zones::get_zone($zone['zone_id']);
        $shipping_methods = $zone_obj->get_shipping_methods();
        foreach ( $shipping_methods as $shipping_method ) {
          if( $shipping_method->id != 'local_pickup' ) continue;
          if( $shipping_method->instance_id == $_POST['local_pickup_details_local_pickup']) {
            $title = $shipping_method->title;
            $data['post_title'] =  $title ; //Updates the post title to your new title.
            return $data; // Returns the modified data.
          }
        }
      }
    }
    return $data; // Returns the modified data.
  }

}
