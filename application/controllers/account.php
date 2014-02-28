<?php

class Account extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->user->requireOnline();
	}

	public function downloads()
	{
		$this->load->model('product_model');
		$products = $this->user->getProducts();

		$failed_query = $this->db->query("SELECT COUNT(*) `total` FROM paypal_logs WHERE user_id=? AND validated=0", array($this->user->getId()));

		$row = $failed_query->result_array();

		if($row[0]['total'])
		{
			$failed = true;
		}
		else
		{
			$failed = false;
		}

		$output = $this->template->loadPage("productslist", array('products' => $products, 'failed' => $failed, 'back_url' => false));
		
		$this->template->setTitle("Downloads - ".$this->config->item('site-title'));
		$this->template->setHeadline("Your purchased digital products");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}

	
	public function download($id = false)
	{
		$this->load->model('product_model');
		
		$product = $this->product_model->getNotValidateProduct($id); //I know, it's a little bit ugly coded, but important for pending download
										  //17-08-2013 and it's important for the unique system

		if(!$product)
		{
			die("Invalid product");
		}

		if($product['price'] == 0)
		{
			$this->product_model->addDownload($id);
		}

		if($this->user->hasProduct($id) || $product['price'] == 0 || $this->user->getRank() >= 2)
		{
			//$basedir = $_SERVER['DOCUMENT_ROOT']."/files";
			//$filename = sprintf("%s/%s", $basedir, $product['download']);
			//header("Content-Type: application/zip");
			//$save_as_name = basename($product['name']).".zip";
			//header("Content-Length: ".filesize("/".$filename));  
			//header("Content-Disposition: attachment; filename=\"$save_as_name\"");
			//readfile("/".$filename);
			header("Location: ".cdn_url().$product['download']); // let's use this for the moment untill we fix that way
		}
		else
		{
			die("You don't own this product");
		}
	}

	public function settings()
	{
		$output = $this->template->loadPage("settings");
		
		$this->template->setTitle("Account settings - ".$this->config->item('site-title'));
		$this->template->setHeadline("Update your account details");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}

	public function saveSettings()
	{
		$data['real_name'] = $this->input->post('real_name');
		$data['email'] = $this->input->post('email');
		$data['website'] = $this->input->post('website');

		if(strlen($data['email']) == 0
		|| !filter_var($data['email'], FILTER_VALIDATE_EMAIL))
		{
			die("Email can't be blank and has to be a valid email");
		}

		$this->user_model->saveSettings($this->user->getId(), $data);

		die('Account details have been updated!');
	}

	public function changePassword()
	{
		$old_password = $this->input->post('old_password');
		$data['password_sha1'] = sha1($this->input->post('new_password'));

		if(sha1($old_password) != $this->user->getPassword())
		{
			die("Old password was invalid");
		}
		else
		{
			$this->user_model->saveSettings($this->user->getId(), $data);

			die('Password has beeen changed!');
		}
	}

}
