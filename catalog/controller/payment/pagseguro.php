<?php

/*
 ************************************************************************
 Copyright [2013] [PagSeguro Internet Ltda.]

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 ************************************************************************
 */

/**
 * Controller Payment PagSeguro
 * Class responsible for payment PagSeguro.
 */
include_once 'PagSeguroLibrary/PagSeguroLibrary.php';
class ControllerPaymentPagSeguro extends Controller
{

	/**
	 *
	 * @var pagSeguroPaymentRequestObject
	 */
	private $_pagSeguroPaymentRequestObject;

	/**
	 * Credential
	 * @var credential
	 */
	private $_credential;

	/**
	 * model_checkout_order->getOrder
	 * @var array
	 */
	private $_order_info;

	/**
	 * Url PagSeguro
	 * @var url
	 */
	private $_urlPagSeguro;

	/**
	 * Version API
	 * @var version
	 */
	private $api_version = '1.0';

	/**
	 * The first method to be called by the payment PagSeguro treatment.
	 */
	protected function index()
	{

		$this->_load();
		$this->_retrievePagSeguroModuleVersion();
		$this->_setCmsVersion();
		$this->_generatePagSeguroOrderStatus();

		$this->_order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$this->_pagSeguroPaymentRequestObject = $this->_generatePagSeguroPaymentRequestObject();
		$this->_pagSeguroPaymentRequestObject->setReference($this->session->data['order_id']);

		$this->_performPagSeguroRequest($this->_pagSeguroPaymentRequestObject);
		$this->_updateOrderStatus();

		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['action'] = '';
		$this->data['url_ps'] = '';
		$this->_action();
		$this->template = 'default/template/payment/pagseguro.tpl';

		$this->render();
	}

	/**
	 * Load model and language
	 */
	private function _load()
	{
		PagSeguroConfig::activeLog($this->_getDirectoryLog());
		$this->language->load('payment/pagseguro');
		$this->load->model('checkout/order');
		$this->load->model('setting/setting');
		$this->load->model('payment/pagseguro');
		$this->language->load('payment/pagseguro');
	}

	/**
	 * Retrieve PagSeguro osCommerce module version
	 */
	private function _retrievePagSeguroModuleVersion()
	{
		PagSeguroLibrary::setModuleVersion('opencart' . ':' . $this->api_version);
	}

	/**
	 * Set version CMS
	 */
	private function _setCmsVersion()
	{
		PagSeguroLibrary::setCMSVersion('opencart' . ':' . VERSION);
	}

	/**
	 * Generate PagSeguro Payment Request
	 * @return \PagSeguroPaymentRequest
	 */
	private function _generatePagSeguroPaymentRequestObject()
	{
		$paymentRequest = new PagSeguroPaymentRequest();
		$paymentRequest->setCurrency(PagSeguroCurrencies::getIsoCodeByName("REAL"));
		$paymentRequest->setExtraAmount($this->_generateExtraAmount());
		$paymentRequest->setRedirectURL($this->_getPagSeguroRedirectUrl());
		$paymentRequest->setNotificationURL($this->_getPagSeguroNotificationURL());
		$paymentRequest->setItems($this->_generatePagSeguroProductsData());
		$paymentRequest->setSender($this->_generatepagSeguroSenderDataObject());
		$paymentRequest->setShipping($this->_generatePagSeguroShippingDataObject());
		return $paymentRequest;
	}

	/**
	 * Generate Extra Amount.
	 * @return double
	 */
	private function _generateExtraAmount()
	{
		$_extra = 0;
		$_shipping_cost = $this->_generatePagSeguroShippingCost();
		$_sub_total = $this->cart->getSubTotal();
		$_order_total = $this->_order_info['total'];

		if (($_shipping_cost + $_sub_total) != $_order_total) {

			if ($_shipping_cost != 0)
				$_order_total = $_order_total - $_shipping_cost;

			$_extra = $_order_total - $_sub_total;
		}
		return $this->currency->format($_extra, $this->_order_info['currency_code'], false, false);
	}

	/**
	 * PagSeguro Redirect url
	 * @return redirect url
	 */
	private function _getPagSeguroRedirectUrl()
	{

		if ($this->_isNotNull($this->config->get('pagseguro_forwarding')))
			return $this->config->get('pagseguro_forwarding');

		return HTTPS_SERVER . "index.php?route=checkout/success";

	}

	/**
	 * PagSeguro notification url
	 * @return notification url
	 */
	private function _getPagSeguroNotificationURL()
	{

		if ($this->_isNotNull($this->config->get('pagseguro_url_notification')))
			return $this->config->get('pagseguro_url_notification');

		return HTTPS_SERVER . "index.php?route=payment/pagseguro_notification";
	}

	/**
	 * Generate PagSeguro Products
	 * @return array
	 */
	private function _generatePagSeguroProductsData()
	{

		$pagSeguroItens = array();

		$cont = 1;
		foreach ($this->cart->getProducts() as $product) {

			$pagSeguroItem = new PagSeguroItem();
			$pagSeguroItem->setId($cont++);
			$pagSeguroItem->setDescription($product['name']);
			$pagSeguroItem->setQuantity($product['quantity']);
			$pagSeguroItem->setAmount(str_replace("R$", "", $this->currency->format($product['price'], $this->_order_info['currency_code'], false, false)));
			$pagSeguroItem->setWeight($product['weight'] * 1000);

			array_push($pagSeguroItens, $pagSeguroItem);
		}
		return $pagSeguroItens;
	}

