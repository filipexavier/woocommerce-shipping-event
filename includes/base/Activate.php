<?php
/**
 * @package WoocommerceShippingEvent
*/

namespace WCShippingEvent\Base;

/**
 *
 */
class Activate
{

  function __construct()
  {
    // code...
  }

  public static function activate() {
    // $this->generate_post_types();
    flush_rewrite_rules();
  }

  public static function deactivate() {
    flush_rewrite_rules();
  }

}
