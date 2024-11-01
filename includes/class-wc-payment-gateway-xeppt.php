<?php

/**
 * Xeppt Card Payment Gateway.
 *
 * Provides a Xeppt Card Payment Gateway.
 *
 * @class       WC_Gateway_Xeppt
 * @extends     WC_Payment_Gateway
 * @version     2.0
 * @package     WooCommerce\Classes\Payment
 */

class WC_Gateway_Xeppt extends WC_Payment_Gateway
{

    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {
        // Setup general properties.
        $this->setup_properties();

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Get settings.
        $this->enabled         = esc_html($this->get_option('enabled'));
        $this->title           = esc_html($this->get_option('title'));
        $this->description     = esc_html($this->get_option('description'));
        $this->instructions    = esc_html($this->get_option('instructions'));
        $this->merchant_id     = esc_html($this->get_option('merchant_id'));
        $this->password     = esc_html($this->get_option('password'));
        $this->test_mode    = esc_html($this->get_option('test_mode'));
        $this->test_merchant_id     = esc_html($this->get_option('test_merchant_id'));
        $this->test_password     = esc_html($this->get_option('test_password'));
        $this->test_secret_key = esc_html($this->get_option('test_secret_key'));
        $this->test_api_url = esc_html($this->get_option('test_api_url'));
        $this->production_api_url = esc_html($this->get_option('production_api_url'));
        $this->production_secret_key      = esc_html($this->get_option('production_secret_key'));
        $this->supports = array('products', 'refunds');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
        add_filter('woocommerce_payment_complete_order_status', array($this, 'change_payment_complete_order_status'), 10, 3);

        // Customer Emails
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
    }

