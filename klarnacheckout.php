<?php
class KlarnaCheckout extends PaymentModule
{
	
	public function __construct()
	{
		$this->name = 'klarnacheckout';
		$this->tab = 'payments_gateways';
		$this->version = '2.38';
		$this->author = 'Prestaworks AB';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Klarna Checkout');
		$this->description = $this->l('Adds a cart block with klarna checkout links.');
	}
	
	public function install()
	{
		if (
			parent::install() == false
			|| $this->registerHook('top') == false
			|| $this->registerHook('header') == false
			|| $this->registerHook('footer') == false
			|| $this->registerHook('displayProductButtons') == false
			|| Configuration::updateValue('PS_BLOCK_CART_AJAX', 1) == false
			|| Configuration::updateValue('KLARNACHECKOUT_ROUNDOFF', 0) == false
			|| $this->setKCOCountrySettings() == false
			)
			return false;
		return true;
	}
	
	public function setKCOCountrySettings()
	{
		$norway_done = false;
		$finland_done = false;
		$sweden_done = false;
		
		$sql = 'SELECT id_address FROM '._DB_PREFIX_.'address WHERE alias=\'KCO_SVERIGE_DEFAULT\'';
		$id_address_sweden = Db::getInstance()->getValue($sql);
		if((int)($id_address_sweden)>0)
		{
			Configuration::updateValue('KLARNACHECKOUT_SWEDEN_ADDR', $id_address_sweden);
			$sweden_done = true;
		}
		else
		{
			$id_country = Country::getByIso('SE');
			$insert_sql = "INSERT INTO "._DB_PREFIX_."address (id_country, id_state, id_customer, id_manufacturer, id_supplier, id_warehouse, alias, company, lastname, firstname, address1, address2, postcode, city, other,phone, phone_mobile, vat_number, dni, active, deleted, date_add, date_upd) VALUES ($id_country, 0,0,0,0,0,'KCO_SVERIGE_DEFAULT','','Sverige', 'Person', 'Standardgatan 1', '', '12345', 'Stockholm', '', '1234567890','','','',1,0, NOW(), NOW());";
			Db::getInstance()->execute($insert_sql);
			$id_address_sweden = Db::getInstance()->getValue($sql);
			if((int)($id_address_sweden)>0)
			{
				Configuration::updateValue('KLARNACHECKOUT_SWEDEN_ADDR', $id_address_sweden);
				$sweden_done = true;
			}
		}
		
		$sql = 'SELECT id_address FROM '._DB_PREFIX_.'address WHERE alias=\'KCO_NORGE_DEFAULT\'';
		$id_address_norway = Db::getInstance()->getValue($sql);
		if((int)($id_address_norway)>0)
		{
			Configuration::updateValue('KLARNACHECKOUT_NORWAY_ADDR', $id_address_norway);
			$norway_done = true;
		}
		else
		{
			$id_country = Country::getByIso('NO');
			$insert_sql = "INSERT INTO "._DB_PREFIX_."address (id_country, id_state, id_customer, id_manufacturer, id_supplier, id_warehouse, alias, company, lastname, firstname, address1, address2, postcode, city, other,phone, phone_mobile, vat_number, dni, active, deleted, date_add, date_upd) VALUES ($id_country, 0,0,0,0,0,'KCO_NORGE_DEFAULT','','Norge', 'Person', 'Standardgatan 1', '', '12345', 'Oslo', '', '1234567890','','','',1,0, NOW(), NOW());";
			Db::getInstance()->execute($insert_sql);
			$id_address_norway = Db::getInstance()->getValue($sql);
			if((int)($id_address_norway)>0)
			{
				Configuration::updateValue('KLARNACHECKOUT_NORWAY_ADDR', $id_address_norway);
				$norway_done = true;
			}
			
		}
		$sql = 'SELECT id_address FROM '._DB_PREFIX_.'address WHERE alias=\'KCO_FINLAND_DEFAULT\'';
		$id_address_finland = Db::getInstance()->getValue($sql);
		if((int)($id_address_finland)>0)
		{
			Configuration::updateValue('KLARNACHECKOUT_FINLAND_ADDR', $id_address_finland);
			$finland_done = true;
		}
		else
		{
			$id_country = Country::getByIso('FI');
			$insert_sql = "INSERT INTO "._DB_PREFIX_."address (id_country, id_state, id_customer, id_manufacturer, id_supplier, id_warehouse, alias, company, lastname, firstname, address1, address2, postcode, city, other,phone, phone_mobile, vat_number, dni, active, deleted, date_add, date_upd) VALUES ($id_country, 0,0,0,0,0,'KCO_FINLAND_DEFAULT','','Finland', 'Person', 'Standardgatan 1', '', '12345', 'Helsinkki', '', '1234567890','','','',1,0, NOW(), NOW());";
			Db::getInstance()->execute($insert_sql);
			$id_address_finland = Db::getInstance()->getValue($sql);
			if((int)($id_address_finland)>0)
			{
				Configuration::updateValue('KLARNACHECKOUT_FINLAND_ADDR', $id_address_finland);
				$finland_done = true;
			}
		}
		if($finland_done===true AND $norway_done===true AND $sweden_done===true)
			return true;
		else
			return false;
	}
	
