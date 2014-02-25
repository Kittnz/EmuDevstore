<?php

class Products extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		if(!$this->user->isOnline())
		{
			die('<script type="text/javascript">alert(\'You need to be logged in\');window.location=\''.base_url().'\'</script>');
		}
		
		$this->load->model('product_model');
	}
	
	public function index()
	{
		$this->load->helper('text');
		
		$products = $this->product_model->getProductsByUserId($this->user->getId());
		
		$output = $this->template->loadPage("publisher/products", array('products' => $products));
		
		$this->template->setTitle("My products - ".$this->config->item('site-title'));
		$this->template->setHeadline("Your products");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
	
	public function delete($id)
	{
		if($this->user_model->isPublisherOf($id))
		{
			$this->product_model->deleteProduct($id);

			redirect('publisher/products');
		}
	}

	public function edit($id)
	{
		if($this->user_model->isPublisherOf($id))
		{
			$product = $this->product_model->getProduct($id);

			$output = $this->template->loadPage("publisher/edit", array('product' => $product));
		
			$this->template->setTitle($product['name']);
			$this->template->setHeadline($product['name']);
			$this->template->setBigHeader(false);
			$this->template->view($output);
		}
	}

	public function save($id)
	{
		if($this->user_model->isPublisherOf($id))
		{
			$data = array(
				'name' => $this->input->post('product_name'),
				'description' => $this->input->post('product_description'),
				'price' => $this->input->post('product_price'),
				'paypal_email' => $this->input->post('paypal_email')
			);

			if(!is_numeric($data['price']))
			{
				die('Price has to be a number!');
			}

			if(!strlen($data['name']))
			{
				die('Name can\'t be empty!');
			}
			
			// files updated?
			if ($this->input->post('update_files'))
			{
				$this->load->library('filestorage');
				
				// generate new filenames & upload
				try {
					if (isset($_FILES['product_file']) && is_uploaded_file($_FILES['product_file']['tmp_name'])) {
						$data['download'] = $this->filestorage->getName($data['name'], 'zip', 'file');
						$this->filestorage->upload($_FILES['product_file'], $data['download'], 'zip');
					}
					
					if (isset($_FILES['product_screenshot']) && is_uploaded_file($_FILES['product_screenshot']['tmp_name'])) {
						$data['screenshot'] = $this->filestorage->getName($data['name'], 'jpg', 'screen');
						$this->filestorage->upload($_FILES['product_screenshot'], $data['screenshot'], array('jpg','jpeg'));
					}
					
					if (isset($_FILES['product_thumbnail']) && is_uploaded_file($_FILES['product_thumbnail']['tmp_name'])) {
						$data['thumbnail'] = $this->filestorage->getName($data['name'], 'jpg', 'thumb');
						$this->filestorage->upload($_FILES['product_thumbnail'], $data['thumbnail'], array('jpg','jpeg'));
					}
				}
				catch (FilestorageException $e)
				{
					var_dump($data);
					
					if ($e->getCode() == FilestorageException::ERR_FILETYPE)
						die('Please check the file types of your updated files.');
					else
						die("The file server is currently unavailable, please try again later.");
				}
			}

			$this->product_model->updateProduct($id, $data);

			redirect('publisher/products');
		}
	}
}