<?php

class forum extends CI_Controller
{
	public function index($loginPage = false)
	{	
		$output = $this->template->loadPage("forum");
		
		$this->template->setTitle("Forum - ".$this->config->item('site-title'));
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
}