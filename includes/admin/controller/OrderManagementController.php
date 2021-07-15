<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Admin\Controller;

use \WCShippingEvent\Base\ShippingEventController;

class OrderManagementController {

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function init() {
    if ( is_admin() ) {
      add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_shipping_event') , 20 );
      add_filter( 'pre_get_posts',         array( $this, 'filter_orders_by_shipping_event_query' ) );
    }
  }

  public function filter_orders_by_shipping_event_query( $query ) {
    global $pagenow;

    if ( !isset( $_GET['_shipping_event'] ) ) return;
    $selected_shipping_event = $_GET['_shipping_event'];

    if ( is_admin() && $pagenow=='edit.php' && $query->query_vars['post_type'] == 'shop_order' && !empty($selected_shipping_event) ) {

      $meta_query = (array)$query->get('meta_query');

      // Add your criteria
      $meta_query[] = array(
              'key'     => 'shipping_event',
              'value'   => $selected_shipping_event,
              'compare' => '=',
      );

      // Set the meta query to the complete, altered query
      $query->set('meta_query',$meta_query);
    }
  }

  // Add selectbox for filtering ORDERS by shipping event
  public function filter_orders_by_shipping_event() {

    if ( !isset( $_GET['post_type'] ) || $_GET['post_type'] != 'shop_order' ) return;

    $exp_types = array();
    $shipping_event_list = ShippingEventController::get_instance()->order_by_date( get_posts( array( 'post_type' => 'shipping_event' ) ) );

    ?>
    <select name="_shipping_event">
        <option value=""><?php _e('Shipping Events', 'woocommerce-shipping-event'); ?></option>
        <?php
        $current_v = isset($_GET['_shipping_event']) ? $_GET['_shipping_event'] : '';
        foreach ($shipping_event_list as $shipping_event) {
            printf
            (
                '<option value="%s"%s>%s</option>',
                $shipping_event->get_id(),
                $shipping_event->get_id() == $current_v? ' selected="selected"':'',
                $shipping_event->get_title()
            );
        }
        ?>
    </select>
    <?php
  }
}
