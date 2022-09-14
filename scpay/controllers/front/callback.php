<?php

class SCPayCallbackModuleFrontController extends ModuleFrontController
{
	public function postProcess(){
		$cart = $this->context->cart;
		$customer = new Customer($cart->id_customer);
		
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$signstr = "";
		foreach ($_REQUEST as $key => $value) {
			if ($key == 'SCSign' || $key == 'fc' || $key == 'module' || $key == 'controller') {
				continue;
			}

			$signstr .= $value;
		}

		$hash_signstr = hash_hmac('sha256', $signstr, Configuration::get('SCPAY_MERCHANT_SKEY'));
			
		$resp_code = $_REQUEST['RespCode'];
		$resp_desc = $_REQUEST['RespDesc'];
		$order_id = $_REQUEST['MerchantRef1'];
		$transaction_ref = $_REQUEST['TxnRefNo'];
			
		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);

		$pay_status = Configuration::get('PS_OS_ERROR');

		if ($hash_signstr == $_REQUEST['SCSign']) {
			if ($_REQUEST['RespCode'] == '00' || $var['RespDesc'] == 'Success') {
				$pay_status = Configuration::get('PS_OS_PAYMENT');
			} else {
				$pay_status = Configuration::get('PS_OS_ERROR');
			}
		}

		$this->module->validateOrder($cart->id, $pay_status, $total, $this->module->displayName, 'transaction reference: '.$resp_desc, [], (int)$currency->id, false, $customer->secure_key, null);

		die("OK");
	}
}