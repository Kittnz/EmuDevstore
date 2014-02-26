<?php

class contact extends CI_Controller
{
	public function index()
	{
		if($this->user->isOnline())
		{
			$customer = array(
				"email" => $this->user->getEmail(),
				"real_name" => $this->user->getName()
			);
		}
		else
		{
			$customer = false;
		}

		$output = $this->template->loadPage("contact", array('customer' => $customer));

		$this->template->setTitle("Contact us - ".$this->config->item('site-title'));
		$this->template->setHeadline("Contact us: <span style='color:lime;'>".$this->config->item('contact-email')."</span>");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}

	public function send()
	{
		$name = $this->input->post('name');
		$email = $this->input->post('email');
		$subject = $this->input->post('subject');
		$message = $this->input->post('message');

		// Validation
		if(strlen($name) == 0)
		{
			die("Name can't be blank");
		}

		if(strlen($email) == 0
		|| !filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			die("Email can't be blank and has to be a valid email");
		}

		if(strlen($subject) == 0)
		{
			die("Subject can't be blank");
		}

		if(strlen($message) == 0)
		{
			die("Message can't be blank");
		}

		$content = '<html>
						<body>
							<b style="color:#0099cc;">Name:</b> '.$name.'<br />
							<b style="color:#0099cc;">Email:</b> '.$email.'<br /><br />
							'.nl2br(htmlentities($message)).'
						</body>
					</html>';
		
		if($this->mail($this->config->item('contact-email'), "[".$this->config->item('site-title')."] ".$subject, $email, $content))
		{
			die("1");
		}
	}

	private function mail($to, $subject, $from, $message)
	{
		$headers = "From: <" .$from. ">\r\n" .
					'Reply-To: ' .$from. "\r\n" .
					"Content-type: text/html\r\n"
					.'X-Mailer: PHP/' . phpversion();

		if(mail($to, $subject, $message, $headers))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}