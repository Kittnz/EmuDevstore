<?php

class Create extends CI_Controller
{
	public function index()
	{
		$email = $this->input->post('email');
		$real_name = $this->input->post('real_name');
		$password = $this->input->post('password');

		if(empty($email) || empty($real_name) || empty($password))
		{
			die("Fields can't be empty");
		}	

		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			die("Invalid email address");
		}

		if($this->user_model->getUserByEmail($email) != false)
		{
			die("Email already exists");
		}

		$this->user_model->createUser($email, sha1($password), $real_name);
		
		$loginStatus = $this->user->logIn($email, $password);

		//die("The Account was created!");
		die("<script type='text/javascript'>window.location='createaccount/success';</script>");
	}
}