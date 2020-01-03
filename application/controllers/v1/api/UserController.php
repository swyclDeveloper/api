<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserController extends API_Controller {
    
    function __construct()
    {
        parent::__construct();

    }
	public function regist_delete(){
		$inputData['nation']		= $this->dataNullCheck($this->delete('nation'));
		$inputData['phone']			= $this->dataNullCheck($this->delete('phone'));
		$inputData = array_map('trim', $inputData);
		$inputData['interNumber']	= makeInternationalNumber($inputData['nation'], $inputData['phone']);
		$inputData['id']			= base64_encode($inputData['interNumber']);
		$this->load->model('UserModel');
		if ($this->UserModel->delete($inputData['id'])){
			$this->response(['status' => true, 'res_code' => '999','message' => 'Success'], 201);
		} else {
			if (!$i1Result){$this->response(['status' => false, 'res_code' => '002','message' => 'Server Error'], 500);}
		}
	}
	public function regist_post(){
		$inputData['nation']		= $this->dataNullCheck($this->post('nation'));
		$inputData['phone']			= $this->dataNullCheck($this->post('phone'));
		$inputData['sms_ph']		= $this->dataNullCheck($this->post('sms_ph'));
		$inputData['pw']			= $this->dataNullCheck($this->post('pw'));
		$inputData['re_pw']			= $this->dataNullCheck($this->post('re_pw'));
		$inputData['key']			= $this->dataNullCheck($this->post('key'));
		$inputData['name']			= $this->dataNullCheck($this->post('name'));
		$inputData['recomm_nation']	= $this->post('recomm_nation');
		$inputData['recomm_phone']	= $this->post('recomm_phone');
		
		$inputData = array_map('trim', $inputData);
		$inputData['interNumber']	= makeInternationalNumber($inputData['nation'], $inputData['phone']);
		$inputData['id']			= base64_encode($inputData['interNumber']);
		if ($inputData['recomm_nation'] == '' || $inputData['recomm_phone'] == ''){// 추천인 정보 세팅 국가코드, 폰번호 둘중 하나라도 없으면 리셋
			$inputData['recomm_nation'] = '';
			$inputData['recomm_phone']	= '';
			$inputData['recomm_id']		= '';
		} else {
			$inputData['recomm_id']		= base64_encode(makeInternationalNumber($inputData['recomm_nation'], $inputData['recomm_phone']));
		}
		
		
		$this->load->model('UserModel');

		if (!$this->UserModel->checkUserRegistSmsAutn($inputData)){
			$this->response(['status' => false, 'res_code' => '004','message' => '인증 key 정보가 정확하지 않습니다.'], 400);
		}
		// 0. nation + phone 와 sms_ph 값이 동일한지 확인
		if ($inputData['id'] != $inputData['sms_ph']){
			$this->response(['status' => false, 'res_code' => '006','message' => '휴대폰번호 정보가 정확하지 않습니다.'], 400);
		}
		// 1. 기존 가입자인지 확인
		if($this->UserModel->checkUser($inputData)){$this->response(['status' => false,'res_code' => '103', 'message' => '기존가입자'], 400);}
		// 2. pw ,re_pw 확인
		if($inputData['pw'] != $inputData['re_pw']){$this->response(['status' => false, 'res_code' => '105','message' => '비밀번호를 확인하세요.'], 400);}
		// 3. pw 규칙 확인
		if( !preg_match( "/^[0-9]{6,6}$/", $inputData['pw'] ) ) {
			$this->response(['status' => false, 'res_code' => '106','message' => '비밀번호 규칙 오류'], 400);
		}
		// 가입완료 작업 start
		// users_temp 에서 users 로 데이터 이동
		$result = $this->UserModel->userInsert($inputData);
		// output
		// 4. 임시 데이터 insert
		
		if ($result){
			$this->response(['status' => true, 'res_code' => '001','message' => 'Success'], 201);
		} else {
			if (!$i1Result){$this->response(['status' => false, 'res_code' => '002','message' => 'Server Error'], 500);}
		}
	}
	public function registTemp_post(){
		/*
		$inputData['nation']		= $this->dataNullCheck($this->post('nation'));
		$inputData['phone']			= $this->dataNullCheck($this->post('phone'));
		$inputData['sms_ph']		= $this->dataNullCheck($this->post('sms_ph'));
		*/
		
		$inputData['pw']			= $this->dataNullCheck($this->post('pw'));
		$inputData['re_pw']			= $this->dataNullCheck($this->post('re_pw'));

		$inputData = array_map('trim', $inputData);
		
		$inputData['interNumber']	= makeInternationalNumber($inputData['nation'], $inputData['phone']);
		$inputData['id']			= base64_encode($inputData['interNumber']);
		$this->load->model('UserModel');
		
		// 0. nation + phone 와 sms_ph 값이 동일한지 확인
		if ($inputData['id'] != $inputData['sms_ph']){
			$this->response(['status' => false, 'res_code' => '006','message' => '휴대폰번호 정보가 정확하지 않습니다.'], 400);
		}
		// 1. 기존 가입자인지 확인
		if($this->UserModel->checkUser($inputData)){$this->response(['status' => false, 'res_code' => '103','message' => '기존가입자'], 400);}
		// 2. pw ,re_pw 확인
		if($inputData['pw'] != $inputData['re_pw']){$this->response(['status' => false, 'res_code' => '105','message' => '비밀번호를 확인하세요.'], 400);}
		// 3. pw 규칙 확인
		if( !preg_match( "/^[0-9]{6,10}$/", $inputData['pw'] ) ) {
			$this->response(['status' => false, 'res_code' => '106','message' => '비밀번호 규칙 오류'], 400);
		}
		// 4. 임시 데이터 insert
		$result = $this->UserModel->userTempInsert($inputData);
		if ($result){
			$this->response(['status' => true, 'res_code' => '005','tempSid' => $result, 'timelimit' => '30 MINUTE', 'message' => 'Success'], 200);
		} else {
			if (!$i1Result){$this->response(['status' => false,'res_code' => '002','message' => 'Server Error'], 500);}
		}
	}

	public function login_post(){
		$inputData['nation']	= $this->dataNullCheck($this->post('nation'));
		$inputData['phone']		= $this->dataNullCheck($this->post('phone'));
		$inputData['sms_ph']	= $this->dataNullCheck($this->post('sms_ph'));
		$inputData['pw']		= $this->dataNullCheck($this->post('pw'));
		
		$inputData = array_map('trim', $inputData);
		
		$inputData['interNumber']	= makeInternationalNumber($inputData['nation'], $inputData['phone']);
		$inputData['id']			= base64_encode($inputData['interNumber']);
		$this->load->model('UserModel');
		
		// 0. nation + phone 와 sms_ph 값이 동일한지 확인
		if ($inputData['id'] != $inputData['sms_ph']){
			$this->response(['status' => false, 'res_code' => '006','message' => '휴대폰번호 정보가 정확하지 않습니다.'], 400);
		}

		//exdebug($inputData);
		$userInfo = $this->UserModel->loginCheck($inputData);
		
		if ($userInfo){ // 로그인 프로세서 동작
			
			// token 발급 진행
			$data['level']			= '1';
			$data['ignore_limits']	= '100';
			$key = $this->_generate_key();
			//exdebug($key);
			//exit;

			// Insert the new key
			if ($this->_insert_key($key, ['level' => $data['level'], 'ignore_limits' => $data['ignore_limits'], 'user_id' => $userInfo->sid])){
				$this->response([
					'status' => true,
					'res_code' => '001',
					'message' => 'Success',
					'key' => $key
				], 201); // CREATED (201) being the HTTP response code
				
			} else {
				$this->response([
					'status' => false,
					'res_code' => '007',
					'message' => 'Could not save the key'
				], 500); // INTERNAL_SERVER_ERROR (500) being the HTTP response code
			}
			// token return 해준다.
		} else {
			$this->response(['status' => false, 'res_code' => '107','message' => '유저 정보가 없습니다. ID/PW를 확인하세요.'], 400);
		}
	}

	public function logout_post(){
		// Destroy it
        $this->_delete_key($this->rest->key);

        // Respond that the key was destroyed
        $this->response([
            'status' => true,
			'message'	=> 'Success',
            'message' => 'API key was deleted'
            ], 200); // NO_CONTENT (204) being the HTTP response code
	}
	
	public function reflash_post(){
		$result = $this->_reflash_key($this->rest->key);
		if ($result){
			$this->response([
				'status' => true,
				'res_code' => '001',
				'key' => $this->rest->key
			], 201);
		} else {
			$this->response([
				'status' => false,
				'res_code' => '007',
				'message' => 'Could not save the key'
			], 500);
		}
	}

	public function userInfo_get(){
		$key = $this->rest->key;
		$this->load->model('UserModel');
		$this->_getUserInfo(Array('key' => $this->rest->key), true);
		$userInfo = $this->userInfo;
		unset($userInfo->sid); unset($userInfo->id); unset($userInfo->passwd);	unset($userInfo->send_passwd);
		unset($userInfo->recomm_id);
		$this->response([
			'status' => true,
			'res_code' => '001',
			'message'	=> 'Success',
			'data' => $userInfo
		], 201);
	}

	public function checkPasswd_post(){
		$inputData['pw']		= $this->dataNullCheck($this->post('pw'));
		$inputData['key']		= $this->rest->key;
		$this->load->model('UserModel');
		$this->_getUserInfo(Array('key' => $this->rest->key), true);
		if($this->UserModel->checkPasswd($inputData)){
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

	public function updatePasswd_post(){
		$inputData['pw']		= $this->dataNullCheck($this->post('pw'));
		$inputData['key']		= $this->rest->key;
		if( !preg_match( "/^[0-9]{6,10}$/", $inputData['pw'] ) ) {
			$this->response(['status' => false, 'res_code' => '106','message' => '비밀번호 규칙 오류'], 400);
		}
		$this->load->model('UserModel');
		$this->_getUserInfo(Array('key' => $this->rest->key), true);
		
		if($this->UserModel->updatePasswd($inputData)){
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

	/* Helper Methods */

    private function _generate_key()
    {
        do
        {
            // Generate a random salt
            $salt = base_convert(bin2hex($this->security->get_random_bytes(64)), 16, 36);

            // If an error occurred, then fall back to the previous method
            if ($salt === false)
            {
                $salt = hash('sha256', time() . mt_rand());
            }

            $new_key = substr($salt, 0, config_item('rest_key_length'));
        }
        while ($this->_key_exists($new_key));

        return $new_key;
    }

	/* Private Data Methods */

    private function _get_key($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->get(config_item('rest_keys_table'))
            ->row();
    }

    private function _key_exists($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->count_all_results(config_item('rest_keys_table')) > 0;
    }

    private function _insert_key($key, $data)
    {
        $data[config_item('rest_key_column')] = $key;
        $data['date_created'] = function_exists('now') ? now() : time();
		//@doraemon01
		$data['created_ip'] = $_SERVER['REMOTE_ADDR'];
		$this->rest->db->query(" UPDATE `".config_item('rest_keys_table')."` SET status = 'N' WHERE user_id = '{$data['user_id']}' ");
        return $this->rest->db
            ->set($data)
            ->insert(config_item('rest_keys_table'));
    }

	private function _reflash_key($key)
    {
        return $this->rest->db->query("update `keys` set level = level + 1 where `key` = '{$key}'");
    }

    private function _update_key($key, $data)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->update(config_item('rest_keys_table'), $data);
    }

    private function _delete_key($key)
    {
        return $this->rest->db->query("update `keys` set status = 'N' where `key` = '{$key}'");
    }

}
















