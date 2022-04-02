<?php
/*
Plugin Name: WooCommerce Customer Numbering
Author: Samiullah Jamil
Version: 1.0.0
Author URI: https://www.samiullahjaml.com/
*/

function custom_register_additional_fields() {
	echo "Register Test";
}
add_action('woocommerce_register_form','custom_register_additional_fields');

function wcn_add_frontend_scripts() {

}
add_action('wp_enqueue_scripts','wcn_add_frontend_scripts');