	/**
	 * Generate PagSeguro Sender Data
	 * @return \PagSeguroSender
	 */
	private function _generatepagSeguroSenderDataObject()
	{

		$sender = new PagSeguroSender();
		$sender->setEmail(str_replace(' ', '', $this->_order_info['email']));
		$sender->setName($this->_order_info['firstname'] . ' ' . $this->_order_info['lastname']);
		return $sender;
	}

	/**
	 * Generate PagSeguro Shipping Data
	 * @return \PagSeguroShipping
	 */
	private function _generatePagSeguroShippingDataObject()
	{

		$shipping = new PagSeguroShipping();
		$shipping->setAddress($this->_generatePagSeguroShippingAddressDataObject());
		$shipping->setType($this->_generatePagSeguroShippingTypeObject());
		$shipping->setCost(str_replace("R$", '', $this->currency->format($this->_generatePagSeguroShippingCost(), $this->_order_info['currency_code'], false, false)));
		return $shipping;
	}

	/**
	 * Generate PagSeguro Shipping Address
	 * @return \PagSeguroAddress
	 */
	private function _generatePagSeguroShippingAddressDataObject()
	{

		$address = new PagSeguroAddress();
		$address->setCity(html_entity_decode($this->_order_info['payment_city']));
		$address->setPostalCode(html_entity_decode($this->_order_info['payment_postcode']));
		$address->setStreet(html_entity_decode($this->_order_info['payment_address_1']));
		$address->setDistrict(html_entity_decode($this->_order_info['payment_zone']));
		$address->setCountry($this->_order_info['payment_iso_code_2']);
		return $address;
	}

	/**
	 * Generate PagSeguro Shipping Type
	 * @return \PagSeguroShippingTyp
	 */
	private function _generatePagSeguroShippingTypeObject()
	{

		$shippingType = new PagSeguroShippingType();
		$shippingType->setByType('NOT_SPECIFIED');
		return $shippingType;
	}

	/**
	 * Generate PagSeguro Shipping Cost
	 * @return double
	 */
	private function _generatePagSeguroShippingCost()
	{
		$_value = 0;

		if (!empty($this->_order_info['shipping_code'])) {
			$_shipping_code = $this->_order_info['shipping_code'];
			$_shipping_cost = explode(".", $_shipping_code);

			if ($_shipping_cost['0'] != 'free') {
				$_array_setting = $this->model_setting_setting->getSetting($_shipping_cost['0']);

				$_value = $_array_setting[$_shipping_cost['0'] . "_cost"];
			}
		}

		return $_value;
	}

	/**
	 * Perform PagSeguro Request
	 * @param PagSeguroPaymentRequest $paymentRequest
	 */
	private function _performPagSeguroRequest(PagSeguroPaymentRequest $paymentRequest)
	{
		$this->_credential = new PagSeguroAccountCredentials($this->config->get('pagseguro_email'), $this->config->get('pagseguro_token'));

		try
		{
			$this->_urlPagSeguro = $paymentRequest->register($this->_credential);
		}
		catch (Exception $exc)
		{
			die($this->language->get('text_info'));
		}
	}

	/**
	 * Generate PagSEguro Order Status
	 */
	private function _generatePagSeguroOrderStatus()
	{
		$this->model_payment_pagseguro->savePagSeguroOrderStatus();
	}

	/**
	 * Update Order Status
	 */
	private function _updateOrderStatus()
	{

		$id_language = (int) $this->_order_info['language_id'];
		$code_language = $this->model_payment_pagseguro->getCodeLanguageById($id_language);
		$array_language = $this->model_payment_pagseguro->getOrderStatus();
		$array_language = $array_language['1'];
		$wating_payment = $array_language[$code_language];

		$id_order_status = $this->model_payment_pagseguro->getOrderStatusByName($wating_payment, $id_language);
		$this->model_payment_pagseguro->updateOrder($this->_order_info['order_id'], $id_order_status);
	}
	/**
	 * Link for redirect PagSeguro
	 */
	private function _action()
	{

		if (!empty($this->_urlPagSeguro )) {
			$this->data['action'] = HTTP_SERVER . "index.php?route=payment/pagseguro_redirect";
			$this->data['url_ps'] = $this->_urlPagSeguro;
		}
	}

	/**
	 * Validate if value is not null
	 * @param type $value
	 * @return boolean
	 */
	private function _isNotNull($value)
	{

		if ($value != null && $value != "")
			return TRUE;

		return false;
	}

	/**
	 * Return directory log
	 */
	private function _getDirectoryLog()
	{
		$_dir = str_replace('catalog/', '', DIR_APPLICATION);
		return ($this->_isNotNull($this->config->get('pagseguro_directory')) == TRUE) ? $_dir . $this->config->get('pagseguro_directory') : null;
	}
}
?>
