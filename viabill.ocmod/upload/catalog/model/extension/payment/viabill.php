<?php
class ModelExtensionPaymentViabill extends Model {    
    /**
     * Determine if this payment method should be available     
     */
    public function getMethod($address, $total) {
        // Load language file for text strings
        $this->load->language('extension/payment/viabill');

        // Check if module is enabled
        if (!$this->config->get('payment_viabill_status')) {
            return [];
        }

        // Check if cart has subscription products (if you don't support subscriptions)
        if (method_exists($this->cart, 'hasSubscription') && $this->cart->hasSubscription()) {
            return [];
        }

        // Get the configured geo zone
        $geo_zone_id = $this->config->get('payment_viabill_geo_zone_id');
        $status = true;

        // Check if geo zone is set and address is provided
        /*
        if ($geo_zone_id && !empty($address)) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

            if (!$query->num_rows) {
                $status = false;
            }
        }
        */

        // Define method data
        $method_data = [];        

        $payment_options = [];

        if ($status) {                        
            $method_data = array(
                'code'       => 'viabill',
                'title'      => $this->language->get('text_title'),
                'name'       => $this->language->get('text_title'),
                'terms'      => '',
                'test_mode_warning' => 'Test mode is enabled. This payment will be processed in the ViaBill sandbox environment.',
                'sort_order' => $this->config->get('payment_viabill_sort_order')
            );                        
        }

        return $method_data;
    }

    /**
     * Get ViaBill transaction details for an order
     *
     * @param int $order_id The order ID
     * @return array Transaction details
     */
    public function getTransaction(int $order_id): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "viabill_transaction` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

        if ($query->num_rows) {
            return $query->row;
        } else {
            return [];
        }
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

    private function loadViabillHelpers() {
        require_once(DIR_SYSTEM . 'library/viabill/viabill_constants.php');
        require_once(DIR_SYSTEM . 'library/viabill/viabill_services.php');
        require_once(DIR_SYSTEM . 'library/viabill/viabill_helper.php');
    }

    /**
     * Capture a previously authorized ViaBill transaction.
     * @param array $capture_data The data used for the capture request.     
     * @return bool success
     */
    public function capturePayment($capture_data): array {
        // Load language class
        $this->load->language('extension/payment/viabill');
        
        // Load helper classes
        $this->loadViabillHelpers();
    
        $registry = $this->registry;
        ViaBillServices::setRegistry($registry);        
        
        // Endpoint for capture
        $capture_endpoint = ViaBillServices::getApiEndPoint('capture_transaction');        
        $response = ViaBillHelper::sendApiRequest($capture_endpoint['endpoint'], $capture_data, $capture_endpoint['method']);
        if ($response['status'] == 'success') {                        
            return ['success' => true];
        } else {
            // Log or handle capture failure
            return ['success' => false];
        }
    }
}
?>
