<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TransactionController extends API_Controller {
    
    function __construct()
    {
        parent::__construct();
    }

	public function transfer_post(){

		$inputData['txid_fee']		= $this->dataNullCheck($this->post('txid_fee'));
		$inputData['txid_token']	= $this->dataNullCheck($this->post('txid_token'));
		$inputData['fromAddr']		= $this->dataNullCheck($this->post('fromAddr'));
		$inputData['tokenAddr']		= $this->dataNullCheck($this->post('tokenAddr'));
		$inputData['platform']		= $this->dataNullCheck($this->post('platform'));
		$inputData['sendAddrs']		= $this->dataNullCheck($this->post('sendAddrs'));
		
		$inputData = array_map('trim', $inputData);
		$inputData['sendAddrsObjects'] = json_decode($inputData['sendAddrs']);
		$key = $this->rest->key;
		$this->load->model('UserModel');
		$userInfo = $this->UserModel->getUserInfo(Array('key' => $key));
		if ($userInfo){
			$this->load->library("web3");
			$versionNetwork = $this->web3->versionNetwork();
			$standardNonce = $this->web3->getSenderNonce();
			if ($versionNetwork->error || $standardNonce->error){
				if (!$i1Result){$this->response(['status' => false, 'res_code' => '002','message' => 'Server Error'], 500);}
			}
			$transData['user_sid']		= $userInfo->sid;
			$transData['platform']		= $inputData['platform'];
			$transData['txid']			= '';
			$transData['fromAddr']		= $inputData['fromAddr'];
			$transData['txid_fee']		= $inputData['txid_fee'];
			$transData['txid_token']	= $inputData['txid_token'];
			$transData['tokenAddr']		= $inputData['tokenAddr'];
			$transData['netIdName']		= $versionNetwork->body->netIdName;
			$transData['sendAddrs']		= $inputData['sendAddrsObjects'];
			$transData['nonce']			= $standardNonce->body->nonce;
			$transData['created_ip']	= $_SERVER['REMOTE_ADDR'];
			$this->load->model('TransctionModel');
			// 저장 전 nonce 값 확인
			// 1. 실제 web3 에서 데이터 가져온다.
			//exdebug($transData);
			$transData['nonce_'] = $this->TransctionModel->nonceCheck($transData);
			
			$result = $this->TransctionModel->insert($transData);
			if ($result){
				// node js 에 데이터 forward
				// 데이터 select
				$transInfo = $this->TransctionModel->select($result);
				$requestData['txid_fee']		= $transInfo->txid_fee;
				$requestData['txid_token']		= $transInfo->txid_token;
				$requestData['fromAddr']		= $transInfo->fromAddress;
				$requestData['tokenAddr']		= $transInfo->token;
				$requestData['nonce']			= $transInfo->nonce;
				$requestData['index']			= $transInfo->sid;
				$requestData['sendAddrs']		= Array();
				foreach($transInfo->datas as $row){
					array_push($requestData['sendAddrs'], Array("addr" => $row->address, "amount" => $row->amounts));
				}
				//exdebug(json_encode($requestData));
				$nodeResult = $this->web3->multisend($requestData);
				//exdebug($nodeResult);
				//exit;
				if ($nodeResult->error){
					$this->TransctionModel->dataError($result);
					$this->response(['status' => true, 'res_code' => '350','message' => '전송 정보가 잘못 되었습니다.'], 400);
				} else {
					$this->response(['status' => true, 'res_code' => '001','data' => Array('idx' => $result),'message' => 'Success'], 201);
				}
				exit;
				//
				
			} else {
				$this->response(['status' => false, 'res_code' => '002','message' => 'Server Error'], 500);
			}
			// model 호출 해서 저장
		} else {
			$this->set_response(['status' => true, 'res_code' => '102','message' => '회원 정보 없음'], 400);
		}
	}

	public function transfer_get($sid = 0){
		exdebug($sid);
	}

	public function updateHash_post(){
		$data['index']		= $this->post('index');
		$data['hash']		= $this->post('hash');
		$data['logs']		= $this->post('logs');
		$data['result']		= "transactionHash";
		$data['created_ip']	= $_SERVER['REMOTE_ADDR'];
		$this->load->model('TransctionModel');
		$this->TransctionModel->updateTransctionHashs($data);
		
	}

	public function updateReceipt_post(){
		$data['index']		= $this->post('index');
		$data['hash']		= $this->post('hash');
		$data['logs']		= $this->post('logs');
		$data['result']		= "receipt";
		$data['created_ip']	= $_SERVER['REMOTE_ADDR'];
		$this->load->model('TransctionModel');
		$this->TransctionModel->updateTransctionHashs($data);
	}

	public function updateError_post(){
		$data['index']		= $this->post('index');
		$data['hash']		= $this->post('hash');
		$data['logs']		= $this->post('logs');
		$data['result']		= "error";
		$data['created_ip']	= $_SERVER['REMOTE_ADDR'];
		$this->load->model('TransctionModel');
		$this->TransctionModel->updateTransctionHashs($data);
	}
}