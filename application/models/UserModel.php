<?php
class UserModel extends API_Model{

    public function __construct(){
        parent::__construct();
    }

	public function checkUser($data){
		$SQL = "
			SELECT count(*) as cnt FROM users WHERE id = '{$data['id']}'
		";
		$RESULT = $this->apiDB->query($SQL);
		foreach($RESULT->result() as $ROW) {
			return $ROW->cnt;
		}
		return false;
	}

	public function userTempInsert($data){
		//exdebug($data);
		$SQL = "
			INSERT INTO `api`.`users_temp` 
				(  `id`, `passwd`, `nation`, `phone`, `status`, `created_ip` )
			VALUES
				( '{$data['id']}', PASSWORD('{$data['pw']}'), '{$data['nation']}', '{$data['phone']}', 'T', '{$_SERVER['REMOTE_ADDR']}' )
		";
		//exdebug($data);
		$RESULT = $this->apiDB->query($SQL);
		if ($RESULT){
			return $this->apiDB->insert_id();
		} else {
			return false;
		}
	}

	public function getUserTemp($data){
		$SQL = "
			SELECT 
				* 
			FROM users_temp 
			WHERE sid = '{$data['tempSid']}'
			AND id = '{$data['id']}'
			AND passwd = PASSWORD('{$data['pw']}')
			AND nation = '{$data['nation']}'
			AND phone = '{$data['phone']}'
			AND created_at > SUBDATE(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE)
		";
		$RESULT = $this->apiDB->query($SQL);
		foreach($RESULT->result() as $ROW) {
			return $ROW;
		}
		return false;
	}

	public function checkUserRegistSmsAutn($data){
		$SQL = "
			SELECT count(*) as cnt FROM authkey 
			WHERE user_id = '{$data['id']}'
			AND `key` = '{$data['key']}'
			AND `status` = 'Y'
			AND SUBDATE(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE) < created_at
		";
		$RESULT = $this->apiDB->query($SQL);
		foreach($RESULT->result() as $ROW) {
			return $ROW->cnt;
		}
		return false;
	}

	public function userInsert($data){
		
		$SQL = "
			INSERT INTO `api`.`users`
			(`id`, `name`, `passwd`, `nation`, `phone`, `recomm_id`,`recomm_nation`, `recomm_phone`, `status`, `created_ip`) 
			VALUES 
			('{$data['id']}', '{$data['name']}', PASSWORD('{$data['pw']}'), '{$data['nation']}', '{$data['phone']}', '{$data['recomm_id']}', '{$data['recomm_nation']}', '{$data['recomm_phone']}', 'Y', '{$_SERVER['REMOTE_ADDR']}')
		";
		
		$RESULT = $this->apiDB->query($SQL);
		if ($RESULT){
			return $this->apiDB->insert_id();
		} else {
			return false;
		}
	}

	public function loginCheck($data){
		$SQL = "
			SELECT
				sid,
				id,
				nation,
				phone
			FROM users
			WHERE id = '{$data['id']}'
			AND passwd = PASSWORD('{$data['pw']}')
			AND status IN ('Y', 'P')
		";
		$RESULT = $this->apiDB->query($SQL);
		foreach($RESULT->result() as $ROW) {
			return $ROW;
		}
		return false;
	}

	public function getUserInfo($data){
		if (isset($data['user_sid'])){
			$SQL = " SELECT * FROM users WHERE sid = '{$data['user_sid']}'";
		} else if (isset($data['id'])){
			$SQL = " SELECT * FROM users WHERE id = '{$data['id']}'";
		} else if (isset($data['key'])){
			$SQL = "
				SELECT 
					t2.*
				FROM `keys` t1 , users t2
				WHERE t1.`key` = '{$data['key']}' 
				AND t1.status = 'Y'
				AND t1.user_id = t2.sid
			";
		} else {
			return false;
		}
		$RESULT = $this->apiDB->query($SQL);
		foreach($RESULT->result() as $ROW) {
			return $ROW;
		}
		return false;
	}

	public function checkPasswd($data){
		$SQL = "
			SELECT
				COUNT(t2.sid) AS cnt
			FROM `keys` as t1, users t2
			WHERE t1.`key` = '{$data['key']}'
			AND t1.status = 'Y'
			AND t1.user_id = t2.sid
			AND t2.passwd = PASSWORD('{$data['pw']}')
		";
		$RESULT = $this->apiDB->query($SQL);
		foreach($RESULT->result() as $ROW) {
			return $ROW->cnt;
		}
		return false;
	}

	public function checkSendPasswd($data){
		$SQL = "
			SELECT
				COUNT(t2.sid) AS cnt
			FROM `keys` as t1, users t2
			WHERE t1.`key` = '{$data['key']}'
			AND t1.status = 'Y'
			AND t1.user_id = t2.sid
			AND t2.send_passwd = PASSWORD('{$data['pw']}')
		";
		$RESULT = $this->apiDB->query($SQL);
		foreach($RESULT->result() as $ROW) {
			return $ROW->cnt;
		}
		return false;
	}

	public function updatePasswd($data){
		$SQL = "
			UPDATE `keys` as t1, users t2
			SET 
				t2.passwd = PASSWORD('{$data['pw']}'),
				t2.status =	(CASE
								WHEN t2.status = 'P' THEN 'Y'
								ELSE t2.status
							END)
			WHERE t1.`key` = '{$data['key']}'
			AND t1.status = 'Y'
			AND t1.user_id = t2.sid
		";
		return $this->apiDB->query($SQL);
	}
	
	public function delete($id){
		$SQL = "
			DELETE FROM users
			WHERE id = '{$id}'
		";
		return $this->apiDB->query($SQL);
	}
} 