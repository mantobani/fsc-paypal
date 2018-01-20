<?php
/**
 * @author Mayantigo Marrero      mantobani@gmail.com
 * @copyright 2014-2018, Mayantigo Marrero. All Rights Reserved. 
 */
require_once 'extras/phpmailer/class.phpmailer.php';
require_once 'extras/phpmailer/class.smtp.php';


class paypal_pago extends fs_controller {


	public function __construct()
    {
        parent::__construct(__CLASS__, 'Paypal pago', 'admin', FALSE, FALSE, FALSE);
    }
	
	protected function private_core(){
		
		parent::private_core();
		$this->template = 'paypal_pago';
		$this->generarpago();
	}
	
    protected function public_core() {
        
		parent::public_core();
		$this->template = 'paypal_pago';
		$this->generarpago();

    }
	
	protected function my_decrypt($data, $key) {
		// Remove the base64 encoding from our key
		$encryption_key = base64_decode($key);
		// To decrypt, split the encrypted data from our IV - our unique separator used was "::"
		list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
		return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, base64_decode($iv));
	}
	
	public function generarpago(){

		$fsvar = new fs_var();
        $sandbox = $fsvar->simple_get('paypal_sandbox');
		if ($sandbox){
			$this->entorno = 'sandbox';
		} else {
			$this->entorno = 'production';
		}
		$this->client_id = $fsvar->simple_get('paypal_client_id');
        $this->sandbox_client_id = $fsvar->simple_get('paypal_sandbox_client_id');
        $this->mostrartlf = $fsvar->simple_get('paypal_mostrartlf');
        $this->tlfmostrado = $fsvar->simple_get('paypal_tlfmostrado');
        $this->mostrarform = $fsvar->simple_get('paypal_mostrarform');
		$this->txt_info = nl2br( $fsvar->simple_get('paypal_txt_info'));
        $this->mostrarrecaptcha = $fsvar->simple_get('paypal_mostrarrecaptcha');
        $this->recaptcha_secreta = $fsvar->simple_get('paypal_recaptcha_secreta');
        $this->recaptcha_sitio = $fsvar->simple_get('paypal_recaptcha_sitio');
		$encryption_key = $fsvar->simple_get('paypal_encryption_key');
		
		//recogemos el hash de la url y lo decodificamos
		if(!isset($_GET["data"])){
			$this->tienehash = FALSE;
			$this->new_error_msg("<i class='fa fa-exclamation-triangle'></i> No se reconoce el pago.");
		}else{
			$data=explode("|", $this->my_decrypt (urldecode($_GET["data"]), $encryption_key));
		
			$this->importe = str_replace(",",".",$data[0]);
			$this->enlace_pago = $_GET["data"];
			
			if (isset($data[1]) && isset($data[2]) && isset($data[3]) && isset($data[4]) ){
				
				$this->doc_codigo = $data[1];
				$this->nombre_cliente = $data[2];
				$this->doc_tipo = $data[3];
				$this->doc_coddivisa = $data[4];
				$this->tienehash = TRUE;
				$importePasarela=$this->importe*100;
				$this->importeformateado = money_format('%i', $this->importe);

			}
			else{
				$this->tienehash = FALSE;
				$this->new_error_msg("<i class='fa fa-exclamation-triangle'></i> Datos incompletos.");
			}
		}
	}
	
	public function new_error_msg($msg = FALSE, $tipo = 'paypal_pago', $alerta = FALSE, $guardar = TRUE)
    {
        parent::new_error_msg($msg, $tipo, $alerta, $guardar);

        return $msg . '<br/>';
    }
	
}
