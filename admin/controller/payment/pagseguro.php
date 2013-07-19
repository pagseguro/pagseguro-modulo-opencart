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
 * Controller Payment PagSeguro.
 * Class responsible for the configuration data of the user of PagSeguro (adm)
 */
class ControllerPaymentPagSeguro extends Controller
{

	/**
	 * Array Error
	 * @var array
	 */
	private $error = array();

	/**
	 *Regex validate e-mail
	 * @var regex
	 */
	private $pattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';

	/**
	 * Array of extensions
	 * @var string
	 */
	private $array_extension = array(".txt", ".log");

	public function index()
	{

		$this->_addPagSeguroLibrary();
		$this->language->load('payment/pagseguro');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('pagseguro', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->_setPagSeguroConfiguration();
			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->_createInput();
		$this->_createText();
		$this->_createRadio();
		$this->_createButtons();
		$this->_createBreadcrumbs();
		$this->_createLink();
		$this->_createError();

		$this->template = 'payment/pagseguro.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	/**
	 * Add PagSeguro Libary
	 */
	private function _addPagSeguroLibrary()
	{
		include_once DIR_CATALOG . 'controller/payment/PagSeguroLibrary/PagSeguroLibrary.php';
	}

	/**
	 * Create Input
	 */
	private function _createInput()
	{

		if (isset($this->request->post['pagseguro_status']))
			$this->data['pagseguro_status'] = $this->request->post['pagseguro_status'];
		else
			$this->data['pagseguro_status'] = $this->config->get('pagseguro_status');

		if (isset($this->request->post['pagseguro_sort_order']))
			$this->data['pagseguro_sort_order'] = $this->request->post['pagseguro_sort_order'];
		else
			$this->data['pagseguro_sort_order'] = $this->config->get('pagseguro_sort_order');

		if (isset($this->request->post['pagseguro_email']))
			$this->data['pagseguro_email'] = $this->request->post['pagseguro_email'];
		else
			$this->data['pagseguro_email'] = $this->config->get('pagseguro_email');

		if (isset($this->request->post['pagseguro_token']))
			$this->data['pagseguro_token'] = $this->request->post['pagseguro_token'];
		else
			$this->data['pagseguro_token'] = $this->config->get('pagseguro_token');

		if (isset($this->request->post['pagseguro_forwarding']))
			$this->data['pagseguro_forwarding'] = $this->request->post['pagseguro_forwarding'];
		else
			$this->data['pagseguro_forwarding'] = $this->validateRedirectUrl();

		if (isset($this->request->post['pagseguro_url_notification']))
			$this->data['pagseguro_url_notification'] = $this->request->post['pagseguro_url_notification'];
		else
			$this->data['pagseguro_url_notification'] = $this->validateNotificationUrl();

		if (isset($this->request->post['pagseguro_charset']))
			$this->data['pagseguro_charset'] = $this->request->post['pagseguro_charset'];
		else
			$this->data['pagseguro_charset'] = $this->config->get('pagseguro_charset');

		if (isset($this->request->post['pagseguro_log']))
			$this->data['pagseguro_log'] = $this->request->post['pagseguro_log'];
		else
			$this->data['pagseguro_log'] = $this->config->get('pagseguro_log');

		if (isset($this->request->post['pagseguro_directory']))
			$this->data['pagseguro_directory'] = $this->request->post['pagseguro_directory'];
		else
			$this->data['pagseguro_directory'] = $this->config->get('pagseguro_directory');
	}

	/**
	 * Create Text
	 */
	private function _createText()
	{

		$this->data['enable_module'] = $this->language->get('enable_module');
		$this->data['text_module'] = $this->language->get('text_module');

		$this->data['display_order'] = $this->language->get('display_order');
		$this->data['text_order'] = $this->language->get('text_order');

		$this->data['ps_email'] = $this->language->get('ps_email');
		$this->data['text_email'] = $this->language->get('text_email');

		$this->data['ps_token'] = $this->language->get('ps_token');
		$this->data['text_token'] = $this->language->get('text_token');

		$this->data['url_forwarding'] = $this->language->get('url_forwarding');
		$this->data['text_url_forwarding'] = $this->language->get('text_url_forwarding');

		$this->data['url_notification'] = $this->language->get('url_notification');
		$this->data['text_url_notification'] = $this->language->get('text_url_notification');

		$this->data['charset'] = $this->language->get('charset');
		$this->data['text_charset'] = $this->language->get('text_charset');

		$this->data['log'] = $this->language->get('log');
		$this->data['text_log'] = $this->language->get('text_log');

		$this->data['directory'] = $this->language->get('directory');
		$this->data['text_directory'] = $this->language->get('text_directory');
	}

	/**
	 * Create Radio Button
	 */
	private function _createRadio()
	{
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');

		$this->data['iso'] = $this->language->get('iso');
		$this->data['utf'] = $this->language->get('utf');
	}

	/**
	 * Create Buttons
	 */
	private function _createButtons()
	{
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
	}

	/**
	 * Create Breadcrumbs to view.
	 */
	private function _createBreadcrumbs()
	{

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'		 => $this->language->get('text_home'),
			'href'		 => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator'	 => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'		 => $this->language->get('text_payment'),
			'href'		 => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator'	 => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'		 => $this->language->get('heading_title'),
			'href'		 => $this->url->link('payment/pagseguro', 'token=' . $this->session->data['token'], 'SSL'),
			'separator'	 => ' :: '
		);
	}

	/**
	 * Create Link's
	 */
	private function _createLink()
	{
		$this->data['action'] = $this->url->link('payment/pagseguro', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
	}

	/**
	 * Create Error
	 */
	private function _createError()
	{

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}

		if (isset($this->error['token'])) {
			$this->data['error_token'] = $this->error['token'];
		} else {
			$this->data['error_token'] = '';
		}
	}

	/**
	 * Validate
	 * @return boolean
	 */
	protected function validate()
	{

		$this->_permission();
		$this->_validateEmail();
		$this->_validateToken();
		$this->_notificationUrl();
		$this->_redirectUrl();

		return (empty($this->error )) ? true : false;
	}

	/**
	 * Validate Permisson
	 */
	private function _permission()
	{
		if (!$this->user->hasPermission('modify', 'payment/pp_standard'))
			$this->error['warning'] = $this->language->get('error_permission');
	}

	/**
	 * Validate E-mail
	 */
	private function _validateEmail()
	{

		if (empty($this->request->post['pagseguro_email']))
			$this->error['email'] = $this->language->get('error_email_required');

		if (!empty($this->request->post['pagseguro_email'])) {
			$valid = preg_match($this->pattern, $this->request->post['pagseguro_email']);

			if ($valid != 1) {
				$this->error['email'] = $this->language->get('error_email_invalid');
			}
		}
	}

	/**
	 * Validate Token
	 */
	private function _validateToken()
	{

		if (empty($this->request->post['pagseguro_token']))
			$this->error['token'] = $this->language->get('error_token_required');

		if (strlen(trim($this->request->post['pagseguro_token'])) != 32 && !(empty($this->request->post['pagseguro_token'])))
			$this->error['token'] = $this->language->get('error_token_invalid');
	}

	/**
	 * Retrieve PagSeguro data configuration from database
	 */
	private function _setPagSeguroConfiguration()
	{
		$charset = ($this->request->post['pagseguro_charset'] == 1) ? $this->language->get('iso') : $this->language->get('utf');

		// setting configurated default charset
		PagSeguroConfig::setApplicationCharset($charset);

		$activeLog = ($this->request->post['pagseguro_log'] == 1) ? TRUE : FALSE;

		// setting configurated default log info
		if ($activeLog) {
			$directory = $this->_getDirectoryLog();
			$this->_verifyLogFile($directory);
			PagSeguroConfig::activeLog($directory);
		}
	}

	/**
	 * Verify if PagSeguro log file exists.
	 * Case log file not exists, try create
	 * else, log will be created as name as PagSeguro.log as name into PagseguroLibrary folder into module
	 */
	private function _verifyLogFile($file)
	{

		try
		{
			$f = fopen($file, "a");
			fclose($f);
		}
		catch (Exception $e)
		{
			die($e);
		}
	}

	/**
	 * Validate Notification Url
	 * @return url
	 */
	private function validateNotificationUrl()
	{

		$value = $this->config->get('pagseguro_url_notification');

		if (empty($value))
			return $this->_generateNotificationUrl();

		return $value;
	}

	/**
	 * Validate Redirect Url
	 * @return url
	 */
	private function validateRedirectUrl()
	{

		$value = $this->config->get('pagseguro_forwarding');

		if (empty($value))
			return $this->_generationRedirectUrl();

		return $value;
	}

	/**
	 * Notification Url
	 */
	private function _notificationUrl()
	{

		if (empty($this->request->post['pagseguro_url_notification']))
			$this->data['pagseguro_url_notification'] = $this->_generateNotificationUrl();
	}

	/**
	 * Redirect Url
	 */
	private function _redirectUrl()
	{

		if (empty($this->request->post['pagseguro_forwarding']))
			$this->data['pagseguro_forwarding'] = $this->_generationRedirectUrl();
	}

	/**
	 * Url Notification
	 * @return url notification
	 */
	private function _generateNotificationUrl()
	{
		return HTTP_CATALOG . "index.php?route=payment/pagseguro_notification";
	}

	/**
	 * Redirect Url
	 * @return url redirect
	 */
	private function _generationRedirectUrl()
	{
		return HTTP_CATALOG . "index.php?route=checkout/success";
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
		$_dir = str_replace('catalog/', '', DIR_CATALOG);
		$directory = NULL;
		$validate_extension = FALSE;

		if ($this->_isNotNull($this->request->post['pagseguro_directory'])) {
			$directory = $this->request->post['pagseguro_directory'];

			foreach ($this->array_extension as $extension) {
				if (stripos($directory, $extension))
					$validate_extension = TRUE;
			}
		}

		if ($directory != NULL && $validate_extension == FALSE)
			$directory = $this->_createFileDirectory($directory);

		return ($directory != NULL) ? $_dir . $directory : null;
	}

	/**
	 * Create file
	 * @param type $directory
	 * @return string
	 */
	private function _createFileDirectory($directory)
	{

		$directory = explode('/', $directory);
		$path = '';

		foreach ($directory as $char) {
			$path = $path . $char . DIRECTORY_SEPARATOR;
		}

		return $path . 'PagSeguro.log';
	}
}
?>