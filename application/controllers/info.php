<?php

class info extends CI_Controller
{

	public function impressum()
	{
		$output = $this->template->loadPage("impressum");

		$this->template->setTitle("Impressum - ".$this->config->item('site-title'));
		$this->template->setHeadline("Emu-Devstore Details");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}

	public function guide()
	{
		$output = $this->template->loadPage("guide");

		$this->template->setTitle("Guide - ".$this->config->item('site-title'));
		$this->template->setHeadline("Emu-Devstore Guide");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
}