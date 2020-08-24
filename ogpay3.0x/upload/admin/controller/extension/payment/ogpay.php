<?php
class ControllerExtensionPaymentOgpay extends Controller {
	private $error = array();
	
	public function index() {
		$this->load->language('extension/payment/ogpay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
		
        if(isset($this->request->post['payment_ogpay_payment_method_mode']) && $this->request->post['payment_ogpay_payment_method_mode']=="customize" && isset($this->request->post['payment_ogpay_customize_method'])){
        
			$mc = array();
			$mc = $this->request->post['payment_ogpay_customize_method'];
			$this->request->post['payment_ogpay_customize_method'] = json_encode($mc,true);
        }			
 
        $this->model_setting_setting->editSetting('payment_ogpay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['button_configure'] = $this->url->link('extension/module/ogpay_button/configure', 'user_token=' . $this->session->data['user_token'], true);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['account'])) {
			$data['error_account'] = $this->error['account'];
		} else {
			$data['error_account'] = array();
		}
		
		if (isset($this->error['merchantname'])) {
			$data['error_merchantname'] = $this->error['merchantname'];
		} else {
			$data['error_merchantname'] = '';
		}
		if (isset($this->error['authkey'])) {
			$data['error_authkey'] = $this->error['authkey'];
		} else {
			$data['error_authkey'] = '';
		}
		if (isset($this->error['secretkey'])) {
			$data['error_secretkey'] = $this->error['secretkey'];
		} else {
			$data['error_secretkey'] = '';
		}
		if (isset($this->error['endpointurl'])) {
			$data['error_endpointurl'] = $this->error['endpointurl'];
		} else {
			$data['error_endpointurl'] = '';
		}
		if (isset($this->error['currency'])) {
			$data['error_currency'] = $this->error['currency'];
		} else {
			$data['error_currency'] = '';
		}
		if (isset($this->error['language'])) {
			$data['error_language'] = $this->error['language'];
		} else {
			$data['error_language'] = '';
		}


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/ogpay', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/ogpay', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
		
		$data['tab_general'] = $this->language->get('tab_general');
		
		// Genral Settings
		if (isset($this->request->post['payment_ogpay_checkout_title'])) {
			$data['payment_ogpay_checkout_title'] = $this->request->post['payment_ogpay_checkout_title'];
		} else {
			$data['payment_ogpay_checkout_title'] = $this->config->get('payment_ogpay_checkout_title');
		}
		if (isset($this->request->post['payment_ogpay_checkout_description'])) {
			$data['payment_ogpay_checkout_description'] = $this->request->post['payment_ogpay_checkout_description'];
		} else {
			$data['payment_ogpay_checkout_description'] = $this->config->get('payment_ogpay_checkout_description');
		}
		if (isset($this->request->post['payment_ogpay_status'])) {
			$data['payment_ogpay_status'] = $this->request->post['payment_ogpay_status'];
		} else {
			$data['payment_ogpay_status'] = $this->config->get('payment_ogpay_status');
		}		
		
		// Configuration
		if (isset($this->request->post['payment_ogpay_merchant_name'])) {
			$data['payment_ogpay_merchant_name'] = $this->request->post['payment_ogpay_merchant_name'];
		} else {
			$data['payment_ogpay_merchant_name'] = $this->config->get('payment_ogpay_merchant_name');
		}
		if (isset($this->request->post['payment_ogpay_auth_key'])) {
			$data['payment_ogpay_auth_key'] = $this->request->post['payment_ogpay_auth_key'];
		} else {
			$data['payment_ogpay_auth_key'] = $this->config->get('payment_ogpay_auth_key');
		}
		if (isset($this->request->post['payment_ogpay_secret_key'])) {
			$data['payment_ogpay_secret_key'] = $this->request->post['payment_ogpay_secret_key'];
		} else {
			$data['payment_ogpay_secret_key'] = $this->config->get('payment_ogpay_secret_key');
		}
		
		if (isset($this->request->post['payment_ogpay_endpoint_url'])) {
			$data['payment_ogpay_endpoint_url'] = $this->request->post['payment_ogpay_endpoint_url'];
		} else {
			$data['payment_ogpay_endpoint_url'] = $this->config->get('payment_ogpay_endpoint_url');
		}
		
		if (isset($this->request->post['payment_ogpay_callback_url'])) {
			$data['payment_ogpay_callback_url'] = $this->request->post['payment_ogpay_callback_url'];
		} else {
			$data['payment_ogpay_callback_url'] = $this->config->get('payment_ogpay_callback_url');
		}


		// Payment Configuration
		if (isset($this->request->post['payment_ogpay_currency'])) {
			$data['payment_ogpay_currency'] = $this->request->post['payment_ogpay_currency'];
		} else {
			$data['payment_ogpay_currency'] = $this->config->get('payment_ogpay_currency');
		}
		if (isset($this->request->post['payment_ogpay_language'])) {
			$data['payment_ogpay_language'] = $this->request->post['payment_ogpay_language'];
		} else {
			$data['payment_ogpay_language'] = $this->config->get('payment_ogpay_language');
		}
		if (isset($this->request->post['payment_ogpay_tunnel'])) {
			$data['payment_ogpay_tunnel'] = $this->request->post['payment_ogpay_tunnel'];
		} else {
			$data['payment_ogpay_tunnel'] = $this->config->get('payment_ogpay_tunnel');
		}

        //Payment channels Configuration
		if (isset($this->request->post['payment_ogpay_payment_method_mode'])) {
			$data['payment_ogpay_payment_method_mode'] = $this->request->post['payment_ogpay_payment_method_mode'];
		} else {
			$data['payment_ogpay_payment_method_mode'] = $this->config->get('payment_ogpay_payment_method_mode');
		}	

        if(isset($this->request->post['payment_ogpay_payment_method_mode']) && $this->request->post['payment_ogpay_payment_method_mode']=="ogpayment"){
			if (isset($this->request->post['payment_ogpay_ogpayment_name'])) {
				$data['payment_ogpay_ogpayment_name'] = $this->request->post['payment_ogpay_ogpayment_name'];
			} else {
				$data['payment_ogpay_ogpayment_name'] = $this->config->get('payment_ogpay_ogpayment_name');
			}
			if (isset($this->request->post['payment_ogpay_ogpayment_currency'])) {
				$data['payment_ogpay_ogpayment_currency'] = $this->request->post['payment_ogpay_ogpayment_currency'];
			} else {
				$data['payment_ogpay_ogpayment_currency'] = $this->config->get('payment_ogpay_ogpayment_currency');
			}			
        }elseif($this->config->get('payment_ogpay_payment_method_mode') && $this->config->get('payment_ogpay_payment_method_mode')=="ogpayment"){
                if($this->config->get('payment_ogpay_ogpayment_name')){
    			   $data['payment_ogpay_ogpayment_name'] = $this->config->get('payment_ogpay_ogpayment_name');
                }else{
                   $data['payment_ogpay_ogpayment_name'] = ''; 
                }
                
                if($this->config->get('payment_ogpay_ogpayment_currency')){
    			   $data['payment_ogpay_ogpayment_currency'] = $this->config->get('payment_ogpay_ogpayment_currency');
                }else{
                   $data['payment_ogpay_ogpayment_currency'] = ''; 
                }
                
	   }
		
        if(isset($this->request->post['payment_ogpay_payment_method_mode']) && $this->request->post['payment_ogpay_payment_method_mode']=="customize"){
            if(isset($this->request->post['payment_ogpay_customize_method'])){
    			$data['customize_methods'] = array();
    			$data['customize_methods'] = json_decode($this->request->post('payment_ogpay_customize_method'),true);
    		}else{
    			$data['customize_methods'] = array();
    			$data['customize_methods'] = json_decode($this->config->get('payment_ogpay_customize_method'),true);		    
    		}	
	   }elseif($this->config->get('payment_ogpay_payment_method_mode') && $this->config->get('payment_ogpay_payment_method_mode')=="customize"){

    			$data['customize_methods'] = array();
    			$data['customize_methods'] = json_decode($this->config->get('payment_ogpay_customize_method'),true);
	   }

		if (isset($this->request->post['payment_ogpay_status'])) {
			$data['payment_ogpay_status'] = $this->request->post['payment_ogpay_status'];
		} else {
			$data['payment_ogpay_status'] = $this->config->get('payment_ogpay_status');
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/ogpay', $data));
	}

	public function install() {
		$this->load->model('setting/setting');
        $this->load->model('extension/payment/ogpay');
		$defaults = array();

		// Genral Settings
		$defaults['payment_ogpay_checkout_title'] = "";
		$defaults['payment_ogpay_checkout_description'] = "";
		
		// Configuration
		$defaults['payment_ogpay_merchant_name'] = "";
		$defaults['payment_ogpay_auth_key'] = "";
		$defaults['payment_ogpay_secret_key'] = "";
		$defaults['payment_ogpay_endpoint_url'] = "";
		$defaults['payment_ogpay_callback_url'] = "";

		// Payment Configuration
		$defaults['payment_ogpay_currency'] = "";
		$defaults['payment_ogpay_language'] = "";
		$defaults['payment_ogpay_tunnel'] = "";

        //Payment channels Configuration
		$defaults['payment_ogpay_payment_method_mode'] = "";
		$defaults['payment_ogpay_customize_method'] = "";
		$defaults['payment_ogpay_ogpayment_method'] = "";        
		
		$this->model_setting_setting->editSetting('payment_ogpay', $defaults);
		$this->model_extension_payment_ogpay->addColumn();
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/paypoint')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_ogpay_merchant_name']) {
			$this->error['merchantname'] = $this->language->get('error_ogpay_merchant_name');
		}
		if (!$this->request->post['payment_ogpay_auth_key']) {
			$this->error['authkey'] = $this->language->get('error_ogpay_auth_key');
		}
		if (!$this->request->post['payment_ogpay_secret_key']) {
			$this->error['secretkey'] = $this->language->get('error_ogpay_secret_key');
		}
		if (!$this->request->post['payment_ogpay_endpoint_url']) {
			$this->error['endpointurl'] = $this->language->get('error_ogpay_endpoint_url');
		}
		if (!$this->request->post['payment_ogpay_currency']) {
			$this->error['currency'] = $this->language->get('error_ogpay_currency');
		}
		if (!$this->request->post['payment_ogpay_language']) {
			$this->error['language'] = $this->language->get('error_ogpay_language');
		}
		
		return !$this->error;
	}

}
