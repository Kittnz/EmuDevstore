<?php

class Home extends CI_Controller
{
	public function index($loginPage = false)
	{
		$this->template->setLoginRedirection($loginPage);
		
		$output = $this->template->loadPage("home");
		
		$this->template->setTitle($this->config->item('site-title')." | WoW tools");
		$this->template->setHeadline($this->config->item('site-title')." | WoW emulator tools");
		$this->template->setBigHeader(true);
		$this->template->view($output);
	}
}