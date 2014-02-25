<?php

class Buy extends CI_Controller
{
	public function index($id = false)
	{
		if(!$id)
		{
			die();
		}

		$this->load->model('product_model');
		$product = $this->product_model->getProduct($id);

		if(!$product)
		{
			die("Product not found");
		}

		if($product['is_unique'] && $product['downloads'] != 0)
		{
			die("You can't buy this product!");
		}

		$output = $this->template->loadPage("buy", array('product' => $product, 'paypal' => $this->config->item('paypal')));

		$this->template->setTitle($product['name']);
		$this->template->setHeadline($product['name']);
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}

	public function success()
	{
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

		$output = $this->template->loadPage("success", array('failed' => $failed));

		$this->template->setTitle("Thank you! - ".$this->config->item('site-title'));
		$this->template->setHeadline("Thanks for your payment");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
}