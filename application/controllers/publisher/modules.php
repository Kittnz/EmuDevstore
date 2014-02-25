<?php

class Modules extends CI_Controller
{
	public function __construct()
	{
			parent::__construct();
			$this->load->model('publisher_modules_model');
	}
	
	public function index()
	{
		$modules = $this->publisher_modules_model->getModules($this->user->getId());
		
		if($modules == false)
		{
			$output = $this->template->loadPage("publisher/modules", array('modules' => array()));
		}
		else
		{
			$output = $this->template->loadPage("publisher/modules", array('modules' => $modules));
		}
		
		$this->template->setTitle("modules - FusionHub");
		$this->template->setHeadline("Your published modules");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
	
	public function delete($moduleId)
	{
		//Make sure that we got the module
		if($this->publisher_modules_model->isMyModule($this->user->getId(), $moduleId))
		{
			//Delete it
			$this->publisher_modules_model->deleteModule($moduleId);
			
			$output = $this->template->loadPage("error", array("error" => "Deleted the module successfully"));
			$this->template->setHeadline("An error occured");
			$this->template->setBigHeader(false);
			$this->template->view($output);
		}
		else
		{
			//Not ours....
			$output = $this->template->loadPage("error", array("error" => "Wrong module selected to delete, failed."));
			$this->template->setHeadline("An error occured");
			$this->template->setBigHeader(false);
			$this->template->view($output);
		}
	}
}