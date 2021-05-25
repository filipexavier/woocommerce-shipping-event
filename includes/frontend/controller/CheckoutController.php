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
      add_action( 'woocommerce_before_checkout_form', array( $this, 'show_shipping_date_on_header' ), 1 );
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
      add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'show_admin_order_local_pickup_details' ), 11, 1 );
    }
  }

  public function show_shipping_date_on_header() {
    if( is_null( ShopController::get_instance()->get_shipping_event() ) ) return;
    $shipping_event = ShopController::get_instance()->get_shipping_event();
    wc_add_notice( __( 'Your order will be delivered on ', 'woocommerce-shipping-event' ) .  DateController::format_date( $shipping_event->get_shipping_date() ), 'success' );
  }

  public function filter_shipping_methods( $package_rates ) {
    if( ShopController::get_instance()->get_shipping_event() ) {
      $selected_shipping_methods = ShopController::get_instance()->get_shipping_event()->get_shipping_methods();
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

  // Show pickup address and time details on admin order page
  function show_admin_order_local_pickup_details( $order ) {
    $shipping_event_id = get_post_meta( $order->get_id(), 'shipping_event', true );
    if( empty( $shipping_event_id ) ) return;
    $shipping_event = new ShippingEvent( $shipping_event_id );

    //Show Date of shipping and event type name
    echo '<p><strong>' . __('Date').':</strong> ' . DateController::format_date( $shipping_event->get_shipping_date() ) . '</p>';
    echo '<p><strong>' . __( 'Event Type:', 'woocommerce-shipping-event' ) . '</strong> ' . $shipping_event->get_event_type()->post_title . '</p>';

    //Show name of shipping method and pickup address
    $this->show_order_local_pickup_details( $order );

    //Show pickup address if local pickup
    // if( !ShopController::get_instance()->is_delivery_order( $order ) ) {
    //   echo sprintf( '<p><strong>%s:</strong>%s</p> ', __( 'Local Pickup Address', 'woocommerce-shipping-event' ), get_post_meta( $order->get_id(), 'local_pickup_details_address', true ) );
    // }
  }

  function show_order_shipping_event( $order ) {
    $shipping_event_id = get_post_meta( $order->get_id(), 'shipping_event', true );
    if( empty( $shipping_event_id ) ) return;

    $shipping_event = new ShippingEvent( $shipping_event_id );
    ?>
    <div class="large-12 col">
      <div class="is-well col-inner entry-content">
        <h3 class="woocommerce-column__title"><?php echo __( 'Don\'t forget:', 'woocommerce-shipping-event' )?></h3>
        <?php
          do_action('woocommerce_shipping_event_before_order_details', $order );
          echo '<p>' . __( 'Delivery/pickup date', 'woocommerce-shipping-event' ) . ': <strong>' . DateController::format_date( $shipping_event->get_shipping_date() ) . '</strong></p>';
          do_action('woocommerce_shipping_event_after_order_details', $order );
        ?>
      </div>
    </div>
    <?php
  }

  function show_order_local_pickup_details( $order ) {
		foreach ( $order->get_shipping_methods() as $shipping_method ) {
      ?>
      <p><?php echo $shipping_method->get_method_title() ?></p>
      <?php
      if( !ShopController::get_instance()->is_delivery_order( $order ) ) {
        $shipping_address = get_post_meta( $shipping_method->get_instance_id(), 'local_pickup_details_address', true );
        ?>
        <address><?php echo __( 'Local Pickup Address', 'woocommerce-shipping-event' ) . "<strong>: " . $shipping_address ?></strong></address>
        <br />
        <?php
      }
		}
  }

  function add_shipping_details_to_email( $order, $sent_to_admin, $plain_text, $email ) {
    $address = get_post_meta( $order->get_id(), 'local_pickup_details_address', true );
    $shipping_event_id = get_post_meta( $order->get_id(), 'shipping_event', true );
    if( empty( $shipping_event_id ) ) return;

    $shipping_event = new ShippingEvent( $shipping_event_id );
    $shipping_method_name = "";
    foreach ( $order->get_shipping_methods() as $shipping_method ) {
      $shipping_method_name = $shipping_method->get_method_title();
    }

    if( $plain_text ) {
      echo __( 'Don\'t forget:', 'woocommerce-shipping-event' ) . "!";
      echo __( 'Delivery/pickup date', 'woocommerce-shipping-event' ) . ": " . DateController::format_date( $shipping_event->get_shipping_date() );
      echo $shipping_method_name;
      if( !ShopController::get_instance()->is_delivery_order( $order ) )
        echo __( 'Local Pickup Address', 'woocommerce-shipping-event' ) . ": " . $address;
    } else { ?>
      <p><strong><?php echo __( 'Don\'t forget:', 'woocommerce-shipping-event' ) . "!"?></strong></p>
      <p><?php echo __( 'Delivery/pickup date', 'woocommerce-shipping-event' ) . ": " ?>
        <strong><?php echo DateController::format_date( $shipping_event->get_shipping_date() )?></strong>
      </p>
      <p><?php echo $shipping_method_name ?></p>
      <?php
      if( !ShopController::get_instance()->is_delivery_order( $order ) ) { ?>
        <p><?php echo __( 'Local Pickup Address', 'woocommerce-shipping-event' ) . ": " . $address ?></p>
      <?php } ?>
      <br /><br />
      <?php
    }
  }

  function add_shipping_event_to_email_subject( $subject, $order ) {
    $shipping_event_id = get_post_meta( $order->get_id(), 'shipping_event', true );
    if( empty( $shipping_event_id ) ) return $subject;
    $shipping_event = new ShippingEvent( $shipping_event_id );
    $subject_append = "[" . __('Data de entrega') . ": " . DateController::format_date( $shipping_event->get_shipping_date() ) . "]";
    return $subject . $subject_append;
  }

}
