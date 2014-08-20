<?php
class klarnacheckoutcheckout_klarnaModuleFrontController extends ModuleFrontController
{
	public $display_column_left = false;
	public $display_column_right = false;
	public $ssl = true;
	
	public function setMedia()
	{
		parent::setMedia();
		if ($this->context->getMobileDevice() == false)
			$this->addJqueryPlugin(array('fancybox'));
		$this->addJS(_MODULE_DIR_.'klarnacheckout/js/klarna_checkout.js');
	}
	
	public function postProcess()
	{
		if (Tools::isSubmit('kco_change_country'))
		{
			$id_lang = 0;
			$id_currency = 0;
			if(Tools::getValue('kco_change_country')=='sv')
			{
				$id_lang = Language::getIdByIso('sv');
				$id_currency = Currency::getIdByIsoCode('SEK');
				$id_tmp_address = Configuration::get('KLARNACHECKOUT_SWEDEN_ADDR');
			}
			
			if(Tools::getValue('kco_change_country')=='fi')
			{
				$id_lang = Language::getIdByIso('fi');
				if((int)($id_lang)==0)
					$id_lang = Language::getIdByIso('sv');
				$id_currency = Currency::getIdByIsoCode('EUR');
				$id_tmp_address = Configuration::get('KLARNACHECKOUT_FINLAND_ADDR');
			}
			
			if(Tools::getValue('kco_change_country')=='no')
			{
				$id_lang = Language::getIdByIso('en');
				$id_currency = Currency::getIdByIsoCode('NOK');
				$id_tmp_address = Configuration::get('KLARNACHECKOUT_NORWAY_ADDR');
			}
			
			if(Tools::getValue('kco_change_country')=='de')
			{
				$id_lang = Language::getIdByIso('de');
				$id_currency = Currency::getIdByIsoCode('EUR');
				$id_tmp_address = Configuration::get('KLARNACHECKOUT_GERMANY_ADDR');
			}
			
			if($id_lang > 0 AND $id_currency > 0)
			{
				$_GET['id_lang'] = $id_lang;
				$_POST['id_lang'] = $id_lang;
				$_POST['id_currency'] = $id_currency;
				$_POST['SubmitCurrency'] = $id_currency;
				Tools::switchLanguage();
				Tools::setCurrency($this->context->cookie);
				$this->context->cart->id_lang = $id_lang;
				$this->context->cart->id_currency = $id_currency;
				$this->context->cart->id_address_delivery = $id_tmp_address;
				$this->context->cart->update();
				Tools::redirect('index.php?fc=module&module=klarnacheckout&controller=checkout_klarna');
			}
		}
		if (Tools::isSubmit('savemessagebutton'))
		{
			$messageContent = strip_tags($_POST['message']);
			$message_result = $this->updateMessage($messageContent, $this->context->cart);
			if(!$message_result)
				$this->context->smarty->assign('gift_error', Tools::displayError('Invalid message'));
		}
		if (Tools::isSubmit('savegift'))
		{
			$this->context->cart->gift = (int)(Tools::getValue('gift'));
			$gift_error = '';
			if (!Validate::isMessage($_POST['gift_message']))
				$gift_error = Tools::displayError('Invalid gift message');
			else
				$this->context->cart->gift_message = strip_tags($_POST['gift_message']);
			$this->context->cart->update();
			$this->context->smarty->assign('gift_error', $gift_error);
		}
		if (CartRule::isFeatureActive())
		{
			$vouchererrors = '';
			if (Tools::isSubmit('submitAddDiscount'))
			{
				if (!($code = trim(Tools::getValue('discount_name'))))
					$vouchererrors = Tools::displayError('You must enter a voucher code');
				elseif (!Validate::isCleanHtml($code))
					$vouchererrors = Tools::displayError('Voucher code invalid');
				else
				{
					if (($cartRule = new CartRule(CartRule::getIdByCode($code))) && Validate::isLoadedObject($cartRule))
					{
						if ($error = $cartRule->checkValidity($this->context, false, true))
							$vouchererrors = $error;
						else
						{
							$this->context->cart->addCartRule($cartRule->id);
							Tools::redirect('index.php?fc=module&module=klarnacheckout&controller=checkout_klarna');
						}
					}
					else
						$vouchererrors = Tools::displayError('This voucher does not exists');
				}
				$this->context->smarty->assign(array(
					'vouchererrors' => $vouchererrors,
					'discount_name' => Tools::safeOutput($code)
				));
			}
			elseif (($id_cart_rule = (int)Tools::getValue('deleteDiscount')) && Validate::isUnsignedId($id_cart_rule))
			{
				$this->context->cart->removeCartRule($id_cart_rule);
				Tools::redirect('index.php?fc=module&module=klarnacheckout&controller=checkout_klarna');
			}
		}
		
		if (Tools::getIsset('delivery_option'))
		{
			if ($this->validateDeliveryOption(Tools::getValue('delivery_option')))
				$this->context->cart->setDeliveryOption(Tools::getValue('delivery_option'));
				
			if (!$this->context->cart->update())
			{
				$this->context->smarty->assign(array(
					'vouchererrors' => Tools::displayError('Could not save carrier selection')
				));
			}

			// Carrier has changed, so we check if the cart rules still apply
			CartRule::autoRemoveFromCart($this->context);
			CartRule::autoAddToCart($this->context);
		}
	}
	
