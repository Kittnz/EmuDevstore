<?php

class Update extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->user->requireOnline("update");
	}

	public function view()
	{

		$updates = file_get_contents("application/views/updates/data.json");
		$updates = json_decode($updates, true);

		if(!is_array($updates))
		{
			$updates = array();
		}

		$updates = array_reverse($updates);

		$output = $this->template->loadPage("view-updates", array('updates' => $updates));
		
		$this->template->setTitle("Updates - ".$this->config->item('site-title'));
		$this->template->setHeadline("Update your old FusionCMS installation");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}

	public function index()
	{
		$this->view();
	}
}