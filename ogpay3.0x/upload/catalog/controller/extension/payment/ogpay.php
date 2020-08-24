<?php
class ControllerExtensionPaymentOgpay extends Controller {

	public function index() {

		$this->load->language('extension/payment/ogpay');
		
		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['payment_method_mode'] = $this->config->get('payment_ogpay_payment_method_mode');
		
		if($data['payment_method_mode']=="ogpayment"){
		   
		    if(empty($this->config->get('payment_ogpay_ogpayment_name'))){
		       $paymentMethodCode = 'all';			   
			}else{
			   $paymentMethodCode = $this->config->get('payment_ogpay_ogpayment_name');
			}
			
			if(empty($this->config->get('payment_ogpay_ogpayment_currency'))){
				$paymentMethodCurrency = $this->config->get('payment_ogpay_currency');
			}else{
				$paymentMethodCurrency = $this->config->get('payment_ogpay_ogpayment_currency');
			}
			
			
			
		    $response = $this->getRedirectUrl($paymentMethodCode,$paymentMethodCurrency);
			
    	    if(isset($response['success'])){
    	        $data['success'] = true;
    	        $data['url'] = $response['url'];
    	    }else{
    	        $data['error'] = true;
    	        $data['error_message'] = $response['errorMessgae'];	        
    	    }
		    $data['payment_channels'] = '';
		    
		    if(empty($this->config->get('payment_ogpay_checkout_title'))){
		        $data['title'] = $this->language->get('default_title');		        
		    }else{
		        $data['title'] = $this->config->get('payment_ogpay_checkout_title');
		    }
		    
		    if(empty($this->config->get('payment_ogpay_checkout_description'))){
			    $data['description'] = $this->language->get('default_description');		        
		    }else{
			    $data['description'] = $this->config->get('payment_ogpay_checkout_description');		        
		    }
		    
            

			$data['custom'] = $this->session->data['order_id'];

			return $this->load->view('extension/payment/ogpay', $data);	
			
		}elseif($data['payment_method_mode']=="customize"){
		
		    $data['url'] = '';
		    $data['payment_channels'] = json_decode($this->config->get('payment_ogpay_customize_method'),true);
		    
		    if(empty($this->config->get('payment_ogpay_checkout_title'))){
		        $data['title'] = $this->language->get('default_title');		        
		    }else{
		        $data['title'] = $this->config->get('payment_ogpay_checkout_title');
		    }
		    
		    if(empty($this->config->get('payment_ogpay_checkout_description'))){
			    $data['description'] = $this->language->get('default_description');		        
		    }else{
			    $data['description'] = $this->config->get('payment_ogpay_checkout_description');		        
		    }
			$data['custom'] = $this->session->data['order_id'];

			return $this->load->view('extension/payment/ogpay', $data);
			
		}

	}
	
	public function getUrl(){
	    $json = array();
		
	    if(isset($this->request->post['payment_code'])){
	        $paymentCode = $this->request->post['payment_code'];
    		$payment_channels = json_decode($this->config->get('payment_ogpay_customize_method'),true);
    		$paymentMethodCurrency = '';
    		foreach($payment_channels as $key=>$channel){
    		    if($channel['code']==$paymentCode){
    		        $paymentMethodCurrency = $channel['currency'];
    		    }
    		}	        
	    }else{
	        $paymentCode = 'all';
	    	$paymentMethodCurrency = '';
	    }

	    $response = $this->getRedirectUrl($paymentCode,$paymentMethodCurrency);
	  
	    if(isset($response['success'])){
	        $json['success'] = true;
	        $json['url'] = $response['url'];
	    }else{
	        $json['error'] = true;
	        $json['error_message'] = $response['errorMessgae'];	        
	    }
	    
	    
	    $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getRedirectUrl($paymentCode="",$paymentMethodCurrency=""){
	
	   $authKey = $this->config->get('payment_ogpay_auth_key');
	   $currency = $this->config->get('payment_ogpay_currency');
	   $merchantID = $this->config->get('payment_ogpay_merchant_name');
	   $secretkey = $this->config->get('payment_ogpay_secret_key');
	   $tunnel = $this->config->get('payment_ogpay_tunnel');
	   $endpoint = $this->config->get('payment_ogpay_endpoint_url');
	   $callback = $this->url->link('extension/payment/ogpay/callback/',true);
	   
	   if(empty($this->config->get('payment_ogpay_language'))){
	       $language = 'en';
	   }else{
	       $language = $this->config->get('payment_ogpay_language');
	   }	   
	   
	   if($this->config->get('payment_ogpay_payment_method_mode')=='customize'){
    	   if($paymentMethodCurrency==$currency){
    		   $doConvert = "N";
    		   $sourceCurrency = "";
    	   }else{
    		   $doConvert = "Y";
    		   $sourceCurrency = $currency;		   
    	   }	       
	   }
	   
	   if($this->config->get('payment_ogpay_payment_method_mode')=='ogpayment'){
	       
	       if(strcasecmp($paymentCode,"all")==0){
    		   $doConvert = "N";
    		   $sourceCurrency = "";	
    		   $paymentMethodCurrency = $currency;
	       }else{
        	   if($paymentMethodCurrency==$currency){
        		   $doConvert = "N";
        		   $sourceCurrency = "";
        	   }else{
        		   $doConvert = "Y";
        		   $sourceCurrency = $currency;		   
        	   }	           
	       }
	       
	   }	   

	
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data[] = array();
        
        
		if ($order_info) {	

        $description = array();

		foreach ($this->cart->getProducts() as $product) {
            $description[] = $product['name'];
		}
			
        $description = implode(' | ',$description);
		
        $ref=$this->generateRefrenceId($this->session->data['order_id']); // set up a blank string
		    

		$timestamp = date("y/m/d H:m:s t");
		$tunnel = '';
		$userReference = $order_info['telephone'];
		$amount = $order_info['total'];

	   
        $datatocomputeHash = $amount.$authKey.$paymentMethodCurrency.$merchantID.$paymentCode.$ref.$sourceCurrency.$timestamp.$tunnel.$userReference;
		
        $hash = strtoupper(hash_hmac("sha256", $datatocomputeHash,$secretkey));

		   $data = array(
			  'merchantCode' => $merchantID,
			  'authKey' => $authKey,
			  'currency' => $paymentMethodCurrency,
			  'pc'=> $paymentCode,
			  'tunnel'=> $tunnel,
			  'amount'=> (float)$amount,
			  'doConvert'=> $doConvert,
			  'sourceCurrency'=> $sourceCurrency,
			  'description'=> $description,
			  'referenceID'=> (int)$ref,
			  'timeStamp'=> $timestamp,
			  'language'=> $language,
			  'callbackURL'=> $callback,
			  'hash'=> $hash,
			  'userReference'=> (int)$userReference,
			  'billingDetails'=> array(
					'fName'=> $order_info['payment_firstname'],
					'lName'=> $order_info['payment_lastname'],
					'mobile'=> $userReference,
					'email'=> $order_info['email'],
					'city'=> $order_info['payment_city'],
					'pincode'=> $order_info['payment_postcode'],
					'state'=> $order_info['payment_state'],
					'address1'=> $order_info['payment_address_1'],
					'address2'=> $order_info['payment_address_2']
			  ),
		);	
        $request = json_encode($data,true);
       
		if (!$endpoint) {
		    $curl = curl_init('https://ogpaystage.oneglobal.com/OgPay/V1/api/GenToken/Validate');
		} else {
			$curl = curl_init($endpoint);
		}

			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);            
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));			
			$ch = curl_exec($curl);
			curl_close($curl);
		
