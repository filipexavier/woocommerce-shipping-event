<?php

  use \WCShippingEvent\Base\ShippingEventController;
  use \WCShippingEvent\Init;
  use \WCShippingEvent\Cpt\ShippingEventType;
  use \WCShippingEvent\Frontend\Controller\ShopController;

  global $wpdb;

  $shipping_event = ShippingEventController::get_instance()->single_opened_event();

  if( $shipping_event->get_event_type() ) {
    $html_event = $shipping_event->get_event_details()->post_content;
    echo ShippingEventType::translate_event_tags( $shipping_event, $html_event );
  }
?>
