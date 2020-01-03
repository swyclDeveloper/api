<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TestController extends API_Controller {

	function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

	public function f1_post() {
	}

	public function f1_get(){
		$data = '[{"nation":"82","phone":"01062955163","sms_ph":"ODIxMDYyOTU1MTYz"},{"nation":"82","phone":"0106295516","sms_ph":"ODIxMDYyOTU1MTY="},{"nation":"82","phone":"010629551","sms_ph":"ODIxMDYyOTU1MQ=="},{"nation":"82","phone":"01062955","sms_ph":"ODIxMDYyOTU1"},{"nation":"82","phone":"0106295","sms_ph":"ODIxMDYyOTU="}]';
		$data_ = json_decode($data);
		
		
		// status, address
		foreach($data_ as $index => $obj){
			exdebug($index);
			exdebug($obj);
			$data_[$index]->status = false;
			$data_[$index]->address = '';
		}
		exdebug($data_);
	}
}

