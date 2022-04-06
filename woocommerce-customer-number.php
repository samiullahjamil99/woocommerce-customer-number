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
	if ( isset( $_POST['customer_number'] ) && !is_numeric( $_POST['customer_number'] ) ) {
		$validation_errors->add( 'customer_number_error', __( 'Customer Number must be a Numerical Value!', 'wcn' ) );
	}
	return $validation_errors;
}
add_action( 'woocommerce_register_post', 'wcn_validate_extra_register_fields', 10, 3 );

function wcn_save_extra_register_fields($customer_id) {
	if ( isset( $_POST['customer_number'] ) ) {
		update_user_meta( $customer_id, 'customer_number', sanitize_text_field( $_POST['customer_number'] ) );
	}
}
add_action( 'woocommerce_created_customer', 'wcn_save_extra_register_fields' );

function my_admin_menu() {
		add_menu_page(
			__( 'Customer Numbers', 'wcn' ),
			__( 'Customer Numbers', 'wcn' ),
			'manage_options',
			'customer-numbers',
			'wcn_customer_numbers_admin_page_contents',
			'dashicons-schedule',
			3
		);
}
add_action( 'admin_menu', 'my_admin_menu' );

function wcn_customer_numbers_admin_page_contents() {
		?>
			<div class="wcn-page-container">
				<section class="wcn-add-new-debit">
					<h1 class="wcn-section-title">Add New Debit</h1>
					<form id="wcnAddDebit" enctype="multipart/form-data" method="post">
						<div class="wcn-input-group">
							<label for="customer_number">Customer Number *</label>
							<input type="text" name="customer_number" id="customer_number">
						</div>
						<div class="wcn-input-group">
							<label for="debit_amount">Amount to Pay *</label>
							<input type="number" name="debit_amount" id="debit_amount">
						</div>
						<div class="wcn-input-group">
							<label for="customer_invoice">Customer Invoice (optional)</label>
							<input type="file" name="customer_invoice" id="customer_invoice" accept="image/*">
						</div>
						<div class="wcn-submit-btn">
							<input type="submit" value="Submit">
						</div>
					</form>
				</section>
				<section class="wcn-show-customers">

				</section>
			</div>
			<style>
			.wcn-page-container {
				padding: 30px;
			}
			section.wcn-add-new-debit {
				background-color: white;
		    padding: 40px 50px;
		    border-radius: 10px;
		    box-shadow: 0px 3px 6px rgb(0 0 0 / 16%);
				max-width: 500px;
				margin:auto;
			}
			section h1.wcn-section-title {
				margin-top: 0;
		    margin-bottom: 30px;
		    text-align: center;
			}
			section.wcn-add-new-debit form {
		    width: 100%;
		    margin: auto;
			}
			section.wcn-add-new-debit .wcn-input-group {
				display: flex;
		    margin-bottom: 10px;
		    align-items: center;
		    justify-content: space-between;
			}
			section.wcn-add-new-debit .wcn-input-group input {
				width:50%;
			}
			section.wcn-add-new-debit .wcn-submit-btn {
				margin-top: 30px;
			}
			section.wcn-add-new-debit .wcn-submit-btn input {
				padding: 7px 30px;
		    background-color: green;
		    border: none;
		    outline: none;
		    color: white;
		    font-size: 20px;
		    border-radius: 6px;
		    cursor: pointer;
			}
			@media only screen and (max-width: 767px) {
				section.wcn-add-new-debit .wcn-input-group {
					flex-direction: column;
					align-items: flex-start;
				}
				section.wcn-add-new-debit .wcn-input-group label {
					margin-bottom: 10px;
				}
				section.wcn-add-new-debit .wcn-input-group input {
					width:100%;
				}
			}
			</style>
		<?php
}
