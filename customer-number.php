<?php
/*
Plugin Name: Customer Numbering
Author: Samiullah Jamil
Version: 1.0.0
Author URI: https://www.samiullahjaml.com/
*/

function custom_register_additional_fields() {
	echo "Register Test";
}
add_action('woocommerce_register_form','custom_register_additional_fields');