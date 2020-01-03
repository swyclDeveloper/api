<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

	function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

	public function index() {
		exdebug('test');
	}

}

