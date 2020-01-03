<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WalletController extends API_Controller {
    
    function __construct()
    {
        parent::__construct();
		/*if (!$this->_key_exists($this->rest->key))
        {
            $this->response([
                'status' => false,
                'message' => 'Invalid API key'
            ], 400);
        }*/
    }

	public function wallet_post(){
		//log_to_file('1');
		$inputData['address']		= $this->dataNullCheck($this->post('address'));
		$inputData['platform']		= $this->dataNullCheck($this->post('platform'));
		$inputData['nickname']		= $this->post('nickname');
		
		$inputData = array_map('trim', $inputData);
		$key = $this->rest->key;
		$this->load->model('UserModel');
		$userInfo = $this->UserModel->getUserInfo(Array('key' => $key));
		if ($userInfo){
			$this->load->model('WalletModel');
			$walletData['user_sid']		= $userInfo->sid;
			$walletData['address']		= $inputData['address'];
			$walletData['platform']		= $inputData['platform'];
			$walletData['nickname']		= $inputData['nickname'];
			$walletData['created_ip']	= $_SERVER['REMOTE_ADDR'];
			// user + platform + address 가 입력 되어 있는지 확인
			if($this->WalletModel->checkWallet($walletData, 'i')) {
				$this->response(['status' => true, 'res_code' => '201','message' => '이미 등록되어 있는 지갑 주소'], 400);
			}
			$result = $this->WalletModel->insertWallet($walletData);
			if ($result){
				$this->set_response(['status' => true, 'res_code' => '202' ,'message' => 'Success'], 201);
			} else {
				$this->response(['status' => false, 'res_code' => '002','message' => 'Server Error'], 500);
			}
		} else {
			$this->set_response(['status' => true, 'res_code' => '102','message' => '회원 정보 없음'], 400);
		}
	}
	
	public function wallet_get($platform = false){
		if (!$platform){$this->response(['status' => false, 'res_code' => '203','message' => 'platform is null'], 400);}
		$key = $this->rest->key;
		$this->load->model('UserModel');
		$userInfo = $this->UserModel->getUserInfo(Array('key' => $key));
		if ($userInfo){
			$this->load->model('WalletModel');
			$walletData['user_sid']		= $userInfo->sid;
			$walletData['platform']		= $platform;
			$walletInfo = $this->WalletModel->getWallet($walletData);
			$this->load->library("web3");
			$versionNetwork = $this->web3->versionNetwork();
			$this->load->model('TokensModel');

			$tokenInfo = $this->TokensModel->getTokenInfo($userInfo->sid, @$versionNetwork->body->netIdName, $walletInfo);
			$this->set_response([
				'status' => true,
				'data_size' => sizeof($walletInfo),
				'data' => $walletInfo,
				'tokenInfo' => $tokenInfo,
				'versionNetwork' => $versionNetwork,
				'res_code' => '001',
				'message' => 'Success'
			], 200);
			
		} else {
			$this->set_response(['status' => true, 'res_code' => '102','message' => '회원 정보 없음'], 400);
		}
	}

	public function wallet_delete(){
		$inputData['address']		= $this->dataNullCheck($this->delete('address'));
		$inputData['platform']		= $this->dataNullCheck($this->delete('platform'));
		$inputData = array_map('trim', $inputData);
		$key = $this->rest->key;
		$this->load->model('UserModel');
		$userInfo = $this->UserModel->getUserInfo(Array('key' => $key));
		if ($userInfo){
			$this->load->model('WalletModel');
			$walletData['user_sid']		= $userInfo->sid;
			$walletData['address']		= $inputData['address'];
			$walletData['platform']		= $inputData['platform'];
			$walletData['created_ip']	= $_SERVER['REMOTE_ADDR'];
			// user + platform + address 가 입력 되어 있는지 확인
			if(!$this->WalletModel->checkWallet($walletData , 'd')) {
				$this->response(['status' => true, 'res_code' => '204','message' => '지갑 정보가 없습니다.'], 400);
			}
			$result = $this->WalletModel->deleteWallet($walletData);
			if ($result){
				$this->set_response(['status' => true, 'res_code' => '001' ,'message' => 'Success'], 201);
			} else {
				$this->response(['status' => false, 'res_code' => '002','message' => 'Server Error'], 500);
			}
		} else {
			$this->set_response(['status' => true, 'res_code' => '102','message' => '회원 정보 없음'], 400);
		}
	}

	public function walletRestore_get(){
		$key = $this->rest->key;
		$this->load->model('UserModel');
		$userInfo = $this->UserModel->getUserInfo(Array('key' => $key));
		if ($userInfo){
			$this->load->model('WalletModel');
			$walletData['user_sid']		= $userInfo->sid;
			$walletInfo = $this->WalletModel->getRestoreWallet($walletData);
			$this->set_response([
				'status' => true,
				'data_size' => sizeof($walletInfo),
				'data' => $walletInfo,
				'res_code' => '001',
				'message' => 'Success'
			], 200);
		} else {
			$this->set_response(['status' => true, 'res_code' => '102','message' => '회원 정보 없음'], 400);
		}
	}

	public function walletRestore_put(){
		$inputData['address']		= $this->dataNullCheck($this->put('address'));
		$inputData['platform']		= $this->dataNullCheck($this->put('platform'));
		$inputData = array_map('trim', $inputData);
		$key = $this->rest->key;
		$this->load->model('UserModel');
		$userInfo = $this->UserModel->getUserInfo(Array('key' => $key));
		
		if ($userInfo){
			$this->load->model('WalletModel');
			$walletData['user_sid']		= $userInfo->sid;
			$walletData['address']		= $inputData['address'];
			$walletData['platform']		= $inputData['platform'];
			$walletData['created_ip']	= $_SERVER['REMOTE_ADDR'];
			// user + platform + address 가 입력 되어 있는지 확인
			if(!$this->WalletModel->checkWallet($walletData , 'r')) {
				$this->response(['status' => true, 'res_code' => '204','message' => '지갑 정보가 없습니다.'], 400);
			}
			$result = $this->WalletModel->restoreWallet($walletData);
			if ($result){
				$this->set_response(['status' => true, 'res_code' => '001' ,'message' => 'Success'], 201);
			} else {
				$this->response(['status' => false, 'res_code' => '002','message' => 'Server Error'], 500);
			}
		} else {
			$this->set_response(['status' => true, 'res_code' => '102','message' => '회원 정보 없음'], 400);
		}
	}

	public function search_post(){
		$inputData['name']				= $this->dataNullCheck($this->post('name'));
		$inputData['platform']			= $this->dataNullCheck($this->post('platform'));
		$inputData['contract_address']	= $this->post('contract_address');
		$inputData['phones']			= $this->dataNullCheck($this->post('phones'));
		$inputData = array_map('trim', $inputData);
		$inputData['phonesObjects'] = json_decode($inputData['phones']);
		if(!sizeof($inputData['phonesObjects'])) {
			$this->response(['status' => false, 'res_code' => '009', 'message' => 'please check your input'], 400);
		};
		foreach($inputData['phonesObjects'] as $index => $obj){
			$inputData['phonesObjects'][$index]->status = false;
			$inputData['phonesObjects'][$index]->address = '';
			$inputData['phonesObjects'][$index]->user_id = base64_encode(makeInternationalNumber($obj->nation, $obj->phone));
		}
		$key = $this->rest->key;
		
		$this->load->model('UserModel');
		$userInfo = $this->UserModel->getUserInfo(Array('key' => $key));
		
		//exdebug($inputData);
		//exdebug($userInfo);
		if ($userInfo){
			$walletInfo = '';
			$this->load->model('WalletModel');
			// 1. 받은 정보의 미가입자가 있는지 확인(없는경우 4번 실행)
			if ($this->WalletModel->checkUserWallets($inputData)){
				$walletInfo = $this->WalletModel->phonesObjects;
				$this->response(['status' => true, 'res_code' => '001' ,'message' => 'Success','data' => $walletInfo], 200);
			} else {
				$walletInfo = $this->WalletModel->phonesObjects;
				// 2. 지갑이 없는경우 신규 생성
				// 3. 신규 생성한 지갑 정보를 디비에 저장
				if(!$this->WalletModel->updateAnonymous($walletInfo)){
					$this->response(['status' => false, 'res_code' => '002','message' => 'Server Error'], 500);
				}
				// 4. 지갑  정보 조회 하기
				$this->WalletModel->checkUserWallets($inputData);
				// 5. 결과 return
				$walletInfo = $this->WalletModel->phonesObjects;
				$this->response(['status' => true, 'res_code' => '001' ,'message' => 'Success','data' => $walletInfo], 200);
			}
			
		} else {
			$this->set_response(['status' => true, 'res_code' => '102','message' => '회원 정보 없음'], 400);
		}
	}

	public function create_get($arg){
		if ($arg == 'ethereum'){
			$this->load->library("web3");
			$accountInfo = $this->web3->account();
			if (!$accountInfo){
				//throw new Exception('web3 error');
				$this->set_response(['status' => true, 'res_code' => '401','message' => '생성 실패'], 400);
			} else {
				$this->response(['status' => true, 'res_code' => '001' ,'message' => 'Success','data' => $accountInfo], 200);
			}
		} else {
			$this->set_response(['status' => true, 'res_code' => '401','message' => '지원 하지 않는 서비스'], 400);
		}
	}

	public function getTokenInfo_get(){
		$address = $this->dataNullCheck($this->get('address'));
		$this->load->library("web3");
		$accountInfo = $this->web3->getTokenInfo($address);
		//$res = json_decode($accountInfo);
		if (!$accountInfo->error){
			$accountInfo->status = true;
			$accountInfo->message = 'success';
		} else {
			$accountInfo->status = false;
			$accountInfo->message = 'sever error';
		}
		echo json_encode($accountInfo);
	}

	public function setToken_post(){
		$inputData['address'] = $this->dataNullCheck($this->post('address'));
		$inputData['name'] = $this->dataNullCheck($this->post('name'));
		$inputData['symbol'] = $this->dataNullCheck($this->post('symbol'));
		$inputData['decimal'] = $this->dataNullCheck($this->post('decimal'));
		$inputData['netIdName'] = $this->dataNullCheck($this->post('netIdName'));
		$inputData['wallet_sid'] = $this->dataNullCheck($this->post('wallet_sid'));
		$inputData['platform'] = $this->dataNullCheck($this->post('platform'));
		$inputData = array_map('trim', $inputData);
		$key = $this->rest->key;
		$this->load->model('UserModel');
		$userInfo = $this->UserModel->getUserInfo(Array('key' => $key));
		if ($userInfo){
			$this->load->model('TokensModel');
			$tokenData['user_sid']		= $userInfo->sid;
			$tokenData['wallets_sid']	= $inputData['wallet_sid'];
			$tokenData['platform']		= $inputData['platform'];
			$tokenData['address']		= $inputData['address'];
			$tokenData['name']			= $inputData['name'];
			$tokenData['symbol']		= $inputData['symbol'];
			$tokenData['decimal']		= $inputData['decimal'];
			$tokenData['netIdName']		= $inputData['netIdName'];
			$tokenData['created_ip']	= $_SERVER['REMOTE_ADDR'];
			if($this->TokensModel->checkToken($tokenData)) {
				$this->response(['status' => true, 'res_code' => '201','message' => '이미 등록되어 있는 토큰 주소'], 400);
			}
			$result = $this->TokensModel->insertToken($tokenData);
			if ($result){
				$this->set_response(['status' => true, 'res_code' => '202' ,'message' => 'Success'], 201);
			} else {
				$this->response(['status' => false, 'res_code' => '002','message' => 'Server Error'], 500);
			}
			
		} else {
			$this->set_response(['status' => true, 'res_code' => '102','message' => '회원 정보 없음'], 400);
		}
	}

	function removeToken_post(){
		$inputData['sid'] = $this->dataNullCheck($this->post('sid'));
		$inputData = array_map('trim', $inputData);
		$key = $this->rest->key;
		$this->load->model('UserModel');
		$userInfo = $this->UserModel->getUserInfo(Array('key' => $key));
		if ($userInfo){
			$this->load->model('TokensModel');
			$tokenData['user_sid']		= $userInfo->sid;
			$tokenData['sid']			= $inputData['sid'];
			$result = $this->TokensModel->deleteToken($tokenData);
			if ($result){
				$this->set_response(['status' => true, 'res_code' => '202' ,'message' => 'Success'], 201);
			} else {
				$this->response(['status' => false, 'res_code' => '002','message' => 'Server Error'], 500);
			}
			
		} else {
			$this->set_response(['status' => true, 'res_code' => '102','message' => '회원 정보 없음'], 400);
		}
	}


	private function _key_exists($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->count_all_results(config_item('rest_keys_table')) > 0;
    }
}
















