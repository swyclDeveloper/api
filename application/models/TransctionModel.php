<?php
class TransctionModel extends API_Model{

    public function __construct(){
        parent::__construct();
    }

	public function nonceCheck($data){
		$SQL = "
			SELECT nonce FROM `trasctions`
			WHERE nonce > {$data['nonce']}
			ORDER BY nonce DESC
			LIMIT 1
		";
		$RESULT = $this->apiDB->query($SQL);
		$nonce = $data['nonce'];
		foreach($RESULT->result() as $ROW) {
			$nonce = $ROW->nonce;
			break;
		}
		return $nonce;
	}

	public function insert($data){
		$SQL = "
			INSERT INTO `api`.`trasctions` 
			(`user_sid`, `platform`, `txid`, `txid_fee`, `token`, `fromAddress`,
			`nonce`,`txid_token`, `netIdName`, `created_ip`)
			VALUES
			('{$data['user_sid']}', '{$data['platform']}', '{$data['txid']}', '{$data['txid_fee']}', '{$data['tokenAddr']}','{$data['fromAddr']}',
			'{$data['nonce_']}','{$data['txid_token']}', '{$data['netIdName']}', '{$data['created_ip']}')
		";
		$this->apiDB->trans_start();
		$result1 = $this->apiDB->query($SQL);
		if (!$result1) {
			$this->apiDB->trans_rollback();
			return false;
		}
		$idx = $this->apiDB->insert_id();
		$status = true;
		foreach($data['sendAddrs'] as $row){
			if($this->_dataInsert($idx, $row)){
				continue;
			} else {
				$status = false;
			}
		}
		if (!$status){
			$this->apiDB->trans_rollback();
			return false;
		}
		$this->apiDB->trans_complete();
		return $idx;
	}

	public function updateTransctionHashs($data){
		$SQL = "
			INSERT INTO `api`.`trasctions_hashs` (`trans_sid`, `txid`, `result`, `logs`, `created_ip`)
			VALUES
				( '{$data['index']}', '{$data['hash']}', '{$data['result']}', '{$data['logs']}', '{$data['created_ip']}' )
		";
		$this->apiDB->query($SQL);
	}

	public function dataError($sid){
		$SQL = "
			update trasctions
			set nonce = 0, status = 'data error'
			where sid = {$sid}
		";
		$this->apiDB->query($SQL);
	}

	public function update($data){}

	public function delete($data){}

	public function select($sid){
		$SQL = "SELECT * FROM `trasctions` WHERE sid = '{$sid}'";
		$RESULT = $this->apiDB->query($SQL);
		$returnData = Array();
		foreach($RESULT->result() as $ROW) {
			$ROW->datas = $this->_dataSelect($ROW->sid);
			array_push($returnData, $ROW);
		}
		return $returnData[0];
	}

	protected function _dataSelect($idx){
		$SQL = "SELECT * FROM `trasctions_datas` WHERE trans_sid = '{$idx}'";
		$RESULT = $this->apiDB->query($SQL);
		$returnData = Array();
		foreach($RESULT->result() as $ROW) {
			array_push($returnData, $ROW);
		}
		return $returnData;
	}

	protected function _dataInsert($idx, $data){
		$SQL = "
			INSERT INTO `api`.`trasctions_datas` (`trans_sid`, `address`, `amounts`, `created_ip` )
			VALUES
				( '{$idx}', '{$data->addr}', '{$data->amount}', '".$_SERVER['REMOTE_ADDR']."' )
		";
		return $this->apiDB->query($SQL);
	}

	public function transSucessProc1($sid){
		$SQL = "
			UPDATE trasctions A, trasctions_hashs B
			SET A.txid = B.txid
			WHERE A.sid = '{$sid}'
			AND A.sid = B.trans_sid 
			AND B.result = 'transactionHash'
		";
	}

	public function transSucessProc2($sid){
		$SQL = "
			
		";
	}
	
} 