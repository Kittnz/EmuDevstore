<?php

class Userproducts extends CI_Controller
{
	public function index($id = false)
	{
		if ( ! $id)
			die("Something went wrong :O");
		
		$this->load->model('product_model');
		$products = $this->product_model->getProductsByUserId($id);
		$userData = $this->user_model->getUserById($id);
		
		$products = ($products) ? $products : array();

		$output = $this->template->loadPage("productslist", array('products' => $products));
        
		$this->template->setTitle("User products - ".$this->config->item('site-title'));
		$this->template->setHeadline("Products by ".$userData['real_name']);
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
}