<?php
	Class Web3{
		
		public $CI;
		//protected $nodeHost = 'http://15.164.51.11:3001';
		protected $nodeHost = 'http://15.164.51.11:3000';

		public function __construct(){
			$this->CI =&get_instance();
		}

		protected function jwt_request($method='post',$token, $data, $url) {
			$ch = curl_init($url); 
			$data = json_encode($data); // 
			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = "Authorization: Bearer ".$token; 
			//$headers[] = "X-Partner-User-Token: "; 
			
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			if($method == 'post'){
				curl_setopt($ch, CURLOPT_POST, 1); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			}
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

			$output = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if ($httpcode == "200"){
				return json_decode($output);
			} else {
				return false;
			}
		}

		protected function jwt_request2($method='post',$token, $data, $url) {
			$ch = curl_init($url); 
			$data = json_encode($data); // 
			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = "Authorization: Bearer ".$token; 
			//$headers[] = "X-Partner-User-Token: "; 
			
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			if($method == 'post'){
				curl_setopt($ch, CURLOPT_POST, 1); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			}
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

			$output = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			return json_decode($output);
		}

		public function account(){
			$url = '/ether/account';
			$result = $this->jwt_request('get',null, null, $this->nodeHost.$url);
			return $result;
		}

		public function getEthBalance($address){
			$url = '/ether/getbalance?address='.$address;
			$result = $this->jwt_request('get',null, null, $this->nodeHost.$url);
			return $result;
		}

		public function versionNetwork(){
			$url = '/ether/version/network';
			$result = $this->jwt_request('get',null, null, $this->nodeHost.$url);
			return $result;
		}

		public function getTokenInfo($address){
			$url = '/token/info?address='.$address;
			$result = $this->jwt_request('get',null, null, $this->nodeHost.$url);
			return $result;
		}

		public function getTokenBalance($token, $address , $decimal){
			$url = '/token/getbalance?token='.$token.'&address='.$address.'&decimal='.$decimal;
			$result = $this->jwt_request('get',null, null, $this->nodeHost.$url);
			return $result;
		}
		
		public function getSenderNonce(){
			$url = '/senderNonce';
			$result = $this->jwt_request('get',null, null, $this->nodeHost.$url);
			return $result;
		}

		public function multisend($data){
			$url = '/multisend';
			$result = $this->jwt_request2('post',null, $data, $this->nodeHost.$url);
			return $result;
		}

		public function __destruct(){
		
		
		}
	}
?>