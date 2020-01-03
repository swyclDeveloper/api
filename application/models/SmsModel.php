<?php
class SmsModel extends API_Model{

    public function __construct(){
        parent::__construct();
    }

	public function test(){
		//exdebug($this->apiDB);
		$SQL = "SELECT * FROM SUREData";
		$this->smsDB->query($SQL);
		$RESULT = $this->smsDB->query($SQL);
        $list = array();
        $ListRow = 0;
		foreach($RESULT->result() as $ROW) {
			array_push($list ,$ROW);
			$ListRow ++;
        }
		exdebug($list);
        //return $list[0];
	}
	/*
		INSERT INTO SUREData
		(USERCODE, REQNAME, REQPHONE, CALLNAME, CALLPHONE, SUBJECT, MSG, REQTIME, RESULT, KIND)
		VALUES
		  ('usercode'           -- usercode (surem 아이디)
			, '전송자'        -- 회신자명
			, '15884640'    -- 회신자 번호
			, '받는자'        -- 수신자명
			, '01011111111'   -- 수신자 번호
			, '제목입니다'    -- MMS 제목 (sms일 땐 ''로 해도 됨)
			, '테스트 문자'    -- 문자내용
			, '00000000000000'  -- 예약문자 전송시 'YYYYmmddHHMMss', 즉시전송시 '00000000000000'
			, '0'   -- Default = 0, ( 0 : 즉시전송(숫자 0) R : 예약전송 )
			, 'S' -- M : MMS, S : SMS, I : 국제문자, L : 국제 MMS
		  )
	*/
	public function send($data){
		$SQL = "
			INSERT INTO SUREData
			(USERCODE, REQNAME, REQPHONE, CALLNAME, COUNTRY, CALLPHONE, SUBJECT, MSG, REQTIME, RESULT, KIND)
			VALUES
			  ('{$data['USERCODE']}'
				, '{$data['REQNAME']}'
				, '{$data['REQPHONE']}'
				, '{$data['CALLNAME']}'
				, '{$data['COUNTRY']}'
				, '{$data['CALLPHONE']}'
				, '{$data['SUBJECT']}'
				, '{$data['MSG']}'
				, '{$data['REQTIME']}'
				, '{$data['RESULT']}'
				, '{$data['KIND']}'
			  )
		";
		$RESULT = $this->smsDB->query($SQL);
		if ($RESULT){
			return $this->smsDB->insert_id();
		} else {
			return false;
		}
	}

	public function sendLog($data){
		$SQL = "
			INSERT INTO `api`.`smsLogs` (
				`usercode`,
				`deptcode`,
				`nation`,
				`to`,
				`text`,
				`from`,
				`reserved_time`,
				`created_ip`
			)VALUES	(
				'{$data['usercode']}',
				'{$data['deptcode']}',
				'{$data['nation']}',
				'{$data['to']}',
				'{$data['text']}',
				'{$data['from']}',
				'{$data['reserved_time']}',
				'{$data['created_ip']}')
			";
		$RESULT = $this->apiDB->query($SQL);
		if ($RESULT){
			return $this->apiDB->insert_id();
		} else {
			return false;
		}
	}

	public function sendLogUpdate($logData){
		$SQL = "
			UPDATE `api`.`smsLogs`
			SET
				`res_code` = '{$logData['res_code']}',
				`res_message` = '{$logData['res_message']}',
				`additional_info` = '{$logData['additional_info']}',
				`result` = '{$logData['result']}'
			WHERE 
				`message_id` = '{$logData['message_id']}'
		";
		$this->apiDB->query($SQL);
	}
} 