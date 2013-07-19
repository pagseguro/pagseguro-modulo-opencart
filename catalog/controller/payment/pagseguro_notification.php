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
 * Class Controller Payment PagSeguro Notification
 * Class responsible for receiving and handling of notifications sent by PagSeguro.
 */
class ControllerPaymentPagSeguroNotification extends Controller
{

	/**
	 * $_POST['notificationType']
	 * @var string
	 */
	private $notification_type;

	/**
	 * $_POST['notificationCode']
	 * @var string
	 */
	private $notification_code;

	/**
	 * Reference purchase
	 * @var int
	 */
	private $reference;

	/**
	 * Code language active in the system
	 * @var string
	 */
	private $code_language;

	/**
	 * Status PagSeguro
	 * @var array
	 */
	private $array_order_status;

	/**
	 * PagSeguroAccountCredentials
	 * @var PagSeguroAccountCredentials
	 */
	private $obj_credentials;

	/**
	 * PagSeguroNotificationType
	 * @var PagSeguroNotificationType
	 */
	private $obj_notification_type;

	/**
	 * PagSeguroNotificationService
	 * @var Transaction
	 */
	private $obj_transaction;

	/**
	 * The first method to be called by the notification PagSeguro treatment.
	 */
	public function index()
	{

		$this->_load();
		$this->_addPagSeguroLibrary();
		$this->_validatePost();
		$this->_createArrayOrderStatus();
		$this->_codeLanguage();
		$this->_createCredentials();
		$this->_createNotificationType();

		if ($this->obj_notification_type->getValue() == $this->notification_type ) {
			$this->_createTransaction();
			$this->_updateCms();
		}
	}

	/**
	 * Load Model PagSeguro.
	 */
	private function _load()
	{
		$this->load->model('payment/pagseguro');
	}

	/**
	 * Add Libary PagSeguro.
	 */
	private function _addPagSeguroLibrary()
	{
		include_once DIR_APPLICATION . 'controller/payment/PagSeguroLibrary/PagSeguroLibrary.php';
	}

	/**
	 * Verifies that post is not empty.
	 */
	private function _validatePost()
	{
		$this->notification_type = (isset($this->request->post['notificationType']) && trim($this->request->post['notificationType']) != "") ? trim($this->request->post['notificationType']) : null;
		$this->notification_code = (isset($this->request->post['notificationCode']) && trim($this->request->post['notificationCode']) != "") ? trim($this->request->post['notificationCode']) : null;
	}

	/**
	 * Retrieves list of status PagSeguro
	 */
	private function _createArrayOrderStatus()
	{
		$this->array_order_status = $this->model_payment_pagseguro->getOrderStatus();
	}

	/**
	 * Create Credentials
	 */
	private function _createCredentials()
	{
		$this->obj_credentials = new PagSeguroAccountCredentials($this->config->get('pagseguro_email'), $this->config->get('pagseguro_token'));
	}

	/**
	 * Retrieves the language code active in the system.
	 */
	private function _codeLanguage()
	{
		$this->code_language = $this->session->data['language'];
	}

	/**
	 * Create Notification type
	 */
	private function _createNotificationType()
	{
		$this->obj_notification_type = new PagSeguroNotificationType();
		$this->obj_notification_type->setByType("TRANSACTION");
	}

	/**
	 * Create Transaction
	 */
	private function _createTransaction()
	{
		$this->obj_transaction = PagSeguroNotificationService::checkTransaction($this->obj_credentials, $this->notification_code);
		$this->reference = $this->obj_transaction->getReference();
	}

	/**
	 * Updates the transaction status
	 */
	private function _updateCms()
	{
		$value_array = $this->array_order_status[$this->obj_transaction->getStatus()->getValue()];
		$id_language = $this->_getIdLanguage();
		$id_order_status = $this->model_payment_pagseguro->getOrderStatusByName($value_array[$this->code_language], $id_language);
		$this->_updateOrder($id_order_status);
	}

	/**
	 * Returns the id of the language active in the system.
	 * @return int
	 */
	private function _getIdLanguage()
	{
		return $this->model_payment_pagseguro->_getIdLanguageByCode($this->code_language);
	}

	/**
	 * Update table order and order_history
	 * @param type $id_order_status
	 */
	private function _updateOrder($id_order_status)
	{
		$this->model_payment_pagseguro->updateOrder($this->reference, $id_order_status);
		$this->model_payment_pagseguro->saveOrderHistory($this->reference, $id_order_status);
	}
}
?>