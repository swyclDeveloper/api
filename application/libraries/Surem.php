<?php

class Surem {
	public $CI;

	public $usercode;
	public $deptcode;
	public $messages;

	public $message_id;
	public $nation;
	public $to;
	public $text;
	public $from;
	public $reserved_time;
	public $res_code;
	public $res_message;
	public $result;
	public $additional_info;
	public $created_ip;

	public $sendDatas = Array();

	public function __construct(){
		$this->CI =&get_instance();
		$this->usercode = SUREM_ID;
		$this->deptcode = SUREM_DEPTCODE;
		$this->from		= SUREM_REQ_PHONE;
	}

	public function send($data){
		$url = 'https://rest.surem.com/sms/v1/json';
		$this->nation = $data['nation'];
		$this->text = $data['text'];
		$this->reserved_time = $data['reserved_time'] == '' ? '000000000000' : $data['reserved_time'];
		$reqData['usercode']		= $this->usercode;
		$reqData['deptcode']		= $this->deptcode;
		$reqData['messages']		=  $this->startSmsLog($data['messages']);
		$reqData['text']			= $this->text;
		$reqData['from']			= $this->from;
		$reqData['reserved_time']	= $this->reserved_time;
		if (!$reqData['messages']) return false;
		$ch = curl_init( $url );
		# Setup request to send json via POST.
		$payload = json_encode( $reqData );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		# Return response instead of printing.
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		# Send request.
		$result = curl_exec($ch);
		curl_close($ch);
		# Print response.
		//echo "<pre>$result</pre>";
		$resData = json_decode($result);
		return $this->endSmsLog($resData);
	}

	protected function startSmsLog($messages){
		if (is_array($messages)  && sizeof($messages) > 0){
			$this->CI->load->model('SmsModel');
			foreach($messages as $row){
				$sendLog['message_id']		= '';
				$sendLog['usercode']		= $this->usercode;
				$sendLog['deptcode']		= $this->deptcode;
				$sendLog['nation']			= $this->nation;
				$sendLog['to']				= $row['to'];
				$sendLog['text']			= $this->text;
				$sendLog['from']			= $this->from;
				$sendLog['reserved_time']	= '';
				$sendLog['res_code']		= '';
				$sendLog['res_message']		= '';
				$sendLog['result']			= '';
				$sendLog['additional_info'] = '';
				$sendLog['created_ip']		= $_SERVER['REMOTE_ADDR'];

				$sendLog['message_id'] = $this->CI->SmsModel->sendLog($sendLog);
				array_push($this->sendDatas, $sendLog);
			}
			// 최종 to, message_id 만 리턴한다.
			if (sizeof($this->sendDatas) > 0){
				$returnData = Array();
				foreach($this->sendDatas as $row){
					array_push($returnData, Array('message_id' => $row['message_id'], 'to' => $row['to']));
				}
				return $returnData;
			} else {
				return false;
			}
			exit;
		} else {
			return false;
		}
	}

	protected function endSmsLog($resData){
		$this->CI->load->model('SmsModel');
		$logData['res_code']			= $resData->code;
		$logData['res_message']			= $resData->message;
		$logData['additional_info']		= isset($resData->additional_information) ? $resData->additional_information : '';
		if ($resData->code == '200'){
			foreach($resData->results as $row){
				$logData['message_id']	= $row->message_id;
				$logData['result']		= $row->result;
				$this->CI->SmsModel->sendLogUpdate($logData);
			}
			return true;
		} else {
			foreach($this->sendDatas as $row){
				$logData['message_id']	= $row['message_id'];
				$logData['result']		= 'error';
				$this->CI->SmsModel->sendLogUpdate($logData);
			}
			return false;
		}
	}
}

?>