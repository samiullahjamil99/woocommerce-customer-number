<?php
if ($_GET['user_id']) {
  $user_id = intval($_GET['user_id']);
  $args = array(
    'visibility' => 'hidden',
    'limit' => -1,
  );
  $products = wc_get_products( $args );
  ?>
  <form method="post">
    <select name="assign_product">
      <option value="">Select Product</option>
      <?php
      foreach ($products as $product): ?>
        <option value="<?php echo $product->get_id(); ?>"><?php echo $product->get_name(); ?></option>
      <?php
      endforeach;
      ?>
    </select>
    <input type="submit" value="Assign Product">
  </form>
  <?php
  if (isset($_POST['assign_product']) && !empty($_POST['assign_product'])) {
    include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
    include_once WC_ABSPATH . 'includes/class-wc-cart.php';
    include_once WC_ABSPATH . 'includes/class-wc-session-handler.php';
    wc_load_cart();
    $product_id = intval($_POST['assign_product']);
    $product_data = wc_get_product($product_id);
    if ($product_data) {
      $variation_id = 0;
      $variation = array();
      $quantity = 1;
      $cart = new WC_Cart();
      $cart_id = $cart->generate_cart_id($product_id);
      $saved_cart_meta = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
      if ( isset( $saved_cart_meta['cart'] ) ) {
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
        echo "<p>Assigned Product</p>";
      }
    }
  }
}