	public function initContent()
	{
		parent::initContent();
		
		if(isset($_GET['kco_update']) AND $_GET['kco_update']=='1')	
			if($this->context->cart->nbProducts() < 1)
				die;		
		
		if(!isset($this->context->cart->id))
			Tools::redirect('index.php');
		
		$currency = NEW Currency($this->context->cart->id_currency);
		$language = new Language($this->context->cart->id_lang);
		
		// you can have the website on any language you like but Klarna checkout must be on swedish, finish, norways lang..
		// Contry DE have special conditions it is whole site must be on DE if it is not then you for not using klarna checkout	
		$user_ip = '';
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$user_ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$user_ip = $_SERVER['REMOTE_ADDR'];
		}

		$lang_iso_code = $language->iso_code;
		$currency_iso_code = $currency->iso_code;
		$user_ip_iso_code = 'DE'; //geoip_country_code_by_name($user_ip);
	
		switch($currency_iso_code) {
			case 'SEK':
				$lang_iso_code = 'sv';
				break;
			
			case 'NOK':
				$lang_iso_code = 'no';
				break;
			
			case 'EUR':
				$lang_iso_code = 'de';
				if ($user_ip_iso_code != 'DE') { 
					$lang_iso_code = 'fi';
				}
				break;
		}
			
		$country_information = $this->getKlarnaCountryInformation($currency->iso_code, $lang_iso_code);

		if($country_information === false)
			Tools::redirect('index.php?controller=order&step=1');
		
		$tmp_address = new Address((int)($this->context->cart->id_address_delivery));
		$country = new Country($tmp_address->id_country);
		if($country_information['purchase_country']=='SE')
		{
			if($country->iso_code!='SE')
			{
				$this->context->cart->id_address_delivery = Configuration::get('KLARNACHECKOUT_SWEDEN_ADDR');
				$this->context->cart->update();
				Tools::redirect('index.php?fc=module&module=klarnacheckout&controller=checkout_klarna');
			}
		}
		elseif($country_information['purchase_country']=='FI')
		{
			if($country->iso_code!='FI')
			{
				$this->context->cart->id_address_delivery = Configuration::get('KLARNACHECKOUT_FINLAND_ADDR');
				$this->context->cart->update();
				Tools::redirect('index.php?fc=module&module=klarnacheckout&controller=checkout_klarna');
			}
		}
		elseif($country_information['purchase_country']=='NO')
		{
			if($country->iso_code!='NO')
			{
				$this->context->cart->id_address_delivery = Configuration::get('KLARNACHECKOUT_NORWAY_ADDR');
				$this->context->cart->update();
				Tools::redirect('index.php?fc=module&module=klarnacheckout&controller=checkout_klarna');
			}
		}
		elseif($country_information['purchase_country']=='DE')
		{
			if($country->iso_code!='DE')
			{
				$this->context->cart->id_address_delivery = Configuration::get('KLARNACHECKOUT_NORWAY_ADDR');
				$this->context->cart->update();
				Tools::redirect('index.php?fc=module&module=klarnacheckout&controller=checkout_klarna');
			}
		}
		
		$layout = 'desktop';
		//if($this->context->getMobileDevice())
		//	$layout = 'mobile';
		require_once(_PS_TOOL_DIR_.'mobile_Detect/Mobile_Detect.php');
		$mobile_detect_class = new Mobile_Detect();
		if($mobile_detect_class->isMobile() or $mobile_detect_class->isMobile())
			$layout = 'mobile';
			
