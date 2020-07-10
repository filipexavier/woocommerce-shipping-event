<p> Escolha a data de entrega </p>

<?php
  $shipping_event_list = get_posts( array( 'post_type' => 'shipping_event' ) );
  foreach( $shipping_event_list as $shipping_event ) {
 ?>
  <form action="<?php echo wc_get_page_permalink( 'shop' ) ?>" method="post">
    <input type="hidden" name="chosen_shipping_event_id" value="<?php echo $shipping_event->ID ?>">
    <button type="submit" class="button primary"><?php echo get_the_title( $shipping_event ) ?></button>
  </form>

<?php
  }
?>
