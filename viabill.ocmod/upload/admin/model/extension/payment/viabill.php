<?php
class ModelExtensionPaymentViabill extends Model {
    
    // Save configuration settings to the database
    public function saveSettings(array $data): void {
        $this->load->model('setting/setting');
        // Save all settings for this payment module (prefix "payment_viabill")
        $this->model_setting_setting->editSetting('payment_viabill', $data);
    }
    
    // Set up necessary database table(s) or events on installation
    public function install(): void {
        // Create the database table for ViaBill transaction logs (if not exists)
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "viabill_transaction` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `order_id` INT(11) NOT NULL,
                `transaction_id` VARCHAR(64) NOT NULL,
                `amount` DECIMAL(15,4) NOT NULL,
                `currency` VARCHAR(20) NOT NULL,
                `captured_amount` DECIMAL(15,4) NOT NULL DEFAULT 0,
                `refunded_amount` DECIMAL(15,4) NOT NULL DEFAULT 0,
                `type` VARCHAR(20) NOT NULL,                
                `status` varchar(20) NOT NULL DEFAULT 'INITIATED',
                `date_added` DATETIME NOT NULL,
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `order_id` (`order_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        // Register an event to inject ViaBill PriceTag script on product pages (if needed)
        $this->load->model('setting/event');                

        // Remove any existing event with same code to avoid duplicates
        $this->model_setting_event->deleteEventByCode('viabill_product_pricetag');
        $this->model_setting_event->deleteEventByCode('viabill_cart_pricetag');
        $this->model_setting_event->deleteEventByCode('viabill_checkout_pricetag');

        // Register the event for product page, to display pricetags
        $this->model_setting_event->addEvent(
            'viabill_product_pricetag',
            'catalog/view/product/product/after',
            'extension/payment/viabill/injectProductPriceTag',
            1,
            1
        );

        // Register the event for cart page, to display pricetags
        $this->model_setting_event->addEvent(
            'viabill_cart_pricetag',
            'catalog/view/checkout/cart/after',
            'extension/payment/viabill/injectCartPriceTag',
            1,
            1
        );

        // Register the event for checkout page, to display pricetags
        $this->model_setting_event->addEvent(
            'viabill_checkout_pricetag',
            'catalog/view/checkout/checkout/after',
            'extension/payment/viabill/injectCheckoutPriceTag',
            1,
            1
        );

        // Add the proper permissions for the admin to access and edit Viabill Payments config page
        $this->load->model('user/user_group');

        // Get the current admin user group ID, typically the administrator group
        $user_group_id = $this->user->getGroupId();
        
        // Grant access and modify permission for this module’s route
        $this->model_user_user_group->addPermission($user_group_id, 'access', 'extension/payment/viabill');
        $this->model_user_user_group->addPermission($user_group_id, 'modify', 'extension/payment/viabill');
        $this->model_user_user_group->addPermission($user_group_id, 'access', 'extension/payment/viabill/auth');
        $this->model_user_user_group->addPermission($user_group_id, 'modify', 'extension/payment/viabill/auth');
        $this->model_user_user_group->addPermission($user_group_id, 'access', 'extension/payment/viabill/order');
        $this->model_user_user_group->addPermission($user_group_id, 'modify', 'extension/payment/viabill/order');
        $this->model_user_user_group->addPermission($user_group_id, 'access', 'extension/payment/viabill/login');
        $this->model_user_user_group->addPermission($user_group_id, 'modify', 'extension/payment/viabill/login');
        $this->model_user_user_group->addPermission($user_group_id, 'access', 'extension/payment/viabill/register');
        $this->model_user_user_group->addPermission($user_group_id, 'modify', 'extension/payment/viabill/register');
        $this->model_user_user_group->addPermission($user_group_id, 'access', 'extension/payment/viabill/injectProductPriceTag');
        $this->model_user_user_group->addPermission($user_group_id, 'modify', 'extension/payment/viabill/injectProductPriceTag');
        $this->model_user_user_group->addPermission($user_group_id, 'access', 'extension/payment/viabill/injectCartPriceTag');
        $this->model_user_user_group->addPermission($user_group_id, 'modify', 'extension/payment/viabill/injectCartPriceTag');
        $this->model_user_user_group->addPermission($user_group_id, 'access', 'extension/payment/viabill/injectCheckoutPriceTag');
        $this->model_user_user_group->addPermission($user_group_id, 'modify', 'extension/payment/viabill/injectCheckoutPriceTag');
        
        // Grant access & modify on the core order-info route
        $this->model_user_user_group->addPermission($user_group_id, 'access', 'sale/order');
        $this->model_user_user_group->addPermission($user_group_id, 'modify', 'sale/order');                                
    }
    
    // Clean up on uninstallation
    public function uninstall(): void {
        $this->load->model('setting/event');
        // Remove the PriceTag injection event
        $this->model_setting_event->deleteEventByCode('viabill_product_pricetag');
        $this->model_setting_event->deleteEventByCode('viabill_cart_pricetag');

        // (Optional) Drop the transaction log table
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "viabill_transaction`;");

        // Optional
        // Αφαίρεση δικαιωμάτων από όλα τα user groups
        $this->load->model('user/user_group');
        $user_groups = $this->model_user_user_group->getUserGroups();
        
        foreach ($user_groups as $user_group) {
            $user_group_id = $user_group['user_group_id'];
            
            // More permissions
            $this->model_user_user_group->removePermission($user_group_id, 'access', 'extension/payment/viabill');
            $this->model_user_user_group->removePermission($user_group_id, 'modify', 'extension/payment/viabill');
            $this->model_user_user_group->removePermission($user_group_id, 'access', 'extension/payment/viabill/auth');
            $this->model_user_user_group->removePermission($user_group_id, 'modify', 'extension/payment/viabill/auth');
            $this->model_user_user_group->removePermission($user_group_id, 'access', 'extension/payment/viabill/order');
            $this->model_user_user_group->removePermission($user_group_id, 'modify', 'extension/payment/viabill/order');
            $this->model_user_user_group->removePermission($user_group_id, 'access', 'extension/payment/viabill/login');
            $this->model_user_user_group->removePermission($user_group_id, 'modify', 'extension/payment/viabill/login');
            $this->model_user_user_group->removePermission($user_group_id, 'access', 'extension/payment/viabill/register');
            $this->model_user_user_group->removePermission($user_group_id, 'modify', 'extension/payment/viabill/register');
        }
    }

    /**
     * Get ViaBill transaction details for an order
     *
     * @param int $order_id The order ID
     * @return array Transaction details
     */
    public function getOrder(int $order_id): array {
        // Query the viabill_transaction table to get transaction details
        $query = $this->db->query("SELECT vt.*, o.total 
            FROM `" . DB_PREFIX . "viabill_transaction` vt 
            LEFT JOIN `" . DB_PREFIX . "order` o ON (vt.order_id = o.order_id) 
            WHERE vt.order_id = '" . (int)$order_id . "' 
            LIMIT 1");

        if ($query->num_rows) {
            return $query->row;
        } else {
            // If no ViaBill transaction exists, get basic order info
            $this->load->model('sale/order');
            $order_info = $this->model_sale_order->getOrder($order_id);
            
            if ($order_info) {
                return [
                    'order_id' => $order_id,
                    'total' => $order_info['total'],
                    'currency' => $order_info['currency_code'],
                    'transaction_id' => $order_info['transaction_id'],
                    'captured_amount' => $order_info['captured_amount'],
                    'refunded_amount' => $order_info['refunded_amount']
                ];
            }
            
            return [];
        }
    }
    
    // --- Admin Operations: Capture, Refund, Void ---
    /**
     * Capture a previously authorized ViaBill transaction.
     * @param int $order_id The OpenCart order ID to capture.
     * @param float|null $amount Amount to capture (null for full order amount).
     * @return bool success
     */
    public function capturePayment(int $order_id, ?float $amount = null): array {
        // Load language class
        $this->load->language('extension/payment/viabill');
        
        // Load order and transaction info from OpenCart
        $this->load->model('sale/order');

        // Load helper classes
        $this->loadViabillHelpers();        
    
        $registry = $this->registry;
        ViaBillServices::setRegistry($registry);

        $order_info = $this->model_sale_order->getOrder($order_id);
        if (!$order_info) return false;
        // Determine amount to capture
        $captureAmount = $amount ?? $order_info['total'];
        
        // Retrieve ViaBill transaction ID (stored during authorization)
        $viaTransactionId = $this->getViaBillTransactionId($order_id);  // A helper method to fetch from our log table or order history
        if (!$viaTransactionId) return false;
        
        // Prepare ViaBill capture API request (assuming ViaBill provides an endpoint for capture)
        $apiKey = $this->config->get('payment_viabill_api_key');
        $secret = $this->config->get('payment_viabill_secret_key');
        
        $capture_data = [
            'apikey'      => $apiKey,
            'secret'      => $secret,
            'transaction' => $viaTransactionId,
            'amount'      => number_format($captureAmount, 2, '.', ''), // formatted amount
            'currency'    => $order_info['currency_code']
        ];
        
        // Endpoint for capture
        $capture_endpoint = ViaBillServices::getApiEndPoint('capture_transaction');        
        $response = ViaBillHelper::sendApiRequest($capture_endpoint['endpoint'], $capture_data, $capture_endpoint['method']);
        if ($response['status'] == 'success') {
            $capturedStatusId = $this->config->get('payment_viabill_capture_order_status_id') ?: $this->config->get('config_order_status_id');
            $this->addOrderHistory($order_id, $capturedStatusId, 'ViaBill payment captured', true);
            return ['success' => true];
        } else {
            // Log or handle capture failure
            return ['success' => false];
        }
    }
    
    /**
     * Refund a captured ViaBill transaction (partial or full).
     * @param int $order_id
     * @param float|null $amount Amount to refund (null for full refund).
     * @return bool success
     */
    public function refundPayment(int $order_id, ?float $amount = null): array {
        // Load language class
        $this->load->language('extension/payment/viabill');
        
        // Load order and transaction info from OpenCart
        $this->load->model('sale/order');

        // Load helper classes
        $this->loadViabillHelpers();
        
        $registry = $this->registry;
        ViaBillServices::setRegistry($registry);

        $order_info = $this->model_sale_order->getOrder($order_id);
        if (!$order_info) return false;
        $refundAmount = $amount ?? $order_info['total'];
        $viaTransactionId = $this->getViaBillTransactionId($order_id);
        if (!$viaTransactionId) return false;
        
        // Prepare ViaBill refund API request (placeholder endpoint)
        $apiKey = $this->config->get('payment_viabill_api_key');
        $secret = $this->config->get('payment_viabill_secret_key');
        
        $refund_data = [
            'apikey'      => $apiKey,
            'secret'      => $secret,
            'transaction' => $viaTransactionId,
            'amount'      => number_format($refundAmount, 2, '.', ''),
            'currency'    => $order_info['currency_code']
        ];

        // Endpoint for refund
        $refund_endpoint = ViaBillServices::getApiEndPoint('refund_transaction');
        $response = ViaBillHelper::sendApiRequest($refund_endpoint['endpoint'], $refund_data, $refund_endpoint['method']);
        if ($response['status'] == 'success') {            
            // Typically, you’d have a specific "Refunded" status ID configured or use OpenCart's "Refunded" if exists            
            $refundedStatusId = $this->getOrderStatusIdByName('Refunded') ?? $this->config->get('config_order_status_id');
            $this->addOrderHistory($order_id, $refundedStatusId, 'ViaBill payment refunded', true);       
            return ['success' => true];
        }
        return ['success' => false];
    }
    
    /**
     * Void a previously authorized ViaBill transaction (cancel before capture).
     * @param int $order_id
     * @return bool success
     */
    public function voidPayment(int $order_id): array {
        // Load language class
        $this->load->language('extension/payment/viabill');
                
        // Load order and transaction info from OpenCart
        $this->load->model('sale/order');

        // Load helper classes
        $this->loadViabillHelpers();

        $registry = $this->registry;
        ViaBillServices::setRegistry($registry);

        $viaTransactionId = $this->getViaBillTransactionId($order_id);
        if (!$viaTransactionId) return false;
        // Prepare ViaBill void API request (placeholder endpoint)
        $apiKey = $this->config->get('payment_viabill_api_key');
        $secret = $this->config->get('payment_viabill_secret_key');
        
        $void_data = [
            'apikey'      => $apiKey,
            'secret'      => $secret,
            'transaction' => $viaTransactionId
        ];

        // Endpoint for void
        $void_endpoint = ViaBillServices::getApiEndPoint('cancel_transaction');
        $response = ViaBillHelper::sendApiRequest($void_endpoint['endpoint'], $void_data, $void_endpoint['method']);        
        if ($response['status'] == 'success') {
            // Typically, you’d have a specific "Cancelled" status ID configured or use OpenCart's "Cancelled" if exists            
            $cancelStatusId = $this->getOrderStatusIdByName('Voided') ?? $this->config->get('config_order_status_id');
            $this->addOrderHistory($order_id, $cancelStatusId, 'ViaBill authorization voided', false);            
            return ['success' => true];
        }
        return ['success' => false];
    }

    /**
     * Add an order-history record (same as catalog/model/checkout/order->addOrderHistory).
     */
    private function addOrderHistory(
        int   $order_id,
        int   $order_status_id,
        string $comment = '',
        bool  $notify = false,
        bool  $override = false
    ): void {
        // 1) Insert history record
        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "order_history` SET
                `order_id`        = '" . (int)$order_id . "',
                `order_status_id` = '" . (int)$order_status_id . "',
                `notify`          = '" . ($notify ? 1 : 0) . "',
                `comment`         = '" . $this->db->escape($comment) . "',
                `date_added`      = NOW()"
        );

        // 2) Unless override is true, update the order’s current status
        if (!$override) {
            $this->db->query(
                "UPDATE `" . DB_PREFIX . "order` SET
                    `order_status_id` = '" . (int)$order_status_id . "',
                    `date_modified`   = NOW()
                WHERE `order_id`       = '" . (int)$order_id . "'"
            );
        }
    }
    
    // Helper: get ViaBill transaction ID stored for an order (e.g., from our log table)
    private function getViaBillTransactionId(int $order_id): ?string {
        $query = $this->db->query("SELECT `transaction_id` FROM `" . DB_PREFIX . "viabill_transaction` WHERE `order_id` = " . (int)$order_id);
        return $query->num_rows ? $query->row['transaction_id'] : null;
    }       

    /**
     * Update transaction amounts for an order
     *
     * @param int $order_id The order ID
     * @param float|null $captured_amount The amount to add to the captured total (or null to skip)
     * @param float|null $refunded_amount The amount to add to the refunded total (or null to skip)
     * @return void
     */
    public function updateTransactionAmounts(int $order_id, ?float $captured_amount, ?float $refunded_amount): void {
        $fields = [];

        if (!is_null($captured_amount)) {
            $fields[] = "`captured_amount` = `captured_amount` + " . (float)$captured_amount;
        }

        if (!is_null($refunded_amount)) {
            $fields[] = "`refunded_amount` = `refunded_amount` + " . (float)$refunded_amount;
        }

        // Always update the modification date
        $fields[] = "`date_modified` = NOW()";

        if (!empty($fields)) {
            $this->db->query("UPDATE `" . DB_PREFIX . "viabill_transaction` SET " . implode(', ', $fields) . " WHERE `order_id` = '" . (int)$order_id . "'");
        }
    }
    
    // Helper: find order status ID by name (for example "Refunded" or "Canceled")
    private function getOrderStatusIdByName(string $statusName): ?int {
        $query = $this->db->query("SELECT order_status_id FROM " . DB_PREFIX . "order_status WHERE name = '" . $this->db->escape($statusName) . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
        return $query->num_rows ? (int)$query->row['order_status_id'] : null;
    } 

    // Load helper classes
    private function loadViabillHelpers() {
        require_once(DIR_SYSTEM . 'library/viabill/viabill_constants.php');
        require_once(DIR_SYSTEM . 'library/viabill/viabill_services.php');
        require_once(DIR_SYSTEM . 'library/viabill/viabill_helper.php');
    }
}
?>
