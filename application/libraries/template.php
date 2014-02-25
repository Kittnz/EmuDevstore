<?php

class Template
{
	private $CI;
	private $title;
	private $headline;
	private $hasBigHeader;
	private $loginRedirection;

	public function __construct()
	{
		$this->CI = &get_instance();
		$this->title = "Emu-Devstore";
		$this->hasBigHeader = false;
	}

	/** 
	 * Make the log in redirect to a certain URL after signed in
	 * @param String $url
	 */
	public function setLoginRedirection($url)
	{
		$this->loginRedirection = $url;
	}

	/**
	 * Change the page title
	 * @param String $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Change the page headline
	 * @param String $headline
	 */
	public function setHeadline($headline)
	{
		$this->headline = $headline;
	}

	/**
	 * Make the header big
	 * @param Boolean $bool
	 */
	public function setBigHeader($bool)
	{
		$this->hasBigHeader = $bool;
	}
	
	/**
	 * Loads the template
	 * @param String $content The page content
	 */
	public function view($content)
	{
		if($this->CI->input->is_ajax_request() && isset($_GET['is_ajax']) && $_GET['is_ajax'] == 1)
		{
			$array = array(
				"title" => $this->title, 
				"content" => $content,
				"headline" => $this->headline,
				"big" => $this->hasBigHeader
			);

			die(json_encode($array));
		}
		else
		{
			// Gather the theme data
			$data = array(
				"user" => $this->CI->user,
				"title" => $this->title, 
				"content" => $content,
				"headline" => $this->headline,
				"hasBigHeader" => $this->hasBigHeader,
				"controller" => $this->CI->router->fetch_class()
			);
			
			$this->CI->load->model('category_model');
			$data['mainCategories'] = $this->CI->category_model->getMainCategories();
			
			// Load the main template
			$output = $this->CI->load->view("template", $data);
		}
	}

	/**
	 * Load a page template
	 * @param $page Filename
	 * @param $data Array of additional template data
	 * @return String/HTML
	 */
	public function loadPage($page, $data = '')
	{
		if(!$data)
		{
			$data = array();
		}
		
		$data['user'] = $this->CI->user;
		$data['login'] = $this->CI->load->view("login", array('user' => $this->CI->user, 'loginPage' => $this->loginRedirection), true);

		return $this->CI->load->view($page, $data, true);
	}
}