<?php
class ControllerExtensionPaymentViabill extends Controller {        
    private $error = array(); // This is used to set the errors, if any.

    // Display the configuration form
    public function index(): void {
        // Load language entries for labels and messages
        $this->load->language('extension/payment/viabill');

        $data = array();       
        
        // Or manually assign specific ones you need
        $language_keys = [
            'heading_title', 'text_viabill_setup', 'text_viabill_account_required',
            'tab_login', 'tab_register', 'entry_email', 'entry_password',
            'entry_name', 'entry_url', 'entry_country', 'entry_tax_id',
            'help_tax_id', 'button_login', 'button_register', 'button_cancel',
            'text_select'
        ];
        
        foreach ($language_keys as $key) {
            $data[$key] = $this->language->get($key);
        } 

        // Add the token as a separate variable for forms
        $data['user_token'] = $this->session->data['user_token'];
                       
        // Set page title
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Load model for settings and any installation if not already done
        $this->load->model('extension/payment/viabill');   
                
        $this->loadViabillHelpers();
        
        // If this is a POST request and the user has permission, save the settings
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->user->hasPermission('modify', 'extension/payment/viabill')) {
            // Save settings via model
            $this->model_extension_payment_viabill->saveSettings($this->request->post);
            
            // Set a success message and redirect back to extension list
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/payment/viabill', 
                'user_token=' . $this->session->data['user_token'] . '&type=payment'));
        }
        
        // Prepare breadcrumb navigation
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 
                     'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 
                     'user_token=' . $this->session->data['user_token'] . '&type=payment')
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/viabill', 
                     'user_token=' . $this->session->data['user_token'])
        ];
        
        // Form action and cancel URLs
        $data['save_action'] = $this->url->link('extension/payment/viabill', 
                                'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('marketplace/extension', 
                               'user_token=' . $this->session->data['user_token'] . '&type=payment');

        // Load list of order statuses for the dropdown
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // Set default values by name
        $default_authorized_order_status = $this->getOrderStatusIdByName('Processing', $data['order_statuses']) ?? 0;
        $default_captured_order_status = $this->getOrderStatusIdByName('Complete', $data['order_statuses']) ?? 0;
        
        // Load existing configuration values (or defaults if not set)
        $data['payment_viabill_status'] = $this->config->get('payment_viabill_status') ?? 0;
        $data['payment_viabill_api_key'] = $this->config->get('payment_viabill_api_key') ?? '';
        $data['payment_viabill_secret_key'] = $this->config->get('payment_viabill_secret_key') ?? '';
        $data['payment_viabill_test'] = $this->config->get('payment_viabill_test') ?? 0;
        $data['payment_viabill_transaction_mode'] = $this->config->get('payment_viabill_transaction_mode') ?? 'authorize_capture';
        $data['payment_viabill_pricetag'] = $this->config->get('payment_viabill_pricetag') ?? '';
        $data['payment_viabill_authorize_order_status_id'] = $this->config->get('payment_viabill_authorize_order_status_id') ?? $default_authorized_order_status;
        $data['payment_viabill_capture_order_status_id'] = $this->config->get('payment_viabill_capture_order_status_id') ?? $default_captured_order_status;
        $data['payment_viabill_sort_order'] = $this->config->get('payment_viabill_sort_order') ?? 0;

        $data['payment_viabill_hide_checkout'] = $this->config->get('payment_viabill_hide_checkout') ?? 0;        

        // Add Geo Zone configuration
        $data['payment_viabill_geo_zone_id'] = $this->config->get('payment_viabill_geo_zone_id') ?? 0;

        // Labels for each view
        $data['entry_pricetag_product_label']          = $this->language->get('entry_pricetag_product_label');
        $data['entry_pricetag_cart_label']             = $this->language->get('entry_pricetag_cart_label');
        $data['entry_pricetag_checkout_label']         = $this->language->get('entry_pricetag_checkout_label');

        // Values (defaults)
        $data['payment_viabill_pricetag_product_label']  = $this->config->get('payment_viabill_pricetag_product_label')  ?? '';
        $data['payment_viabill_pricetag_cart_label']     = $this->config->get('payment_viabill_pricetag_cart_label')     ?? '';
        $data['payment_viabill_pricetag_checkout_label'] = $this->config->get('payment_viabill_pricetag_checkout_label') ?? '';

        // Check if API key is set
        $api_key_exists = !empty($data['payment_viabill_api_key']);
        $data['api_key_exists'] = $api_key_exists;
        
        // URLs for login and registration actions
        $data['login_action'] = $this->url->link('extension/payment/viabill/login', 
                                'user_token=' . $this->session->data['user_token']);
        $data['register_action'] = $this->url->link('extension/payment/viabill/register', 
                                    'user_token=' . $this->session->data['user_token']);                
        
        // Load geo zones for the dropdown
        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        
        // Add "All Zones" option for the geo zone dropdown
        array_unshift($data['geo_zones'], [
            'geo_zone_id' => 0,
            'name' => $this->language->get('text_all_zones')
        ]);                  
        
        // Common PriceTag configuration
        $data['payment_viabill_pricetag_language']          = $this->config->get('payment_viabill_pricetag_language')          ?? 'auto';
        $data['payment_viabill_pricetag_country']           = $this->config->get('payment_viabill_pricetag_country')           ?? 'auto';
        $data['payment_viabill_pricetag_currency']          = $this->config->get('payment_viabill_pricetag_currency')          ?? 'auto';
        $data['payment_viabill_pricetag_decimal_separator'] = $this->config->get('payment_viabill_pricetag_decimal_separator') ?? 'auto';

        // Product page PriceTag configuration
        $data['payment_viabill_pricetag_product_dynamic_price']               = $this->config->get('payment_viabill_pricetag_product_dynamic_price')               ?? '';
        $data['payment_viabill_pricetag_product_dynamic_price_trigger']       = $this->config->get('payment_viabill_pricetag_product_dynamic_price_trigger')       ?? '';
        $data['payment_viabill_pricetag_product_dynamic_price_trigger_delay'] = $this->config->get('payment_viabill_pricetag_product_dynamic_price_trigger_delay') ?? 0;
        $data['payment_viabill_pricetag_product_append_selector']             = $this->config->get('payment_viabill_pricetag_product_append_selector')             ?? '';
        $data['payment_viabill_pricetag_product_append_after']                = $this->config->get('payment_viabill_pricetag_product_append_after')                ?? 1;
        $data['payment_viabill_pricetag_product_align']                       = $this->config->get('payment_viabill_pricetag_product_align') ?? 'default';
        $data['payment_viabill_pricetag_product_width']                       = $this->config->get('payment_viabill_pricetag_product_width') ?? '';

        // Cart page PriceTag configuration
        $data['payment_viabill_pricetag_cart_dynamic_price']               = $this->config->get('payment_viabill_pricetag_cart_dynamic_price')               ?? '';
        $data['payment_viabill_pricetag_cart_dynamic_price_trigger']       = $this->config->get('payment_viabill_pricetag_cart_dynamic_price_trigger')       ?? '';
        $data['payment_viabill_pricetag_cart_dynamic_price_trigger_delay'] = $this->config->get('payment_viabill_pricetag_cart_dynamic_price_trigger_delay') ?? 0;
        $data['payment_viabill_pricetag_cart_append_selector']             = $this->config->get('payment_viabill_pricetag_cart_append_selector')             ?? '';
        $data['payment_viabill_pricetag_cart_append_after']                = $this->config->get('payment_viabill_pricetag_cart_append_after')                ?? 1;
        $data['payment_viabill_pricetag_cart_align']                       = $this->config->get('payment_viabill_pricetag_cart_align') ?? 'default';
        $data['payment_viabill_pricetag_cart_width']                       = $this->config->get('payment_viabill_pricetag_cart_width') ?? '';

        // Checkout page PriceTag configuration
        $data['payment_viabill_pricetag_checkout_dynamic_price']               = $this->config->get('payment_viabill_pricetag_checkout_dynamic_price')               ?? '';
        $data['payment_viabill_pricetag_checkout_dynamic_price_trigger']       = $this->config->get('payment_viabill_pricetag_checkout_dynamic_price_trigger')       ?? '';
        $data['payment_viabill_pricetag_checkout_dynamic_price_trigger_delay'] = $this->config->get('payment_viabill_pricetag_checkout_dynamic_price_trigger_delay') ?? 0;
        $data['payment_viabill_pricetag_checkout_append_selector']             = $this->config->get('payment_viabill_pricetag_checkout_append_selector')             ?? '';
        $data['payment_viabill_pricetag_checkout_append_after']                = $this->config->get('payment_viabill_pricetag_checkout_append_after')                ?? 1;
        $data['payment_viabill_pricetag_checkout_align']                       = $this->config->get('payment_viabill_pricetag_checkout_align') ?? 'default';
        $data['payment_viabill_pricetag_checkout_width']                       = $this->config->get('payment_viabill_pricetag_checkout_width') ?? '';

        // Add any error or success messages from login/register attempts
        if (isset($this->session->data['viabill_error'])) {
            $data['error_warning'] = $this->session->data['viabill_error'];
            unset($this->session->data['viabill_error']);
        }
        
        if (isset($this->session->data['viabill_success'])) {
            $data['success'] = $this->session->data['viabill_success'];
            unset($this->session->data['viabill_success']);
        }
                
        // CSRF protection token and user token
        $data['user_token'] = $this->session->data['user_token'];
        
        // Common admin UI components
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Render the appropriate template based on whether API key exists
        if ($api_key_exists) {
            // API key exists, show configuration form
            $this->response->setOutput($this->load->view('extension/payment/viabill', $data));
        } else {
            // No API key, show login/register form
            $data['store_name'] = $store_info['config_name'] ?? '';
            $data['store_url'] = $store_info['config_url'] ?? '';
            $data['store_email'] = $store_info['config_email'] ?? '';
            
            // Load list of countries for registration form
            $this->load->model('localisation/country');
            $data['countries'] = $this->model_localisation_country->getCountries();

            $this->response->setOutput($this->load->view('extension/payment/viabill_auth', $data));
        }        
    }

    // Get MyViaBill
    public function getMyViaBill(): string {
        $myViaBillURL = null;

        $this->loadViabillHelpers();        

        $key = $this->config->get('payment_viabill_api_key') ?? '';
        $secret = $this->config->get('payment_viabill_secret_key') ?? '';

        if (!empty($key) && !empty($secret)) {
            // Get myviabill endpoint
            $myviabill_endpoint = ViaBillServices::getApiEndPoint('myviabill');

            if (ViaBillConstants::PROTOCOL == '3.0') {                
                $signature = md5($key . '#' . $secret);
            } else {                
                $signature = hash('sha256', $key . '#' . $secret);
            }            

            // Prepare  data
            $myviabill_data = [
                'key' => $key,
                'signature' => $signature,
            ];            
                                        
            // Send registration request to ViaBill API            
            $response = ViaBillHelper::sendApiRequest($myviabill_endpoint['endpoint'], $myviabill_data, $myviabill_endpoint['method']);
                
            if (isset($response['status']) && $response['status'] === 'success') {
                // Extract API credentials from response
                $api_data = json_decode($response['body'], true);
				if (isset($api_data['url'])) {
					$myViaBillURL = $api_data['url'];
				}             
            }
        }    

        return $myViaBillURL;
    }

    // Get MyViaBill
    public function getViaBillNotifications(): array {
        $messages = [];

        $this->loadViabillHelpers();        

        $key = $this->config->get('payment_viabill_api_key') ?? '';
        $secret = $this->config->get('payment_viabill_secret_key') ?? '';

        if (!empty($key) && !empty($secret)) {
            // Get notifications endpoint
            $notifications_endpoint = ViaBillServices::getApiEndPoint('notifications');

            if (ViaBillConstants::PROTOCOL == '3.0') {                
                $signature = md5($key . '#' . $secret);
            } else {                
                $signature = hash('sha256', $key . '#' . $secret);
            }            

            // Prepare  data
            $notifications_data = [
                'key' => $key,
                'signature' => $signature,
            ];            
                                        
            // Send registration request to ViaBill API            
            $response = ViaBillHelper::sendApiRequest($notifications_endpoint['endpoint'], $notifications_data, $notifications_endpoint['method']);
                
            if (isset($response['status']) && $response['status'] === 'success') {
                // Extract API credentials from response
                $api_data = json_decode($response['body'], true);
				if (isset($api_data['messages'])) {
					$messages = $api_data['messages'];
				}          
            }
        }

        return $messages;
    }

    // Handle ViaBill login
    public function login(): void {
        $this->load->language('extension/payment/viabill');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateLogin()) {
            
            $this->loadViabillHelpers();
            
            // Get login endpoint
            $login_endpoint = ViaBillServices::getApiEndPoint('login');
            
            // Prepare login data
            $login_data = [
                'email' => $this->request->post['email'],
                'password' => $this->request->post['password']
            ];
            
            // Send login request to ViaBill API                        
            $response = ViaBillHelper::sendApiRequest($login_endpoint['endpoint'], $login_data, $login_endpoint['method']);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                // Extract API credentials from response
                $api_data = json_decode($response['body'], true);
                                
                if (isset($api_data['key']) && isset($api_data['secret'])) {
                    // Save API credentials
                    $settings = [
                        'payment_viabill_api_key' => $api_data['key'],
                        'payment_viabill_secret_key' => $api_data['secret']
                    ];
                    
                    // Save pricetag script if provided
                    if (isset($api_data['pricetagScript'])) {
                        $settings['payment_viabill_pricetag'] = $api_data['pricetagScript'];
                    }
                    
                    $this->load->model('extension/payment/viabill');
                    $this->model_extension_payment_viabill->saveSettings($settings);
                    
                    $this->session->data['viabill_success'] = $this->language->get('text_login_success');
                    $json['success'] = true;
                    $json['redirect'] = $this->url->link('extension/payment/viabill', 'user_token=' . $this->session->data['user_token']);
                } else {
                    $json['error'] = $this->language->get('error_api_credentials');
                }
            } else {
                $error_message = isset($response['body']) ? json_decode($response['body'], true) : [];
                $json['error'] = isset($error_message['message']) ? $error_message['message'] : $this->language->get('error_login');
            }
        } else {
            $json['error'] = $this->language->get('error_login_form');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Handle ViaBill registration
    public function register(): void {
        $this->load->language('extension/payment/viabill');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateRegistration()) {
            
            $this->loadViabillHelpers();
            
            // Get registration endpoint
            $register_endpoint = ViaBillServices::getApiEndPoint('registration');
            
            // Prepare registration data
            $register_data = [
                'email' => $this->request->post['email'],
                'name' => $this->request->post['name'],
                'url' => $this->request->post['url'],
                'country' => $this->request->post['country'],                
                'affiliate' => ViaBillConstants::AFFILIATE
            ];            
            
            // Add optional fields if provided
            if (!empty($this->request->post['tax_id'])) {
                $register_data['taxId'] = ViaBillHelper::sanitizeTaxID(trim($this->request->post['tax_id']), $register_data['country']);
            }
            
            // Send registration request to ViaBill API            
            $response = ViaBillHelper::sendApiRequest($register_endpoint['endpoint'], $register_data, $register_endpoint['method']);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                // Extract API credentials from response
                $api_data = json_decode($response['body'], true);
                
                if (isset($api_data['key']) && isset($api_data['secret'])) {
                    // Save API credentials
                    $settings = [
                        'payment_viabill_api_key' => $api_data['key'],
                        'payment_viabill_secret_key' => $api_data['secret']
                    ];
                    
                    // Save pricetag script if provided
                    if (isset($api_data['pricetagScript'])) {
                        $settings['payment_viabill_pricetag'] = $api_data['pricetagScript'];
                    }
                    
                    $this->load->model('extension/payment/viabill');
                    $this->model_extension_payment_viabill->saveSettings($settings);
                    
                    $this->session->data['viabill_success'] = $this->language->get('text_login_success');
                    $json['success'] = true;
                    $json['redirect'] = $this->url->link('extension/payment/viabill', 'user_token=' . $this->session->data['user_token']);
                } else {
                    $json['error'] = $this->language->get('error_api_credentials');
                    $json['error'] .= '[R]'.print_r($response, true);
                }
            } else {
                $error_message = isset($response['body']) ? json_decode($response['body'], true) : [];
                $json['error'] = isset($error_message['message']) ? $error_message['message'] : $this->language->get('error_registration');                
            }
        } else {
            $error_message = (empty($this->error))?$this->language->get('error_registration_form'):implode(',',$this->error);
            $json['error'] = $error_message;            
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Validate login form
    private function validateLogin(): bool {
        if (empty($this->request->post['email'])) {
            $this->error['email'] = $this->language->get('error_email');
        } elseif (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = $this->language->get('error_email_format');
        }
        
        if (empty($this->request->post['password'])) {
            $this->error['password'] = $this->language->get('error_password');
        }
        
        return !$this->error;
    }

    // Validate registration form
    private function validateRegistration(): bool {
        if (empty($this->request->post['email'])) {
            $this->error['email'] = $this->language->get('error_email');
        } elseif (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = $this->language->get('error_email_format');
        }
        
        if (empty($this->request->post['name'])) {
            $this->error['name'] = $this->language->get('error_name');
        }
        
        if (empty($this->request->post['url'])) {
            $this->error['url'] = $this->language->get('error_url');
        }
        
        if (empty($this->request->post['country'])) {
            $this->error['country'] = $this->language->get('error_country');
        }

        if (!empty($this->request->post['tax_id'])) {
            // add missing code here
        }
        
        return !$this->error;
    }

    /**
     * Display order details for ViaBill transactions
     *
     * @return string The rendered HTML for the order details page
     */
    public function order(): string {
        // Load language entries for labels and messages        
        $this->load->language('extension/payment/viabill');

        // Load model class
        $this->load->model('extension/payment/viabill');

        // Load helper classes

        $order_id = $this->request->get['order_id'];

        // Fetch order details from the database or API
        $order_info = $this->model_extension_payment_viabill->getOrder($order_id);

        if (!$order_info) {
            return $this->load->view('error/not_found', ['error' => 'Order not found']);
        }

        // Prepare data for the template
        $data = [
            'order_id' => $order_id,
            'transaction_id' => $order_info['transaction_id'] ?? '',
            'order_total' => $order_info['total'],
            'currency' => $order_info['currency'],
            'captured_amount' => $order_info['captured_amount'] ?? 0,
            'refunded_amount' => $order_info['refunded_amount'] ?? 0,
            'remaining_capture' => $order_info['amount'] - ($order_info['captured_amount'] ?? 0),
            'remaining_refund' => ($order_info['captured_amount'] ?? 0) - ($order_info['refunded_amount'] ?? 0),            
            'capture_url' => $this->url->link('extension/payment/viabill/capture', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_id),
            'refund_url' => $this->url->link('extension/payment/viabill/refund', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_id),
            'void_url' => $this->url->link('extension/payment/viabill/void', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_id),
            'header' => $this->load->controller('common/header'),
            'column_left' => $this->load->controller('common/column_left'),
            'footer' => $this->load->controller('common/footer'),
        ];

        // Render the template with the prepared data
        return $this->load->view('extension/payment/viabill_order', $data);
    }

    /**
     * Capture payment for an authorized transaction
     */
    public function capture(): void {
        $this->load->language('extension/payment/viabill');
        $this->load->model('extension/payment/viabill');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->user->hasPermission('modify', 'extension/payment/viabill')) {
            $order_id = $this->request->post['order_id'];
            $capture_amount = $this->request->post['capture_amount'];

            // Here you would call your API or service to capture the payment
            $result = $this->model_extension_payment_viabill->capturePayment($order_id, $capture_amount);

            if ($result['success']) {
                $this->session->data['success'] = $this->language->get('text_capture_success');
                $this->model_extension_payment_viabill->updateTransactionAmounts($order_id, $capture_amount, null);
            } else {
                $this->session->data['error'] = $this->language->get('text_capture_error') . ' ' . $result['message'];
            }
        }

        $this->response->redirect($this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_id));
    }

    /**
     * Refund payment for a captured transaction
     */
    public function refund(): void {
        $this->load->language('extension/payment/viabill');
        $this->load->model('extension/payment/viabill');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->user->hasPermission('modify', 'extension/payment/viabill')) {
            $order_id = $this->request->post['order_id'];
            $refund_amount = $this->request->post['refund_amount'];

            // Here you would call your API or service to refund the payment
            $result = $this->model_extension_payment_viabill->refundPayment($order_id, $refund_amount);

            if ($result['success']) {
                $this->session->data['success'] = $this->language->get('text_refund_success');
                $this->model_extension_payment_viabill->updateTransactionAmounts($order_id, null, $refund_amount);
            } else {
                $this->session->data['error'] = $this->language->get('text_refund_error') . ' ' . $result['message'];
            }
        }

        $this->response->redirect($this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_id));
    }

    /**
     * Void an authorized transaction
     */
    public function void(): void {
        $this->load->language('extension/payment/viabill');
        $this->load->model('extension/payment/viabill');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->user->hasPermission('modify', 'extension/payment/viabill')) {
            $order_id = $this->request->post['order_id'];

            // Here you would call your API or service to void the payment
            $result = $this->model_extension_payment_viabill->voidPayment($order_id);

            if ($result['success']) {
                $this->session->data['success'] = $this->language->get('text_void_success');
            } else {
                $this->session->data['error'] = $this->language->get('text_void_error') . ' ' . $result['message'];
            }
        }

        $this->response->redirect($this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_id));
    }

    /**
    * Get the order status ID by its name.
    *
    * @param string $name The order status name to search for (e.g., "Processing")
    * @param array $order_statuses The list returned by getOrderStatuses()
    * @return int|null The matching order status ID or null if not found
    */
    private function getOrderStatusIdByName(string $name, array $order_statuses): ?int {
        foreach ($order_statuses as $status) {
            if (strcasecmp($status['name'], $name) === 0) {
                return (int)$status['order_status_id'];
            }
        }
        return null;
    }

    // Load helper files
    private function loadViabillHelpers() {
        require_once(DIR_SYSTEM . 'library/viabill/viabill_constants.php');
        require_once(DIR_SYSTEM . 'library/viabill/viabill_services.php');
        require_once(DIR_SYSTEM . 'library/viabill/viabill_helper.php');
    }
    
    // Installation hook – called when installing the extension
    public function install(): void {
        $this->load->model('extension/payment/viabill');
        $this->model_extension_payment_viabill->install();  // e.g., create DB table, register events
    }
    
    // Uninstallation hook – called when uninstalling the extension
    public function uninstall(): void {
        $this->load->model('extension/payment/viabill');
        $this->model_extension_payment_viabill->uninstall();  // e.g., drop DB table, remove events
    }
    
}
?>