		$totalCartValue = 0;
		$round_diff = 0;
		
		if(Configuration::get('KLARNACHECKOUT_ROUNDOFF')==1)
		{
			$total_cart_price_before_round = $this->context->cart->getOrderTotal(true, Cart::BOTH);
			$total_cart_price_after_round = round($total_cart_price_before_round);
			$round_diff = $total_cart_price_after_round - $total_cart_price_before_round;
		}
		
		if(isset($this->context->cart) and $this->context->cart->nbProducts() > 0)
		{
			if (!$this->context->cart->checkQuantities())
			{
				Tools::redirect('index.php?controller=order&step=1');
			}
			else
			{
				require_once(dirname(__FILE__).'/Checkout.php');			
				session_start();
				foreach($this->context->cart->getProducts() as $product)
				{		
					$price = $product['price_wt'];
					$totalCartValue += ($price * (int)($product['cart_quantity']));
					
					$price = ($price * 100);
					$checkoutcart[] = array
					(
					'reference' => (isset($product['reference']) && $product['reference'] != '' ? $product['reference'] : $product['id_product']),     
					'name' => strip_tags($product['name'].((isset($product['attributes']) AND $product['attributes'] != NULL) ? ' - '.$product['attributes'] : '').((isset($product['instructions']) AND $product['instructions'] != NULL) ? ' - '.$product['instructions'] : '')),           
					'quantity' => (int)($product['cart_quantity']),  
					'unit_price' => $price,  
					'discount_rate' => 0,  
					'tax_rate' => (int)($product['rate'])*100  
					);
				}
				
				$shipping_cost_with_tax = $this->context->cart->getOrderTotal(true,Cart::ONLY_SHIPPING);
				$shipping_cost_without_tax = $this->context->cart->getOrderTotal(false,Cart::ONLY_SHIPPING);
				
				if($shipping_cost_without_tax>0)
				{
					$shipping_tax_rate = ($shipping_cost_with_tax / $shipping_cost_without_tax)-1;
					$totalCartValue += $shipping_cost_with_tax;
					
					$checkoutcart[] = array
					(
						'type' => 'shipping_fee',      
						'reference' => 'frakt',  
						'name' => 'Frakt',  
						'quantity' => 1,          
						'unit_price' => ($shipping_cost_with_tax * 100),  
						'tax_rate' => (int)($shipping_tax_rate * 10000)  
					);  
				}
				if($this->context->cart->gift==1)
				{
					$cart_wrapping = $this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
					$common_tax_rate = 25; //Discounts are set to 25% for now
					if($cart_wrapping > 0)
					{
						$totalCartValue += $cart_wrapping;
						$checkoutcart[] = array
						(     
							'reference' => 'inslagning',  
							'name' => 'Inslagning',  
							'quantity' => 1,          
							'unit_price' => ($cart_wrapping * 100),  
							'tax_rate' => (int)($common_tax_rate * 100)  
						);  
					}
				}
				
				//DISCOUNTS
				$discounts = $this->context->cart->getCartRules();
				$totalDiscounts = 0;
				foreach($discounts as $discount)
				{
					$price = $discount['value_real'];
					$totalDiscounts += $price;
				}
				if($totalDiscounts > $totalCartValue)
				{
					//Free order
					$common_tax_rate = 25; //Discounts are set to 25% for now
					$price = $totalCartValue;
					$checkoutcart[] = array
					(
						'type' => 'discount',      
						'reference' => $discount['name'],  
						'name' => $discount['name'],  
						'quantity' => 1,          
						'unit_price' => -($price * 100),  
						'tax_rate' => (int)($common_tax_rate * 100)  
					);  
				}
				else
				{
					//Add discounts
					foreach($discounts as $discount)
					{
						$common_tax_rate = 25; //Discounts are set to 25% for now
						$price = $discount['value_real'];
						$checkoutcart[] = array
						(
							'type' => 'discount',      
							'reference' => $discount['name'],  
							'name' => $discount['name'],  
							'quantity' => 1,          
							'unit_price' => -($price * 100),  
							'tax_rate' => (int)($common_tax_rate * 100)  
						);  
					}
				}
				
				if($round_diff!=0)
				{
					$checkoutcart[] = array
					(
						'reference' => '',     
						'name' => 'Avrundning',           
						'quantity' => 1,  
						'unit_price' => (int)($round_diff * 100),  
						'discount_rate' => 0,  
						'tax_rate' => 0  
					);
				}
			
					
				$checkoutPage = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://'). $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
				$callbackPage = $this->context->link->getModuleLink('klarnacheckout', 'thank_you');
				//$pushPage = $this->context->link->getModuleLink('klarnacheckout', 'push', array('klarna_order' => '{checkout.order.uri}', 'sid' => '123'));
				$pushPage = $this->context->link->getModuleLink('klarnacheckout', 'push', array('sid' => '123'));
				$pushPage .= '&klarna_order={checkout.order.uri}';
				$checkout = $this->context->link->getModuleLink('klarnacheckout', 'checkout_klarna');
				$cms = new CMS((int)(Configuration::get('PS_CONDITIONS_CMS_ID')), (int)($this->context->cookie->id_lang));
				$link_conditions = $this->context->link->getCMSLink($cms, $cms->link_rewrite, true);
				$termsPage = $link_conditions;
				try 
				{
					if((int)(Configuration::get('KLARNACHECKOUT_TESTMODE'))==1)
						Klarna_Checkout_Order::$baseUri = 'https://checkout.testdrive.klarna.com/checkout/orders';
					else
						Klarna_Checkout_Order::$baseUri = 'https://checkout.klarna.com/checkout/orders';
					Klarna_Checkout_Order::$contentType = "application/vnd.klarna.checkout.aggregated-order-v2+json";
					$eid = (int)(Configuration::get('KLARNACHECKOUT_EID'));  
					$sharedSecret = Configuration::get('KLARNACHECKOUT_SECRET');  
					$connector = Klarna_Checkout_Connector::create($sharedSecret);
					$klarnaorder = null;  
					if (array_key_exists('klarna_checkout', $_SESSION)) 
					{  
						// Resume session  
						$klarnaorder = new Klarna_Checkout_Order(  
							$connector,  
							$_SESSION['klarna_checkout']  
						);  
						try 
						{  
							$klarnaorder->fetch();  
					  
							// Reset cart  
							$update['cart']['items'] = array();  
							foreach ($checkoutcart as $item) {  
								$update['cart']['items'][] = $item;  
							}
							
							$update['purchase_country'] = $country_information['purchase_country'];
							$update['purchase_currency'] = $country_information['purchase_currency'];
							$update['locale'] = $country_information['locale'];
							$klarnaorder->update($update);  
						} 
						catch (Exception $e) {  
							// Reset session  
							$klarnaorder = null;  
							unset($_SESSION['klarna_checkout']);  
						}  
					}
					if ($klarnaorder == null) 
					{
						$klarnaorder = new Klarna_Checkout_Order($connector);
						
						$create['purchase_country'] = $country_information['purchase_country'];
						$create['purchase_currency'] = $country_information['purchase_currency'];
						$create['locale'] = $country_information['locale'];
						if(Configuration::get('KLARNACHECKOUT_LAYOUT')==0)
							$create['gui']['options'] = array('disable_autofocus');
						$create['gui']['layout'] = $layout;
						$create['merchant']['id'] = "".$eid;
						$create['merchant']['terms_uri'] = $termsPage;
						$create['merchant']['checkout_uri'] = $checkout;
						$create['merchant']['confirmation_uri']  = $callbackPage;
						$create['merchant']['push_uri'] = $pushPage;
						$create['merchant_reference']['orderid2'] = "".(int)($this->context->cart->id);
					   
						foreach ($checkoutcart as $item) 
						{  
							$create['cart']['items'][] = $item;  
						}
						$klarnaorder->create($create);
						$klarnaorder->fetch();
						$_SESSION['klarna_checkout'] = $sessionId = $klarnaorder->getLocation(); 
					}
					
					$id_country = 0;
					if($country_information['purchase_country']=='SV')
						$id_country = Country::getByIso('se');
					if($country_information['purchase_country']=='FI')
						$id_country = Country::getByIso('fi');
					if($country_information['purchase_country']=='NO')
						$id_country = Country::getByIso('no');
					if($country_information['purchase_country']=='DE')
						$id_country = Country::getByIso('de');
						
					$cart_summary = $this->context->cart->getSummaryDetails();
					
					if($klarnaorder!=null)
					{
						$snippet = $klarnaorder['gui']['snippet'];
						if(isset($_GET['kco_update']) AND $_GET['kco_update']=='1')
							die($snippet);

						$this->context->smarty->assign('klarna_checkout', $snippet);
						
						
							
						/*$wrapping_fees = (float)(Configuration::get('PS_GIFT_WRAPPING_PRICE'));
						$wrapping_fees_tax = new Tax((int)(Configuration::get('PS_GIFT_WRAPPING_TAX')));
						$wrapping_fees_tax_inc = $wrapping_fees * (1 + (((float)($wrapping_fees_tax->rate) / 100)));
					*/
						$wrapping_fees_tax_inc = $this->context->cart->getGiftWrappingPrice(true);

						$this->context->smarty->assign('discounts', $this->context->cart->getCartRules());
						$this->context->smarty->assign('cart_is_empty', false);
						$this->context->smarty->assign('gift', $this->context->cart->gift);
						$this->context->smarty->assign('gift_message', $this->context->cart->gift_message);
						$this->context->smarty->assign('giftAllowed', (int)(Configuration::get('PS_GIFT_WRAPPING')));
						$this->context->smarty->assign('gift_wrapping_price', Tools::convertPrice($wrapping_fees_tax_inc, NEW Currency($this->context->cart->id_currency)));
						$this->context->smarty->assign('message', Message::getMessageByCartId((int)($this->context->cart->id)));
					}

					
					

					if($id_country>0)
						$delivery_option_list = $this->context->cart->getDeliveryOptionList(new Country($id_country), true);
					else
						$delivery_option_list = $this->context->cart->getDeliveryOptionList();

					$free_shipping = false;
					foreach ($this->context->cart->getCartRules() as $rule)
						if ($rule['free_shipping'])
						{
							$free_shipping = true;
							break;
						}
					$free_fees_price = 0;
					$configuration = Configuration::getMultiple(array('PS_SHIPPING_FREE_PRICE','PS_SHIPPING_FREE_WEIGHT'));
					if (isset($configuration['PS_SHIPPING_FREE_PRICE']) AND $configuration['PS_SHIPPING_FREE_PRICE'] > 0)
					{
						$free_fees_price = Tools::convertPrice((float)$configuration['PS_SHIPPING_FREE_PRICE'], Currency::getCurrencyInstance((int)$this->context->cart->id_currency));
						$orderTotalwithDiscounts = $this->context->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, null, null, false);
						$left_to_get_free_shipping = $free_fees_price - $orderTotalwithDiscounts;
						$this->context->smarty->assign('left_to_get_free_shipping', $left_to_get_free_shipping);
					}
					if (isset($configuration['PS_SHIPPING_FREE_WEIGHT']) AND $configuration['PS_SHIPPING_FREE_WEIGHT'] > 0)
					{
						$free_fees_weight = $configuration['PS_SHIPPING_FREE_WEIGHT'];
						$total_weight = $this->context->cart->getTotalWeight();
						$left_to_get_free_shipping_weight = $free_fees_weight - $total_weight;
						$this->context->smarty->assign('left_to_get_free_shipping_weight', $left_to_get_free_shipping_weight);
					}
					
					$delivery_option = $this->context->cart->getDeliveryOption(new Country($id_country), false, false);

					$currencySign = $this->context->currency->sign;
					$currencyRate = $this->context->currency->conversion_rate;
					$currencyFormat = $this->context->currency->format;
					$currencyBlank = $this->context->currency->blank;
					$voucherAllowed = CartRule::isFeatureActive();
					
					$no_active_countries = 0;
					$show_sweden = false;
					$show_norway = false;
					$show_finland = false;
					$show_germany = false;
					
					if((int)(Configuration::get('KLARNACHECKOUT_SWEDEN')) == 1)
					{
						$no_active_countries++;
						$show_sweden = true;
					}
					if((int)(Configuration::get('KLARNACHECKOUT_FINLAND')) == 1)
					{
						$no_active_countries++;
						$show_finland = true;
					}
					if((int)(Configuration::get('KLARNACHECKOUT_NORWAY')) == 1)
					{
						$no_active_countries++;
						$show_norway = true;
					}
					if((int)(Configuration::get('KLARNACHECKOUT_GERMANY')) == 1)
					{
						$no_active_countries++;
						$show_germany = true;
					}
						
					$this->context->smarty->assign(array(
						'no_active_countries' => $no_active_countries,
						'show_norway' => $show_norway,
						'show_finland' => $show_finland,
						'show_sweden' => $show_sweden,
						'show_germany' => $show_germany,
						'kco_selected_country' => $country_information['purchase_country'],
						'klarna_checkout' => $snippet,
						//'displayVouchers' => Discount::getVouchersToCartDisplay($this->context->language->id, (isset($this->context->customer->id) ? $this->context->customer->id : 0)),
						'discounts' => $this->getDiscounts(),
						'posturl' => Tools::safeOutput($_SERVER['REQUEST_URI']),
						'free_shipping' => $free_shipping,
						'cart_summary' => $cart_summary,
						'token_cart' => $this->context->cart->secure_key,
						'HOOK_SHOPPING_CART' => Hook::exec('displayShoppingCartFooter', $cart_summary),
						'HOOK_SHOPPING_CART_EXTRA' => Hook::exec('displayShoppingCart', $cart_summary),
						'delivery_option_list' => $delivery_option_list,
						'delivery_option' => $delivery_option,
						'currencySign' => $currencySign,
						'currencyRate' => $currencyRate,
						'currencyFormat' => $currencyFormat,
						'currencyBlank' => $currencyBlank,
						'voucherAllowed' => $voucherAllowed,
						'kcourl' => $checkout,
					));

				}
				catch(Exception $e) 
				{
					$this->context->smarty->assign('klarna_error', $e->getMessage());
				}
			}
		}
		else
			$this->context->smarty->assign('klarna_error', 'empty_cart');
		if($layout == 'mobile')
			$this->setTemplate('mobilecheckoutpage.tpl');
		else
		{
			if(Configuration::get('KLARNACHECKOUT_LAYOUT')==1)
				$this->setTemplate('checkoutpage.tpl');
			else
				$this->setTemplate('checkoutpage_height.tpl');
		}
	}
	
	public function getDiscounts()
	{
		$cart_rules = $this->context->cart->getSummaryDetails();
		return $cart_rules['discounts'];
	}
	
	protected function validateDeliveryOption($delivery_option)
	{
		if (!is_array($delivery_option))
			return false;
		
		foreach ($delivery_option as $option)
			if (!preg_match('/(\d+,)?\d+/', $option))
				return false;
		
		return true;
	}
	
	
	protected function updateMessage($messageContent, $cart)
	{
		if ($messageContent)
		{
			if (!Validate::isMessage($messageContent))
				return false;
			elseif ($oldMessage = Message::getMessageByCartId((int)($cart->id)))
			{
				$message = new Message((int)($oldMessage['id_message']));
				$message->message = htmlentities($messageContent, ENT_COMPAT, 'UTF-8');
				$message->update();
			}
			else
			{
				$message = new Message();
				$message->message = htmlentities($messageContent, ENT_COMPAT, 'UTF-8');
				$message->id_cart = (int)($cart->id);
				$message->id_customer = (int)($cart->id_customer);
				$message->add();
			}
		}
		else
		{
			if ($oldMessage = Message::getMessageByCartId((int)($cart->id)))
			{
				$message = new Message((int)($oldMessage['id_message']));
				$message->delete();
			}
		}
		return true;
	}
	
	protected function getKlarnaCountryInformation($currency_iso_code, $language_iso_code)
	{
		if($currency_iso_code == 'SEK' AND $language_iso_code=='sv' AND Configuration::get('KLARNACHECKOUT_SWEDEN')==1)
			return array('locale' => 'sv-se', 'purchase_currency' => 'SEK', 'purchase_country' => 'SE');
		elseif($currency_iso_code == 'EUR' AND $language_iso_code=='fi' AND Configuration::get('KLARNACHECKOUT_FINLAND')==1)
			return array('locale' => 'fi-fi', 'purchase_currency' => 'EUR', 'purchase_country' => 'FI');
		elseif($currency_iso_code == 'NOK' AND $language_iso_code=='no' AND Configuration::get('KLARNACHECKOUT_NORWAY')==1)
			return array('locale' => 'nb-no', 'purchase_currency' => 'NOK', 'purchase_country' => 'NO');
		elseif($currency_iso_code == 'EUR' AND $language_iso_code=='sv' AND Configuration::get('KLARNACHECKOUT_FINLAND')==1)
			return array('locale' => 'sv-fi', 'purchase_currency' => 'EUR', 'purchase_country' => 'FI');
		elseif($currency_iso_code == 'EUR' AND $language_iso_code=='de' AND Configuration::get('KLARNACHECKOUT_GERMANY')==1)
			return array('locale' => 'de-de', 'purchase_currency' => 'EUR', 'purchase_country' => 'DE');
		else
			return false;
	}
}
?>