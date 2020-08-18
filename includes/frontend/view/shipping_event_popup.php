<div class="screen-fade <?php echo $active == "false" ? 'hidden' : '' ?>"></div>
<div id="shipping_event_popup" class="overlay-shipping-event <?php echo $active == "false" ? 'hidden' : '' ?>">
	<div class="popup">
		<h3 class="<?php echo $close_btn == "false" ? 'center' : '' ?>"><?php echo $title ?></h3>
		<?php if( $close_btn != "false" ) { ?>
			<a class="close-btn close" <?php if( $close_btn_target ) echo "href='" . $close_btn_target . "'"?>>&times;</a>
		<?php } ?>
		<div class="content">
			<?php echo $msg ?>
		</div>
		<div class="buttonset">
			<a class="button ok" <?php if( $ok_btn_target ) echo "href='" . $ok_btn_target . "'"?>>
				<span><?php echo __('OK') ?></span>
			</a>
			<?php if( $cancel_btn != "false" ) { ?>
				<a class="button close">
					<span><?php echo __('Cancel') ?></span>
				</a>
			<?php } ?>
		</div>
	</div>
</div>
