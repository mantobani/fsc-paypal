<?php 
/**
 * @author Mayantigo Marrero      mantobani@gmail.com
 * @copyright 2014-2018, Mayantigo Marrero. All Rights Reserved. 
 */
if (!function_exists('my_encrypt')) {
	
	function my_encrypt($data, $key) {
			// Remove the base64 encoding from our key
			$encryption_key = base64_decode($key);
			// Generate an initialization vector
			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
			// Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
			$encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
			// The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
			return base64_encode($encrypted . '::' . base64_encode($iv));
	}
}	

if (!function_exists('plantilla_email')) {

    /**
     * Devuelve el texto para un email con las modificaciones oportunas.
     * @param string $tipo
     * @param string $documento
     * @param string $firma
     * @return string
     */
    function plantilla_email($tipo, $documento, $firma)
    {
        /// obtenemos las plantillas
        $fsvar = new fs_var();
        $plantillas = array(
            'mail_factura' => "Buenos días, le adjunto su #DOCUMENTO#.\n#FIRMA#",
            'mail_albaran' => "Buenos días, le adjunto su #DOCUMENTO#.\n#FIRMA#",
            'mail_pedido' => "Buenos días, le adjunto su #DOCUMENTO#.\n#FIRMA#",
            'mail_presupuesto' => "Buenos días, le adjunto su #DOCUMENTO#.\n#FIRMA#",
        );
        $plantillas = $fsvar->array_get($plantillas, FALSE);

		$documento_orig = $documento;
		
		$id_doc = filter_input(INPUT_GET, 'id');
		$doc0 = new factura_cliente();
		
        if ($tipo == 'albaran') {
            $documento = FS_ALBARAN . ' ' . $documento;
			$doc0 = new albaran_cliente();
        } else if ($tipo == 'pedido') {
            $documento = FS_PEDIDO . ' ' . $documento;
			$doc0 = new pedido_cliente();
        } else if ($tipo == 'presupuesto') {
            $documento = FS_PRESUPUESTO . ' ' . $documento;
			$doc0 = new presupuesto_cliente();
		} else if ($tipo == 'factura') { 
            $documento = FS_FACTURA . ' ' . $documento;
			$doc0 = new factura_cliente();
        } else {
            $documento = $tipo . ' ' . $documento;
        }

		$documento_data = $doc0->get($id_doc);
		
		// Generamos enlace de pago
		$parameters = $documento_data->total .'|'. $documento_orig .'|'. $documento_data->nombrecliente . '|'. ucwords($tipo) .'|'. $documento_data->coddivisa ;
		
		//encriptamos 
		$parameters_encrypted = my_encrypt($parameters, $fsvar->simple_get('paypal_encryption_key'));
		
		$texto_enlace = $fsvar->simple_get('paypal_texto_enlace');
		$base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'] .  rtrim(dirname($_SERVER['SCRIPT_NAME']),'/' );
		$enlace_pago_paypal = '<a href="'. $base_url .'/index.php?page=paypal_pago&data='. $parameters_encrypted .'">'. $texto_enlace .'</a>';
		
		
		// Juntamos todo y lo metemos en la firma
		$shortcode =  array (
			'#DOCUMENTO#', 
			'#ENLACEDEPAGO_PAYPAL#',
			'#FIRMA#'
			);
		$parsedcode = array ( 
			$documento,
			$enlace_pago_paypal,
			$firma
			);
			
		
        $firma_final = str_replace($shortcode, $parsedcode, $plantillas['mail_' . $tipo]);
		return print_r( $firma_final,true );
    }
}
