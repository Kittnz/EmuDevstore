<?php

class Forgot extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('email');
	}

	public function index()
	{
		$output = $this->template->loadPage("forgot");
		
		$this->template->setTitle("Recover account - ".$this->config->item('site-title'));
		$this->template->setHeadline("Have you forgot your details?");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}

	public function send()
	{
		$email = $this->input->post('email');

		if(!$email)
		{
			die("Please provide an email");
		}

		$user = $this->user_model->getUserByEmail($email);

		if(!$user)
		{
			die("Account does not exist. Please contact us for further assistance.");
		}
		else
		{
			$link = base_url().'forgot/requestPassword/'.$this->generateKey($email);
			$this->sendMail($email, "no-reply@raxezdev.com", 'FusionHub: reset your password', 'You have requested to reset your password, to complete the request please navigate to <a href="'.$link.'">'.$link.'</a>');
		
			die("A mail has been sent with further instructions");
		}
	}

	private function sendMail($receiver, $sender, $subject, $message)
	{
		$config['charset'] = 'utf-8';
		$config['wordwrap'] = TRUE;
		$config['mailtype'] = 'html';

		$this->email->initialize($config);

		$this->email->from($sender, $this->config->item('server_name'));
		$this->email->to($receiver); 

		$this->email->subject($subject);
		$this->email->message($message);	

		$this->email->send();
	}
	
	public function requestPassword($key = "")
	{
		if($key)
		{
			//Make sure a key is entered and make sure that it is the right key
			if(($key == $this->session->userdata('password_recovery_key')) && $this->session->userdata('password_recovery_key') != false)
			{
				//Reset password
				$username = $this->session->userdata('password_recovery_username'); //Username
				$newPassword = $this->generatePassword(); //New password
				
				//Hash password for the database
				$newPasswordHash = sha1($newPassword);
				
				//Change the password
				$this->user_model->changePassword($username, $newPasswordHash);
				
				//Send a mail with the new password
				$this->sendMail($username, "no-reply@raxezdev.com", 'FusionHub: your new password', 'Your new password is <b>'.$newPassword.'</b>');
			
				$this->session->unset_userdata('password_recovery_key');
				$this->session->unset_userdata('password_recovery_username');

				die("Your password has been changed! The new password has been sent to your email adress.");
			}
			else
			{
				die("Invalid key");
			}
		}
		else
		{
			die("Please enter a key");
		}
	}

	private function generateKey($username)
	{
		$key = sha1($username);
		$this->session->set_userdata('password_recovery_key', $key);
		$this->session->set_userdata('password_recovery_username', $username);
		
		return $key;
	}
	
	private function generatePassword()
	{
		return substr(sha1(time()), 0, 10);
	}
}