<?php
class ControllerExtensionPaymentViabill extends Controller {    
    /**
     * Index method renders the ViaBill checkout confirmation page with all the required template data.
     *
     * @return string HTML output from the view
     */
    public function index(): string {
        // Load the language file containing all necessary text strings.
        $this->load->language('extension/payment/viabill');
        
        // Prepare template data
        $data = [];
        
        // Logo – you can change the filename or make it a config value.
        $data['viabill_logo'] = 'via_logo.png';
        
        // Text labels from the language file
        $data['text_description'] = $this->language->get('text_description');
        $data['text_instruction'] = $this->language->get('text_instruction');
        $data['text_payment'] = $this->language->get('text_payment');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_test_mode_warning'] = $this->language->get('text_test_mode_warning');
        $data['text_redirecting'] = $this->language->get('text_redirecting');
        
        // Retrieve order details (assuming order_id is stored in session)
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/viabill');   

        if (isset($this->session->data['order_id'])) {
            $order_id = $this->session->data['order_id'];
            $order_info = $this->model_checkout_order->getOrder($order_id);
            
            // Populate order-related variables for the view.
            $data['order_id'] = $order_id;
            $data['amount'] = $order_info['total'];
            $data['currency'] = $order_info['currency_code'];
        } else {
            $data['order_id'] = '';
            $data['amount'] = 0;
            $data['currency'] = '';
        }
        
        // Check if the module is operating in test mode
        $data['test_mode'] = $this->config->get('payment_viabill_test') ? true : false;
        
        // Define the action URL to which the confirm button will send the AJAX request.
        // This should point to your "confirm" method in this controller.
        $data['action'] = $this->url->link('extension/payment/viabill/confirm', '', true);
        
        // Render and return the view template
        return $this->load->view('extension/payment/viabill', $data);
    }
    
