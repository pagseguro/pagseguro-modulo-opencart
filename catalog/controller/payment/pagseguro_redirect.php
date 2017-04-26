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
 * Class Controller Payment PagSeguro Redirect
 */
class ControllerPaymentPagSeguroRedirect extends Controller
{

    /**
     * Url PagSeguro
     * @var url
     */
	private $_urlPagSeguro;

	/**
	 * The first method to be called by the redirect PagSeguro treatment.
	 */
	public function index()
	{

		if ($_POST) {
            if ($this->config->get('pagseguro_checkout') == 'lightbox') {
                $this->data['breadcrumbs'] = array();

                $this->data['breadcrumbs'][] = array(
                    'text'      => $this->language->get('text_home'),
                    'href'      => $this->url->link('common/home'),
                    'separator' => false
                );

                $this->data['breadcrumbs'][] = array(
                    'text'      => 'Checkout',
                    'href'      => $this->url->link('information/static'),
                    'separator' => $this->language->get('text_separator')
                );

                $this->template = 'default/template/payment/pagseguro_lightbox.tpl';
                $this->children = array( //Required. The children files for the page.
                    'common/column_left',
                    'common/column_right',
                    'common/content_top',
                    'common/content_bottom',
                    'common/footer',
                    'common/header'
                );

                $this->data['code'] = '';
                $this->data['environment'] = '';
            }
            $this->_redirect();
        }
        $this->response->setOutput($this->render());

	}

	/**
	 * Redirect Store for PagSeguro and Clean Cart
	 */
	private function _redirect()
	{

		$this->_urlPagSeguro = $this->request->post['url_ps'];

		if (!empty($this->_urlPagSeguro )) {
		    if($this->config->get('pagseguro_checkout') == 'lightbox'){
		        $this->data['code'] = $this->_urlPagSeguro;
		        $this->data['environment'] = $this->config->get('pagseguro_environment');

                $this->cart->clear();
            }else{
                header('Location: ' . $this->_urlPagSeguro);
                $this->cart->clear();
            }

		}
	}
}
?>