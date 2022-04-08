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
	<p class="customer-number-error" style="display:none;color:red;"><b>Error:</b> Please Enter the Number First</p>
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

function wcn_validate_extra_register_fields( $username, $email, $validation_errors ) {
	if (isset($_POST['customer_number_present']) && $_POST['customer_number_present'] === "yes") {
		if ( isset( $_POST['customer_number'] ) && !is_numeric( $_POST['customer_number'] ) ) {
			$validation_errors->add( 'customer_number_error', __( 'Customer Number must be a Numerical Value!', 'wcn' ) );
		} else {
			$user = reset(
			 get_users(
			  array(
			   'meta_key' => 'customer_number',
			   'meta_value' => $_POST['customer_number'],
			   'number' => 1
			  )
			 )
			);
			if ($user) {
				$validation_errors->add( 'customer_number_error', __( 'This Number is already assigned to Someone!', 'wcn' ) );
			}
		}
	}
	return $validation_errors;
}
add_action( 'woocommerce_register_post', 'wcn_validate_extra_register_fields', 10, 3 );
function wcn_save_extra_register_fields($customer_id) {
	$customer_number = '';
	if ( isset( $_POST['customer_number'] ) && !empty($_POST['customer_number']) ) {
		$customer_number = $_POST['customer_number'];
	} else {
		$user = reset(get_users(
			array(
				'role' => array(
					'customer',
				),
				'meta_key' => 'customer_number',
				'orderby' => 'meta_value_num',
				'order'	=> 'DESC',
				'meta_query' => array(
					'relation' => 'AND',
					array(
							'key' => 'customer_number',
							'compare' => 'EXISTS'
					),
					array(
							'key' => 'customer_number',
							'compare' => '>=',
							'value' => '4300',
							'type' => 'NUMERIC',
					)
				),
			)
		));
		if ($user) {
			$max_cn = get_user_meta($user->ID,'customer_number',true);
			$new_cn = intval($max_cn) + 1;
		} else {
			$new_cn = 4300;
		}
		$customer_number = strval($new_cn);
	}
	if ($customer_number !== '') {
		update_user_meta( $customer_id, 'customer_number', sanitize_text_field( $customer_number ) );
	}
	$email = WC()->mailer()->emails['WCN_Email_Customer_New_Account'];
	$email->trigger($customer_id);
}
add_action( 'woocommerce_created_customer', 'wcn_save_extra_register_fields',10,1 );
function wcn_add_emails_hooks($emailClass) {
	remove_action( 'woocommerce_created_customer_notification', array( $emailClass, 'customer_new_account' ), 10, 3 );
}
add_action('woocommerce_email','wcn_add_emails_hooks',10,1);
function wcn_add_account_creation_email_template($emails) {
	$emails['WCN_Email_Customer_New_Account'] = include WCN_DIR . '/inc/class-wcn-email-customer-new-account.php';
	return $emails;
}
add_filter('woocommerce_email_classes','wcn_add_account_creation_email_template',10,1);