	public function getContent()
	{
		$isSaved = false;
		if (Tools::isSubmit('submitklarnacheckoutsettings'))
		{
			Configuration::updateValue('KLARNACHECKOUT_SECRET', Tools::getValue('KLARNACHECKOUT_SECRET'));
			Configuration::updateValue('KLARNACHECKOUT_EID', (int)Tools::getValue('KLARNACHECKOUT_EID'));
			Configuration::updateValue('KLARNACHECKOUT_TESTMODE', (int)Tools::getValue('KLARNACHECKOUT_TESTMODE'));
			Configuration::updateValue('KLARNACHECKOUT_ROUNDOFF', (int)Tools::getValue('KLARNACHECKOUT_ROUNDOFF'));
			Configuration::updateValue('PS_BLOCK_CART_AJAX', (int)Tools::getValue('PS_BLOCK_CART_AJAX'));
			Configuration::updateValue('KLARNACHECKOUT_LAYOUT', (int)Tools::getValue('KLARNACHECKOUT_LAYOUT'));
			Configuration::updateValue('KLARNACHECKOUT_NORWAY', (int)Tools::getValue('KLARNACHECKOUT_NORWAY'));
			Configuration::updateValue('KLARNACHECKOUT_FINLAND', (int)Tools::getValue('KLARNACHECKOUT_FINLAND'));
			Configuration::updateValue('KLARNACHECKOUT_SWEDEN', (int)Tools::getValue('KLARNACHECKOUT_SWEDEN'));
			Configuration::updateValue('KLARNACHECKOUT_ORDERID', (int)Tools::getValue('KLARNACHECKOUT_ORDERID'));
			$isSaved = true;
		}
		$this->context->smarty->assign(array(
			'PS_GUEST_CHECKOUT_ENABLED' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
			'KLARNACHECKOUT_TESTMODE' => Configuration::get('KLARNACHECKOUT_TESTMODE'),
			'KLARNACHECKOUT_SECRET' => Configuration::get('KLARNACHECKOUT_SECRET'),
			'KLARNACHECKOUT_EID' => Configuration::get('KLARNACHECKOUT_EID'),
			'KLARNACHECKOUT_LAYOUT' => Configuration::get('KLARNACHECKOUT_LAYOUT'),
			'PS_BLOCK_CART_AJAX' => Configuration::get('PS_BLOCK_CART_AJAX'),
			'KLARNACHECKOUT_NORWAY' => Configuration::get('KLARNACHECKOUT_NORWAY'),
			'KLARNACHECKOUT_FINLAND' => Configuration::get('KLARNACHECKOUT_FINLAND'),
			'KLARNACHECKOUT_SWEDEN' => Configuration::get('KLARNACHECKOUT_SWEDEN'),
			'KLARNACHECKOUT_ROUNDOFF' => Configuration::get('KLARNACHECKOUT_ROUNDOFF'),
			'KLARNACHECKOUT_ORDERID' => Configuration::get('KLARNACHECKOUT_ORDERID'),
			'isSaved' => $isSaved,
			'REQUEST_URI' => Tools::safeOutput($_SERVER['REQUEST_URI']),
		));
		return $this->display(__FILE__, 'views/templates/admin/klarnacheckout_admin.tpl');
	}

	public function hookDisplayProductButtons($params)
	{	
		if (Configuration::get('PS_CATALOG_MODE'))
			return;
		$this->context->smarty->assign('kcoeid', Configuration::get('KLARNACHECKOUT_EID'));
		$productPrice = Product::getPriceStatic((int)Tools::getValue('id_product'), true, NULL, 6, NULL, false, true, 1, false);
		$this->context->smarty->assign('kcoproductPrice', $productPrice);
		return $this->display(__FILE__, 'klarnaproductpage.tpl');
	}
	public function hookFooter($params)
	{
		if (Configuration::get('PS_CATALOG_MODE'))
			return;
		return $this->display(__FILE__, 'klarnafooter.tpl');
	}
	public function hookRightColumn($params)
	{
		if (Configuration::get('PS_CATALOG_MODE'))
			return;
		$this->context->smarty->assign('order_page', strpos($_SERVER['PHP_SELF'], 'order') !== false);
		$this->assignContentVars($params);
		
		return $this->display(__FILE__, 'klarnacheckout.tpl');
	}

	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

	public function hookAjaxCall($params)
	{
		if (Configuration::get('PS_CATALOG_MODE'))
			return;
		$this->assignContentVars($params);
		$res = $this->display(__FILE__, 'klarnacheckout-json.tpl');
		return $res;
	}

	public function hookHeader()
	{
		if (Configuration::get('PS_CATALOG_MODE'))
			return;
		$this->context->controller->addCSS(($this->_path).'klarnacheckout.css', 'all');
		if ((int)(Configuration::get('PS_BLOCK_CART_AJAX')))
			$this->context->controller->addJS(($this->_path).'klarnacheckout-ajax-cart.js');
	}
	
