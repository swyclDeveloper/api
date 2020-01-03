<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthController extends API_Controller {
    
    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        //$this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        //$this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        //$this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
    }

	public function authPhoneTry_post(){
		$inputData['nation']		= $this->dataNullCheck($this->post('nation'));
		$inputData['phone']			= $this->dataNullCheck($this->post('phone'));
		$inputData['method']		= $this->dataNullCheck($this->post('method'));
		$inputData = array_map('trim', $inputData);
		$inputData['interNumber'] = makeInternationalNumber($inputData['nation'], $inputData['phone']);
		//$this->devF1($inputData);
		$this->load->model('AuthModel');
		$this->load->helper('string');
		$key = random_string('numeric', 6);
		$keyData['service']			= 'SMS';
		$keyData['method']			= $inputData['method'];
		$keyData['user_id']			= base64_encode($inputData['interNumber']);
		$keyData['key']				= $key;
		$keyData['ip_addresses']	= $_SERVER['REMOTE_ADDR'];
		// 1. 3분이내 5번 반복 확인
		$c1Result = $this->AuthModel->checkAuthF1($keyData);
		if ($c1Result > 5){$this->response(['status' => false,'res_code' => '003','message' => '3분이내 5번 반복'], 400);}
		// 2. 키 생성해서 디비 저장
		$i1Result = $this->AuthModel->insertAuthKey($keyData);
		if (!$i1Result){$this->response(['status' => false,'res_code' => '002','message' => 'Server Error'], 500);}

		// 3. sms 발송
		$this->load->library('surem');

		$data['nation']			= '82';
		$data['text']			= "[토큰패스 본인인증] 본인확인을 위하여 인증번호 [{$key}]를 입력해주세요.";

		$data['reserved_time']	= '';
		$data['messages']		= Array(
			Array('message_id' => "","to" => $inputData['phone']),
		);
		
		$s1Result = $this->surem->send($data);
		
		if ($s1Result){
			$this->set_response(['status' => true, 'timelimit' => '3 MINUTE','res_code' => '005','message' => 'Success'], 201);
		} else {
			$this->AuthModel->deleteAuthKey($i1Result);
			$this->response(['status' => false,'message' => 'Server Error'], 500);
		}
	}

	public function authPhoneCheck_post(){
		$this->load->model('AuthModel');
		
		$inputData['nation']		= $this->dataNullCheck($this->post('nation'));
		$inputData['phone']			= $this->dataNullCheck($this->post('phone'));
		$inputData['key']			= $this->dataNullCheck($this->post('key'));
		$inputData['method']		= $this->dataNullCheck($this->post('method'));
		$inputData = array_map('trim', $inputData);
		$inputData['interNumber'] = makeInternationalNumber($inputData['nation'], $inputData['phone']);
		$inputData['user_id']		= base64_encode($inputData['interNumber']);
		
		// 1. 인증 체크
		$c1Result = $this->AuthModel->checkAuthKey($inputData);
		if ($c1Result){
			$this->set_response(['status' => true, 'res_code' => '001','message' => 'Success'], 200);
		} else {
			$this->response(['status' => false,'res_code' => '004','message' => '인증 key 정보가 정확하지 않습니다.'], 400);
		}
	}


}
