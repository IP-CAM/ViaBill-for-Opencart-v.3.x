<?php

/**
 * A helper class for various ViaBill utility methods.
 */
class ViaBillHelper { 

  /**
   * Format the value of the TBYB parameter.
   */
  public static function formatTbyb($tbyb) {
    if (empty($tbyb)) {
      return ViaBillConstants::TBYB_OFF;
    }
    elseif (($tbyb == 'true')||($tbyb == '1')||($tbyb == 1)) {
      return ViaBillConstants::TBYB_ON;
    }
    else {
      return ViaBillConstants::TBYB_OFF;
    }
  }

  /**
   * Get the TBYB value in the proper format.
   */
  public static function getFormattedTbyb() {
    return self::formatTbyb($this->getTbyb());
  }

  /**
   * Format the Test Mode value properly.
   */
  public static function formatTestMode($mode) {
    if (empty($mode)) {
      return ViaBillConstants::TEST_MODE_ON;
    }
    elseif (($mode == 'test')||($mode == 'true')||($mode == '1')||($mode == 1)) {
      return ViaBillConstants::TEST_MODE_ON;
    }
    else {
      return ViaBillConstants::TEST_MODE_OFF;
    }
  }

  /**
   * Sanitize and format the Tax ID (if given)
   */
  public static function sanitizeTaxID($tax_id, $country) {
    $tax_id = str_replace(array(' ','-'), '', trim($tax_id));
    if ($country == 'ES') {        
      $regex_with_prefix = '/^ES[0-9A-Z]*/';
      if (preg_match($regex_with_prefix, $tax_id)) {
        return $tax_id;
      }
      $regex_without_prefix = '/^[0-9A-Z]+/';
      if (preg_match($regex_without_prefix, $tax_id)) {
        return 'ES'.$tax_id;
      }
    } else if ($country == 'DK') {
      $regex_with_prefix = '/^DK[0-9]{8}$/';
      if (preg_match($regex_with_prefix, $tax_id)) {
        return $tax_id;
      }
      $regex_without_prefix = '/^[0-9]{8}$/';
      if (preg_match($regex_without_prefix, $tax_id)) {
        return 'DK'.$tax_id;
      }
    }
    return '';
  }
  
  /**
   * Get the ViaBill platform (affiliate) for this module.
   */
  public static function getViaBillApiPlatform() {
    return ViaBillConstants::AFFILIATE;
  }

  /**
     * Verify callback signature from ViaBill
     * 
     * @param array $data
     * @return bool
     */
    public function verifyCallbackSignature(array $data): bool {
      if (!isset($data['signature']) || !isset($data['transaction']) || !isset($data['amount']) || !isset($data['currency'])) {
          return false;
      }
      
      $api_key = $this->config->get('payment_viabill_api_key');
      $secret = $this->config->get('payment_viabill_secret');
      
      $signature_data = $api_key . $data['transaction'] . $data['amount'] . $data['currency'] . $secret;
      $calculated_signature = md5($signature_data);
      
      return $calculated_signature === $data['signature'];
  }

  /**
   * Generate a random string for the payment transaction Id.
   */
  public static function generateRandomString($length = 10) {
    // Define the characters you want to include in the random string.
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    // Loop for the number of characters needed.
    for ($i = 0; $i < $length; $i++) {
      // Use random_int for better randomness and security.
      $index = random_int(0, $charactersLength - 1);
      $randomString .= $characters[$index];
    }

    return $randomString;
  }
  
  
  /**
   * Send a request to the specified URL with the given method and data.
   *
   * @param string $method_type The HTTP method to use (e.g., 'POST', 'GET')
   * @param string $url The URL to send the request to
   * @param array|string $data The data to send with the request
   * @param bool $auto_redirect Whether to automatically follow redirects
   * @return array An associative array containing the response details
   */
   public static function sendApiRequest($url, $data, $method_type = 'POST', $auto_redirect = false) {
      $ch = curl_init();             

      // Convert the payload array into JSON format for POST requests
      $jsonData = json_encode($data);          
  
      // Set the URL
      curl_setopt($ch, CURLOPT_URL, $url);      

      // Set the request method
      if (strtoupper($method_type) === 'POST') {          
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
      } else {
          // For GET requests, append data to URL if it's an array
          if (is_array($data)) {
              $url .= '?' . http_build_query($data);
              curl_setopt($ch, CURLOPT_URL, $url);
          }
      }

      // Return the transfer as a string instead of outputting it directly
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      // Set headers for JSON if data is an array
      if (is_array($data)) {
          curl_setopt($ch, CURLOPT_HTTPHEADER, [            
            'Accept: */*',
            'User-Agent: ViaBill-OpenCart/1.0',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
          ]);
      }

      // Handle redirects
      if ($auto_redirect) {
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      } else {
          curl_setopt($ch, CURLOPT_HEADER, true); // Include header in the output
      }

      // Execute the request
      $response = curl_exec($ch);

      // Check for errors
      if (curl_errno($ch)) {
          $error = curl_error($ch);
          curl_close($ch);            
          return [
              'status' => 'error',
              'message' => $error
          ];            
      }

      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      $headers = substr($response, 0, $header_size);
      $body = substr($response, $header_size);

      curl_close($ch);

      // If not auto_redirecting, check for redirect in headers
      if (!$auto_redirect && preg_match('/Location:\s*(.*)/i', $headers, $matches)) {
          return [
              'status' => 'redirect',
              'redirect_url' => trim($matches[1]),
              'http_code' => $http_code,
              'headers' => $headers,
              'body' => $body
          ];
      }

      return [
          'status' => 'success',
          'http_code' => $http_code,
          'headers' => $headers,
          'body' => $body
      ];
  }  
  
  /**
   * Log messages to OpenCart's error log or a custom ViaBill log file.
   *
   * @param string $message The message to log
   * @param string $level The log level ('info', 'error', 'debug', etc.)
   * @param bool $use_custom_log Whether to use a custom ViaBill log file instead of the default error log
   */
  public static function log($message, $level = 'info', $use_custom_log = true) {
    // Format the message with timestamp and level
    $formatted_message = '[' . strtoupper($level) . '] ' . $message;    
    
    if ($use_custom_log) {
        // Use a custom log file for ViaBill-specific logs
        $log = new \Opencart\System\Library\Log('viabill.log');
        $log->write($formatted_message);
    } else {
        // Use OpenCart's default error logging system
        if ($level == 'error') {
            // For errors, use the system logger
            $this->log->write($formatted_message);
        } else {
            // For info and other levels, only log if in debug mode
            if ($this->config->get('payment_viabill_debug')) {
                $this->log->write($formatted_message);
            }
        }
    }
  }


}
