<p> Escolha a data de entrega </p>

<?php

  use \WCShippingEvent\Cpt\ShippingEvent;
  $shipping_event_list = get_posts( array( 'post_type' => 'shipping_event' ) );
  $num_enabled = 0;
  foreach( $shipping_event_list as $shipping_event ) {
    $order_pending = ShippingEvent::get_open_order_pending( $shipping_event );
    if( !$order_pending && !ShippingEvent::get_orderable( $shipping_event ) ) continue;
    $num_enabled++;
    ?>
    <form action="<?php echo wc_get_page_permalink( 'shop' ) ?>" method="post">
      <input type="hidden" name="chosen_shipping_event_id" value="<?php echo $shipping_event->ID ?>">
      <button type="submit" class="button primary"
        <?php disabled( $order_pending, true ); ?>>
        <?php echo get_the_title( $shipping_event ) ?>
      </button>
    </form>
    <?php
  }

  if( $num_enabled == 0 ) {
    //TODO: pretty notice
    ?>
    <h2>Não temos nenhuma entrega disponível para pedido.</h2>
  <?php
  }
?>
