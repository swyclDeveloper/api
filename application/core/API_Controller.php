<?php

use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class API_Controller extends CI_Controller {

	public $userInfo = null;
	
	use REST_Controller {
        REST_Controller::__construct as private __resTraitConstruct;
    }

	public function __construct()
	{
		parent::__construct();
        $this->__resTraitConstruct();
		$this->load->library('session');

		// doraemon01
		if (isset($_REQUEST['dev1'])){
			if ($_REQUEST['dev1'] == 'y'){
				foreach (getallheaders() as $name => $value) {
					log_to_file_dev1("$name: $value\n");
				}
			}
		}
	}

	protected function dataNullCheck($val){
		if($val == null || $val == ''){
			$this->response([
				//'inputData' => $val,
				'status' => false,
				'res_code' => '009',
				'message' => 'please check your input'
			], 400);
		}
		return $val;
	}

	protected function devF1($inputData){
		$this->response([
			'inputData' => $inputData,
			'status' => false,
			'message' => 'devF1'
		], 200);
	}

	protected function _sendPasswordCheck($_str){
		$pw = $_str;
		$num = preg_match('/[0-9]/u', $pw);
		$eng = preg_match('/[a-zA-Z]/u', $pw);
		$spe = preg_match("/[\!\@\#\$\%\^\&\*]/u",$pw);
	 
		if(strlen($pw) < 8 || strlen($pw) > 30)
		{
			return array(false, "비밀번호는 영문, 숫자를 혼합하여 최소 8자리 ~ 최대 30자리 이내로 입력해주세요.");
			exit;
		}
	 
		if(preg_match("/\s/u", $pw) == true)
		{
			return array(false, "비밀번호는 공백없이 입력해주세요.");
			exit;
		}
	 
		if( $num == 0 || $eng == 0)
		{
			return array(false, "영문, 숫자를 혼합하여 입력해주세요.");
			exit;
		}
	 
		return array(true);
	}

	protected function _getUserInfo($data, $a = false){
		$this->load->model('UserModel');
		$this->userInfo = $this->UserModel->getUserInfo($data);
		if ($a && !$this->userInfo){
			$this->load->model('AuthModel');
			$this->AuthModel->forceQuitkey($data['key']);
			$this->response([
				'status' => false,
				'res_code' => '109',
				'message' => 'user info error'
			], 400);
		}
	}
}