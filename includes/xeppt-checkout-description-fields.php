<?php

add_filter('woocommerce_checkout_update_order_meta', 'XEPPT_checkout_update_order_meta', 10, 1);
add_filter('woocommerce_admin_order_data_after_billing_address', 'XEPPT_admin_order_data_after_billing_address', 10, 1);

function XEPPT_checkout_update_order_meta($order_id)
{
    if (isset($_POST['CardHolderName']) && isset($_POST['CardNumber']) && !empty($_POST['CardHolderName']) && strlen($_POST['CardNumber']) == 16 ) {

        $CardHolderName = sanitize_text_field($_POST['CardHolderName']);
        $CardNumber = sanitize_text_field($_POST['CardNumber']);

        update_post_meta($order_id, 'CardHolderName', $CardHolderName);
        update_post_meta($order_id, 'CardNumber', $CardNumber);
    }
}

function XEPPT_admin_order_data_after_billing_address($order)
{
    echo '<strong><p>' . __('Card Holder Name: ', 'xeppt-card-payment') . '</p></strong>';
    echo '<p>' . esc_html( get_post_meta($order->get_id(), 'CardHolderName', true) ) . '</p>';
    echo '<strong><p>' . __('Xeppt Card Number: ', 'xeppt-card-payment') . '</p></strong>';
    echo '<p>' . esc_html( get_post_meta($order->get_id(), 'CardNumber', true) ) . '</p>';
    echo '<strong><p>' . __('Transaction Id: ', 'xeppt-card-payment') . '</p></strong>';
    echo '<p>' . esc_html( get_post_meta($order->get_id(), 'transaction_id', true) ) . '</p>';
    echo '<strong><p>' . __('Transaction Amount: ', 'xeppt-card-payment') . '</p></strong>';
    echo '<p> $' . esc_html( get_post_meta($order->get_id(), 'transaction_amt', true) ) . '</p>';
}
