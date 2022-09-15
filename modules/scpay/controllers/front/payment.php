<?php

class SCPayPaymentModuleFrontController extends ModuleFrontController{
	public function initContent(){
		parent::initContent();
		
		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');

		$total = sprintf(
			$this->getTranslator()->trans('%1$s (tax incl.)', array(), 'Modules.SCPay.Shop'),
			Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH))
		);

		$order_id = $this->context->cart->id;
		$address = new Address(intval($this->context->cart->id_address_delivery));
		$phone = isset($address->phone) && !empty($address->phone) ? $address->phone : $address->phone_mobile;

		$data = array(
            'MerchantID' => $this->module->SCPAY_MERCHANT_ID,
            'CurrencyCode' => 'MYR',
            'TxnAmount' => number_format($this->context->cart->getOrderTotal(true), 2, '.', ''),
            'MerchantOrderNo' => $order_id . '_' . time(),
            'MerchantOrderDesc' => "Payment for Order No. : " . $order_id,
            'MerchantRef1' => $order_id,
            'MerchantRef2' => '',
            'MerchantRef3' => '',
            'CustReference' => '',
            'CustName' => $this->context->customer->firstname . ' ' . $this->context->customer->lastname,
            'CustEmail' => $this->context->customer->email,
            'CustPhoneNo' => str_replace(array('+','-'), '', $phone),
            'CustAddress1' => $address->address1,
            'CustAddress2' => $address->address2,
            'CustCountryCode' => $this->context->country->iso_code,
            'CustAddressState' => '',
            'CustAddressCity' => $address->city,
            'RedirectUrl' => ( isset($_SERVER['HTTPS'])  ? 'https' : 'http' ).'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'index.php?fc=module&module=scpay&controller=validation',
        );

		// create sign
		$signstr = "";
        foreach ($data as $key => $value) {
            $signstr .= $value;
        }

		$data['SCSign'] = hash_hmac('sha256', $signstr, $this->module->SCPAY_MERCHANT_SKEY);

		if ($this->module->SCPAY_ENVIRONMENT=='Test'){
			$data['action'] = 'https://staging.payment.share-commerce.com/payment'; 
		}else{
			$data['action'] = 'https://payment.share-commerce.com/payment';
		}

        $this->context->smarty->assign($data);
		
		$this->setTemplate('module:scpay/views/templates/front/payment_page.tpl');
	}
}