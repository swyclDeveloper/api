<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InitController extends API_Controller {
    
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

	public function version_get(){
		$version = Array(
			'ANDROID_APP_VERSION' => ANDROID_APP_VERSION,
			'IOS_APP_VERSION' => IOS_APP_VERSION,
		);
		$this->set_response(['status' => true, 'version' => $version], 200);
	}

	public function error404(){
		$this->set_response(['status' => false, 'error' => 'Unknown method'], 404);
	}
}
