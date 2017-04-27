<?php

/*
 ************************************************************************
 Copyright [2017] [PagSeguro Internet Ltda.]

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

include_once 'PagSeguroLibrary/PagSeguroLibrary.php';
/**
 * Class Controller Payment PagSeguro Error
 */
class ControllerPaymentPagSeguroError extends Controller{

    /**
     * model_checkout_order->getOrder
     * @var array
     */
    private $_order_info;

    /**
     * The first method to be called by the error PagSeguro treatment.
     */
    public function index()
    {

        $this->_load();
        $this->_order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $this->_updateOrderStatus();
        $this->_redirect();
    }

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
     * Update Order Status for Canceled if is payment aborted
     */
    private function _updateOrderStatus()
    {

        $id_language = (int) $this->_order_info['language_id'];
        $code_language = $this->model_payment_pagseguro->getCodeLanguageById($id_language);
        $array_language = $this->model_payment_pagseguro->getOrderStatus();
        $array_language = $array_language['7'];
        $canceled_payment = $array_language[$code_language];

        $id_order_status = $this->model_payment_pagseguro->getOrderStatusByName($canceled_payment, $id_language);
        $this->model_payment_pagseguro->updateOrder($this->_order_info['order_id'], $id_order_status);
    }

    /**
     * Return directory log
     */
    private function _getDirectoryLog()
    {

        $_dir = str_replace('catalog/', '', DIR_APPLICATION);
        return ($this->_isNotNull($this->config->get('pagseguro_directory')) == TRUE) ? $_dir . $this->config->get('pagseguro_directory') : null;
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
     * Redirect for home
     */
    private function _redirect()
    {

        header('Location: ' . HTTP_SERVER . "index.php?route=common/home");
    }
}
?>