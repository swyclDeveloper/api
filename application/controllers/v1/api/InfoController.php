
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InfoController extends API_Controller {
    
    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
    }

	public function coinprice_get($id = 'all'){
		// https://wallet.henaplatform.io/getcoinpriceall
		/* API URL */
		$url = 'https://wallet.henaplatform.io/getcoinpriceall';
        $ch = curl_init($url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_TIMEOUT,10);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($httpcode == 200){
			//exdebug($output);
			$this->response(['status' => true, 'data' => json_decode($output)], 200);
		} else {
			$this->response(['status' => false], $httpcode);
		}
	}

	public function usdtokrw_get(){
		// https://wallet.henaplatform.io/getusdtokrw
		/* API URL */
		$url = 'https://wallet.henaplatform.io/getusdtokrw';
        $ch = curl_init($url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_TIMEOUT,10);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($httpcode == 200){
			//exdebug($output);
			$this->response(['status' => true, 'data' => json_decode($output)], 200);
		} else {
			$this->response(['status' => false], $httpcode);
		}
	}

	public function exchangeRate_get($id = 'all'){
		$url = 'https://wallet.henaplatform.io/getExchangeRateAll';
        $ch = curl_init($url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_TIMEOUT,10);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($httpcode == 200){
			//exdebug($output);
			$this->response(['status' => true, 'data' => json_decode($output)], 200);
		} else {
			$this->response(['status' => false], $httpcode);
		}
	}

	public function senderOwner_get(){
		$senderOwner = '0xc987F3E6f5735eC26f281388a41D99C4FE4999D3';
		$this->response(['status' => true, 'data' => $senderOwner], 200);
	}

	public function sender_get(){
		$sender = '0x184c2B344A58Ff780aB552d94325feeB80B4911B';
		$this->response(['status' => true, 'data' => $sender], 200);
	}

	public function currentFee_get(){
		$data = Array(
			"current_fee"	=> "1000000000000000",
			"array_limit"	=> "100",
		);
		$this->response(['status' => true, 'data' => $data], 200);
	}

}
