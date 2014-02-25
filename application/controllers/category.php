<?php

class Category extends CI_Controller
{	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('category_model');
	}

	public function show_subcats($category_id = null)
	{
		$category = $this->category_model->getCategoryById($category_id);
		
		if ( ! $category)
			show_error('Invalid category.');
        
		$subcats = $this->category_model->getSubcatsByParentId($category_id);
		
		// determine category url (show subcats or products list)
		foreach ($subcats as &$cat) 
		{
			if ($cat['has_childs'])
				$cat['url'] = $this->category_model->getUrl($cat, $category);
			else
				$cat['url'] = $this->category_model->getProductsUrl($cat, $category);
		}
		
		$back_url = false;
		if ($category['parent_id']) {
			$c = $this->category_model->getCategoryById($category['parent_id']);
			$back_url = $this->category_model->getUrl($c);
		}
		
		$output = $this->template->loadPage('subcats', array('parent_cat' => $category, 'subcats' => $subcats, 'back_url' => $back_url));
		$this->template->setTitle($category['title'].' Products - '.$this->config->item('site-title'));
		$this->template->setHeadline($category['title']." - Choose a category");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
	
	public function show_products($category_id = null)
	{
		$category = $this->category_model->getCategoryById($category_id);
		
		if ( ! $category)
			show_error('Invalid category.');
		
		if ($category['parent_id']) {
			$parent_cat = $this->category_model->getCategoryById($category['parent_id']);
			$headline = $parent_cat['title'].' '.$category['title'];
			
			$this->data['back_url'] = $this->category_model->getUrl($parent_cat, $category);
		}
		else {
			$headline = $category['title'];
		}
		
		$this->load->model('product_model');
		$this->data['products'] = $this->product_model->getProductsByCategoryId($category_id);
    	$output = $this->template->loadPage('productslist', $this->data);
		
		$this->template->setTitle($headline." Products - ".$this->config->item('site-title'));
		$this->template->setHeadline($headline);
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
}