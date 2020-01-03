<?php
class API_Model extends CI_Model{
	
	protected $apiDB;
	protected $smsDB;

    public function __construct(){
        parent::__construct();
        $this->apiDB = $this->load->database('default', TRUE);
		$this->smsDB = $this->load->database('sms', TRUE);
    }
} 