<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UsersettingController extends API_Controller {
    
    function __construct()
    {
        parent::__construct();
    }

	public function findPasswordCheck_post(){
		$inputData['nation']		= $this->dataNullCheck($this->post('nation'));
		$inputData['phone']			= $this->dataNullCheck($this->post('phone'));
		$inputData['method']		= $this->dataNullCheck($this->post('method'));
		
		$inputData = array_map('trim', $inputData);
		$inputData['interNumber'] = makeInternationalNumber($inputData['nation'], $inputData['phone']);
		
		$this->load->model('AuthModel');
		$this->load->helper('string');
		$key = random_string('numeric', 6);
		$keyData['service']			= 'SMS';
		$keyData['method']			= $inputData['method'];
		$keyData['user_id']			= base64_encode($inputData['interNumber']);
		$keyData['key']				= $key;
		$keyData['ip_addresses']	= $_SERVER['REMOTE_ADDR'];
		
		if ($inputData['method'] !== 'findpasswd') {$this->response(['status' => false,'res_code' => '101','message' => 'method error'], 400);}
		
		// 1. 3분이내 5번 반복 확인
		$c1Result = $this->AuthModel->checkAuthF1($keyData);
		if ($c1Result > 5){$this->response(['status' => false,'res_code' => '005','message' => '3분이내 5번 반복'], 400);}
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
			$this->set_response(['status' => true, 'timelimit' => '3 MINUTE','res_code' => '101','message' => 'Success'], 201);
		} else {
			$this->AuthModel->deleteAuthKey($i1Result);
			$this->response(['status' => false,'res_code' => '002','message' => 'Server Error'], 500);
		}
	}

	public function findPassword_post(){
		$inputData['nation']		= $this->dataNullCheck($this->post('nation'));
		$inputData['phone']			= $this->dataNullCheck($this->post('phone'));
		$inputData['key']			= $this->dataNullCheck($this->post('key'));
		$inputData['method']		= $this->dataNullCheck($this->post('method'));
		$inputData = array_map('trim', $inputData);
		if ($inputData['method'] !== 'findpasswd') {$this->response(['status' => false,'res_code' => '101','message' => 'method error'], 400);}
		
		//exdebug($inputData);
		$this->load->model('UserModel');
		$this->load->model('AuthModel');
		$inputData['interNumber']	= makeInternationalNumber($inputData['nation'], $inputData['phone']);
		$inputData['user_id']		= base64_encode($inputData['interNumber']);
		// 0. 가입여부 확인
		if(!$this->UserModel->checkUser(Array('id' => $inputData['user_id']))){
			$this->response(['status' => false,'res_code' => '102','message' => '회원 정보 없음'], 400);
		}
		// 1. key 값 확인
		$c1Result = $this->AuthModel->checkAuthKey($inputData);
		//exdebug($c1Result);
		
		if ($c1Result){
			//$this->set_response(['status' => true, 'message' => 'Success'], 200);
			// 임시 비밀번호 생성
			
			$this->load->helper('string');
			$findPassword['newPw']		= random_string('numeric', 6);
			$findPassword['key']		= $inputData['key'];
			// user status = P update , passwd $key 값으로 변경
			$this->AuthModel->findPassword($findPassword);
			// 3. sms 발송
			$this->load->library('surem');

			$data['nation']			= $inputData['nation'];
			$data['text']			= "[토큰패스 비밀번호찾기] 임시비밀번호 [{$findPassword['newPw']}]";
			$data['reserved_time']	= '';
			$data['messages']		= Array(
				Array('message_id' => "","to" => $inputData['phone']),
			);
			$s1Result = $this->surem->send($data);
		
			if ($s1Result){
				$this->set_response(['status' => true, 'res_code' => '101','message' => 'Success'], 201);
			} else {
				$this->response(['status' => false,'res_code' => '002','message' => 'Server Error'], 500);
			}
		} else {
			$this->response(['status' => false,'res_code' => '004','message' => '인증 key 정보가 정확하지 않습니다.'], 400);
		}
		
		
	}

	public function updateSendPasswd_post(){
		$inputData['use_at']	= $this->dataNullCheck($this->post('use_at'));
		$inputData['pw']		= $inputData['use_at'] == 'Y' ? $this->dataNullCheck($this->post('pw')) : '';
		$inputData['key']		= $this->rest->key;
		$inputData = array_map('trim', $inputData);
		$pwCheck = $this->_sendPasswordCheck($inputData['pw']);
		if(!$pwCheck[0] && $inputData['use_at'] == 'Y'){
			$this->response([
					'status' => false,
					'res_code' => '106',
					'message' => $pwCheck[1]
				], 500);
		}
		$this->_getUserInfo(Array('key' => $this->rest->key), true);
		$this->load->model('UserSettingModel');
		if($this->UserSettingModel->updateSendPasswd($inputData)){
			$this->response([
				'status' => true,
				'res_code' => '001',
				'message' => 'Success'
			], 200);
		} else {
			$this->response([
				'status' => false,
				'res_code' => '002',
				'message' => 'Server Error'
			], 500);
		}
	}
	public function updateSendPasswd_get(){
		$key = $this->rest->key;
		$this->load->model('UserModel');
		$this->_getUserInfo(Array('key' => $this->rest->key), true);
		$userInfo = $this->userInfo;
		
		$this->response([
			'status' => true,
			'res_code' => '001',
			'message'	=> 'Success',
			'data' => Array(
				"send_passwd_useAt" => $userInfo->send_passwd_useAt,
				"biometrics_useAt" => $userInfo->biometrics_useAt,
			)
		], 201);
	}

	public function checkSendPasswd_post(){
		$inputData['pw']		= $this->dataNullCheck($this->post('pw'));
		$inputData['key']		= $this->rest->key;
		$inputData = array_map('trim', $inputData);
		$this->_getUserInfo(Array('key' => $this->rest->key), true);
		$this->load->model('UserModel');
		if($this->UserModel->checkSendPasswd($inputData)){
			$this->response([
				'status' => true,
				'res_code' => '001',
				'message' => 'Success'
			], 201);
		} else {
			$this->response([
				'status' => false,
				'res_code' => '108',
				'message' => 'Password error'
			], 400);
		}
	}

	public function updateBiometricAuth_post(){
		$inputData['use_at']	= $this->dataNullCheck($this->post('use_at'));
		$inputData['key']		= $this->rest->key;
		$inputData = array_map('trim', $inputData);
		
		$this->_getUserInfo(Array('key' => $this->rest->key), true);
		$this->load->model('UserSettingModel');
		if($this->UserSettingModel->updateBiometricAuth($inputData)){
			$this->response([
				'status' => true,
				'res_code' => '001',
				'message' => 'Success'
			], 200);
		} else {
			$this->response([
				'status' => false,
				'res_code' => '002',
				'message' => 'Server Error'
			], 500);
		}
	}
	public function checkBiometricAuth_post(){
		$inputData['key']		= $this->rest->key;
		$inputData = array_map('trim', $inputData);
		$this->_getUserInfo(Array('key' => $this->rest->key), true);
		$this->response([
			'status' => true,
			'data'	=> Array('biometrics_useAt' => $this->userInfo->biometrics_useAt),
			'res_code' => '001',
			'message' => 'Success'
		], 201);
	}

	public function updateUserEmail_post(){
		$inputData['email']	= $this->dataNullCheck($this->post('email'));
		$inputData['key']		= $this->rest->key;
		$inputData = array_map('trim', $inputData);
		
		$this->_getUserInfo(Array('key' => $this->rest->key), true);
		
		if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $inputData['email'])){
			$this->response(['status' => false,'res_code' => '110','message' => 'Email error'], 400);
		}
		$this->load->model('UserSettingModel');
		if($this->UserSettingModel->updateUserEmail($inputData)){
			$this->response([
				'status' => true,
				'res_code' => '001',
				'message' => 'Success'
			], 200);
		} else {
			$this->response([
				'status' => false,
				'res_code' => '002',
				'message' => 'Server Error'
			], 500);
		}
	}

}
















