jQuery(document).ready(function($){

  var get_shipping_method_id = function( instance_id ) {
    return instance_id.split(":").pop();
  };

  const formatter = new Intl.NumberFormat(php_vars.locale.replace("_", "-"), {
    style: 'currency',
    currency: php_vars.currency_format,
    minimumFractionDigits: 2
  });

  var convert_currency = function( value ) {
    return formatter.format(value);
  };

  var addShippingDetails = function() {
    $('.shipping__list_item').each(function() {
      //Move title and price to new label
      $radio_label = $(this).children('.shipping__list_label')[0];
      $details_div_node = $(this).children('.shipping_method_details')
      $details_div = $details_div_node[0];
      $input = $(this).children('input');
      $shipping_instance_id = $input.val();
      // alert(php_vars.blocked_shipping_methods["5"]["min_order_value"]);
      $shipping_method_data = php_vars.blocked_shipping_methods[get_shipping_method_id($shipping_instance_id)];
      if($shipping_method_data !== undefined) {
        $min_value = $shipping_method_data["min_order_value"];
        $(this).addClass('shipping_method_disabled');
        $address_span = $details_div_node.children('span.shipping_method_address');
        $address_span.after("<div class='woocommerce-info minimum_alert'>* " + php_vars.blocked_shipping_alert + " " + convert_currency($min_value) + "</div>");
        $address_span.addClass("");
      }
      $label_title = $details_div.firstElementChild;
      $label_title.append($radio_label.firstChild);
      $price_selector = $(this).find('.woocommerce-Price-amount');
      if($price_selector.length > 0)
        $label_title.append($price_selector[0]);

      //Move div to original label of radio button
      $radio_label.appendChild($details_div);
      $details_div.hidden = false;

      //Move all elements inside the div
      $(this).children().each(function() {
        if($(this).hasClass("shipping__list_label") || $(this).hasClass("shipping_method"))
          return;
        $details_div.append($(this)[0]);
      })
    });
  }
  $(document.body).on('updated_shipping_method', addShippingDetails);
  $(document.body).on('updated_checkout', addShippingDetails);
  $(document.body).on('updated_cart_totals', addShippingDetails);
  addShippingDetails();
});
