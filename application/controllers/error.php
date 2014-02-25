<?php

class Error extends CI_Controller
{
	public function index()
	{
		$output = "<center style='padding:100px'><img src='".base_url()."static/images/404.jpg' /></center>";

		$this->template->setTitle("404");
		$this->template->setHeadline("404");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
}