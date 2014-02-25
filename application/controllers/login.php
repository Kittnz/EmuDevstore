<?php

class Login extends CI_Controller
{
	public function index()
	{
		$loginStatus = $this->user->logIn($this->input->post('username'), $this->input->post('password'));

		die((string)$loginStatus);
	}
}