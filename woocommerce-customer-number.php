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
			<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="customer_number_present" value="yes" /> <span><?php esc_html_e( 'I already have my Customer Number', 'wcn' ); ?></span>
		</label>
	</p>
	<?php
}
add_action('woocommerce_register_form','custom_register_additional_fields');

function wcn_add_frontend_scripts() {
	wp_enqueue_script('register-fields',plugin_dir_url( WCN_DIR ) . '/woocommerce-customer-number/assets/js/wcn-register-fields.js',array('jquery-core'),'',true);
}
add_action('wp_enqueue_scripts','wcn_add_frontend_scripts');
