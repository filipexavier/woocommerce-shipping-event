/* global woocommerce_admin_meta_boxes */

jQuery(function($){

// Attribute Tables.

// Initial order.
// var woocommerce_attribute_items = $( '.product_attributes' ).find( '.woocommerce_attribute' ).get();
//
// woocommerce_attribute_items.sort( function( a, b ) {
//    var compA = parseInt( $( a ).attr( 'rel' ), 10 );
//    var compB = parseInt( $( b ).attr( 'rel' ), 10 );
//    return ( compA < compB ) ? -1 : ( compA > compB ) ? 1 : 0;
// });
// $( woocommerce_attribute_items ).each( function( index, el ) {
//   $( '.product_attributes' ).append( el );
// });

// function attribute_row_indexes() {
//   $( '.product_attributes .woocommerce_attribute' ).each( function( index, el ) {
//     $( '.attribute_position', el ).val( parseInt( $( el ).index( '.product_attributes .woocommerce_attribute' ), 10 ) );
//   });
// }

// $( '.product_attributes .woocommerce_attribute' ).each( function( index, el ) {
//   if ( $( el ).css( 'display' ) !== 'none' && $( el ).is( '.taxonomy' ) ) {
//     $( 'select.attribute_taxonomy' ).find( 'option[value="' + $( el ).data( 'taxonomy' ) + '"]' ).attr( 'disabled', 'disabled' );
//   }
// });

// Add rows.
$( 'button.add_attribute' ).on( 'click', function() {
  var size         = $( '.shipping_event_products .woocommerce_attribute' ).length;
  var attribute    = $( 'select.shipping_event_products' ).val();
  var $wrapper     = $( this ).closest( '#shipping_event_product_list' );
  var $attributes  = $wrapper.find( '.shipping_event_products' );
  var data         = {
    action:   'woocommerce_add_attribute',
    taxonomy: attribute,
    i:        size,
		security: woocommerce_admin_meta_boxes.add_attribute_nonce
  };

  // $wrapper.block({
  //   message: null,
  //   overlayCSS: {
  //     background: '#fff',
  //     opacity: 0.6
  //   }
  // });

  $.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
    $attributes.append( response );

    $( document.body ).trigger( 'wc-enhanced-select-init' );

    //attribute_row_indexes();

    $attributes.find( '.woocommerce_attribute' ).last().find( 'h3' ).click();

    //$wrapper.unblock();

    $( document.body ).trigger( 'woocommerce_added_attribute' );
  });

  if ( attribute ) {
    $( 'select.attribute_taxonomy' ).find( 'option[value="' + attribute + '"]' ).attr( 'disabled','disabled' );
    $( 'select.attribute_taxonomy' ).val( '' );
  }

  return false;
});

// $( '.product_attributes' ).on( 'blur', 'input.attribute_name', function() {
//   $( this ).closest( '.woocommerce_attribute' ).find( 'strong.attribute_name' ).text( $( this ).val() );
// });
//
// $( '.product_attributes' ).on( 'click', 'button.select_all_attributes', function() {
//   $( this ).closest( 'td' ).find( 'select option' ).attr( 'selected', 'selected' );
//   $( this ).closest( 'td' ).find( 'select' ).change();
//   return false;
// });
//
// $( '.product_attributes' ).on( 'click', 'button.select_no_attributes', function() {
//   $( this ).closest( 'td' ).find( 'select option' ).removeAttr( 'selected' );
//   $( this ).closest( 'td' ).find( 'select' ).change();
//   return false;
// });
//
// $( '.product_attributes' ).on( 'click', '.remove_row', function() {
//   if ( window.confirm( woocommerce_admin_meta_boxes.remove_attribute ) ) {
//     var $parent = $( this ).parent().parent();
//
//     if ( $parent.is( '.taxonomy' ) ) {
//       $parent.find( 'select, input[type=text]' ).val( '' );
//       $parent.hide();
//       $( 'select.attribute_taxonomy' ).find( 'option[value="' + $parent.data( 'taxonomy' ) + '"]' ).removeAttr( 'disabled' );
//     } else {
//       $parent.find( 'select, input[type=text]' ).val( '' );
//       $parent.hide();
//       attribute_row_indexes();
//     }
//   }
//   return false;
// });
//
// // Attribute ordering.
// $( '.product_attributes' ).sortable({
//   items: '.woocommerce_attribute',
//   cursor: 'move',
//   axis: 'y',
//   handle: 'h3',
//   scrollSensitivity: 40,
//   forcePlaceholderSize: true,
//   helper: 'clone',
//   opacity: 0.65,
//   placeholder: 'wc-metabox-sortable-placeholder',
//   start: function( event, ui ) {
//     ui.item.css( 'background-color', '#f6f6f6' );
//   },
//   stop: function( event, ui ) {
//     ui.item.removeAttr( 'style' );
//     attribute_row_indexes();
//   }
// });


// Save attributes and update variations.
$( '.save_attributes' ).on( 'click', function() {

  $( '.shipping_event_products' ).block({
    message: null,
    overlayCSS: {
      background: '#fff',
      opacity: 0.6
    }
  });
  var original_data = $( '.shipping_event_products' ).find( 'input, select, textarea' );
  var data = {
    post_id     : woocommerce_admin_meta_boxes.post_id,
    product_type: $( '#product-type' ).val(),
    data        : original_data.serialize(),
    action      : 'woocommerce_shipping_event_save_attributes'
  };

  $.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
    if ( response.error ) {
      // Error.
      window.alert( response.error );
    } else if ( response.data ) {
      // Success.
      alert(responde.data);
      $( '.product_attributes' ).html( response.data.html );
      $( '.product_attributes' ).unblock();

      // Make sure the dropdown is not disabled for empty value attributes.
      $( 'select.attribute_taxonomy' ).find( 'option' ).prop( 'disabled', false );

      $( '.product_attributes .woocommerce_attribute' ).each( function( index, el ) {
        if ( $( el ).css( 'display' ) !== 'none' && $( el ).is( '.taxonomy' ) ) {
          $( 'select.attribute_taxonomy' ).find( 'option[value="' + $( el ).data( 'taxonomy' ) + '"]' ).prop( 'disabled', true );
        }
      });

      // Reload variations panel.
      var this_page = window.location.toString();
      this_page = this_page.replace( 'post-new.php?', 'post.php?post=' + woocommerce_admin_meta_boxes.post_id + '&action=edit&' );

      $( '#variable_product_options' ).load( this_page + ' #variable_product_options_inner', function() {
        $( '#variable_product_options' ).trigger( 'reload' );
      } );
    }
  });
});

});
