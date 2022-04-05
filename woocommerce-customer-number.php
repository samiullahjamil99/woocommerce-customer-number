<?php
/*
Plugin Name: WooCommerce Customer Numbering
Author: Samiullah Jamil
Version: 1.0.0
Author URI: https://www.samiullahjaml.com/
*/

define( 'WCN_DIR', WP_PLUGIN_DIR.'/woocommerce-customer-number' );

function custom_register_additional_fields() {
	?>
	<p class="form-row">
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-customer_number_present">
			<input class="woocommerce-form__input woocommerce-form__input-checkbox" type="checkbox" name="customer_number_present" id="customer_number_present" value="yes" /> <span><?php esc_html_e( 'I already have my Customer Number', 'wcn' ); ?></span>
		</label>
	</p>
	<p class="customer-number-error" style="display:none;">Error: Please Enter the Number First</p>
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide customer-number-container" style="display:none;">
		<label for="reg_customer_number"><?php esc_html_e( 'Customer Number', 'wcn' ); ?></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="customer_number" id="reg_customer_number" value="<?php echo ( ! empty( $_POST['customer_number'] ) ) ? esc_attr( wp_unslash( $_POST['customer_number'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
	</p>
	<?php
}
add_action('woocommerce_register_form','custom_register_additional_fields');

function wcn_add_frontend_scripts() {
	if (is_account_page())
		wp_enqueue_script('register-fields',plugin_dir_url( WCN_DIR ) . '/woocommerce-customer-number/assets/js/wcn-register-fields.js',array('jquery-core'),'',true);
}
add_action('wp_enqueue_scripts','wcn_add_frontend_scripts');
