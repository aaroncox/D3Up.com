<?php
// --------------------------------------------------------------
// Test Data Creation
// --------------------------------------------------------------
require_once(path('app')."tests/data.php");

abstract class ControllerTestCase extends D3Up_TestCase
{	
	public function call($destination, $parameters = array(), $method = 'GET') {
		\Laravel\Request::foundation()->setMethod($method);
		return \Laravel\Routing\Controller::call($destination, $parameters);
	}

	public function get($destination, $parameters = array(), $get_data = array()) {
		$this->clean_request();
		\Laravel\Request::foundation()->request->add($get_data);
		return $this->call($destination, $parameters, 'GET');
	}

	public function post($destination, $post_data, $parameters = array()) {
		$this->clean_request();
		\Laravel\Request::foundation()->request->add($post_data);
		return $this->call($destination, $parameters, 'POST');
	}

	private function clean_request() {
		$request = \Laravel\Request::foundation()->request;
		foreach ($request->keys() as $key) {
			$request->remove($key);
		}
	}
	
}