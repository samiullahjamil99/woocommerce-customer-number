jQuery(document).ready(function($) {
  $(".woocommerce-form-register").on('change input',function() {
    var form = $(this)[0];
    var customerNumberCheck = form.elements["customer_number_present"].checked;
    var customerNumber = form.elements["customer_number"].value;
    if (customerNumberCheck) {
      $(".customer-number-container").show();
      if (customerNumber == "" || customerNumber == null) {
        $("button.woocommerce-form-register__submit").prop("disabled",true);
      } else {
        $("button.woocommerce-form-register__submit").prop("disabled",false);
      }
    } else {
      $(".customer-number-container").hide();
      $("button.woocommerce-form-register__submit").prop("disabled",false);
    }
  });
});
