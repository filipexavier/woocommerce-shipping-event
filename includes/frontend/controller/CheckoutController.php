<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Frontend\Controller;

use \WCShippingEvent\Cpt\ShippingEvent;
use \WCShippingEvent\Base\ShippingEventController;
use \WCShippingEvent\Base\DateController;
use \WCShippingEvent\Frontend\Controller\ShopController;

class CheckoutController {

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function init() {
    if( !is_admin() ) {
      add_action( 'woocommerce_before_cart', array( $this, 'show_shipping_date_on_header' ), 1 );
      add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'show_shipping_date_on_header' ), 1 );
      add_filter( 'woocommerce_package_rates', array( $this, 'filter_shipping_methods' ) );
      add_action( 'woocommerce_after_shipping_rate' , array( $this, 'add_local_pickup_details_to_checkout_button' ), 10, 2 );
      add_action( 'woocommerce_order_details_before_order_table', array( $this, 'show_order_shipping_event' ) );
      add_action( 'woocommerce_shipping_event_after_order_details', array( $this, 'show_order_local_pickup_details' ) );
      add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_local_pickup_details_to_order' ) );
      add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_shipping_event_to_order' ) );
      add_action( 'woocommerce_email_order_details', array( $this, 'add_shipping_details_to_email' ), 5, 4 );
      add_filter( 'woocommerce_email_subject_customer_processing_order', array( $this, 'add_shipping_event_to_email_subject' ), 1, 2 );
      // add_filter( 'woocommerce_email_subject_customer_completed_order', array( $this, 'add_shipping_event_to_email_subject' ), 1, 2 );
    } else {
      add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'show_admin_order_shipping_event' ), 10, 1 );
      add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'show_admin_order_local_pickup_details' ), 11, 1 );
    }
  }

  public function show_shipping_date_on_header() {
    if( is_null( ShopController::get_instance()->get_shipping_event() ) ) return;
    $shipping_event = ShopController::get_instance()->get_shipping_event();
    wc_add_notice( __("Seu pedido será entregue no dia " .  DateController::format_date( $shipping_event->get_shipping_date() ) ), 'notice');
  }

  public function filter_shipping_methods( $package_rates ) {
    $selected_shipping_methods = ShopController::get_instance()->get_session_shipping_event_method_list();
    if( !isset( $selected_shipping_methods ) ) {
      //TODO: Abort checkout
      return array();
    }
    $unset_list = array();
    foreach( $package_rates as $available_shipping_method ){
      $method_id = $available_shipping_method->instance_id;
      if( !array_key_exists( $method_id, $selected_shipping_methods ) ) {
        array_push( $unset_list, $available_shipping_method->id );
      }
    }

    foreach( $unset_list as $method_id ) {
      unset($package_rates[$method_id]);
    }
    return $package_rates;
  }

  public function add_local_pickup_details_to_checkout_button( $shipping_method, $index ) {
    ?>
    <div class="shipping_method_details" hidden>
      <span class="shipping_method_title"></span>
      <span class="shipping_method_address">
        <?php echo get_post_meta( $shipping_method->instance_id, 'local_pickup_details_address', true ) ?>
      </span>
    </div>
    <?php
  }

  function save_local_pickup_details_to_order( $order_id ) {
    $order = wc_get_order( $order_id );
		foreach ( $order->get_shipping_methods() as $shipping_method ) {
      // $shipping_method = WC_Shipping_Zones::get_shipping_method( $instance_id );
      if( $shipping_method->get_method_id() != 'local_pickup') continue;
			$shipping_address = get_post_meta( $shipping_method->get_instance_id(), 'local_pickup_details_address', true );
      if( empty( $shipping_address ) ) continue;
      var_dump($shipping_address);
      update_post_meta( $order_id, 'local_pickup_details_address', $shipping_address );

		}
  }

  function save_shipping_event_to_order( $order_id ) {
    if( is_null( ShopController::get_instance()->get_shipping_event() ) ) return;
    update_post_meta( $order_id, 'shipping_event', ShopController::get_instance()->get_shipping_event()->get_id() );
  }

  function show_admin_order_shipping_event( $order ) {
    $shipping_event_id = get_post_meta( $order->get_id(), 'shipping_event', true );
    if( empty( $shipping_event_id ) ) return;

    $shipping_event = new ShippingEvent( $shipping_event_id );
    echo '<p><strong>'.__('Date').':</strong> ' .  DateController::format_date( $shipping_event->get_shipping_date() ) . '</p>';
  }

  function show_admin_order_local_pickup_details( $order ) {
    echo '<p><strong>'.__('Local Pickup Address', 'woocommerce-shipping-event').':</strong> ' . get_post_meta( $order->get_id(), 'local_pickup_details_address', true ) . '</p>';
  }

  function show_order_shipping_event( $order ) {
    $shipping_event_id = get_post_meta( $order->get_id(), 'shipping_event', true );
    if( empty( $shipping_event_id ) ) return;

    $shipping_event = new ShippingEvent( $shipping_event_id );
    ?>
    <div class="large-12 col">
      <div class="is-well col-inner entry-content">
        <h3 class="woocommerce-column__title"><?php echo __( 'Lembre-se:', 'woocommerce-shipping-event' )?></h3>
        <?php
          do_action('woocommerce_shipping_event_before_order_details', $order );
          echo '<p>' . __('Data de entrega') . ': <strong>' . DateController::format_date( $shipping_event->get_shipping_date() ) . '</strong></p>';
          do_action('woocommerce_shipping_event_after_order_details', $order );
        ?>
      </div>
    </div>
    <?php
  }

  function show_order_local_pickup_details( $order ) {
		foreach ( $order->get_shipping_methods() as $shipping_method ) {
      if( $shipping_method->get_method_id() != 'local_pickup') continue;
			$shipping_address = get_post_meta( $shipping_method->get_instance_id(), 'local_pickup_details_address', true );
      if( empty( $shipping_address ) ) continue;
      ?>
      <p><?php echo $shipping_method->get_method_title() ?></p>
      <address><?php echo __('Endereço') . ": <strong>" . $shipping_address ?></strong></address>
      <br />
      <?php
		}
  }

  function add_shipping_details_to_email( $order, $sent_to_admin, $plain_text, $email ) {
    $address = get_post_meta( $order->id, 'local_pickup_details_address', true );
    $shipping_event_id = get_post_meta( $order->id, 'shipping_event', true );
    if( empty( $shipping_event_id ) ) return;

    $shipping_event = new ShippingEvent( $shipping_event_id );

    if( $plain_text ) {
      echo __('Lembre-se') . "!";
      echo __('Data de entrega') . ":" . DateController::format_date( $shipping_event->get_shipping_date() );
      echo __('Endereço') . ":" . $address;
    } else { ?>
      <p><strong><?php echo __('Lembre-se') . "!"?></strong></p>
      <p><?php echo __('Data de entrega') . ": " ?>
        <strong><?php echo DateController::format_date( $shipping_event->get_shipping_date() )?></strong>
      </p>
      <p><?php echo __('Endereço') . ": " . $address ?></p>
      <br /><br />
      <?php
    }
  }

  function add_shipping_event_to_email_subject( $subject, $order ) {
    $shipping_event_id = get_post_meta( $order->id, 'shipping_event', true );
    if( empty( $shipping_event_id ) ) return $subject;
    $shipping_event = new ShippingEvent( $shipping_event_id );
    $subject_append = "[" . __('Data de entrega') . ": " . DateController::format_date( $shipping_event->get_shipping_date() ) . "]";
    return $subject . $subject_append;
  }

}
