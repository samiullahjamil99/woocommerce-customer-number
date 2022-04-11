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
			<input class="woocommerce-form__input woocommerce-form__input-checkbox" type="checkbox" name="customer_number_present" id="customer_number_present" value="yes" /> <span><?php esc_html_e( 'Ich habe bereits meine Kundennummer', 'wcn' ); ?></span>
		</label>
	</p>
	<p class="customer-number-error" style="display:none;color:red;"><b>Fehler:</b> Bitte geben Sie zuerst die Nummer ein</p>
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide customer-number-container" style="display:none;">
		<label for="reg_customer_number"><?php esc_html_e( 'Kundennummer', 'wcn' ); ?></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="customer_number" id="reg_customer_number" value="<?php echo ( ! empty( $_POST['customer_number'] ) ) ? esc_attr( wp_unslash( $_POST['customer_number'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
	</p>
	<?php
}
add_action('woocommerce_register_form','custom_register_additional_fields');
function wcn_show_customer_number_on_dashboard() {
	global $current_user;
	$customer_number = get_user_meta($current_user->ID,'customer_number',true);
	?>
	<p>Ihre Kundennummer lautet <strong><?php echo $customer_number; ?></strong>.</p>
	<?php
}
add_action('woocommerce_account_dashboard','wcn_show_customer_number_on_dashboard');

function wcn_add_frontend_scripts() {
	if (is_account_page())
		wp_enqueue_script('register-fields',plugin_dir_url( WCN_DIR ) . '/woocommerce-customer-number/assets/js/wcn-register-fields.js',array('jquery-core'),'',true);
}
add_action('wp_enqueue_scripts','wcn_add_frontend_scripts');

