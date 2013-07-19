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
 * Model Payment PagSeguro.
 * Model responsible for the searches, changes and updates to the tables of PagSeguro.
 */
class ModelPaymentPagSeguro extends Model
{

	private $_languages = array();

	/**
	 * Array with the language codes supported by PagSeguro
	 * @var array
	 */
	private static $array_languages = array(
		"English"	 => "en",
		"Portugues"	 => "br"
	);

	/**
	 * Array with the PagSeguro status, Portuguese and English
	 * @var array
	 */
	private static $array_order_status = array(
		0 => array('br' => 'Iniciada', 'en' => 'Pending'),
		1 => array('br' => 'Aguardando pagamento', 'en' => 'Awaiting payment'),
		2 => array('br' => 'Em análise', 'en' => 'Processing'),
		3 => array('br' => 'Paga', 'en' => 'Paid'),
		4 => array('br' => 'Disponível', 'en' => 'Complete'),
		5 => array('br' => 'Em disputa', 'en' => 'Dispute'),
		6 => array('br' => 'Devolvida', 'en' => 'Refunded'),
		7 => array('br' => 'Cancelada', 'en' => 'Canceled'));

	/**
	 *
	 * @param type $address
	 * @param type $total
	 * @return string
	 */
	public function getMethod($address, $total)
	{

		$this->language->load('payment/pagseguro');

		$method_data = array(
			'code'		 => 'pagseguro',
			'title'		 => $this->language->get('text_title'),
			'sort_order' => $this->config->get('pagseguro_sort_order')
		);

		return $method_data;
	}

	/**
	 * Method responsible for saving the status PagSeguro according to the languages ​​active in the system, by default saves english
	 */
	public function savePagSeguroOrderStatus()
	{
		$this->_languages = $this->_getCodeLanguages();

		if (in_array(self::$array_languages['Portugues'], $this->_languages)) {
			$id_language_br = $this->_getIdLanguageByCode(self::$array_languages['Portugues']);
			$this->_saveOrderStatus(self::$array_languages['Portugues'], $id_language_br);
		}

		$id_language_en = $this->_getIdLanguageByCode(self::$array_languages['English']);
		$this->_saveOrderStatus(self::$array_languages['English'], $id_language_en);
	}

