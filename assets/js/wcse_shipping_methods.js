jQuery(document).ready(function($){

  var addShippingDetails = function() {
    $('.shipping__list_item').each(function() {
      $label = $(this).children('.shipping__list_label')[0];
      $details_div = $(this).children('.shipping_method_details')[0];
      $label.appendChild($details_div);
      $details_div.prepend($label.firstChild);
      $details_div.hidden = false;
    });
  }
  $(document.body).on('updated_shipping_method', addShippingDetails);
  $(document.body).on('updated_checkout', addShippingDetails);
  addShippingDetails();
});
