<?php

class klarnacheckoutpushModuleFrontController extends ModuleFrontController
{
	public $display_column_left = false;
	public $display_column_right = false;
	public $ssl = true;
	
	public function postProcess()
	{
	//$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	 //Logger::addLog($url, 1, NULL, NULL, NULL, true);
	 
		require_once(dirname(__FILE__).'/Checkout.php');
		//Klarna uses iso 3166-1 alpha 3, prestashop uses different iso so we need to convert this.
		$country_iso_codes = array(
		'SWE' => 'SE',
		'NOR' => 'NO',
		'FIN' => 'FI',
		'DNK' => 'DK',
		'DEU' => 'DE',
		'NLD' => 'NL',
		'se' => 'SE',
		'no' => 'NO',
		'fi' => 'FI',
		'dk' => 'DK',
		'de' => 'DE',
		'nl' => 'NL',
		);
		
		
		try
		{
			session_start();  
			Klarna_Checkout_Order::$contentType  = "application/vnd.klarna.checkout.aggregated-order-v2+json";
			if(!Shop::isFeatureActive())
				$connector = Klarna_Checkout_Connector::create(Configuration::get('KLARNACHECKOUT_SECRET'));  
			else
			{
				$id_shop = $this->context->shop->id;
				$connector = Klarna_Checkout_Connector::create(Configuration::get('KLARNACHECKOUT_SECRET'), $id_shop);  
			}
			@$checkoutId = $_GET['klarna_order'];
			$klarnaorder = new Klarna_Checkout_Order($connector, $checkoutId);
			$klarnaorder->fetch();
			
			if ($klarnaorder['status'] == "checkout_complete")
			{
				$id_cart = $klarnaorder['merchant_reference']['orderid2'];
				$cart = new Cart((int)($id_cart));
				//Check and handle errors
				if($cart->OrderExists())
				{
					$klarna_reservation = $klarnaorder['reservation'];
					$sql = 'SELECT * FROM `'._DB_PREFIX_.'message` m LEFT JOIN `'._DB_PREFIX_.'orders` o ON m.id_order=o.id_order WHERE o.id_cart='.(int)($id_cart);
					$messages = Db::getInstance()->ExecuteS($sql);
					foreach($messages AS $message)
					{
						//Check if reference matches
						if (strpos($message['message'],$klarna_reservation) !== false)
						{
							//Already created, send create
							$update['status'] = 'created';  
							$update['merchant_reference'] = array(  
								'orderid1' => ''.$message['id_order'],
								'orderid2' => ''.$cart->id
							);  
							$klarnaorder->update($update);
							Logger::addLog('KCO: created sent: '.$id_cart.' res:'.$klarna_reservation, 1, NULL, NULL, NULL, true);
							die;
						}
					}
					Logger::addLog('KCO: cancel cart: '.$id_cart.' res:'.$klarna_reservation, 1, NULL, NULL, NULL, true);
					
					//Duplicate reservation, cancel reservation.
					$conf = Configuration::getMultiple(array('KLARNACHECKOUT_EID','KLARNACHECKOUT_SECRET'));
					$eid = $conf['KLARNACHECKOUT_EID'];
					$md5key =  base64_encode(pack("H*", hash('md5', $eid.':'.$klarna_reservation.':'.$conf['KLARNACHECKOUT_SECRET'])));
					$params = "<param><value><string>4.1</string></value></param><param><value><string>php:xmlrpc:1.0:test</string></value></param><param><value><string>$klarna_reservation</string></value></param><param><value><int>$eid</int></value></param><param><value><string>$md5key</string></value></param>";
					$request = $this->buildRequest($params, 'cancel_reservation');
					$response = $this->sendToKlarna($request, $this->getKlarnaURL());
					if (strlen(stristr($response,'faultcode'))>0)
						Logger::addLog('KCO: cancel reservation failed: '.$klarnaorder['reservation'], 1, NULL, NULL, NULL, true);
					die;
				}//Check and handle errors
				$shipping = $klarnaorder['shipping_address'];
				$billing = $klarnaorder['billing_address'];
				//$reference = $klarnaorder['reference'];
				$reference = $klarnaorder['reservation'];
				if(!Validate::isEmail($shipping['email']))
					$shipping['email'] = 'ingen_mejl_'.$id_cart.'@ingendoman.cc';
				
				$id_customer = (int)(Customer::customerExists($shipping['email'], true, false));
				if($id_customer>0)
				{
					$customer = new Customer($id_customer);				
				}
				else
				{
					//add customer
					$customer = new Customer();
					$customer->firstname = $this->module->truncateValue($shipping['given_name'], 32, true);
					$customer->lastname =  $this->module->truncateValue($shipping['family_name'], 32, true);
					$customer->email = $shipping['email'];
					$customer->passwd =  md5(time()._COOKIE_KEY_);
					$customer->is_guest = 1;
					$customer->id_default_group = (int)(Configuration::get('PS_GUEST_GROUP', null, $cart->id_shop));
					$customer->newsletter = 0;
					$customer->optin = 0;
					$customer->active = 1;
					$customer->id_gender = 9;
					$customer->add();
				}
				//Check if address already exists, if not, add
				$delivery_address_id = 0;
				$invoice_address_id = 0;
				$shipping_iso = $country_iso_codes[$shipping['country']];
				$invocie_iso = $country_iso_codes[$billing['country']];
				$shipping_country_id = Country::getByIso($shipping_iso);
				$invocie_country_id = Country::getByIso($invocie_iso);
				
				foreach($customer->getAddresses($cart->id_lang) as $address)
				{
					if( $address['firstname'] == $shipping['given_name'] AND $address['lastname'] == $shipping['family_name']
					AND $address['city'] == $shipping['city']
					AND $address['address2'] == $shipping['care_of'] AND $address['address1'] == $shipping['street_address']
					AND $address['postcode'] == $shipping['postal_code'] AND $address['phone_mobile'] == $shipping['phone'] AND $address['id_country'] == $shipping_country_id)
					{
						//LOAD SHIPPING ADDRESS
						$cart->id_address_delivery = $address['id_address'];
						$delivery_address_id = $address['id_address'];
					}
					if( $address['firstname'] == $billing['given_name'] AND $address['lastname'] == $billing['family_name'] 
					AND $address['city'] == $billing['city']
					AND $address['address2'] == $billing['care_of'] AND $address['address1'] == $billing['street_address']
					AND $address['postcode'] == $billing['postal_code'] AND $address['phone_mobile'] == $billing['phone'] AND $address['id_country'] == $invocie_country_id)
					{
							//LOAD SHIPPING ADDRESS
							$cart->id_address_invoice = $address['id_address'];
							$invoice_address_id = $address['id_address'];
					}
				}
				if($invoice_address_id==0)
				{
					//Create address
					$address = new Address();
					$address->firstname = $this->module->truncateValue($billing['given_name'], 32, true);
					$address->lastname = $this->module->truncateValue($billing['family_name'], 32, true);
					if(strlen($billing['care_of'])>0)
					{
						$address->address1 = $billing['care_of'];
						$address->address2 = $billing['street_address'];
					}
					else
					{
						$address->address1 = $billing['street_address'];
					}
					
					$address->postcode = $billing['postal_code'];
					$address->phone = $billing['phone'];
					$address->phone_mobile = $billing['phone'];
					$address->city = $billing['city'];
					$address->id_country = $invocie_country_id;
					$address->id_customer = $customer->id;
					$address->alias = "Klarna Address";
					$address->add();
					$cart->id_address_invoice = $address->id;
					$invoice_address_id = $address->id;
				}
				if($delivery_address_id==0)
				{
					//Create address
					$address = new Address();
					$address->firstname = $this->module->truncateValue($shipping['given_name'], 32, true);
					$address->lastname = $this->module->truncateValue($shipping['family_name'], 32, true);
					
					if(strlen($shipping['care_of'])>0)
					{
						$address->address1 = $shipping['care_of'];
						$address->address2 = $shipping['street_address'];
					}
					else
					{
						$address->address1 = $shipping['street_address'];
					}
					
					$address->city = $shipping['city'];
					$address->postcode = $shipping['postal_code'];
					$address->phone = $shipping['phone'];
					$address->phone_mobile = $shipping['phone'];
					$address->id_country = $shipping_country_id;
					$address->id_customer = $customer->id;
					$address->alias = "Klarna Address";
					$address->add();
					$cart->id_address_delivery = $address->id;
					$delivery_address_id = $address->id;
				}
				
				//$delivery_option = $cart->getDeliveryOption();
				//$delivery_option = array((int)($delivery_address_id) => $cart->id_carrier.',');
				//$cart->setDeliveryOption($delivery_option);
				//$delivery_option_serialized = Db::getInstance()->getValue('SELECT delivery_option FROM '._DB_PREFIX_.'cart WHERE id_cart='.$cart->id);
				//if($delivery_option_serialized AND $delivery_option_serialized!='')
				//{
					/*$delivery_option_values = unserialize($delivery_option_serialized);
					$new_delivery_options = array();
					foreach($delivery_option_values as $key => $value)
						$new_delivery_options[(int)($delivery_address_id)] = $value;
					$new_delivery_options_serialized = serialize($new_delivery_options);*/
					
					$new_delivery_options[(int)($delivery_address_id)] = $cart->id_carrier.',';
					$new_delivery_options_serialized = serialize($new_delivery_options);
					$update_sql = 'UPDATE '._DB_PREFIX_.'cart SET delivery_option=\''.$new_delivery_options_serialized.'\' WHERE id_cart='.$cart->id;
					Db::getInstance()->execute($update_sql);
					if($cart->id_carrier>0)
						$cart->delivery_option = $new_delivery_options_serialized;
					else
						$cart->delivery_option = '';
					$update_sql = 'UPDATE '._DB_PREFIX_.'cart_product SET id_address_delivery='.$delivery_address_id.' WHERE id_cart='.$cart->id;
					Db::getInstance()->execute($update_sql);
					$flush_cache = $cart->getPackageList(true);
				//}
					
				$amount = (int)($klarnaorder['cart']['total_price_including_tax']);
				$amount = (float)($amount/100);
				
				$klarna_checkout = new KlarnaCheckout();
				$cart->id_customer = $customer->id;
				$cart->secure_key = $customer->secure_key;
				//$cart->setNoMultishipping();
				$cart->save();
				
				$update_sql = 'UPDATE '._DB_PREFIX_.'cart SET id_customer='.$customer->id.', secure_key=\''.$customer->secure_key.'\' WHERE id_cart='.$cart->id;
				Db::getInstance()->execute($update_sql);
				
				if(Configuration::get('KLARNACHECKOUT_ROUNDOFF')==1)
				{
					$total_cart_price_before_round = $cart->getOrderTotal(true, Cart::BOTH);
					$total_cart_price_after_round = round($total_cart_price_before_round);
					$diff = abs($total_cart_price_after_round - $total_cart_price_before_round);
					if($diff>0)
						$amount = $total_cart_price_before_round;
				}
				
				$extra['transaction_id'] = $reference;
				$cache_id = 'objectmodel_cart_'.$cart->id.'_0_0';
				Cache::clean($cache_id);
				$cart = new Cart($cart->id);
				//$klarna_checkout->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), number_format($amount, 2, '.', ''), $klarna_checkout->displayName, $reference, $extra, NULL,false,$cart->secure_key);
				$klarna_checkout->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), number_format($amount, 2, '.', ''), $klarna_checkout->displayName, $reference, $extra, NULL,false,$customer->secure_key);
				
				$order_reference = $klarna_checkout->currentOrder;
				if(Configuration::get('KLARNACHECKOUT_ORDERID')==1)
				{
					$order = new Order($klarna_checkout->currentOrder);
					$order_reference = $order->reference;
				}
				$update['status'] = 'created';  
				$update['merchant_reference'] = array(  
					'orderid1' => ''.$order_reference,
					'orderid2' => ''.$cart->id
				);  
				$klarnaorder->update($update);  
			}
		}
		catch (Exception $e) 
		{
		   Logger::addLog('Klarna Checkout: '.htmlspecialchars($e->getMessage()), 1, NULL, NULL, NULL, true);
		}
	}
	
	//FOR CANCEL RESERVATION
	function buildRequest($params, $function)
	{
		$request = '<?xml version="1.0" encoding="ISO-8859-1"?>'.PHP_EOL.'<methodCall>'.PHP_EOL.'<methodName>'.$function.'</methodName>'.PHP_EOL.'<params>'.PHP_EOL;
		$request .= $params;
		$request .= '</params>'.PHP_EOL.'</methodCall>';
		return $request;
	}
	function xmlrpc_encode($value, $type) 
	{
		$output = '<param><value><'.$type.'>'.htmlspecialchars($value).'</'.$type.'></value></param>'.PHP_EOL;
		return $output;
	}
	public function sendToKlarna($data, $url)
	{
		$headers = array(
		"Content-Type: text/xml",
		"User-Agent: PHPRPC/1.0",
		"Content-length: ". strlen($data),
		"Connection: Close"
		);
	  
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		curl_close($ch);
		$response = utf8_encode($response);
		
		return $response;
	}
	function getKlarnaURL()
	{
		return ((int)Configuration::get('KLARNACHECKOUT_TESTMODE')==1 ? 'https://payment.testdrive.klarna.com/' : 'https://payment.klarna.com/');
	}
	//FOR CANCEL RESERVATION
}

?>