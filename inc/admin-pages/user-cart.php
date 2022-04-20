<?php
include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
include_once WC_ABSPATH . 'includes/class-wc-cart.php';
include_once WC_ABSPATH . 'includes/class-wc-session-handler.php';
wc_load_cart();
if ($_GET['user_id']) {
  $user_id = intval($_GET['user_id']);
  $product_id = 44;
  $variation_id = 0;
  $variation = array();
  $quantity = 2;
  $product_data = wc_get_product($product_id);
  $cart = new WC_Cart();
  $cart_id = $cart->generate_cart_id($product_id);
  $saved_cart_meta = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
  if ( isset( $saved_cart_meta['cart'] ) ) {
    print_r($saved_cart_meta);
    $saved_cart_meta['cart'][$cart_id] = array(
      'key'          => $cart_id,
      'product_id'   => $product_id,
      'variation_id' => $variation_id,
      'variation'    => $variation,
      'quantity'     => $quantity,
      'data_hash'    => wc_get_cart_item_data_hash( $product_data ),
    );
    update_user_meta(
				$user_id,
				'_woocommerce_persistent_cart_' . get_current_blog_id(),
				$saved_cart_meta
		);
    update_user_meta(
				$user_id,
				'_force_logout',
				'yes',
		);
    print_r($saved_cart_meta);
  }
}
