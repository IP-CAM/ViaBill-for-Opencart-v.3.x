<?php
// Heading
$_['heading_title']          = 'ViaBill Payments';

// Text
$_['text_extension']         = 'Extensions';
$_['text_success']           = 'Success: You have modified the ViaBill Payments module!';
$_['text_edit']              = 'Edit ViaBill Payments';
$_['text_authorization']     = 'Authorization Only';
$_['text_authorization_capture'] = 'Authorization & Capture';
$_['text_capture_success']   = 'Payment captured successfully!';
$_['text_capture_error']     = 'Error capturing payment:';
$_['text_refund_success']    = 'Payment refunded successfully!';
$_['text_refund_error']      = 'Error refunding payment:';
$_['text_void_success']      = 'Payment voided successfully!';
$_['text_void_error']        = 'Error voiding payment:';
$_['text_all_zones']         = 'All Zones';
$_['text_enabled']           = 'Enabled';
$_['text_disabled']          = 'Disabled';

// Entry Labels (form fields)
$_['entry_api_key']          = 'ViaBill API Key';
$_['entry_secret_key']       = 'ViaBill Secret Key';
$_['entry_test_mode']        = 'Test Mode';
$_['entry_transaction_mode'] = 'Transaction Type';
$_['entry_transaction_mode_help'] = 'Choose whether to authorize payments only, or authorize and capture immediately.';
$_['entry_pricetag']         = 'ViaBill PriceTag Script';
$_['entry_pricetag_help']    = 'The ViaBill PriceTag script responsible for rendering the pricetags.';
$_['entry_authorize_order_status']     = 'Order Status (After Payment Authorization)';
$_['entry_capture_order_status']     = 'Order Status (After Payment Capture)';
$_['entry_status']           = 'Module Status';
$_['entry_hide_checkout']    = 'Hide in checkout';
$_['entry_sort_order']       = 'Sort Order';
$_['entry_geo_zone']         = 'Geo Zone';

// Pricetags
$_['tab_pricetag'] = 'PriceTag Settings';
$_['entry_pricetag_common']  = 'Common PriceTag Options';
$_['entry_pricetag_product']               = 'Product Page PriceTag';
$_['entry_pricetag_product_label']         = 'PriceTag Label';
$_['entry_pricetag_cart']               = 'Cart Page PriceTag';
$_['entry_pricetag_cart_label']         = 'PriceTag Label';
$_['entry_pricetag_checkout']               = 'Checkout Page PriceTag';
$_['entry_pricetag_checkout_label']         = 'PriceTag Label';

$_['entry_pricetag_language'] = 'Language';
$_['entry_pricetag_country'] = 'Country';
$_['entry_pricetag_currency'] = 'Currency';
$_['entry_pricetag_decimal_separator'] = 'Decimal Separator';

$_['entry_pricetag_product_dynamic_price'] = 'Dynamic Price Selector';
$_['entry_pricetag_product_dynamic_price_trigger'] = 'Dynamic Price Trigger';
$_['entry_pricetag_product_dynamic_price_trigger_delay'] = 'Trigger Delay';
$_['entry_pricetag_product_append_selector'] = 'Append PriceTag Selector';
$_['entry_pricetag_product_append_after'] = 'Append After Selector';

$_['entry_pricetag_cart_dynamic_price'] = 'Dynamic Price Selector';
$_['entry_pricetag_cart_dynamic_price_trigger'] = 'Dynamic Price Trigger';
$_['entry_pricetag_cart_dynamic_price_trigger_delay'] = 'Trigger Delay';
$_['entry_pricetag_cart_append_selector'] = 'Append PriceTag Selector';
$_['entry_pricetag_cart_append_after'] = 'Append After Selector';

$_['entry_pricetag_checkout_dynamic_price'] = 'Dynamic Price Selector';
$_['entry_pricetag_checkout_dynamic_price_trigger'] = 'Dynamic Price Trigger';
$_['entry_pricetag_checkout_dynamic_price_trigger_delay'] = 'Trigger Delay';
$_['entry_pricetag_checkout_append_selector'] = 'Append PriceTag Selector';
$_['entry_pricetag_checkout_append_after'] = 'Append After Selector';

$_['entry_pricetag_product_align'] = 'Alignment (Product Page)';
$_['entry_pricetag_product_width'] = 'Width (Product Page)';
$_['entry_pricetag_cart_align'] = 'Alignment (Cart Page)';
$_['entry_pricetag_cart_width'] = 'Width (Cart Page)';
$_['entry_pricetag_checkout_align'] = 'Alignment (Checkout Page)';
$_['entry_pricetag_checkout_width'] = 'Width (Checkout Page)';


// Authentication
$_['text_viabill_setup'] = 'ViaBill Account Setup';
$_['text_viabill_account_required'] = 'You need to connect your ViaBill account before you can configure the payment module. If you don\'t have an account, you can register for one.';
$_['tab_login'] = 'Login';
$_['tab_register'] = 'Register';
$_['entry_email'] = 'Email';
$_['entry_password'] = 'Password';
$_['entry_name'] = 'Business Name';
$_['entry_url'] = 'Website URL';
$_['entry_country'] = 'Country';
$_['entry_tax_id'] = 'Tax ID (VAT Number)';
$_['help_tax_id'] = 'Optional: Your business tax identification number.';
$_['button_login'] = 'Login';
$_['button_register'] = 'Register';
$_['text_login_success'] = 'You have successfully logged in to your ViaBill account!';
$_['text_register_success'] = 'Your ViaBill account has been successfully created!';
$_['text_select'] = '-- Please Select --';
$_['error_email'] = 'Email is required!';
$_['error_email_format'] = 'Please enter a valid email address!';
$_['error_password'] = 'Password is required!';
$_['error_name'] = 'Business name is required!';
$_['error_url'] = 'Website URL is required!';
$_['error_country'] = 'Please select a country!';
$_['error_login'] = 'Login failed. Please check your credentials and try again.';
$_['error_registration'] = 'Registration failed. Please check your information and try again.';
$_['error_login_form'] = 'Please check your login information and try again.';
$_['error_registration_form'] = 'Please check your registration information and try again.';
$_['error_api_credentials'] = 'API credentials not received from ViaBill. Please contact support.';

// Buttons
$_['button_save']            = 'Save';
$_['button_cancel']          = 'Cancel';

// Error
$_['error_permission']       = 'Warning: You do not have permission to modify ViaBill Payments!';
$_['error_api_key']          = 'API Key is required!';
$_['error_secret_key']       = 'Secret Key is required!';
