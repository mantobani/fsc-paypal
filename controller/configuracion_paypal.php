<?php 
/**
 * @author Mayantigo Marrero      mantobani@gmail.com
 * @copyright 2014-2018, Mayantigo Marrero. All Rights Reserved. 
 */
 
class configuracion_paypal extends fs_controller
{
    public $paypal_setup;
	public $empresa;
  
	public function __construct()
	{
		parent::__construct(__CLASS__, 'Configuración Paypal', 'admin');
	}


	protected function private_core()
	{
		parent::private_core();
        /// cargamos la configuración
        $fsvar = new fs_var();
		
        $this->paypal_setup = $fsvar->array_get(
			array(
				'paypal_sandbox' => FALSE,
				'paypal_client_id' => '',
				'paypal_sandbox_client_id' => '',
				'paypal_encryption_key' => '',
				'paypal_txt_info' => 'Al pulsar el botón pagar se abrirá la ventana de pago donde deberá iniciar sesión con PayPal para proceder con el pago. <br />También puede pagar con tarjeta sin necesidad de tener cuenta en PayPal.',
				'paypal_texto_enlace' => 'Pulse aquí para pagar mediante PayPal.'
            ),
            FALSE
         );
		 
		$paypal_encryption_key = $fsvar->simple_get('paypal_encryption_key');
		if (empty ($paypal_encryption_key)){
			$this->paypal_setup['paypal_encryption_key'] = base64_encode(openssl_random_pseudo_bytes(32));
			if( $fsvar->array_save($this->paypal_setup) ){};
		}
         
         if( isset($_POST['paypal_setup']) )
         {
			$this->paypal_setup['paypal_sandbox'] = filter_input(INPUT_POST, 'sandbox');
			$this->paypal_setup['paypal_client_id'] = filter_input(INPUT_POST, 'client_id');
			$this->paypal_setup['paypal_sandbox_client_id'] = filter_input(INPUT_POST, 'sandbox_client_id');
			$this->paypal_setup['paypal_txt_info'] = filter_input(INPUT_POST, 'txt_info');
			if (empty($_POST['texto_enlace'])){
				$this->paypal_setupp['paypal_texto_enlace'] = 'Pulse aquí para pagar mediante Paypal';
			} else {
			$this->paypal_setup['paypal_texto_enlace'] = filter_input(INPUT_POST, 'texto_enlace');
			}
			
            if( $fsvar->array_save($this->paypal_setup) )
            {
               $this->new_message('Datos guardados correctamente.');
            }
            else
               $this->new_error_msg('Error al guardar los datos.');
         }
		 
		 if($this->empresa->can_send_mail()) {
			 $this->can_send_mail = TRUE;
		 } else {
			 $this->can_send_mail = FALSE;
		 }
      
   }
  
   
}