function wcn_validate_extra_register_fields( $username, $email, $validation_errors ) {
	if (isset($_POST['customer_number_present']) && $_POST['customer_number_present'] === "yes") {
		if ( isset( $_POST['customer_number'] ) && !is_numeric( $_POST['customer_number'] ) ) {
			$validation_errors->add( 'customer_number_error', __( 'Die Kundennummer muss ein numerischer Wert sein!', 'wcn' ) );
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
				$validation_errors->add( 'customer_number_error', __( 'Diese Nummer ist bereits jemandem zugewiesen!', 'wcn' ) );
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
	$error = false;
	$success = false;
		if (isset($_POST["action"]) && $_POST["action"] === "adddebit") {
			$error = true;
			$customer = wcn_debit_data_validation($_POST);
			if ($customer) {
				$error = false;
				$order = new WC_Order();
				$order->set_customer_id($customer->ID);
				$order->set_currency( get_woocommerce_currency() );
				$order->set_payment_method();
				$item = new WC_Order_Item_Fee();
				$item->set_props(
					array(
						'name'      => 'Invoice',
						'tax_class' => 0,
						'amount'    => floatval($_POST["debit_amount"]),
						'total'     => floatval($_POST["debit_amount"]),
					)
				);
				$order->add_item( $item );
				if (!empty($_POST["customer_notes"]))
					$order->update_meta_data( '_customer_notes', $_POST["customer_notes"] );
				$order->calculate_totals();
				if (file_exists($_FILES['customer_invoice']['tmp_name']) && is_uploaded_file($_FILES['customer_invoice']['tmp_name'])) {
					$uploadedfile = $_FILES['customer_invoice'];
					$upload_overrides = array( 'test_form' => false );
					$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
					if ( $movefile ) {
					  //var_dump( $movefile);
						$order->update_meta_data( '_invoice_image', $movefile['url'] );
					}
				}
				$order_id = $order->save();
				WC()->mailer()->customer_invoice($order);
				$success = true;
			}
		}
		?>
			<div class="wcn-page-container">
				<section class="wcn-add-new-debit">
					<h1 class="wcn-section-title">Add New Debit</h1>
					<?php if ($error): ?>
					<p class="error" style="color:red;"><b>Error:</b> Customer Number not found</p>
					<?php endif; ?>
					<?php if ($success): ?>
					<p class="success" style="color:green;"><b>Success:</b> Invoice Sent to Customer</p>
					<?php endif; ?>
					<form id="wcnAddDebit" enctype="multipart/form-data" method="post">
						<div class="wcn-input-group">
							<label for="customer_number">Customer Number *</label>
							<input type="text" required name="customer_number" id="customer_number">
						</div>
						<div class="wcn-input-group">
							<label for="debit_amount">Amount to Pay (<?php echo get_woocommerce_currency_symbol(); ?>) *</label>
							<input type="number" required name="debit_amount" id="debit_amount">
						</div>
						<div class="wcn-input-group">
							<label for="customer_invoice">Customer Invoice (optional)</label>
							<input type="file" name="customer_invoice" id="customer_invoice" accept="image/*">
						</div>
						<div class="wcn-input-group">
							<label for="customer_notes">Customer Notes (optional)</label>
							<textarea name="customer_notes" id="customer_notes"></textarea>
						</div>
						<div class="wcn-submit-btn">
							<input type="submit" value="Submit">
						</div>
						<input type="hidden" name="action" value="adddebit">
					</form>
				</section>
				<section class="wcn-show-customers">
					<h1 class="wcn-section-title">Customers Overview</h1>
					<form id="overview_filters">
						<input type="text" name="cn_search" placeholder="Search by Customer Number">
						<input type="submit" value="Filter">
					</form>
					<div id="customers_overview">
					</div>
					<span class="spinner" id="customers_overview_loader"></span>
					<button type="button" id="loadmore_customers">Load More</button>
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
			section.wcn-add-new-debit .wcn-input-group input, section.wcn-add-new-debit .wcn-input-group textarea {
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
			section.wcn-show-customers {
				max-width: 700px;
    		margin: auto;
				margin-top: 25px;
		    background-color: white;
		    border-radius: 10px;
		    box-shadow: 0px 3px 6px rgb(0 0 0 / 16%);
		    padding: 40px 50px;
			}
			section.wcn-show-customers table {
				width: 100%;
			}
			section.wcn-show-customers form#overview_filters {
				margin-bottom: 15px;
			}
			section.wcn-show-customers #customers_overview {
				margin-bottom: 15px;
				text-align: left;
			}
			button#loadmore_customers {
				display: none;
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
function wcn_debit_data_validation($data) {
	$customerNumber = $data["customer_number"];
	$user = reset(
	 get_users(
	  array(
	   'meta_key' => 'customer_number',
	   'meta_value' => $customerNumber,
	   'number' => 1
	  )
	 )
	);
	if($user) {
		return $user;
	}
	return false;
}
function wcn_invoice_image($order) {
	//$orderid = $order->id;
	$notes = get_post_meta($order->get_id(),'_customer_notes',true);
	if ($notes):
	?>
	<h2>Anmerkungen für Kunden</h2>
	<p><?php echo $notes; ?></p>
	<?php
	endif;
	$image = get_post_meta($order->get_id(),'_invoice_image',true);
	if ($image):
	?>
	<h2>Bild der Rechnung</h2>
	<p><?php echo $image; ?></p>
	<?php
	endif;
}
add_action('woocommerce_email_customer_details','wcn_invoice_image',100);
function wcn_display_invoice_data_in_admin( $order ){  ?>
    <div class="order_data_column">
        <h4><?php _e( 'Extra Details' ); ?></h4>
        <?php
            echo '<p><strong>' . __( 'Order Invoice Image' ) . ':</strong> <a href="'. get_post_meta( $order->id, '_invoice_image', true ) .'" target="_blank">' . get_post_meta( $order->id, '_invoice_image', true ) . '</a></p>';
						echo '<p><strong>' . __( 'Customer Notes' ) . ':</strong> ' . get_post_meta( $order->id, '_customer_notes', true ) . '</p>'; ?>
    </div>
<?php }
add_action( 'woocommerce_admin_order_data_after_order_details', 'wcn_display_invoice_data_in_admin' );

function show_customer_numbers() {
	$pagenumber = intval($_POST['paged']);
	$postsperpage = intval($_POST['posts_per_page']);
	$filters = $_POST['filters'];
	$cn_search = $filters['cn_search'];
	$result = array();
	$args = array(
		'paged' => $pagenumber,
		'number' => $postsperpage,
		'role' => array( 'customer' ),
		'meta_query' => array(
			'relation' => 'AND',
		)
	);
	if (!empty($cn_search)) {
		$args['meta_query']['cnsearch'] = array(
			'key' => 'customer_number',
			'value' => $cn_search,
			'type' => 'numeric',
			'compare' => '=',
		);
	}
	$args = wp_parse_args( $args );
  //$args['count_total'] = false;
  $user_search = new WP_User_Query( $args );
  $users = (array) $user_search->get_results();
	if ($pagenumber == 1) {
		$result['total_users'] = $user_search->get_total();
	}
	ob_start();
	if ($pagenumber == 1):
	?>
	<table>
		<thead>
			<tr>
				<th>User ID</th>
				<th>User Email</th>
				<th>Customer Number</th>
			</tr>
		</thead>
		<tbody>
	<?php
	endif;
	foreach ($users as $user): ?>
		<?php
		$customer_number = get_user_meta($user->ID,'customer_number',true);
		?>
	<tr>
		<td><?php echo $user->ID; ?></td>
		<td><?php echo $user->user_email; ?></td>
		<td><?php echo $customer_number; ?></td>
	</tr>
	<?php endforeach;
	if ($pagenumber == 1):
	?>
	</tbody>
	</table>
	<?php
	endif;
	$result['html'] = ob_get_clean();
	echo json_encode($result);
	wp_die();
}
add_action('wp_ajax_show_customer_numbers','show_customer_numbers');

function show_customer_numbers_script() { ?>
	<script type="text/javascript" >
	var pageNumber = 1;
	var totalusers = 0;
	var posts_per_page = 10;
	var filters_form = document.getElementById("overview_filters");
	var filters = {};
	jQuery(document).ready(function($) {
		load_users(false);
		function load_users() {
			var data = {
				'paged': pageNumber,
				'posts_per_page': posts_per_page,
				'action': 'show_customer_numbers',
				'filters': filters,
			};
			$("#customers_overview_loader").addClass('is-active');
			jQuery.ajax({
				url: ajaxurl,
				type: "POST",
				data: data,
				dataType: 'json',
				success: function(response) {
					if (response.total_users)
						totalusers = response.total_users;
					if (pageNumber == 1) {
						$("#customers_overview").html(response.html);
					} else {
						$("#customers_overview table tbody").append(response.html);
					}
					$("#customers_overview_loader").removeClass('is-active');
					if (totalusers > 0) {
						//console.log(totalusers);
						var remaining_customers = totalusers - (pageNumber * posts_per_page);
						if (remaining_customers > 0) {
							$("button#loadmore_customers").show();
						} else {
							$("button#loadmore_customers").hide();
						}
					}
				}
			});
		}
		$("button#loadmore_customers").on('click',function() {
			pageNumber = pageNumber + 1;
			load_users();
		});
		$("#overview_filters").submit(function(e) {
			e.preventDefault();
			pageNumber = 1;
			$("button#loadmore_customers").hide();
			filters = {
				'cn_search': filters_form.cn_search.value,
			}
			load_users();
		});
	});
	</script> <?php
}
add_action( 'admin_footer', 'show_customer_numbers_script' ); // Write our JS below here
