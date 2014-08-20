<?php
class klarnacheckoutthank_youModuleFrontController extends ModuleFrontController
{
	public $display_column_left = false;
	public $display_column_right = false;
	public $ssl = true;
	
	public function initContent()
	{
		parent::initContent();
		require_once(dirname(__FILE__).'/Checkout.php');

		session_start();  
		if(!isset($_SESSION['klarna_checkout']))
			Tools::redirect('index.php');
		try 
		{
			/**
			 * Fetch the checkout resource.
			 */
			Klarna_Checkout_Order::$contentType  = "application/vnd.klarna.checkout.aggregated-order-v2+json";  
			$connector = Klarna_Checkout_Connector::create(Configuration::get('KLARNACHECKOUT_SECRET'));  
			
			$checkoutId = $_SESSION['klarna_checkout'];  
			$klarnaorder = new Klarna_Checkout_Order($connector, $checkoutId);  
			$klarnaorder->fetch();
			
			if ($klarnaorder['status'] == 'checkout_incomplete') 
			{  
				Tools::redirect('index.php?fc=module&module=klarnacheckout&controller=checkout_klarna');
			}  
			
			$snippet = $klarnaorder['gui']['snippet'];  
			
			
			$sql = 'SELECT id_order FROM '._DB_PREFIX_.'orders WHERE id_cart='.(int)($klarnaorder['merchant_reference']['orderid2']);
			$result = Db::getInstance()->getRow($sql);
			if(!isset($result['id_order']))
			{
				//Give push a few extra seconds
				sleep(2);
				$sql = 'SELECT id_order FROM '._DB_PREFIX_.'orders WHERE id_cart='.(int)($klarnaorder['merchant_reference']['orderid2']);
				$result = Db::getInstance()->getRow($sql);
				if(!isset($result['id_order']))
				{
					sleep(3);
					$sql = 'SELECT id_order FROM '._DB_PREFIX_.'orders WHERE id_cart='.(int)($klarnaorder['merchant_reference']['orderid2']);
					$result = Db::getInstance()->getRow($sql);
				}
			}
			$this->context->smarty->assign(array(
					'klarna_html' => $snippet,
					'HOOK_ORDER_CONFIRMATION' => $this->displayOrderConfirmation((int)($result['id_order']))
				));

			unset($_SESSION['klarna_checkout']); 
			
			

		}
		catch (Klarna_Exception $e) 
		{
			$this->context->smarty->assign('klarna_error', $e->getMessage());
		}
		$this->setTemplate('thankyoupage.tpl');
	}
	
	public function displayOrderConfirmation($id_order)
	{
		if (Validate::isUnsignedId($id_order))
		{
			$params = array();
			$order = new Order($id_order);
			$currency = new Currency($order->id_currency);

			if (Validate::isLoadedObject($order))
			{
				$params['total_to_pay'] = $order->getOrdersTotalPaid();
				$params['currency'] = $currency->sign;
				$params['objOrder'] = $order;
				$params['currencyObj'] = $currency;

				return Hook::exec('displayOrderConfirmation', $params);
			}
		}
		return false;
	}
}
?>