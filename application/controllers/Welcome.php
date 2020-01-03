<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		if (2 <= 3){
			exdebug('true');		
		} else {
			exdebug('false');
		}
		exit;

		//$this->load->library('migration');
		$email="dreamload@redinfo.co.kr";
		$check_email=preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email);

		if($check_email==true){
		   exdebug("올바른 이메일 형식입니다.");
		}else{
		    exdebug("잘못된 이메일 형식입니다.");
		}
		exit;
		// tokenAddr, toAddr, privateKey, fromAddrs
		#echo json_encode(Array(
		#	'tokenAddr' => '0x49712dCCb8B9546D3335A9D18Ad14FAd8BfD964d',
		#	'toAddr'	=> '0x1E7A12b193D18027E33cd3Ff0eef2Af31cbBF9ef',
		#	'privateKey' => '0247F746A2894EBC4CFF3F73FA1478A5A6E281CC665639B0F1B0F96BFD77992F',
		#	'fromAddrs' => Array(
		#					['addr'	=> '0x3B6aA724bd2eEEB06D5e90C9FDdE82C91f076887', 'amount' => 100],
		#					['addr'	=> '0xeb26Cd790B30bBB5c1151adBED1427D4f78A3307', 'amount' => 100],
		#					['addr'	=> '0xFDF35C663321Ffd8126688684054aCF8437660c1', 'amount' => 100],
		#					['addr'	=> '0x2Fb423b28c3B95737C458cE39578f74A90D0b2dC', 'amount' => 100],
		#	)
		#));
		exit;
		// 8자리 이상 20자리 이하,  숫자 + 알파벳 조합 (필수 조건), 가능한 문자열(숫자, 알파벳, 특수문자)
		$val1 = '12345678';
		exdebug($this->sendPasswordCheck($val1)[1]);
		exit;
		$this->load->view('welcome_message');
	}

	protected function sendPasswordCheck($_str){
		$pw = $_str;
		$num = preg_match('/[0-9]/u', $pw);
		$eng = preg_match('/[a-zA-Z]/u', $pw);
		$spe = preg_match("/[\!\@\#\$\%\^\&\*]/u",$pw);
	 
		if(strlen($pw) < 8 || strlen($pw) > 30)
		{
			return array(false, "비밀번호는 영문, 숫자, 특수문자를 혼합하여 최소 8자리 ~ 최대 30자리 이내로 입력해주세요.");
			exit;
		}
	 
		if(preg_match("/\s/u", $pw) == true)
		{
			return array(false, "비밀번호는 공백없이 입력해주세요.");
			exit;
		}
	 
		if( $num == 0 || $eng == 0)
		{
			return array(false, "영문, 숫자를 혼합하여 입력해주세요.");
			exit;
		}
	 
		return array(true);
	}
}
