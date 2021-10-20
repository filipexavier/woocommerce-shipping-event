<?php
/**
 * @package WoocommerceShippingEvent
*/


namespace WCShippingEvent\Frontend\Controller;

use \WCShippingEvent\Init;

class ShortcodeController {

  private static $instance;

  public static function get_instance() {
    if( is_null( self::$instance ) ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

	public function init() {
    add_shortcode( 'shipping_event_list', array( $this, 'shipping_event_list_shortcode' ) );
  }

  // Add Shortcode
  public function shipping_event_list_shortcode() {
    ob_start();
    include(  Init::get_instance()->plugin_path . '/includes/frontend/view/' . 'shipping_event_list_shortcode.php' );
    $content = ob_get_clean();
    return do_shortcode( $content);
  }

}
