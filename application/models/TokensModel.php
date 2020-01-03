<?php


class TokensModel extends API_Model{

	public function __construct(){
        parent::__construct();
    }
	
	

	public function checkToken($data){
		$SQL = "
			SELECT 
				COUNT(*) as cnt
			FROM tokens
			WHERE user_sid = '{$data['user_sid']}'
			AND wallets_sid = '{$data['wallets_sid']}'
			AND platform = '{$data['platform']}'
			AND address = '{$data['address']}'
			AND netIdName = '{$data['netIdName']}'
		";
		$RESULT = $this->apiDB->query($SQL);
		foreach($RESULT->result() as $ROW) {
			return $ROW->cnt;
		}
		return false;
	}

	public function insertToken($data){
		$SQL = "
			INSERT INTO `api`.`tokens` 
			( `user_sid`, `wallets_sid`, `platform`, `address`, 
			`name`, `symbol`, `decimal`, `netIdName`, `created_ip` )
			VALUES
			( '{$data['user_sid']}', '{$data['wallets_sid']}', '{$data['platform']}', '{$data['address']}', 
			'{$data['name']}', '{$data['symbol']}', '{$data['decimal']}', '{$data['netIdName']}', '{$data['created_ip']}' )
		";
		$RESULT = $this->apiDB->query($SQL);
		if ($RESULT){
			return $this->apiDB->insert_id();
		} else {
			return false;
		}
	}

	public function getTokenInfo($user_sid, $versionNetwork, $walletInfo){
		
		if (sizeof($walletInfo) < 1){
			return Array();
		} else {
			$SQL = "
				SELECT
					*
				FROM tokens
				WHERE user_sid = '{$user_sid}'
				AND wallets_sid = '{$walletInfo[0]->sid}'
				AND platform = '{$walletInfo[0]->platform}'
				AND netIdName = '{$versionNetwork}'
				AND status = 'Y'
			";
			$RESULT = $this->apiDB->query($SQL);
			$returnData = Array();
			$this->load->library("web3");
			//getEthBalance
			foreach($RESULT->result() as $ROW) {
				$ROW->tokenBalance = $this->web3->getTokenBalance($ROW->address, $walletInfo[0]->address, $ROW->decimal)->body;
				unset($ROW->user_sid);
				array_push($returnData, $ROW);
			}
			return $returnData;
		}
	}

	public function deleteToken($data){
		$SQL = "
			DELETE FROM tokens
			WHERE sid = '{$data['sid']}'
			AND user_sid = '{$data['user_sid']}'
		";
		return $this->apiDB->query($SQL);
	}
} 