            $response = json_decode($ch,true);
            $returnParam = array();
            
            if($response['errorCode']=='0'){
			    $this->load->model('extension/payment/ogpay');
				$this->model_extension_payment_ogpay->updateTrackID($ref,$this->session->data['order_id']);
                $returnParam['success'] = 'Y';
                $returnParam['url'] = $response['result']['redirectURL'];  	
                //print_r($returnParam);exit;
            }else{
                $returnParam['error'] = 'Y';
                $returnParam['errorMessgae'] = $response['errorMessgae'];              
            }
           // print_r($request);exit;
            return $returnParam;
	}
    }	
    
    public function generateRefrenceId($orderid){

        $digits_needed = 15;
        
        $orderid = time().$orderid;
        
        $length = strlen((int)$orderid);
		
        if($length<$digits_needed){
		
			$required = $digits_needed-$length;
			
			$id='';
			for ($i = 1; $i <= $required; $i++) {
				   $id .= 1;
			}
			
			$refrenceId = $id.$orderid;
		}else{
			$refrenceId = $id.$orderid;
		}
		
        return (int)$refrenceId;
    }

	public function callback() {

		if (isset($this->request->get)) {
			$response = $this->request->get;
		} else {
			$response = 0;
		}
        if($response){
		
		$this->load->model('extension/payment/ogpay');

		$this->load->model('checkout/order');
		
		$order_id = $this->session->data['order_id'];

		$order_info = $this->model_extension_payment_ogpay->getOrder($this->session->data['order_id']);

		if ($order_info) {

            //Validate response data
			$secretkey = $this->config->get('payment_ogpay_secret_key');
			$hash = $response['Hash'];
			$outParams = "trackid=".$order_info['track_id']."&result=".$response['result']."&refid=".$response['refid'];
			$outHash = strtoupper(hash_hmac("sha256", $outParams,$secretkey));

			if($hash==$outHash){
			   
			if (isset($response['result'])) {
			
				switch($response['result']) {
					case 'CAPTURED':
						$order_status_id = $this->config->get('config_order_status_id');
						$redirect = 'checkout/success';
						break;
					case 'NOT CAPTURED':
						$order_status_id = 0;	
						$redirect = 'checkout/checkout';
						break;
					case 'DECLINED':
						$order_status_id = 8;
						$redirect = 'checkout/checkout';
						break;
					case 'REJECTED':
						$order_status_id = 8;
						$redirect = 'checkout/checkout';
						break;
					case 'BLOCKED':
						$order_status_id = 8;
						$redirect = 'checkout/checkout';
						break;
				}
                
				$this->model_checkout_order->addOrderHistory($order_id, $order_status_id);
			
			} else {
				$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));
			}
			  $this->load->controller($redirect);
			}else{
			   echo "Hash Not Matched for -".$order_info['track_id']."<br/>";
			    echo $hash."<br/>";
				echo $outHash;
			    exit;
			    
			}
			

		}
	}
}
}