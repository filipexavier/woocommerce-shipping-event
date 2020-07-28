jQuery(document).ready(function($){

  var addShippingDetails = function() {
    $('.shipping__list_item').each(function() {
      //Move title and price to new label
      $radio_label = $(this).children('.shipping__list_label')[0];
      $details_div = $(this).children('.shipping_method_details')[0];
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
  addShippingDetails();
});
