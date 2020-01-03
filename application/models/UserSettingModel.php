<?php
class UserSettingModel extends API_Model{

    public function __construct(){
        parent::__construct();
    }


	public function updateSendPasswd($data){
		if ($data['use_at'] == 'Y'){
			$SQL = "
				UPDATE `keys` as t1, users t2
				SET
					t2.send_passwd = PASSWORD('{$data['pw']}'),
					t2.send_passwd_useAt = '{$data['use_at']}'
				WHERE t1.`key` = '{$data['key']}'
				AND t1.status = 'Y'
				AND t1.user_id = t2.sid
			";
		} else {
			$SQL = "
				UPDATE `keys` as t1, users t2
				SET
					t2.send_passwd = '',
					t2.send_passwd_useAt = '{$data['use_at']}'
				WHERE t1.`key` = '{$data['key']}'
				AND t1.status = 'Y'
				AND t1.user_id = t2.sid
			";
		}
		
		return $this->apiDB->query($SQL);
	}

	public function updateBiometricAuth($data){
		$SQL = "
			UPDATE `keys` as t1, users t2
			SET
				t2.biometrics_useAt = '{$data['use_at']}'
			WHERE t1.`key` = '{$data['key']}'
			AND t1.status = 'Y'
			AND t1.user_id = t2.sid
		";
		return $this->apiDB->query($SQL);
	}

	public function updateUserEmail($data){
		$SQL = "
			UPDATE `keys` as t1, users t2
			SET t2.email = '{$data['email']}'
			WHERE t1.`key` = '{$data['key']}'
			AND t1.status = 'Y'
			AND t1.user_id = t2.sid
		";
		return $this->apiDB->query($SQL);
	}
}