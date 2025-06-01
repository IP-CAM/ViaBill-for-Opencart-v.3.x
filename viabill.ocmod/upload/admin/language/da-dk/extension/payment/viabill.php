<?php
// Heading
$_['heading_title']          = 'ViaBill Betalinger';

// Text
$_['text_extension']         = 'Udvidelser';
$_['text_success']           = 'Succes: Du har ændret ViaBill Betalingsmodulet!';
$_['text_edit']              = 'Rediger ViaBill Betalinger';
$_['text_authorization']     = 'Kun Godkendelse';
$_['text_authorization_capture'] = 'Godkendelse & Hævning';
$_['text_capture_success']   = 'Betaling blev hævet med succes!';
$_['text_capture_error']     = 'Fejl ved hævning af betaling:';
$_['text_refund_success']    = 'Betaling blev refunderet med succes!';
$_['text_refund_error']      = 'Fejl ved refundering af betaling:';
$_['text_void_success']      = 'Betaling blev annulleret med succes!';
$_['text_void_error']        = 'Fejl ved annullering af betaling:';
$_['text_all_zones']         = 'Alle Zoner';
$_['text_enabled']           = 'Aktiveret';
$_['text_disabled']          = 'Deaktiveret';

// Entry Labels (form fields)
$_['entry_api_key']          = 'ViaBill API-nøgle';
$_['entry_secret_key']       = 'ViaBill Secret';
$_['entry_test_mode']        = 'Testtilstand';
$_['entry_transaction_mode'] = 'Transaktionstype';
$_['entry_transaction_mode_help'] = 'Vælg om betalinger kun skal godkendes, eller også hæves med det samme.';
$_['entry_pricetag']         = 'ViaBill PriceTag Script';
$_['entry_pricetag_help']    = 'ViaBill PriceTag scriptet, som er ansvarligt for at vise pricetags.';
$_['entry_authorize_order_status']     = 'Ordrestatus (efter godkendelse)';
$_['entry_capture_order_status']     = 'Ordrestatus (efter hævning)';
$_['entry_status']           = 'Modulstatus';
$_['entry_hide_checkout']    = 'Skjul ved checkout';
$_['entry_sort_order']       = 'Sortering';
$_['entry_geo_zone']         = 'Geozone';

// Pricetags
$_['tab_pricetag'] = 'PriceTag Indstillinger';
$_['entry_pricetag_common']  = 'Generalle PriceTag Indstillinger';
$_['entry_pricetag_product']               = 'Produktvisning PriceTag';
$_['entry_pricetag_product_label']         = 'PriceTag label';
$_['entry_pricetag_cart']               = 'Kurv PriceTag';
$_['entry_pricetag_cart_label']         = 'PriceTag label';
$_['entry_pricetag_checkout']               = 'Checkout PriceTag';
$_['entry_pricetag_checkout_label']         = 'PriceTag label';

$_['entry_pricetag_language'] = 'Sprog';
$_['entry_pricetag_country'] = 'Land';
$_['entry_pricetag_currency'] = 'Valuta';
$_['entry_pricetag_decimal_separator'] = 'Decimalseparator';

$_['entry_pricetag_product_dynamic_price'] = 'Dynamisk Prisselector';
$_['entry_pricetag_product_dynamic_price_trigger'] = 'Dynamisk Trigger';
$_['entry_pricetag_product_dynamic_price_trigger_delay'] = 'Trigger-forsinkelse';
$_['entry_pricetag_product_append_selector'] = 'Tilføj PriceTag Selector';
$_['entry_pricetag_product_append_after'] = 'Tilføj Efter Selector';

$_['entry_pricetag_cart_dynamic_price'] = 'Dynamisk Prisselektor';
$_['entry_pricetag_cart_dynamic_price_trigger'] = 'Dynamisk Trigger';
$_['entry_pricetag_cart_dynamic_price_trigger_delay'] = 'Trigger-forsinkelse';
$_['entry_pricetag_cart_append_selector'] = 'Tilføj PriceTag Selector';
$_['entry_pricetag_cart_append_after'] = 'Tilføj Efter Selector';

$_['entry_pricetag_checkout_dynamic_price'] = 'Dynamisk Prisselektor';
$_['entry_pricetag_checkout_dynamic_price_trigger'] = 'Dynamisk Trigger';
$_['entry_pricetag_checkout_dynamic_price_trigger_delay'] = 'Trigger-forsinkelse';
$_['entry_pricetag_checkout_append_selector'] = 'Tilføj PriceTag Selector';
$_['entry_pricetag_checkout_append_after'] = 'Tilføj Efter Selector';

$_['entry_pricetag_product_align'] = 'Justering (Produktvisning)';
$_['entry_pricetag_product_width'] = 'Bredde (Produktvisning)';
$_['entry_pricetag_cart_align'] = 'Justering (Kurv)';
$_['entry_pricetag_cart_width'] = 'Bredde (Kurv)';
$_['entry_pricetag_checkout_align'] = 'Justering (Checkout)';
$_['entry_pricetag_checkout_width'] = 'Bredde (Checkout)';

// Authentication
$_['text_viabill_setup'] = 'Opsætning af ViaBill-konto';
$_['text_viabill_account_required'] = 'Du skal forbinde din ViaBill-konto, før du kan konfigurere betalingsmodulet. Hvis du ikke har en konto, kan du registrere en.';
$_['tab_login'] = 'Log ind';
$_['tab_register'] = 'Registrer';
$_['entry_email'] = 'E-mail';
$_['entry_password'] = 'Adgangskode';
$_['entry_name'] = 'Firmanavn';
$_['entry_url'] = 'Webadresse';
$_['entry_country'] = 'Land';
$_['entry_tax_id'] = 'CVR-nummer (Momsnummer)';
$_['help_tax_id'] = 'Valgfrit: Din virksomheds momsregistreringsnummer.';
$_['button_login'] = 'Log ind';
$_['button_register'] = 'Registrer';
$_['text_login_success'] = 'Du er nu logget ind med din ViaBill-konto!';
$_['text_register_success'] = 'Din ViaBill-konto blev oprettet med succes!';
$_['text_select'] = '-- Vælg venligst --';
$_['error_email'] = 'E-mail påkrævet!';
$_['error_email_format'] = 'Indtast venligst en gyldig e-mailadresse!';
$_['error_password'] = 'Adgangskode påkrævet!';
$_['error_name'] = 'Firmanavn påkrævet!';
$_['error_url'] = 'Webadresse påkrævet!';
$_['error_country'] = 'Vælg venligst land!';
$_['error_login'] = 'Login mislykkedes. Kontroller dine oplysninger og prøv igen.';
$_['error_registration'] = 'Registrering mislykkedes. Kontroller dine oplysninger og prøv igen.';
$_['error_login_form'] = 'Kontroller dine loginoplysninger og prøv igen.';
$_['error_registration_form'] = 'Kontroller dine registreringsoplysninger og prøv igen.';
$_['error_api_credentials'] = 'API-oplysninger blev ikke modtaget fra ViaBill. Kontakt venligst support.';

// Buttons
$_['button_save']            = 'Gem';
$_['button_cancel']          = 'Annuller';

// Error
$_['error_permission']       = 'Advarsel: Du har ikke tilladelse til at ændre ViaBill Betalinger!';
$_['error_api_key']          = 'API-nøgle er påkrævet!';
$_['error_secret_key']       = 'API Secret er påkrævet!';