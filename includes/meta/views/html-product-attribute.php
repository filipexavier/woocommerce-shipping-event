<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div data-taxonomy="<?php echo esc_attr( $product->get_name() ); ?>" class="woocommerce_attribute wc-metabox taxonomy closed">
	<h3>
		<a href="#" class="remove_row delete"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
		<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'woocommerce' ); ?>"></div>
		<strong class="attribute_name"><?php echo wc_attribute_label( $product->get_name() ); ?></strong>
	</h3>
	<div class="woocommerce_attribute_data wc-metabox-content hidden">
		<table cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td class="attribute_name">
						<label><?php esc_html_e( 'Name', 'woocommerce' ); ?>:</label>

						<?php if ( $attribute->is_taxonomy() ) : ?>
							<strong><?php echo wc_attribute_label( $product->get_name() ); ?></strong>
							<input type="hidden" name="attribute_names[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $product->get_name() ); ?>" />
						<?php else : ?>
							<input type="text" class="attribute_name" name="attribute_names[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $product->get_name() ); ?>" />
						<?php endif; ?>

						<!-- <input type="hidden" name="attribute_position[<?php echo esc_attr( $i ); ?>]" class="attribute_position" value="<?php echo esc_attr( $attribute->get_position() ); ?>" /> -->
					</td>
					<td rowspan="3">
						<label><?php esc_html_e( 'Value(s)', 'woocommerce' ); ?>:</label>
						<?php
						if ( $attribute->is_taxonomy() && $attribute_taxonomy = $attribute->get_taxonomy_object() ) {
							$attribute_types = wc_get_attribute_types();

							if ( ! array_key_exists( $attribute_taxonomy->attribute_type, $attribute_types ) ) {
								$attribute_taxonomy->attribute_type = 'select';
							}

						} else {
							/* translators: %s: WC_DELIMITER */
							?>
							<textarea name="attribute_values[<?php echo esc_attr( $i ); ?>]" cols="5" rows="5" placeholder="<?php printf( esc_attr__( 'Enter some text, or some attributes by "%s" separating values.', 'woocommerce' ), WC_DELIMITER ); ?>"><?php echo esc_textarea( wc_implode_text_attributes( $attribute->get_options() ) ); ?></textarea>
							<?php
						}
						?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
