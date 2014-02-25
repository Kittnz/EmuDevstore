<?php

class Createaccount extends CI_Controller
{

	public function index()
	{
		$output = $this->template->loadPage("createaccount");

		$this->template->setTitle("Account creation - ".$this->config->item('site-title'));
		$this->template->setHeadline("Create a new Account - 100% free!");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}

	public function success()
	{
		$output = $this->template->loadPage("createaccountsuccess");

		$this->template->setTitle("Account creation - ".$this->config->item('site-title'));
		$this->template->setHeadline("Create a new Account - 100% free!");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
}