    /**
     * Confirm the order and initiate ViaBill payment.
     * This function is typically called when the customer clicks "Confirm Order".
     * It will send the checkout-authorize request to ViaBill and handle the redirect.
     */
    public function confirm(): void {
                        
        // Check if this is the correct payment method
        if (!isset($this->session->data['payment_method']['code']) || 
            $this->session->data['payment_method']['code'] != 'viabill') {
            $json['error'] = 'Invalid payment method';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Load language class
        $this->load->language('extension/payment/viabill');
        
        // Load necessary models
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/viabill');
        
        // Load helper classes
        $this->loadViabillHelpers(); 
                
        $registry = $this->registry;
        ViaBillServices::setRegistry($registry);
        
        // Get order info
        $order_id = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($order_id);
        if (!$order_info) {
            // If order not found (should not happen at this stage), redirect to checkout
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
            return;
        }        
        
        // Determine transaction type (auth or auth+capture) from config
        $authOnly = ($this->config->get('payment_viabill_transaction_mode') == 'authorize');
        
        // Build request data to send to ViaBill
        $apiKey    = $this->config->get('payment_viabill_api_key');
        $secret    = $this->config->get('payment_viabill_secret_key');
        $currency  = $order_info['currency_code'];
        $amount    = number_format($order_info['total'], 2, '.', '');  // total amount formatted as e.g. 100.00
        $transactionId = 'via-' . $order_id . '-' . substr(md5(rand()), 0, 6);  // Generate a unique transaction reference (via-<orderid>-<random>)
        
        // Prepare customer info for customParams (e.g., name, email, phone)
        $address = empty($order_info['payment_address_1'])?$order_info['shipping_address_1']:$order_info['payment_address_1'];
        $city = empty($order_info['payment_city'])?$order_info['shipping_city']:$order_info['payment_city'];
        $postalCode = empty($order_info['payment_postcode'])?$order_info['shipping_postcode']:$order_info['payment_postcode'];
        $country = !empty($order_info['shipping_iso_code_2'])?strtolower($order_info['shipping_iso_code_2']):'';

        $customer = [            
            'email'=> $order_info['email'],
            'phoneNumber' => $order_info['telephone'],            
            'firstName' => $order_info['firstname'],
            'lastName' => $order_info['lastname'],
            'fullName' => trim($order_info['firstname'].' '.$order_info['lastname']),
            'address' => $address,
            'city' => $city,
            'postalCode' => $postalCode,
            'country' => $country
        ];        

        // Prepare cart info for cartParams (e.g., products in the order)
        $products = $this->cart->getProducts();
        $cartItems = [];
        foreach ($products as $product) {
            $cartItems[] = [
                'id'       => $product['product_id'],
                'name'     => $product['name'],
                'quantity' => $product['quantity'],
                'price'    => number_format($product['price'], 2, '.', '')
            ];
        }
        $cart = ['items' => $cartItems];
                
        // Determine environment
        $testMode = $this->config->get('payment_viabill_test') ? ViaBillConstants::TEST_MODE_ON : ViaBillConstants::TEST_MODE_OFF;
        
        // Try-Before-You-Buy flag (assuming not used by default)
        $tbybValue = ViaBillConstants::TBYB_OFF;          
        $platformValue = ViaBillConstants::AFFILIATE;  // Identify the platform (ViaBill may expect the platform name)

        // URLs
        $success_url = $this->url->link('checkout/success', '', true);
        $cancel_url = $this->url->link('extension/payment/viabill/cancel', 'order_id=' . $order_id, true);
        $callback_url = $this->url->link('extension/payment/viabill/callback', '', true);

        // Compute MD5 checksum for integrity (using a combination of fields and secret)
        $signature_data = [
            $apiKey,
            $amount,
            $currency,
            $transactionId,
            $order_id,
            $success_url,
            $cancel_url,
            $secret,
        ];
                
        if (ViaBillConstants::PROTOCOL == '3.0') {
            $checksum_key = 'md5check';
            $checksum_value = md5(implode('#', $signature_data));
        } else {
            $checksum_key = '"sha256check';
            $checksum_value = hash('sha256', implode('#', $signature_data));
        }
        
        // Assemble data for POST to ViaBill's checkout-authorize API
        $request_data = [
            'protocol'     => ViaBillConstants::PROTOCOL, 
            'order_number' => $order_id,
            'apikey'       => $apiKey,
            'secret'       => $secret,
            'transaction'  => $transactionId,
            'amount'       => $amount,
            'currency'     => $currency,
            'success_url'  => $success_url,
            'cancel_url'   => $cancel_url,
            'callback_url' => $callback_url,
            'test'         => $testMode,
            'customParams' => $customer,
            // 'cartParams'   => json_encode($cart),
            $checksum_key  => $checksum_value,
            'tbyb'         => $tbybValue,            
        ];        
        
        // Before redirecting, add an initial order history entry with "Pending" status (or a custom status like "Awaiting Payment")
        $pendingStatusId = $this->config->get('config_order_status_id');  // default store pending status
        $this->model_checkout_order->addOrderHistory($order_id, $pendingStatusId, 'ViaBill payment initiated. Transaction: ' . $transactionId, false);
        
        $checkout_endpoint = ViaBillServices::getEndPointData('checkout', $request_data);                
                
        // Send the checkout-authorize request to ViaBill
        $viaResponse = ViaBillHelper::sendApiRequest($checkout_endpoint['endpoint'], $checkout_endpoint['data']);        

        if (!empty($viaResponse['redirect_url'])) {
            // Store the generated transaction ID in our database for reference (log it)
            $this->db->query("INSERT INTO `" . DB_PREFIX . "viabill_transaction` SET order_id = " . (int)$order_id . ", transaction_id = '" . $this->db->escape($transactionId) . "', amount = '" . $this->db->escape($amount) . "', currency = '" . $this->db->escape($currency) . "', status = 'INITIATED'");
            // Redirect the customer to ViaBill's payment page
            // $this->response->redirect($viaResponse['redirect_url']);
                
            $json = ['redirect' => $viaResponse['redirect_url']];                                    
            // Always set proper headers
            $this->response->addHeader('Content-Type: application/json');            
            // Make sure to encode the JSON properly
            $this->response->setOutput(json_encode($json));
        } else {
            // If no redirect URL returned or an error occurred, handle gracefully            
            $json['error'] = $this->language->get('error_payment_failed');
            
            // Always set proper headers
            $this->response->addHeader('Content-Type: application/json');            
            // Make sure to encode the JSON properly
            $this->response->setOutput(json_encode($json));            
        }
    }
    
    /**
     * Callback endpoint for ViaBill.
     * ViaBill will call this URL to notify of payment status (success, cancel, etc).
     * It should update the order status accordingly.
     */
    public function callback(): void {
        // Retrieve data from ViaBill (assuming it's a POST callback)
        $callbackData = $this->request->post;  // or $this->request->get depending on ViaBill implementation
        // For security, verify the callback (e.g., check md5 or a signature, and apikey/secret)
        $order_id = $callbackData['order_number'] ?? 0;
        $transactionId = $callbackData['transaction'] ?? '';
        $status = strtolower($callbackData['status'] ?? '');  // e.g., 'paid', 'cancelled', 'refunded', 'voided'
        
        $this->load->model('checkout/order');        
        $this->load->model('extension/payment/viabill');   
        
        if ($order_id && $transactionId) {
            // Match the callback data with our stored transaction (to ensure it’s valid)
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "viabill_transaction` WHERE order_id = " . (int)$order_id . " AND transaction_id = '" . $this->db->escape($transactionId) . "'");
            if ($query->num_rows) {
                // Determine new order status based on ViaBill status
                $comment = 'ViaBill: ';
                $notifyCustomer = true;
                                
                if ($status == 'paid' || $status == 'captured' || $status == 'authorized') {
                    // Payment completed (or at least authorized)
                    $comment .= 'Payment successful.';
                    if ($status == 'authorized' && $this->config->get('payment_viabill_transaction_mode') == 'authorize') {
                        $comment .= ' (Authorized only, awaiting capture)';
                        // If authorized-only, you might set a different status, e.g., "Pending Payment"
                    }

                    // Set the new order status
                    $newStatusId = null;
                    $payment_type = $this->config->get('payment_viabill_transaction_mode');
                    if ($payment_type == 'authorize') {
                        $newStatusId = $this->config->get('payment_viabill_authorize_order_status_id') ?: $this->config->get('config_order_status_id');
                    } else {  // authorize_capture                       
                        $newStatusId = $this->config->get('payment_viabill_capture_order_status_id') ?: $this->config->get('config_order_status_id');
                    }  

                    // Update transaction table status
                    $this->db->query("UPDATE `" . DB_PREFIX . "viabill_transaction` SET status = 'PAID' WHERE order_id = " . (int)$order_id);
                                                                 
                    // (Optionally, if auth-only, set notifyCustomer = false to not send final email until capture)
                } elseif ($status == 'cancelled' || $status == 'canceled') {
                    $newStatusId = $this->getOrderStatusIdByName('Canceled') ?? $this->config->get('config_order_status_id');
                    $comment .= 'Payment was cancelled or failed.';
                    $notifyCustomer = false;  // No need to notify for cancel in some cases
                    $this->db->query("UPDATE `" . DB_PREFIX . "viabill_transaction` SET status = 'CANCELLED' WHERE order_id = " . (int)$order_id);
                } elseif ($status == 'refunded') {
                    $newStatusId = $this->getOrderStatusIdByName('Refunded') ?? $newStatusId;
                    $comment .= 'Payment refunded.';
                    $notifyCustomer = true;
                    $this->db->query("UPDATE `" . DB_PREFIX . "viabill_transaction` SET status = 'REFUNDED' WHERE order_id = " . (int)$order_id);
                } elseif ($status == 'voided') {
                    $newStatusId = $this->getOrderStatusIdByName('Canceled') ?? $newStatusId;
                    $comment .= 'Payment voided (authorization cancelled).';
                    $notifyCustomer = false;
                    $this->db->query("UPDATE `" . DB_PREFIX . "viabill_transaction` SET status = 'VOIDED' WHERE order_id = " . (int)$order_id);
                } else {
                    // Unknown status or error
                    $comment .= 'Payment status: ' . $status;
                }
                
                // Update OpenCart order history with the determined status and comment
                $this->model_checkout_order->addHistory($order_id, $newStatusId, $comment, $notifyCustomer);
            }
        }

        // Respond to ViaBill if needed (some APIs expect a response, e.g., simple OK text or HTTP 200)        
        http_response_code(200);
        // echo "OK";
    }
    
