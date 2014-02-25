<?php

class Themes extends CI_Controller
{
	public function __construct()
	{
			parent::__construct();
			$this->load->model('publisher_themes_model');
	}
	
	public function index()
	{
		$themes = $this->publisher_themes_model->getThemes($this->user->getId());
		
		if($themes == false)
		{
			$output = $this->template->loadPage("publisher/themes", array('themes' => array()));
		}
		else
		{
			$output = $this->template->loadPage("publisher/themes", array('themes' => $themes));
		}
		
		$this->template->setTitle("Themes - ".$this->config->item('site-title'));
		$this->template->setHeadline("Your published themes");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
	
	public function delete($themeId)
	{
		//Make sure that we got the theme
		if($this->publisher_themes_model->isMyTheme($this->user->getId(), $themeId))
		{
			//Delete it
			$this->publisher_themes_model->deleteTheme($themeId);
			
			$output = $this->template->loadPage("error", array("error" => "Deleted the theme successfully"));
			$this->template->setHeadline("An erorr occured");
			$this->template->setBigHeader(false);
			$this->template->view($output);
		}
		else
		{
			//Not ours....
			$output = $this->template->loadPage("error", array("error" => "Wrong theme selected to delete, failed."));
			$this->template->setHeadline("An erorr occured");
			$this->template->setBigHeader(false);
			$this->template->view($output);
		}
	}
}