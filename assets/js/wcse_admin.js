jQuery(document).ready(function($){
  $(document).on('click', '#cb-select-all', function(e) {
    var check = $(this).is(':checked');
    $(this).closest('table').find('.column-cb > input[type="checkbox"]').each(function() {
      $(this).prop('checked', check);
    });
  });

});
