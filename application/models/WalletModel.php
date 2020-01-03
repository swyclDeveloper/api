<?php


class WalletModel extends API_Model{

	public $phonesObjects;
	public $platform;

    public function __construct(){
        parent::__construct();
    }
	
	public function insertWallet($data){
		$SQL = "
			INSERT INTO `api`.`wallets` ( `user_sid`, `platform`, `address`, `nickname`,`created_ip` )
			VALUES
			( '{$data['user_sid']}', '{$data['platform']}', '{$data['address']}', '{$data['nickname']}','{$data['created_ip']}' )
		";
		$RESULT = $this->apiDB->query($SQL);
		if ($RESULT){
			return $this->apiDB->insert_id();
		} else {
			return false;
		}
	}

	public function getWallet($data){
		$SQL = "
			SELECT 
				sid,
				nickname,
				platform,
				address
			FROM wallets
			WHERE user_sid = '{$data['user_sid']}'
			AND platform = '{$data['platform']}'
			AND status = 'Y'
			ORDER BY 1 DESC
		";
		$RESULT = $this->apiDB->query($SQL);
		$returnData = Array();
		$this->load->library("web3");
		//getEthBalance
		foreach($RESULT->result() as $ROW) {
			$ROW->ethBalance = $this->web3->getEthBalance($ROW->address);
			array_push($returnData, $ROW);
		}
		return $returnData;
	}

	public function checkWallet($data, $stap = 'i'){
		$SQL = "
			SELECT 
				COUNT(*) as cnt
			FROM wallets
			WHERE user_sid = '{$data['user_sid']}'
			AND platform = '{$data['platform']}'
			AND address = '{$data['address']}'
		";
		if ($stap == 'd'){$SQL .= " AND status = 'Y' ";}
		if ($stap == 'r'){$SQL .= " AND status = 'D' ";}
		$RESULT = $this->apiDB->query($SQL);
		foreach($RESULT->result() as $ROW) {
			return $ROW->cnt;
		}
		return false;
	}

	public function deleteWallet($data){
		$SQL = "
			UPDATE wallets
			SET status = 'D'
			WHERE user_sid = '{$data['user_sid']}'
			AND platform = '{$data['platform']}'
			AND address = '{$data['address']}'
			AND status = 'Y'
		";
		return $this->apiDB->query($SQL);
	}

	public function getRestoreWallet($data){
		$SQL = "
			SELECT 
				*
			FROM wallets
			WHERE user_sid = '{$data['user_sid']}'
			AND status = 'D'
			ORDER BY 1 DESC
		";
		$RESULT = $this->apiDB->query($SQL);
		$returnData = Array();
		foreach($RESULT->result() as $ROW) {
			array_push($returnData, $ROW);
		}
		return $returnData;
	}

	public function restoreWallet($data){
		$SQL = "
			UPDATE wallets
			SET status = 'Y'
			WHERE user_sid = '{$data['user_sid']}'
			AND platform = '{$data['platform']}'
			AND address = '{$data['address']}'
			AND status = 'D'
		";
		return $this->apiDB->query($SQL);
	}

	public function checkUserWallets($data){
		$returnStatus = TRUE;
		$this->platform	= $data['platform'];
		if ($data['platform'] == "erc20"){
			foreach($data['phonesObjects'] as $idx => $obj){
				$SQL = "
					SELECT
						*
					FROM wallets
						WHERE (CASE
									WHEN wallets.user_id != '' THEN wallets.user_id = '{$obj->user_id}'
									ELSE (SELECT users.id FROM users WHERE users.sid = wallets.user_sid) = '{$obj->user_id}'
								END)
					AND platform = '{$data['platform']}'
					AND status IN ('Y','H')
					ORDER BY 1 ASC
					LIMIT 1
				";
				$RESULT = $this->apiDB->query($SQL);
				if (sizeof($RESULT->result()) > 0){
					$data['phonesObjects'][$idx]->status	= TRUE;
					$data['phonesObjects'][$idx]->address	= $RESULT->result()[0]->address;
				} else {
					$returnStatus = FALSE;
				}
			}
			$this->phonesObjects = $data['phonesObjects'];
			
			return $returnStatus;
		} else {
			return $returnStatus;
		}
		return $returnStatus;
	}

	public function updateAnonymous($data){
		$this->load->library("web3");

		$this->apiDB->trans_begin();

		foreach($data as $idx => $obj){
			if ($obj->address == ''){
				$accountInfo = $this->web3->account();
				if (!$accountInfo){
					//throw new Exception('web3 error');
					return false;
				}
				$data[$idx]->status = 'H';
				$data[$idx]->address = $accountInfo->address;
				$SQL = "
					INSERT INTO `api`.`wallets` ( `user_id`, `platform`, `address`, `privateKey`, `status` )
					VALUES
					( '{$obj->user_id}', '{$this->platform}', '{$accountInfo->address}', '{$accountInfo->privateKey}', 'H' )
				";
				$this->apiDB->query($SQL);
			}
		}

		if ($this->apiDB->trans_status() === FALSE){
			$this->apiDB->trans_rollback();
			//throw new Exception('server error');
		}else{
			$this->apiDB->trans_commit();
			return true;
		}
		
	}
} 