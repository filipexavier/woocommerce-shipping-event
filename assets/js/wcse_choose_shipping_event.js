jQuery(document).ready(function($){

  var closePopup = function() {
    $('.screen-fade, .overlay-shipping-event').each(function() {
      $(this).addClass("hidden");
    });
  }

  var addPopup = function() {
    var shipping_event_id = $(this).prop("id");
    var new_url = get_new_url( shipping_event_id );
    $('.buttonset .ok').prop("href", new_url);
    $('.close').click(closePopup);
    $('.screen-fade, .overlay-shipping-event').each(function() {
      $(this).removeClass("hidden");
    });
  };

  var get_chosen_shipping_event = function() {
    return $('#' + php_vars.chosen_event_param).val();
  }

  var get_shipping_event_id = function( fullText ) {
    return fullText.split("_").pop().replace("/", "");
  }

  var get_new_url = function( shipping_event_id ) {
    return php_vars.button_target + "?" + php_vars.chosen_event_param + "=" + shipping_event_id;
  }

  var addShippingToButtonURL = function() {
    //Available orders
    $('a[href*="' + php_vars.shipping_id_code + '"]').each(function() {
      var shipping_event_id = get_shipping_event_id( $(this).prop("href") );
      var chosen_shipping_event_id = get_chosen_shipping_event();
      if(chosen_shipping_event_id != null &&
         chosen_shipping_event_id != "" &&
         shipping_event_id != chosen_shipping_event_id) {
        $(this).removeAttr('href');
        $(this).prop("id", shipping_event_id);
        $(this).click(addPopup);
      } else {
        var new_url = get_new_url( shipping_event_id );
        $(this).prop("href", new_url);
      }
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
