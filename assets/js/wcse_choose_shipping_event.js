jQuery(document).ready(function($){

  var addShippingToButtonURL = function() {
    //Available orders
    $('a[href*="' + php_vars.shipping_id_code + '"]').each(function() {
      var shipping_event_id =  $(this).prop("href").split("_").pop().replace("/", "");
      var new_url = php_vars.button_target + "?" + php_vars.chosen_event_param + "=" + shipping_event_id;
      $(this).prop("href", new_url);
    });
    //Orders closed
    $('a[href*="' + php_vars.orders_closed_code + '"]').each(function() {
      if(php_vars.orders_closed_button_target == null || php_vars.orders_closed_button_target == '') {
         $(this).removeAttr('href');
         $(this).attr("disabled", "disabled");
      } else {
        $(this).prop("href", php_vars.orders_closed_button_target);
      }
      $(this).text(php_vars.orders_closed_button_label);
    });
  }
  addShippingToButtonURL();

});