	/**
	 * Retrieves the id and code of languages ​​active in the system
	 * @return array
	 */
	private function _getCodeLanguages()
	{
		$data = array();
		$query = $this->db->query(' SELECT language_id, code
                                            FROM ' . DB_PREFIX . 'language
                                            WHERE status = ' . (int) 1);

		foreach ($query->rows as $result) {
			$data[$result['language_id']] = $result['code'];
		}

		return $data;
	}

	/**
	 * Retrieves the id and code of languages ​​active in the system.
	 * @param type $language_code
	 * @return int
	 */
	public function _getIdLanguageByCode($language_code = 'en')
	{
		$id_language = null;
		$query = $this->db->query(" SELECT language_id
                                            FROM " . DB_PREFIX . "language
                                            WHERE `code` = '" . $this->db->escape($language_code) . "'");

		foreach ($query->rows as $result) {
			$id_language = (int) $result['language_id'];
		}

		return $id_language;
	}

	/**
	 * Retrieves the code of the language according to the id.
	 * @param type $id_language
	 * @return string
	 */
	public function getCodeLanguageById($id_language)
	{
		$code_language = null;
		$query = $this->db->query(" SELECT code
                                            FROM " . DB_PREFIX . "language
                                            WHERE `language_id` = " . (int) $id_language);

		foreach ($query->rows as $result) {
			$code_language = (string) $result['code'];
		}

		return $code_language;
	}

	/**
	 * Save Order Status
	 * @param type $code_language
	 * @param type $id_language
	 */
	private function _saveOrderStatus($code_language, $id_language)
	{
		foreach (self::$array_order_status as $key => $value) {
			if ($this->getOrderStatusByName($value[$code_language], $id_language) == NULL)
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_status SET `language_id` = '" . (int) $id_language . "', `name` = '" . $this->db->escape($value[$code_language]) . "'");
		}
	}

	/**
	 * Retrieves the id of status in accordance with the name and id of the language.
	 * @param type $name_status
	 * @param type $id_language
	 * @return int
	 */
	public function getOrderStatusByName($name_status, $id_language)
	{

		$order_id = NULL;
		$query = $this->db->query("SELECT order_status_id
                                            FROM " . DB_PREFIX . "order_status
                                            WHERE `name` = '" . $this->db->escape($name_status) . "'
                                            AND   `language_id` = '" . (int) $id_language . "'");
		if (!empty($query->rows)) {
			foreach ($query->rows as $result) {
				$order_id = (int) $result['order_status_id'];
			}
		}

		return $order_id;
	}

	/**
	 * Update table order
	 * @param type $id_order
	 * @param type $order_status_id
	 */
	public function updateOrder($id_order, $order_status_id)
	{
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `order_status_id` = '" . (int) $order_status_id . "' WHERE `order_id` = '" . (int) $id_order . "'");
	}

	/**
	 * Save order history
	 * @param type $order_id
	 * @param type $id_order_status
	 */
	public function saveOrderHistory($order_id, $id_order_status)
	{

		$datetime = new DateTime();
		if ($this->_validateOrderHistory($order_id, $id_order_status) == TRUE) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_history
                                 SET `order_id` = '" . (int) $order_id . "',
                                      `order_status_id` = '" . (int) $id_order_status . "',
                                      `notify` = '" . $this->db->escape('1') . "',
                                      `comment` = '" . $this->db->escape('STATUS ATUALIZADO') . "',
                                      `date_added` = '" . $datetime->format('Y-m-d H:i:s') . "'");
		}
	}

	/**
	 * Valid if registration is no longer updated with the same status.
	 * @param type $order_id
	 * @param type $id_order_status
	 * @return boolean
	 */
	private function _validateOrderHistory($order_id, $id_order_status)
	{

		$validate = TRUE;
		$query = $this->db->query("SELECT MAX(order_history_id)
                                            FROM `" . DB_PREFIX . "order_history` WHERE order_id = " . (int) $order_id);

		if (!empty($query->rows)) {
			foreach ($query->rows as $result) {

				$id_history = (int) $result["MAX(order_history_id)"];
				$array_history = $this->_getOrderHistoryById($id_history);

				$name_current = $this->_getNameOrderStatusById($array_history['order_status_id']);
				$name_previous = $this->_getNameOrderStatusById($id_order_status);

				if (!empty($name_current) && !empty($name_previous)) {
					$name_current = $this->removeAccent($name_current);
					$name_previous = $this->removeAccent($name_previous);

					if ($name_current == $name_previous)
						$validate = FALSE;
				}

				if ($id_order_status == $array_history['order_status_id'])
					$validate = FALSE;
			}
		}
		return $validate;
	}

	/**
	 * Return objetc order_history by id
	 * @param type $id_history
	 * @return array order_history
	 */
	private function _getOrderHistoryById($id_history)
	{

		$array_history = array();
		$query = $this->db->query("SELECT *
                                          FROM `" . DB_PREFIX . "order_history` WHERE order_history_id = " . (int) $id_history);

		if (!empty($query->rows)) {
			foreach ($query->rows as $result) {

				$array_history['order_status_id'] = (int) $result['order_status_id'];
			}
		}
		return $array_history;
	}

	/**
	 * Return name order Status by id
	 * @param type $id_order_status
	 * @return string
	 */
	private function _getNameOrderStatusById($id_order_status)
	{

		$name_status = NULL;
		$query = $this->db->query("SELECT `name`
                                            FROM " . DB_PREFIX . "order_status
                                            WHERE   `order_status_id` = '" . (int) $id_order_status . "'");

		if (!empty($query->rows)) {
			foreach ($query->rows as $result) {
				$name_status = $result['name'];
			}
		}
		return $name_status;
	}

	/**
	 * Remove accent
	 * @param type $value
	 * @return string
	 */
	public function removeAccent($value)
	{
		return strtoupper(strtr($value, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ", "aaaaeeiooouucAAAAEEIOOOUUC"));
	}

	/**
	 * Return array of status
	 * @return array
	 */
	public function getOrderStatus()
	{
		return self::$array_order_status;
	}
}
?>