    /**
     * Cancel return endpoint.
     * This can handle when a customer cancels payment on ViaBill and is redirected back.
     * It could clean up or inform the user.
     */
    public function cancel(): void {
        // Maybe add a message that payment was cancelled and redirect to checkout page or cart.
        $this->session->data['error'] = $this->language->get('error_cancelled');
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

    // Or create a single loader method
    private function loadViabillHelpers() {
        require_once(DIR_SYSTEM . 'library/viabill/viabill_constants.php');
        require_once(DIR_SYSTEM . 'library/viabill/viabill_services.php');
        require_once(DIR_SYSTEM . 'library/viabill/viabill_helper.php');
    }
    
    /**
     * Event method to inject ViaBill PriceTag script into product pages.
     * Triggered by the event we registered on install (catalog/view/product/product/after).
     * It appends the PriceTag script HTML (if configured) to the page output.
     * 
     * @param string $route
     * @param mixed $data
     * @param string $output (by reference)
     */
    public function injectProductPriceTag(&$route, &$data, &$output): void {       
        // Check if we're on a product page
        if (isset($this->request->get['route']) && $this->request->get['route'] == 'product/product') {
            // Load your price tag content            
            $pricetagScript = '';
            $priceTagCSS = '';
            $pricetag_content = $this->config->get('payment_viabill_pricetag');			
			if (!empty($pricetag_content)) {                                                                                                        
                $config_prefix = 'payment_viabill_pricetag_product_';
                $config_common_prefix = 'payment_viabill_pricetag_';

                $dynamic_price = $this->config->get($config_prefix.'dynamic_price');                
                $dynamic_price_trigger = $this->config->get($config_prefix.'dynamic_price_trigger');
                $dynamic_price_trigger_delay = $this->config->get($config_prefix.'dynamic_price_trigger_delay');
                $decimal_separator = $this->config->get($config_common_prefix.'decimal_separator');
                $currency = !empty($this->config->get($config_common_prefix.'currency'))?$this->config->get($config_common_prefix.'currency'):$this->autoDetectCurrency($data);
                $country = !empty($this->config->get($config_common_prefix.'country'))?$this->config->get($config_common_prefix.'country'):$this->autoDetectCountry($data);
                $language = !empty($this->config->get($config_common_prefix.'language'))?$this->config->get($config_common_prefix.'language'):$this->autoDetectLanguage($data);
                $append_selector = !empty($this->config->get($config_prefix.'append_selector'))?$this->config->get($config_prefix.'append_selector'):'.price';
                $append_after = !empty($this->config->get($config_prefix.'append_after'))?true:false;
                $append_first = true;
                $alignment = $this->config->get($config_prefix.'align');
                $width = $this->config->get($config_prefix.'width');

                $price = $data['price'];
                if (!empty($dynamic_price)) {
                    $price = '';
                }

                $pricetag_params = [                    
                    'target' => 'product',
                    'price' => $price,
                    'dynamic_price' => $dynamic_price,
                    'decimal_separator' => $decimal_separator,
                    'trigger' => $dynamic_price_trigger,
                    'trigger_delay' => $dynamic_price_trigger_delay,
                    'currency' => $currency,
                    'country' => $country, 
                    'language' => $language,
                    'append_selector' => $append_selector,
                    'append_after' => $append_after,
                    'append_first' => $append_first,
                    'alignment' => $alignment,
                    'width' => $width
                ];               

                $pricetagScript = $this->getViabillPricetagScript($pricetag_params, str_replace(['&lt;','&gt;'],['<','>'],$pricetag_content));

                $priceTagCSS = $this->getViabillPricetagCSS($pricetag_params);
			}

            if (!empty($pricetagScript) && is_string($output)) {
                // Insert the script before </body> (assuming output is HTML)
                $output = str_replace('</body>', $priceTagCSS.$pricetagScript . '</body>', $output);
            }                        
        }
    }

    /**
     * Event method to inject ViaBill PriceTag script into cart pages.
     * Triggered by the event we registered on install (catalog/view/checkout/cart/after).
     * It appends the PriceTag script HTML (if configured) to the page output.
     * 
     * @param string $route
     * @param mixed $data
     * @param string $output (by reference)
     */
    public function injectCartPriceTag(&$route, &$data, &$output): void {                 
        // Check if we're on a product page
        if (isset($this->request->get['route']) && $this->request->get['route'] == 'checkout/cart') {
             // Load your price tag content            
             $pricetagScript = '';
             $priceTagCSS = '';
             $pricetag_content = $this->config->get('payment_viabill_pricetag');			
             if (!empty($pricetag_content)) {                                                                                                      
                 $config_prefix = 'payment_viabill_pricetag_cart_';
                 $config_common_prefix = 'payment_viabill_pricetag_';

                 $dynamic_price = $this->config->get($config_prefix.'dynamic_price');                 
                 $dynamic_price_trigger = $this->config->get($config_prefix.'dynamic_price_trigger');
                 $dynamic_price_trigger_delay = $this->config->get($config_prefix.'dynamic_price_trigger_delay');
                 $decimal_separator = $this->config->get($config_common_prefix.'decimal_separator');
                 $currency = !empty($this->config->get($config_common_prefix.'currency'))?$this->config->get($config_common_prefix.'currency'):$this->autoDetectCurrency($data);
                 $country = !empty($this->config->get($config_common_prefix.'country'))?$this->config->get($config_common_prefix.'country'):$this->autoDetectCountry($data);
                 $language = !empty($this->config->get($config_common_prefix.'language'))?$this->config->get($config_common_prefix.'language'):$this->autoDetectLanguage($data);
                 $append_selector = !empty($this->config->get($config_prefix.'append_selector'))?$this->config->get($config_prefix.'append_selector'):'.price';
                 $append_after = !empty($this->config->get($config_prefix.'append_after'))?true:false;
                 $append_first = true;
                 $alignment = $this->config->get($config_prefix.'align');
                 $width = $this->config->get($config_prefix.'width');
 
                 $price = $this->cart->getTotal();
                 if (!empty($dynamic_price)) {
                     $price = '';
                 }
 
                 $pricetag_params = [                    
                     'target' => 'basket',
                     'price' => $price,
                     'dynamic_price' => $dynamic_price,
                     'decimal_separator' => $decimal_separator,
                     'trigger' => $dynamic_price_trigger,
                     'trigger_delay' => $dynamic_price_trigger_delay,
                     'currency' => $currency,
                     'country' => $country, 
                     'language' => $language,
                     'append_selector' => $append_selector,
                     'append_after' => $append_after,
                     'append_first' => $append_first,
                     'alignment' => $alignment,
                     'width' => $width
                 ];               
 
                 $pricetagScript = $this->getViabillPricetagScript($pricetag_params, str_replace(['&lt;','&gt;'],['<','>'],$pricetag_content));

                 $priceTagCSS = $this->getViabillPricetagCSS($pricetag_params);                  
             }
 
             if (!empty($pricetagScript) && is_string($output)) {
                 // Insert the script before </body> (assuming output is HTML)
                 $output = str_replace('</body>', $priceTagCSS.$pricetagScript . '</body>', $output);
             }                                               
        }
    }

    /**
     * Event method to inject ViaBill PriceTag script into checkout pages.
     * Triggered by the event we registered on install (catalog/view/checkout/checkout/after).
     * It appends the PriceTag script HTML (if configured) to the page output.
     * 
     * @param string $route
     * @param mixed $data
     * @param string $output (by reference)
     */
    public function injectCheckoutPriceTag(&$route, &$data, &$output) {                 
        // Check if ViaBill is enabled
        if (!$this->config->get('payment_viabill_status')) {
            return;
        }

        if (isset($this->request->get['route']) && $this->request->get['route'] == 'checkout/checkout') {        
            // Get the price tag script from your configuration
            $pricetagScript = '';
            $priceTagCSS = '';
            $pricetag_content = $this->config->get('payment_viabill_pricetag');			
            if (!empty($pricetag_content)) {
                // Load your price tag content            
                $pricetagScript = '';
                $pricetag_content = $this->config->get('payment_viabill_pricetag');			
                if (!empty($pricetag_content)) {                                                                                                      
                    $config_prefix = 'payment_viabill_pricetag_checkout_';
                    $config_common_prefix = 'payment_viabill_pricetag_';

                    $dynamic_price = $this->config->get($config_prefix.'dynamic_price');                
                    $dynamic_price_trigger = $this->config->get($config_prefix.'dynamic_price_trigger');
                    $dynamic_price_trigger_delay = $this->config->get($config_prefix.'dynamic_price_trigger_delay');
                    $decimal_separator = $this->config->get($config_common_prefix.'decimal_separator');
                    $currency = !empty($this->config->get($config_common_prefix.'currency'))?$this->config->get($config_common_prefix.'currency'):$this->autoDetectCurrency($data);
                    $country = !empty($this->config->get($config_common_prefix.'country'))?$this->config->get($config_common_prefix.'country'):$this->autoDetectCountry($data);
                    $language = !empty($this->config->get($config_common_prefix.'language'))?$this->config->get($config_common_prefix.'language'):$this->autoDetectLanguage($data);
                    $append_selector = !empty($this->config->get($config_prefix.'append_selector'))?$this->config->get($config_prefix.'append_selector'):'.price';
                    $append_after = !empty($this->config->get($config_prefix.'append_after'))?true:false;
                    $append_first = true;
                    $alignment = $this->config->get($config_prefix.'align');
                    $width = $this->config->get($config_prefix.'width');

                    $price = $this->cart->getTotal();
                    if (!empty($dynamic_price)) {
                        $price = '';
                    }

                    $pricetag_params = [                    
                        'target' => 'payment',
                        'price' => $price,
                        'dynamic_price' => $dynamic_price,
                        'decimal_separator' => $decimal_separator,
                        'trigger' => $dynamic_price_trigger,
                        'trigger_delay' => $dynamic_price_trigger_delay,
                        'currency' => $currency,
                        'country' => $country, 
                        'language' => $language,
                        'append_selector' => $append_selector,
                        'append_after' => $append_after,
                        'append_first' => $append_first,
                        'alignment' => $alignment,
                        'width' => $width,                        
                    ];               

                    $pricetagScript = $this->getViabillPricetagScript($pricetag_params, str_replace(['&lt;','&gt;'],['<','>'],$pricetag_content));

                    $priceTagCSS = $this->getViabillPricetagCSS($pricetag_params);                
                }

                if (!empty($pricetagScript) && is_string($output)) {
                    // Insert the script before </body> (assuming output is HTML)
                    $output = str_replace('</body>', $priceTagCSS.$pricetagScript . '</body>', $output);
                }                 
            }
        
        }
    }
    
    private function getViabillPricetagScript(array $params, $priceTag_script): string {
        // 1) Build the <div> with all data- attributes
        $attrs = [
            'class="viabill-pricetag"',
            'data-view="' . ($params['target']        ?? 'product') . '"',
            'data-price="' . ($params['price']         ?? '') . '"',
            'data-dynamic-price="' . ($params['dynamic_price'] ?? '') . '"',
            'data-price-decimal-separator="' . ($params['decimal_separator'] ?? '') . '"',
            'data-dynamic-price-triggers="' . ($params['trigger'] ?? '') . '"',
            'data-dynamic-price-trigger-delay="' . ($params['trigger_delay'] ?? '') . '"',
            'data-currency="' . ($params['currency'] ?? '') . '"',
            'data-country-code="' . ($params['country']  ?? '') . '"',
            'data-language="' . ($params['language'] ?? '') . '"',
        ];                
  
        // Remove any empty attributes
        $attrs = array_filter($attrs, fn($a) => strpos($a, '=""') === false);

        $alignment_wrapper_class = '';        
        switch ($params['alignment']) {
            case 'center':
            case 'right':
                $alignment_wrapper_class = 'viabill_wrapper_alignment_'.$params['alignment'];
                // $this->document->addStyle('catalog/view/theme/stylesheet/pricetag.css');
                break;
            default:
                // Do nothing.
        }        
  
        $pricetagDiv = '<div class="viabill-pricetag-'.$params['target'].'-wrapper '.$alignment_wrapper_class.'"><div ' . implode(' ', $attrs) . '></div></div>';
  
        // Grab placement parameters, with safe defaults
        $appendSelector = addslashes($params['append_selector'] ?? '.price');
        $insertAfter    = !empty($params['append_after'])   ? 'true'  : 'false';
        $insertFirst    = !empty($params['append_first'])   ? 'true'  : 'false';

        // Clean and insert the pricetag
        $priceTag_script = str_replace(['<script>', '</script>'], ['',''], $priceTag_script);
  
        // 3) Build the JS
        $js = <<<JS
<script>
jQuery(function($){
    var pricetagHtml  = '{$pricetagDiv}';
    var selector      = '{$appendSelector}';
    var after         = {$insertAfter};
    var firstOnly     = {$insertFirst};

    // find the element(s) to attach to
    var \$targets = $(selector);
    if (!\$targets.length) return;

    // choose first or all
    var \$els = firstOnly ? \$targets.first() : \$targets;

    // insert
    if (after) {
    \$els.after(pricetagHtml);
    } else {
    \$els.before(pricetagHtml);
    }

    {$priceTag_script}
});
</script>
JS;
  
        return $js;
    }

    private function getViabillPricetagCSS(array $params): string {
        $css = <<<CSS
<style>
/* Align PriceTags to the right */
div.viabill_wrapper_alignment_right {
    display: flex;
    justify-content: flex-end;
    align-items: center;
}

div.viabill_wrapper_alignment_right .viabill-pricetag {
    display: flex !important;
    width: 300px;
    margin-left: auto;
}

div.viabill_wrapper_alignment_right .viabill-pricetag iframe.viabill {
    width: 100% !important;
}

/* Align PriceTags to the center */
div.viabill_wrapper_alignment_center {
    display: flex;
    justify-content: center;
    align-items: center;
}

div.viabill_wrapper_alignment_center .viabill-pricetag {
    display: flex !important;
    width: 300px;
}

div.viabill_wrapper_alignment_center .viabill-pricetag iframe.viabill {
    width: 100% !important;
}
</style>
CSS;    
        if (!empty($params['width'])) {
            $css .= '<style> div.viabill-pricetag-'.$params['target'].'-wrapper div.viabill-pricetag { width: '.$params['width'].' !important;} </style>';
        }

        return $css;
    }

    private function autoDetectCurrency($data) {     
        if (isset($this->session->data['currency'])) {
            return $this->session->data['currency'];
        }
        return '';
    }

    private function autoDetectCountry($data) {
        return '';
    }

    private function autoDetectLanguage($data) {
        if (isset($data['language'])) {
            $parts = explode('-', $data['language']);
            if (!empty($parts[0])) {
                return strtoupper($parts[0]);
            }
        }
        return '';
    }
            
    // Helper: find an order status ID by name (similar to admin model)
    private function getOrderStatusIdByName(string $statusName): ?int {
        $query = $this->db->query("SELECT order_status_id FROM " . DB_PREFIX . "order_status WHERE name = '" . $this->db->escape($statusName) . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
        return $query->num_rows ? (int)$query->row['order_status_id'] : null;
    }
}
?>
