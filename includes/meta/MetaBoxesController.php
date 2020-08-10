<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Meta;

use WC_Shipping_Zones;
use \WCShippingEvent\Meta\LocalPickupDetailsMetabox;
use \WCShippingEvent\Meta\ShippingEventMetabox;
use \WCShippingEvent\Cpt\ShippingEvent;
use \WCShippingEvent\Base\DateController;

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
    LocalPickupDetailsMetabox::get_instance()->init();
    ShippingEventMetabox::get_instance()->init();
  }

}
