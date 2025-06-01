<?php
// Heading
$_['heading_title']          = 'Pagos ViaBill';

// Text
$_['text_extension']         = 'Extensiones';
$_['text_success']           = 'Éxito: ¡Has modificado el módulo de pagos ViaBill!';
$_['text_edit']              = 'Editar Pagos ViaBill';
$_['text_authorization']     = 'Solo Autorización';
$_['text_authorization_capture'] = 'Autorización y Captura';
$_['text_capture_success']   = '¡Pago capturado con éxito!';
$_['text_capture_error']     = 'Error al capturar el pago:';
$_['text_refund_success']    = '¡Pago reembolsado con éxito!';
$_['text_refund_error']      = 'Error al reembolsar el pago:';
$_['text_void_success']      = '¡Pago anulado con éxito!';
$_['text_void_error']        = 'Error al anular el pago:';
$_['text_all_zones']         = 'Todas las Zonas';
$_['text_enabled']           = 'Habilitado';
$_['text_disabled']          = 'Deshabilitado';

// Entry Labels (form fields)
$_['entry_api_key']          = 'Clave API de ViaBill';
$_['entry_secret_key']       = 'Clave Secreta de ViaBill';
$_['entry_test_mode']        = 'Modo de Pruebas';
$_['entry_transaction_mode'] = 'Tipo de Transacción';
$_['entry_transaction_mode_help'] = 'Elija si desea solo autorizar los pagos o autorizar y capturar inmediatamente.';
$_['entry_pricetag']         = 'Script de PriceTag de ViaBill';
$_['entry_pricetag_help']    = 'El script de ViaBill responsable de mostrar los PriceTags.';
$_['entry_authorize_order_status']     = 'Estado del Pedido (Tras Autorización)';
$_['entry_capture_order_status']      = 'Estado del Pedido (Tras Captura)';
$_['entry_status']           = 'Estado del Módulo';
$_['entry_hide_checkout']    = 'Ocultar en checkout';
$_['entry_sort_order']       = 'Orden de Aparición';
$_['entry_geo_zone']         = 'Zona Geográfica';

// Pricetags
$_['tab_pricetag'] = 'Configuración PriceTag';
$_['entry_pricetag_common']  = 'Opciones Comunes de PriceTag';
$_['entry_pricetag_product']               = 'PriceTag en Página de Producto';
$_['entry_pricetag_product_label']         = 'Etiqueta de PriceTag';
$_['entry_pricetag_cart']               = 'PriceTag en Página del Carrito';
$_['entry_pricetag_cart_label']         = 'Etiqueta de PriceTag';
$_['entry_pricetag_checkout']               = 'PriceTag en Página de Pago';
$_['entry_pricetag_checkout_label']         = 'Etiqueta de PriceTag';

$_['entry_pricetag_language'] = 'Idioma';
$_['entry_pricetag_country'] = 'País';
$_['entry_pricetag_currency'] = 'Moneda';
$_['entry_pricetag_decimal_separator'] = 'Separador Decimal';

$_['entry_pricetag_product_dynamic_price'] = 'Selector de Precio Dinámico';
$_['entry_pricetag_product_dynamic_price_trigger'] = 'Disparador de Precio Dinámico';
$_['entry_pricetag_product_dynamic_price_trigger_delay'] = 'Retraso del Disparador';
$_['entry_pricetag_product_append_selector'] = 'Selector para Añadir PriceTag';
$_['entry_pricetag_product_append_after'] = 'Añadir Después del Selector';

$_['entry_pricetag_cart_dynamic_price'] = 'Selector de Precio Dinámico';
$_['entry_pricetag_cart_dynamic_price_trigger'] = 'Disparador de Precio Dinámico';
$_['entry_pricetag_cart_dynamic_price_trigger_delay'] = 'Retraso del Disparador';
$_['entry_pricetag_cart_append_selector'] = 'Selector para Añadir PriceTag';
$_['entry_pricetag_cart_append_after'] = 'Añadir Después del Selector';

$_['entry_pricetag_checkout_dynamic_price'] = 'Selector de Precio Dinámico';
$_['entry_pricetag_checkout_dynamic_price_trigger'] = 'Disparador de Precio Dinámico';
$_['entry_pricetag_checkout_dynamic_price_trigger_delay'] = 'Retraso del Disparador';
$_['entry_pricetag_checkout_append_selector'] = 'Selector para Añadir PriceTag';
$_['entry_pricetag_checkout_append_after'] = 'Añadir Después del Selector';

$_['entry_pricetag_product_align'] = 'Alineación (Página de Producto)';
$_['entry_pricetag_product_width'] = 'Ancho (Página de Producto)';
$_['entry_pricetag_cart_align'] = 'Alineación (Carrito)';
$_['entry_pricetag_cart_width'] = 'Ancho (Carrito)';
$_['entry_pricetag_checkout_align'] = 'Alineación (Pago)';
$_['entry_pricetag_checkout_width'] = 'Ancho (Pago)';

// Authentication
$_['text_viabill_setup'] = 'Configuración de Cuenta ViaBill';
$_['text_viabill_account_required'] = 'Debes conectar tu cuenta de ViaBill antes de poder configurar el módulo de pago. Si no tienes una cuenta, puedes registrarte.';
$_['tab_login'] = 'Iniciar Sesión';
$_['tab_register'] = 'Registrarse';
$_['entry_email'] = 'Correo Electrónico';
$_['entry_password'] = 'Contraseña';
$_['entry_name'] = 'Nombre del Negocio';
$_['entry_url'] = 'URL del Sitio Web';
$_['entry_country'] = 'País';
$_['entry_tax_id'] = 'ID Fiscal (Número de IVA)';
$_['help_tax_id'] = 'Opcional: El número de identificación fiscal de tu empresa.';
$_['button_login'] = 'Iniciar Sesión';
$_['button_register'] = 'Registrarse';
$_['text_login_success'] = '¡Has iniciado sesión correctamente en tu cuenta de ViaBill!';
$_['text_register_success'] = '¡Tu cuenta de ViaBill ha sido creada con éxito!';
$_['text_select'] = '-- Por favor selecciona --';
$_['error_email'] = '¡El correo electrónico es obligatorio!';
$_['error_email_format'] = '¡Por favor introduce un correo electrónico válido!';
$_['error_password'] = '¡La contraseña es obligatoria!';
$_['error_name'] = '¡El nombre del negocio es obligatorio!';
$_['error_url'] = '¡La URL del sitio web es obligatoria!';
$_['error_country'] = '¡Por favor selecciona un país!';
$_['error_login'] = 'Error al iniciar sesión. Por favor verifica tus credenciales e inténtalo de nuevo.';
$_['error_registration'] = 'Error en el registro. Por favor revisa la información e inténtalo nuevamente.';
$_['error_login_form'] = 'Por favor verifica tu información de inicio de sesión e inténtalo de nuevo.';
$_['error_registration_form'] = 'Por favor verifica tu información de registro e inténtalo de nuevo.';
$_['error_api_credentials'] = 'No se recibieron credenciales API de ViaBill. Por favor contacta con soporte.';

// Buttons
$_['button_save']            = 'Guardar';
$_['button_cancel']          = 'Cancelar';

// Error
$_['error_permission']       = 'Advertencia: ¡No tienes permiso para modificar los pagos ViaBill!';
$_['error_api_key']          = '¡La clave API es obligatoria!';
$_['error_secret_key']       = '¡La clave secreta es obligatoria!';
