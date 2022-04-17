jQuery(document).ready(function($) {
  $(".woocommerce-form-register").on('change input',function() {
    var form = $(this)[0];
    var customerNumberCheck = form.elements["customer_number_present"].checked;
    if (customerNumberCheck) {
      $(".customer-number-container").show();
      $("#social_media_source_field").hide();
    } else {
      $(".customer-number-container").hide();
      $("#social_media_source_field").show();
    }
    $(".customer-number-error").hide();
  });
  $(".woocommerce-form-register").on('submit',function(e) {
    var form = $(this)[0];
    var customerNumber = form.elements["customer_number"].value;
    var customerNumberCheck = form.elements["customer_number_present"].checked;
    if ((customerNumber == "" || customerNumber == null) && customerNumberCheck) {
      e.preventDefault();
      $(".customer-number-error").show();
    }
  });
});
