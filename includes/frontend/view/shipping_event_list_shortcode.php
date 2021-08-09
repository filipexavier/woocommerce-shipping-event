<?php

  use \WCShippingEvent\Base\ShippingEventController;
  use \WCShippingEvent\Init;
  use \WCShippingEvent\Cpt\ShippingEventType;
  use \WCShippingEvent\Frontend\Controller\ShopController;

  ?>

  <input id="chosen_shipping_event_id" type="hidden"
    value="<?php echo ShopController::get_instance()->get_shipping_event_id() ?>">
  </input>
  <?php

  global $wpdb;

  $shipping_event_list = ShippingEventController::get_instance()->order_by_date( get_posts( array( 'post_type' => 'shipping_event' ) ) );
  $num_enabled = 0;
  foreach( $shipping_event_list as $shipping_event ) {

    if( !$shipping_event->show_to_client() ) continue;
    $num_enabled++;
    if( $shipping_event->get_event_type() ) {
      $html_event = $shipping_event->get_event_type()->post_content;
      echo ShippingEventType::translate_event_tags( $shipping_event, $html_event );
    }
  }

  if( $num_enabled == 0 ) {
    //TODO: pretty notice
    ?>
    <h2><?php echo __('Não temos nenhuma entrega disponível para pedido.', 'woocommerce-shipping-event' ) ?></h2>

  <?php
  }

  $cancel_btn = "true";
  $close_btn = "false";
  $close_btn_target = "";
  $title = __( "Change date", 'woocommerce-shipping-event' ) . "?";
  $msg = __( "Are you sure you want to change the date? If you continue, some items of your cart may be deleted.", 'woocommerce-shipping-event' );
  $active = "false";
  include 'shipping_event_popup.php';
?>