	public function hookTop($params)
	{
		return $this->hookRightColumn($params);
	}
	
	//Copied from block cart
	public function assignContentVars(&$params)
	{
		global $errors;

		// Set currency
		if ((int)$params['cart']->id_currency && (int)$params['cart']->id_currency != $this->context->currency->id)
			$currency = new Currency((int)$params['cart']->id_currency);
		else
			$currency = $this->context->currency;

		if ($params['cart']->id_customer)
		{
			$customer = new Customer((int)$params['cart']->id_customer);
			$taxCalculationMethod = Group::getPriceDisplayMethod((int)$customer->id_default_group);
		}
		else
			$taxCalculationMethod = Group::getDefaultPriceDisplayMethod();

		$useTax = !($taxCalculationMethod == PS_TAX_EXC);

		$products = $params['cart']->getProducts(true);
		$nbTotalProducts = 0;
		foreach ($products as $product)
			$nbTotalProducts += (int)$product['cart_quantity'];
		$cart_rules = $params['cart']->getCartRules();

		$shipping_cost = Tools::displayPrice($params['cart']->getOrderTotal($useTax, Cart::ONLY_SHIPPING), $currency);
		$shipping_cost_float = Tools::convertPrice($params['cart']->getOrderTotal($useTax, Cart::ONLY_SHIPPING), $currency);
		$wrappingCost = (float)($params['cart']->getOrderTotal($useTax, Cart::ONLY_WRAPPING));
		$totalToPay = $params['cart']->getOrderTotal($useTax);

		if ($useTax && Configuration::get('PS_TAX_DISPLAY') == 1)
		{
			$totalToPayWithoutTaxes = $params['cart']->getOrderTotal(false);
			$this->smarty->assign('tax_cost', Tools::displayPrice($totalToPay - $totalToPayWithoutTaxes, $currency));
		}
		
		// The cart content is altered for display
		foreach ($cart_rules as &$cart_rule)
		{
			if ($cart_rule['free_shipping'])
			{
				$shipping_cost = Tools::displayPrice(0, $currency);
				$shipping_cost_float = 0;
				$cart_rule['value_real'] -= Tools::convertPrice($params['cart']->getOrderTotal(true, Cart::ONLY_SHIPPING), $currency);
				$cart_rule['value_tax_exc'] = Tools::convertPrice($params['cart']->getOrderTotal(false, Cart::ONLY_SHIPPING), $currency);
			}
			if ($cart_rule['gift_product'])
			{
				foreach ($products as &$product)
					if ($product['id_product'] == $cart_rule['gift_product'] && $product['id_product_attribute'] == $cart_rule['gift_product_attribute'])
					{
						$product['total_wt'] = Tools::ps_round($product['total_wt'] - $product['price_wt'], (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
						$product['total'] = Tools::ps_round($product['total'] - $product['price'], (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
						$cart_rule['value_real'] = Tools::ps_round($cart_rule['value_real'] - $product['price_wt'], (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
						$cart_rule['value_tax_exc'] = Tools::ps_round($cart_rule['value_tax_exc'] - $product['price'], (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
					}
			}
		}

		$this->smarty->assign(array(
			'products' => $products,
			'customizedDatas' => Product::getAllCustomizedDatas((int)($params['cart']->id)),
			'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
			'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
			'discounts' => $cart_rules,
			'nb_total_products' => (int)($nbTotalProducts),
			'shipping_cost' => $shipping_cost,
			'shipping_cost_float' => $shipping_cost_float,
			'show_wrapping' => $wrappingCost > 0 ? true : false,
			'show_tax' => (int)(Configuration::get('PS_TAX_DISPLAY') == 1 && (int)Configuration::get('PS_TAX')),
			'wrapping_cost' => Tools::displayPrice($wrappingCost, $currency),
			'product_total' => Tools::displayPrice($params['cart']->getOrderTotal($useTax, Cart::BOTH_WITHOUT_SHIPPING), $currency),
			'total' => Tools::displayPrice($totalToPay, $currency),
			'order_process' => Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order',
			'ajax_allowed' => (int)(Configuration::get('PS_BLOCK_CART_AJAX')) == 1 ? true : false,
			'static_token' => Tools::getToken(false)
		));
		if (count($errors))
			$this->smarty->assign('errors', $errors);
		if (isset($this->context->cookie->ajax_blockcart_display))
			$this->smarty->assign('colapseExpandStatus', $this->context->cookie->ajax_blockcart_display);
	}
	
	public function truncateValue($string, $length, $abconly=false)
	{
		//$string = utf8_decode($string);
		if($abconly)
			$string = preg_replace("/[^\p{L}\p{N} -]/u", '', $string);
		//$string = utf8_encode($string);
		if (strlen($string) > $length)
		{
			return substr($string, 0, $length);
		}
		else
			return $string;
	}

}
?>