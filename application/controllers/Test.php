<?php
defined('BASEPATH') OR exit('No direct script access allowed');
#use Web3\Web3;

class Test extends CI_Controller {
	function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

	/*
	public function index_get() {
		exdebug('1');
		exdebug($this->input->ip_address());
	}*/
	

	public function index(){
		$data = json_encode(Array('a' => 'a1', 'b' => 'b1'));
		$key = 'abc';
		exdebug($data);
		exdebug($key);
		$enc = $this->EncryptKey($data, $key);
		exdebug($enc);
		$dec = $this->DecryptKey($enc, $key);
		exdebug($dec);
	}

	public function test(){
		$this->load->view('test');
	}

	protected function EncryptKey($src, $realKey = "domain.com"){
		$EncryptData = "";

		$count = 0;
		$length = strlen($src);
		$keylen = strlen($realKey);

		for ($i = 0; $i < $length; $i++) {
			if ($count == $keylen) {
				$count = 0;
			}
			$EncryptData .= substr($src, $i, 1) ^ substr($realKey, $count, 1);
			$count++;
		}
		return base64_encode($EncryptData);
	}

	protected function DecryptKey($EncryptedData, $realKey = "domain.com"){
		$Decrypt = "";

		$EncryptedData = base64_decode($EncryptedData);

		$count = 0;
		$length = strlen($EncryptedData);
		$keylen = strlen($realKey);

		for ($i = 0; $i < $length; $i++) {
			if ($count == $keylen) {
				$count = 0;
			}

			$Decrypt .= substr($EncryptedData, $i, 1) ^ substr($realKey, $count, 1);
			$count++;
		}
		return $Decrypt;
	}
}