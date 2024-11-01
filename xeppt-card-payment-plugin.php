<?php
/*
Plugin Name: XEPPT Card Payment
Description: This plugin allows to use xeppt cards for payments
Version: 2.0
Author: XEPPT Inc.
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: xeppt-card-payment
*
* Class WC_Gateway_Xeppt file.
*
* @package WooCommerce\Xeppt
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * XEPPT Card Payment Gateway.
 *
 * Provides a XEPPT Card Payment Gateway.
 *
 * @class       WC_Gateway_Xeppt
 * @extends     WC_Payment_Gateway
 * @version     2.0
 * @package     WooCommerce\Classes\Payment
 */


// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;

// Create Table on Plugin Activation
register_activation_hook(__FILE__, 'XEPPT_create_table');

function XEPPT_create_table()
{
    global $wpdb;
    $create_table_query = "
            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}xeppt_error_logs` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` text NOT NULL,
              `request` text NOT NULL,
              `response` text NOT NULL,
              `err_message` text NOT NULL,
              `date_added` DATETIME NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (id)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
    ";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($create_table_query);
}

// Action hook to add the error menu on admin panel
add_action('admin_menu', 'XEPPT_error_menu');

function XEPPT_error_menu()
{
    add_menu_page(
        'XEPPT Error Logs',
        'XEPPT Error Logs',
        '8',
        'error_logs',
        'XEPPT_page_callback_function',
        'dashicons-media-spreadsheet'
    );
}

function XEPPT_page_callback_function()
{
    require_once plugin_dir_path(__FILE__) . '/includes/xeppt-error-logs.php';
}

// Action hook to load custom JavaScript in admin
add_action('admin_enqueue_scripts', 'XEPPT_admin_script');

function XEPPT_admin_script()
{
    wp_enqueue_script('ava-test-js', plugins_url('/includes/admin-script.js', __FILE__));
}

// Action hook to redirect to secure web link
add_action('wp_enqueue_scripts', 'XEPPT_redirect_to_secure');

function XEPPT_redirect_to_secure()
{
    wp_enqueue_script('redirect-scr-js', plugins_url('/includes/redirect-script.js', __FILE__));
}

// Action hook to include required files
add_action('plugins_loaded', 'XEPPT_payment_init', 11);

function XEPPT_payment_init()
{
    if (class_exists('WC_Payment_Gateway')) {
        require_once plugin_dir_path(__FILE__) . '/includes/class-wc-payment-gateway-xeppt.php';
        require_once plugin_dir_path(__FILE__) . '/includes/xeppt-checkout-description-fields.php';
    }
}

add_filter('woocommerce_checkout_get_value', '__return_empty_string', 10);

add_filter('woocommerce_thankyou_order_received_text', 'XEPPT_change_order_received_text', 10, 2);

/**
 * function to show transaction id after successful order
 *
 * @param string $str
 * @param object $order Order.
 * @return string
 */
function XEPPT_change_order_received_text($str, $order)
{
    $meta_value = $order->get_meta('transaction_id');

    $new_str = $str . '<br> Your Transaction Id is: <strong>' . $meta_value . '</strong>';
    return $new_str;
}

add_filter('woocommerce_payment_gateways', 'XEPPT_add_to_payment_gateway');

/**
 * function to add XEPPT to payment gateway
 *
 * @param array $gateways Gateway Object.
 * @return array
 */
function XEPPT_add_to_payment_gateway($gateways)
{
    $gateways[] = 'WC_Gateway_Xeppt';
    return $gateways;
}

// plugin deactivation hook
register_deactivation_hook(__FILE__, 'XEPPT_deactivation_function');

// callback function to drop table
function XEPPT_deactivation_function()
{
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS " . XEPPT_my_table_name());
}

// returns table name
function XEPPT_my_table_name()
{
    global $wpdb;
    return $wpdb->prefix . "xeppt_error_logs";
}
