<?php

class Home extends CI_Controller
{
	public function index($loginPage = false)
	{
		$this->template->setLoginRedirection($loginPage);
		
		$output = $this->template->loadPage("home");
		
		$this->template->setTitle($this->config->item('site-title')." | Your place for WoW emulator tools");
		$this->template->setHeadline($this->config->item('site-title')." | Your place for WoW emulator tools");
		$this->template->setBigHeader(true);
		$this->template->view($output);
	}
}