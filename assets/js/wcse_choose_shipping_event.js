jQuery(document).ready(function($){

  var addShippingToButtonURL = function() {
    $('a[href*="' + php_vars.shipping_id_code + '"]').each(function() {
      var shipping_event_id =  $(this).prop("href").split("_").pop().replace("/", "");
      var new_url = php_vars.button_target + "?" + php_vars.chosen_event_param + "=" + shipping_event_id;
      $(this).prop("href", new_url);
    });
  }
  addShippingToButtonURL();
  
});
