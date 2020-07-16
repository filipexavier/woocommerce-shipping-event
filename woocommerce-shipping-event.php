<?php
/*
  Plugin Name: Woocommerce Shipping Event
  Description: This plugin allows the customer to pick up the order themselves by selecting pickup location during order placement, and also select the date of delivery, from a list of preset dates set by the administrator.
  Version: 0.0.1
  Author: Filipe Xavier
  Text Domain: woocommerce-shipping-event
  Copyright 2020 filipexts
 */

 defined( 'ABSPATH' ) or exit;

 use WCShippingEvent\Init;
 use WCShippingEvent\Base\Activate;

 if( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
   require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
 }

 function activate_wc_shipping_event_plugin() {
   Activate::activate();
 }

 function deactivate_wc_shipping_event_plugin() {
   Activate::deactivate();
 }

 register_activation_hook( __FILE__, 'activate_wc_shipping_event_plugin' );
 register_deactivation_hook( __FILE__, 'deactivate_wc_shipping_event_plugin' );

 Init::get_instance()->register_services();