    /**
     * Setup general properties for the gateway.
     */
    protected function setup_properties()
    {
        $this->id = 'xeppt';
        $this->method_title       = __('XEPPT Card Payment', 'xeppt-card-payment');
        $this->method_description = __('Have your customers pay with XEPPT card upon delivery.', 'xeppt-card-payment');
        $this->has_fields         = false;
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled'            => array(
                'title'       => __('Enable/Disable', 'xeppt-card-payment'),
                'label'       => __('Enable XEPPT Card Payment', 'xeppt-card-payment'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no',
            ),
            'title'              => array(
                'title'       => __('Title', 'xeppt-card-payment'),
                'type'        => 'text',
                'description' => __('Payment method description that the customer will see on your checkout.', 'xeppt-card-payment'),
                'default'     => __('XEPPT Card Payment', 'xeppt-card-payment'),
                'desc_tip'    => true,
            ),
            'description'        => array(
                'title'       => __('Description', 'xeppt-card-payment'),
                'type'        => 'textarea',
                'description' => __('Payment method description that the customer will see on your website.', 'xeppt-card-payment'),
                'default'     => __('Pay with XEPPT card to allow for the delivery to be made.', 'xeppt-card-payment'),
                'desc_tip'    => true,
            ),
            'instructions'       => array(
                'title'       => __('Instructions', 'xeppt-card-payment'),
                'type'        => 'textarea',
                'description' => __('Instructions that will be added to the thank you page.', 'xeppt-card-payment'),
                'default'     => __('Paid with XEPPT card.', 'xeppt-card-payment'),
                'desc_tip'    => true,
            ),
            'merchant_id'       => array(
                'title'       => __('Production Merchant ID', 'xeppt-card-payment'),
                'type'        => 'text',
                'description' => __('Merchant ID provided for the XEPPT card payments.', 'xeppt-card-payment'),
                'default'     => __('', 'xeppt-card-payment'),
                'desc_tip'    => true,
            ),
            'password'       => array(
                'title'       => __('Production Password', 'xeppt-card-payment'),
                'type'        => 'password',
                'description' => __('Password provided with the merchant Id.', 'xeppt-card-payment'),
                'default'     => __('', 'xeppt-card-payment'),
                'desc_tip'    => true,
            ),
            'production_api_url'       => array(
                'title'       => __('Production API URL', 'xeppt-card-payment'),
                'type'        => 'text',
                'description' => __('XEPPT Payment API URL provided for the XEPPT payments.', 'xeppt-card-payment'),
                'default'     => __('', 'xeppt-card-payment'),
                'desc_tip'    => true,
            ),
            'production_secret_key'       => array(
                'title'       => __('Production Secret Key', 'xeppt-card-payment'),
                'type'        => 'password',
                'description' => __('Secret key provided for the XEPPT card payments.', 'xeppt-card-payment'),
                'default'     => __('', 'xeppt-card-payment'),
                'desc_tip'    => true,
            ),
            'test_mode' => array(
                'title'       => __('Test mode', 'xeppt-card-payment'),
                'label'       => __('Enable Test Mode', 'xeppt-card-payment'),
                'type'        => 'checkbox',
                'description' => __('Place the payment gateway in test mode using test API keys.', 'xeppt-card-payment'),
                'default'     => 'yes',
                'desc_tip'    => true,
            ),
            'test_merchant_id'       => array(
                'title'       => __('Test Merchant ID', 'xeppt-card-payment'),
                'type'        => 'text',
                'description' => __('Test Merchant ID provided for the XEPPT card payments.', 'xeppt-card-payment'),
                'default'     => __('', 'xeppt-card-payment'),
                'desc_tip'    => true,
            ),
            'test_password'       => array(
                'title'       => __('Test Password', 'xeppt-card-payment'),
                'type'        => 'password',
                'description' => __('Test Password provided with the merchant Id.', 'xeppt-card-payment'),
                'default'     => __('', 'xeppt-card-payment'),
                'desc_tip'    => true,
            ),
            'test_api_url'       => array(
                'title'       => __('Test API URL', 'xeppt-card-payment'),
                'type'        => 'text',
                'description' => __('Test XEPPT Payment API URL provided for the XEPPT payments.', 'xeppt-card-payment'),
                'default'     => __('', 'xeppt-card-payment'),
                'desc_tip'    => true,
            ),
            'test_secret_key'       => array(
                'title'       => __('Test Secret Key', 'xeppt-card-payment'),
                'type'        => 'password',
                'description' => __('Test Secret key provided for testing the XEPPT card payments.', 'xeppt-card-payment'),
                'default'     => __('', 'xeppt-card-payment'),
                'desc_tip'    => true,
            ),
        );
        //
    }


    /**
     * Initialize Payment Form Fields.
     *
     * @return bool
     */

    public function payment_fields()
    {

        if ($this->description) {
            if ('yes' === $this->test_mode) {
                $this->description .= '<br><small>Test mode is enabled. You can use the demo XEPPT card details to test it.</small>';
            }
            echo wpautop(wp_kses_post($this->description));
        }

?>

        <fieldset id="wc-<?php echo esc_attr($this->id); ?>-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent; border:none; font-family: -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Helvetica, sans-serif">

            <?php do_action('woocommerce_credit_card_form_start', $this->id); ?>

            <div class="form-row form-row-wide" style="padding-top: 1rem;">
                <label>Card Holder Name <span class="required">*</span></label>
                <input id="xeppt_chname" type="text" name="CardHolderName" autocomplete="off" style="background: #fff; border-style: solid; min-height: 35px; width: 100%; border-color: #dcd7ca; color: #000;">
            </div>
            <div class="form-row form-row-wide" style="padding-top: 1rem;">
                <label>XEPPT Card Number <span class="required">*</span></label>
                <input id="xeppt_ccno" type="text" name="CardNumber" autocomplete="off" style="background: #fff; border-style: solid; min-height: 35px; width: 100%; border-color: #dcd7ca; color: #000;">
            </div>

            <div class="clear"></div>

            <?php do_action('woocommerce_credit_card_form_end', $this->id); ?>

            <div class="clear"></div>

        </fieldset>

<?php

    }

    /**
     * Validate Custom form fields
     *
     * @return bool
     */

    public function validate_fields()
    {

        if (empty($_POST['CardHolderName'])) {
            wc_add_notice('Please enter Card Holder Name and try again', 'error');
            return false;
        }
        if (empty($_POST['CardNumber']) || strlen($_POST['CardNumber']) != 16) {
            wc_add_notice('Please enter correct XEPPT card number and try again', 'error');
            return false;
        }
        return true;
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id Order ID.
     * @return array
     */
    public function process_payment($order_id)
    {
        global $woocommerce;

        // get order detailes
        $order = wc_get_order($order_id);

        // Array with arguments for Get token API
        $args = array(
            'body'        => array(),
            'timeout'     => '45',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(),
            'cookies'     => array(),
        );

        if ('yes' === $this->test_mode) {
            $response = wp_remote_post('https://' . $this->test_api_url . '/api/Home/AccessToken?merchantId=' . $this->test_merchant_id . '&password=' . $this->test_password . '&secretkey=' . $this->test_secret_key . '', $args);
        } else {
            $response = wp_remote_post('https://' . $this->production_api_url . '/api/Home/AccessToken?merchantId=' . $this->merchant_id . '&password=' . $this->password . '&secretkey=' . $this->production_secret_key . '', $args);
        }

        if (!is_wp_error($response)) {
            $token = '';
            $body = json_decode($response['body'], true);

            if ($body['code'] == 200) {
                $token = $body['data']['access_token'];
            } else if ($body['code'] == 400) {
                wc_add_notice('Invalid credentials, Please check your XEPPT card payment settings', 'error');
                return;
            } else {
                wc_add_notice('Oops! Something Went Wrong, Please try again', 'error');
                return;
            }

            // Main API interaction for Payment
            $order_data = $order->get_data();

            $CardHolderName = sanitize_text_field($_POST['CardHolderName']);
            $CardNumber = sanitize_text_field($_POST['CardNumber']);

            $body2 = array(
                'MerchantId'    => (int)$this->test_merchant_id,
                'CardHolderName'   => $CardHolderName,
                'CardNumber'   => $CardNumber,
                'Currency'   => $order_data['currency'],
                'Amount'   => (float)$order_data['total'],
                'BillingInfo' => array(
                    'Name' => $order_data['billing']['first_name'] . " " . $order_data['billing']['last_name'],
                    'Address' => $order_data['billing']['address_1'] . " " . $order_data['billing']['address_2'],
                    'City' => $order_data['billing']['city'],
                    'Province' => $order_data['billing']['state'],
                    'PostalCode' => $order_data['billing']['postcode'],
                    'Country' => $order_data['billing']['country'],
                    'OrderId' => strval($order_data['id']),
                ),
            );

            $body_json = json_encode($body2);

            $args2 = array(
                'method' => 'POST',
                'body'        => $body_json,
                'timeout'     => '45',
                'redirection' => '5',
                'httpversion' => '1.0',
                'sslverify' => false,
                'blocking'    => true,
                'headers'     => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ),
                'cookies'     => array(),
            );

            // Check for test mode activation
            if ('yes' === $this->test_mode) {
                $response2 = wp_remote_post('https://' . $this->test_api_url . '/Api/Home/CreatePaymentCharge', $args2);
            } else {
                $response2 = wp_remote_post('https://' . $this->production_api_url . '/Api/Home/CreatePaymentCharge', $args2);
            }

            if (!is_wp_error($response2)) {

                $body2 = json_decode($response2['body'], true);

                if ($body2['code'] == 200 && !empty($body2['data']['transRefNumber'])) {

                    // received the payment
                    $order->payment_complete();
                    wc_reduce_stock_levels($order_id);

                    // notes to customer
                    $order->add_order_note('Hey, your order is paid! Thank you!', true);

                    // empty cart
                    $woocommerce->cart->empty_cart();
                    $order->update_meta_data('transaction_id', $body2['data']['transRefNumber']);
                    $order->update_meta_data('transaction_amt', $body2['data']['transAmount']);
                    $order->save();

                    // redirect to the thank you page
                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order)
                    );
                } elseif ($body2['code'] == 400) {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'xeppt_error_logs';

                    $request_json1 = json_decode($body_json, true);
                    $request_json1['CardNumber'] = substr($request_json1['CardNumber'], 0, 4) . str_repeat('X', strlen($request_json1['CardNumber']) - 8) . substr($request_json1['CardNumber'], -4);
                    $request_json = json_encode($request_json1);

                    $response_json = json_encode($response2);
                    $wpdb->insert($table_name, array('order_id' => $order_data['id'], 'request' => $request_json, 'response' => $response_json, 'err_message' => $response2['response']['message']));

                    wc_add_notice($body2['message'], 'error');
                    return;
                } else {

                    global $wpdb;
                    $table_name = $wpdb->prefix . 'xeppt_error_logs';

                    $request_json1 = json_decode($body_json, true);
                    $request_json1['CardNumber'] = substr($request_json1['CardNumber'], 0, 4) . str_repeat('X', strlen($request_json1['CardNumber']) - 8) . substr($request_json1['CardNumber'], -4);
                    $request_json = json_encode($request_json1);

                    $response_json = json_encode($response2);
                    $wpdb->insert($table_name, array('order_id' => $order_data['id'], 'request' => $request_json, 'response' => $response_json, 'err_message' => $response2['response']['message']));

                    wc_add_notice('Oops! Something Went Wrong, Please try again', 'error');
                    return;
                }
            } else {
                wc_add_notice('Please check settings and try again', 'error');
                return;
            }
        } else {
            wc_add_notice('Please check payment settings and try again', 'error');
            return;
        }
    }

    /**
     * Output for the order received page.
     */
    public function thankyou_page()
    {
        if ($this->instructions) {
            echo wp_kses_post(wpautop(wptexturize($this->instructions)));
        }
    }

    /**
     * Change payment complete order status to completed for xeppt orders.
     *
     * @since  3.1.0
     * @param  string         $status Current order status.
     * @param  int            $order_id Order ID.
     * @param  WC_Order|false $order Order object.
     * @return string
     */
    public function change_payment_complete_order_status($status, $order_id = 0, $order = false)
    {
        if ($order && 'xeppt' === $order->get_payment_method()) {
            $status = 'completed';
        }
        return $status;
    }

    /**
     * Add content to the WC emails.
     *
     * @param WC_Order $order Order object.
     * @param bool     $sent_to_admin  Sent to admin.
     * @param bool     $plain_text Email format: plain text or HTML.
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if ($this->instructions && !$sent_to_admin && $this->id === $order->get_payment_method()) {
            echo wp_kses_post(wpautop(wptexturize($this->instructions)) . PHP_EOL);
        }
    }

    /**
     * Process the refund process and return the result.
     *
     * @param int $order_id Order ID.
     * @param string $amount Amount.
     * @param string $reason Refund Reason.
     * @return bool
     */
    public function process_refund($order_id, $amount = null, $reason = '')
    {
        if (function_exists('wc_get_order')) {
            $order = wc_get_order($order_id);
        } else {
            $order = new WC_Order($order_id);
        }

        if (!$this->can_refund_order($order)) {
            return false;
        }

        $success = $this->create_refund($order, $amount, $order_id);

        if ($success) {
            $order->add_order_note(__("Refund of amount " . $amount . " sent to gateway. Reason: " . $reason, 'xeppt-card-payment'));
            return true;
        }

        $order->add_order_note(__("Failed to send refund of amount " . $amount . " to gateway." . $reason, 'xeppt-card-payment'));
        return false;
    }
    
    /**
     * Create the refund and return the result.
     *
     * @param mixed $order
     * @param string $amount Amount.
     * @param int $order_id Order ID.
     * @return bool
     */
    public function create_refund($order, $amount, $order_id)
    {
        $args = array(
            'body'        => array(),
            'timeout'     => '45',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(),
            'cookies'     => array(),
        );

        if ('yes' === $this->test_mode) {
            $response = wp_remote_post('https://' . $this->test_api_url . '/Api/Home/AccessToken?merchantId=' . $this->test_merchant_id . '&password=' . $this->test_password . '&secretkey=' . $this->test_secret_key . '', $args);
        } else {
            $response = wp_remote_post('https://' . $this->production_api_url . '/Api/Home/AccessToken?merchantId=' . $this->merchant_id . '&password=' . $this->password . '&secretkey=' . $this->production_secret_key . '', $args);
        }

        if (!is_wp_error($response)) {
            $token = '';
            $body = json_decode($response['body'], true);

            if ($body['code'] == 200) {
                $token = $body['data']['access_token'];
            } else if ($body['code'] == 400) {
                wc_add_notice('Invalid credentials, Please check your XEPPT card payment settings', 'error');
                return;
            } else {
                wc_add_notice('Oops! Something Went Wrong, Please try again', 'error');
                return;
            }

            // Preparing data for API
            $data = array(
                "ReferenceTransactionId" => $order->get_meta('transaction_id'),
                "MerchantId" =>  (int)$this->test_merchant_id,
                "Currency" => "USD",
                "Amount" => (float)$amount
            );

            $json_array_encoded = json_encode($data);

            $arg = array(
                'method'      => 'POST',
                'body'        => $json_array_encoded,
                'timeout'     => '45',
                'redirection' => '5',
                'httpversion' => '1.0',
                'sslverify'   => false,
                'blocking'    => true,
                'headers'     => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ),
                'cookies'     => array(),
            );

            // Refund API URL
            if ('yes' === $this->test_mode) {
                $response = wp_remote_post('https://' . $this->test_api_url . '/api/Home/RefundTransaction', $arg);
            } else {
                $response = wp_remote_post('https://' . $this->production_api_url . '/api/Home/RefundTransaction', $arg);
            }

            $refund_response = json_decode($response["body"], true);

            if (is_wp_error($response) || $response === false) {
                $order->add_order_note(__("Error in API Call", 'xeppt-card-payment'));
                return false;
            } elseif ($refund_response['code'] == 200) {
                $order->add_order_note(__("$" . $refund_response['data']['amount'] . " Refunded Successfully with Transaction ID: " . $refund_response['data']['transactionId'], 'xeppt-card-payment'));
                return true;
            } elseif ($refund_response['code'] == 400) {
                $order->add_order_note(__($refund_response['message'], 'xeppt-card-payment'));
                return false;
            } else {
                $order->add_order_note(__("Oops! Something Went Wrong", 'xeppt-card-payment'));
                return false;
            }

            return false;
        } else {
            wc_add_notice('Please check payment settings and try again', 'error');
            return;
        }
    } // Refund End

} // end class
