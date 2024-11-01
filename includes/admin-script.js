function disable_test() {
    document.getElementById('woocommerce_xeppt_test_merchant_id').readOnly = true;
    document.getElementById('woocommerce_xeppt_test_password').readOnly = true;
    document.getElementById('woocommerce_xeppt_test_api_url').readOnly = true;
    document.getElementById('woocommerce_xeppt_test_secret_key').readOnly = true;

    document.getElementById('woocommerce_xeppt_test_merchant_id').style.backgroundColor = "#D6D6D6";
    document.getElementById('woocommerce_xeppt_test_password').style.backgroundColor = "#D6D6D6";
    document.getElementById('woocommerce_xeppt_test_api_url').style.backgroundColor = "#D6D6D6";
    document.getElementById('woocommerce_xeppt_test_secret_key').style.backgroundColor = "#D6D6D6";
}

function disable_production() {
    document.getElementById('woocommerce_xeppt_merchant_id').readOnly = true;
    document.getElementById('woocommerce_xeppt_password').readOnly = true;
    document.getElementById('woocommerce_xeppt_production_api_url').readOnly = true;
    document.getElementById('woocommerce_xeppt_production_secret_key').readOnly = true;

    document.getElementById('woocommerce_xeppt_merchant_id').style.backgroundColor = "#D6D6D6";
    document.getElementById('woocommerce_xeppt_password').style.backgroundColor = "#D6D6D6";
    document.getElementById('woocommerce_xeppt_production_api_url').style.backgroundColor = "#D6D6D6";
    document.getElementById('woocommerce_xeppt_production_secret_key').style.backgroundColor = "#D6D6D6";
}

function checkbox_check() {
    var checkbox = document.getElementById('woocommerce_xeppt_test_mode');
    if (checkbox.checked) {
        disable_production();
    } else {
        disable_test();
    }
}

setTimeout(function () {
    checkbox_check();
}, 1000);