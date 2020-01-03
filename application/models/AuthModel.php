<?php
class AuthModel extends API_Model{

    public function __construct(){
        parent::__construct();
    }

	public function checkAuthF1($data){
		$SQL = "
			SELECT 
			COUNT(*) AS cnt
			FROM authkey
			WHERE user_id = '{$data['user_id']}'
			AND SUBDATE(CURRENT_TIMESTAMP, INTERVAL 3 MINUTE) < created_at
		";
		$RESULT = $this->apiDB->query($SQL);
		foreach($RESULT->result() as $ROW) {
			return $ROW->cnt;
		}
		return false;
	}

	public function insertAuthKey($data){
		$SQL = "
			INSERT INTO 
			`api`.`authkey`(`user_id`, `key`, `method`,`ip_addresses`) 
			VALUES 
			('{$data['user_id']}', '{$data['key']}', '{$data['method']}', '{$data['ip_addresses']}')
		";
		$RESULT = $this->apiDB->query($SQL);
		if ($RESULT){
			return $this->apiDB->insert_id();
		} else {
			return false;
		}
	}

	public function deleteAuthKey($i1Result){
		$SQL = "DELETE FROM `api`.`authkey` WHERE sid = '{$i1Result}'";
		@$this->apiDB->query($SQL);
	}

	public function checkAuthKey($data){
		$SQL = "
			SELECT
				t1.sid
			FROM (SELECT 
						*
					FROM authkey
					WHERE user_id = '{$data['user_id']}'
					AND SUBDATE(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE) < created_at
					AND status = 'N'
					AND method = '{$data['method']}'
					ORDER BY created_at desc
					LIMIT 1 ) t1
			WHERE t1.key = '{$data['key']}'
		";
		$RESULT = $this->apiDB->query($SQL);
		foreach($RESULT->result() as $ROW) {
			$sid = $ROW->sid;
			$this->apiDB->query("UPDATE authkey SET status = 'Y' WHERE sid = '{$sid}'");
			return true;
		}
		return false;
	}

	public function findPassword($data){
		$SQL = "
			UPDATE users
			SET STATUS = 'P' ,passwd = PASSWORD('{$data['newPw']}')
			WHERE id = (SELECT user_id FROM `authkey`
						WHERE service = 'SMS'
						AND method = 'findpasswd'
						AND `key` = '{$data['key']}'
						AND status = 'Y')
			AND STATUS IN ('Y','P')
		";
		return $this->apiDB->query($SQL);
	}

	public function forceQuit($user_sid){
		$SQL = "
			UPDATE `keys`
			SET status = 'N'
			WHERE user_id = '{$user_sid}'
		";
		return $this->apiDB->query($SQL);
	}
	public function forceQuitkey($key){
		$SQL = "
			UPDATE `keys`
			SET status = 'N'
			WHERE `key` = '{$key}'
		";
		exdebug($SQL);
		return $this->apiDB->query($SQL);
	}
} 