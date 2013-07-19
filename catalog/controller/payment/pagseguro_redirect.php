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

	private $_urlPagSeguro;

	/**
	 * The first method to be called by the redirect PagSeguro treatment.
	 */
	public function index()
	{

		if ($_POST)
			$this->_redirect();
	}

	/**
	 * Redirect Store for PagSeguro and Clean Cart
	 */
	private function _redirect()
	{

		$this->_urlPagSeguro = $this->request->post['url_ps'];

		if (!empty($this->_urlPagSeguro )) {
			header('Location: ' . $this->_urlPagSeguro);
			$this->cart->clear();
		}
	}
}
?>