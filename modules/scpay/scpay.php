<?php
/**
* Share Commerce - Prestashop Plugin
*
* @package Payment Method
* @author ShareCommerce
*
*/
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_'))
    exit;

class SCPay extends PaymentModule {
	private $_html = '';
	private $_postErrors = array();

	public function __construct() {
		$this->name = 'scpay';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.1';
		$this->author = 'ShareCommerce';
		$this->author_uri = 'https://github.com/share-commerce/Plugin.Prestashop.1.7.-';
		$this->controllers = array('payment', 'validation');
		$this->bout_valide = $this->l('Validate');        
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';        
		$this->bootstrap = true;

		$config = Configuration::getMultiple(array('SCPAY_MERCHANT_SKEY', 'SCPAY_MERCHANT_ID', 'SCPAY_ENVIRONMENT'));
		if(isset($config['SCPAY_MERCHANT_SKEY']))
            $this->SCPAY_MERCHANT_SKEY = $config['SCPAY_MERCHANT_SKEY'];
		if(isset($config['SCPAY_MERCHANT_ID']))
			$this->SCPAY_MERCHANT_ID = $config['SCPAY_MERCHANT_ID'];
		if(isset($config['SCPAY_ENVIRONMENT']))
            $this->SCPAY_ENVIRONMENT = $config['SCPAY_ENVIRONMENT'];

		parent::__construct();
		$this->displayName = 'Share Commerce';
		$this->description = $this->l('We are digital payment platform strive to drive the market towards real digital payments and cashless market, connect agents, merchants and partners with the ease of our leading technologies and customizable modules.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

		if(!count(Currency::checkPaymentCurrencies($this->id)))
				$this->warning = $this->l('No currency set for this module');
		if(!isset($this->SCPAY_MERCHANT_SKEY) || !isset($this->SCPAY_MERCHANT_ID))
				$this->warning = $this->l('Your Share Commerce account must be set correctly');
		if(!isset($this->SCPAY_ENVIRONMENT))
				$this->warning = $this->l('This plugin required an environment type selected.');
	}

	public function install() {
		if (!parent::install() || !$this->registerHook('paymentOptions') || !$this->registerHook('paymentReturn') || !$this->registerHook('payment') || !$this->registerHook('header') )
			return false;
		else
			return true;
	}

	public function uninstall() {
		if (!Configuration::deleteByName('SCPAY_MERCHANT_SKEY') || !Configuration::deleteByName('SCPAY_MERCHANT_ID') || !Configuration::deleteByName('SCPAY_ENVIRONMENT') || !parent::uninstall())
			return false;
		else
			return true;
	}

	protected function _postValidation() {
		if (Tools::isSubmit('btnSubmit')) {
			if (!Tools::getValue('merchant_id'))
				$this->_postErrors[] = $this->l('Merchant ID is required');
			else if (!Tools::getValue('merchant_skey'))
                $this->_postErrors[] = $this->l('Merchant SKey is required.');	
			else if (!Tools::getValue('environment'))
				$this->_postErrors[] = $this->l('Environment Type is required.');
		}
	}

	protected function _postProcess() {
		if (isset($_POST['btnSubmit'])) {
			Configuration::updateValue('SCPAY_MERCHANT_ID', Tools::getValue('merchant_id'));
            Configuration::updateValue('SCPAY_MERCHANT_SKEY', Tools::getValue('merchant_skey'));
			Configuration::updateValue('SCPAY_ENVIRONMENT', Tools::getValue('environment'));
		}
		$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
	}

	private function _displaySCPay() {
		return $this->display(__FILE__, 'infos.tpl');
	}

	public function getContent()
	{

		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= $this->displayError($err);
		}
		else
			$this->_html .= '<br />';


		$this->_html .= $this->_displaySCPay();
		$this->_html .= $this->renderForm();

		return $this->_html;
	}

	/**
	 * Hook the payment form to the prestashop Payment method. Display in payment method selection
	 * 
	 * @param array $params
	 * @return string
	 */
	public function hookPaymentOptions($params) {
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;
		
		$newOption = new PaymentOption();
		$newOption->setCallToActionText($this->trans('Share Commerce', array(), 'Modules.SCPay.Shop'));
		$newOption->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true));
		
		$payment_options = [
			$newOption,
		];

		return $payment_options;
	}

	/**
	 * Check the currency
	 * 
	 * @param object $cart
	 * @return boolean
	 */
	public function checkCurrency($cart) {
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}	

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Account details'),
					'icon' => 'icon-envelope'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Merchant ID'),
						'name' => 'merchant_id',
						'required' => true
					),
					array(
						'type' => 'text',
						'label' => $this->l('Secret Key'),
						'name' => 'merchant_skey',
						'required' => true
					),
					array(
						'type' => 'select',
						'label' => $this->l('Environment Type'),
						'desc' => $this->l('Default will be Test'),
						'name' => 'environment',
						'required' => true,
						'options' => array(
							'query' => array(
										array('environment' => 'Test','name' => 'Test'),
										array('environment' => 'Production','name' => 'Production'),
										),                           
							'id' => 'environment',
							'name' => 'name'
						)
					)
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->id = (int)Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmit';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		$SCPAY_ENVIRONMENT = Configuration::get('SCPAY_ENVIRONMENT');
		if( empty($SCPAY_ENVIRONMENT) )
			$SCPAY_ENVIRONMENT = 'Test';
		
		
		$result = array(
			'merchant_id'	=> Tools::getValue('merchant_id', Configuration::get('SCPAY_MERCHANT_ID')),
			'merchant_skey' => Tools::getValue('merchant_skey', Configuration::get('SCPAY_MERCHANT_SKEY')),
			'environment' 	=> Tools::getValue('environment', $SCPAY_ENVIRONMENT),
		);

		return $result;
	}
}
?>