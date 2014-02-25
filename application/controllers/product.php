<?php

class Product extends CI_Controller
{
	public function show($product_id = null)
	{
		$this->load->model('product_model');
		$this->load->model('category_model');
		
		$product = $this->product_model->getProduct($product_id);
		
		if ( ! $product)
			show_error('Product not found');
		
		// do not show product if not validated / declined, except if user is publisher of it or is an admin
		if ( $product['validated'] != 1
			&& ! ($this->user->isPublisherOf($product['id']) || $this->user->isAdmin()) ) 
		{
			if ($this->input->get('is_ajax'))
				die("<script>document.location='".base_url()."';</script>");
			else
				redirect('/');
		}
		
		$category = $this->category_model->getCategoryById($product['category_id']);
		
		if ($this->category_model->categoryHasChilds($category['id']))
			$back_url = $this->category_model->getUrl($category);
		else
			$back_url = $this->category_model->getProductsUrl($category);
		
		if ($product['is_unique'] && $product['downloads'] != 0 && !$this->user->hasProduct($product_id))
			$product['sold_out'] = true;
		else
			$product['sold_out'] = false;
		
		$output = $this->template->loadPage("productdetail", array('product' => $product, 'back_url' => $back_url));
		
		$this->template->setTitle($product['name']);
		$this->template->setHeadline($product['name']);
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
}