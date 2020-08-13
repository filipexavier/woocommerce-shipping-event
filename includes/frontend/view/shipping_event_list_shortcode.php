<?php

  use \WCShippingEvent\Base\ShippingEventController;
  use \WCShippingEvent\Cpt\ShippingEventType;

  $shipping_event_list = ShippingEventController::get_instance()->order_by_date( get_posts( array( 'post_type' => 'shipping_event' ) ) );
  $num_enabled = 0;
  foreach( $shipping_event_list as $shipping_event ) {
    $order_pending = $shipping_event->open_order_pending();
    if( !$order_pending && !$shipping_event->orders_enabled() ) continue;
    $num_enabled++;
    if( $shipping_event->get_event_type() ) {
      $html_event = $shipping_event->get_event_type()->post_content;
      echo ShippingEventType::translate_event_tags( $shipping_event, $html_event );
    }
  }

  if( $num_enabled == 0 ) {
    //TODO: pretty notice
    ?>
    <h2><?php echo __('Não temos nenhuma entrega disponível para pedido.') ?></h2>
  <?php
  }
?>
