<?php

class Submit extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('publisher_submit_model');

		ini_set('upload_max_filesize', '50M');  
		ini_set('post_max_size', '50M');  
		ini_set('max_input_time', 300);  
		ini_set('max_execution_time', 300); 

		if(!$this->user->isOnline())
		{
			die('<script type="text/javascript">alert(\'You need to be logged in\');window.location=\''.base_url().'\'</script>');
		}
	}
	
	private function _getSubCats(&$cats, $parent_id, $parent_title, $level = 0)
	{
		if ($level)
			$parent_title = str_repeat('&nbsp;', $level * 4).$parent_title;
		$cats[$parent_title] = array();
		
		$subcats_raw = $this->category_model->getSubCatsByParentId($parent_id, false);
		
		foreach ($subcats_raw as $cat) 
		{	
			if ($cat['has_childs']) {
				$this->_getSubCats($cats, $cat['id'], $cat['title'], $level + 1);
			}
			else
			{
				if ($level)
					$cat['title'] = str_repeat('&nbsp;', $level * 4).$cat['title'];
				
				$cats[$parent_title][$cat['id']] = $cat['title'];
			}
		}
	}
	
	public function index()
	{
		$this->load->model('category_model');
		
		$categories = array();
		$cats_raw = $this->category_model->getMainCategories();
		
		foreach ($cats_raw as $cat) {
			if ($cat['has_childs']) {
				$this->_getSubCats($categories, $cat['id'], $cat['title']);
			}
			else {
				$categories[$cat['id']] = $cat['title'];
			}
		}
		
		$this->load->helper('form');
		
		$output = $this->template->loadPage("publisher/submit", array('categories' => $categories));

		$this->template->setTitle("Submit a product - ".$this->config->item('site-title'));
		$this->template->setHeadline("Submit a product");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
	
    /**
     * Handles the AJAX form submit / file upload.
     * Responds with a JSON object.
     */
	public function upload()
	{
		$errors = "";
		$theme_name = $this->input->post('product_name');
		$theme_file = array_key_exists('product_file', $_FILES) ? $_FILES['product_file'] : false;
		$theme_description = $this->input->post('product_description');
		$theme_price = ($this->input->post('product_price')) ? $this->input->post('product_price') : 0;
		$theme_screenshot = array_key_exists('product_screenshot', $_FILES) ? $_FILES['product_screenshot'] : null;
		$theme_thumbnail = array_key_exists('product_thumbnail', $_FILES) ? $_FILES['product_thumbnail'] : null;
		$category_id = $this->input->post('product_category_id');
		$is_unique = $this->input->post('is_unique');
		
		// Make sure no fields were left empty
		if(empty($theme_name)
		|| empty($theme_file)
		|| empty($theme_description)
		|| empty($theme_screenshot)
		|| empty($theme_thumbnail)
		|| empty($category_id))
		{
			$errors .= "Please make sure that everything has been filled in.<br />";
		}
		else
		{
			if(!$this->input->post('accept'))
			{
				$errors .= "You must agree to our Guidelines!<br />";
			}

			if(!is_numeric($theme_price))
			{
				$errors .= "Price is not a number.<br />";
			}

			if($this->input->post('paypal_email') == NULL && $theme_price != 0)
			{
				$errors .= "Please enter your PayPal email.<br />";
			}

			if($is_unique && $theme_price == 0)
			{
				$errors .= "Please enter a <b>Unique Price</b> for your product, because unique products shouldn't be free!<br /><br />";
			}
			
			// upload files
			if ( ! $errors)
			{
				// create filenames
				$this->load->library('filestorage');
			
				$filenames = array(
					'file' => $this->filestorage->getName($theme_name, 'zip', 'file')
				);
			
				if ($theme_screenshot && is_uploaded_file($theme_screenshot['tmp_name']))
					$filenames['screen'] = $this->filestorage->getName($theme_name, 'jpg', 'screen');
					
				if ($theme_thumbnail && is_uploaded_file($theme_thumbnail['tmp_name']))
					$filenames['thumb'] = $this->filestorage->getName($theme_name, 'jpg', 'thumb');
			
				try {
					$this->filestorage->upload($theme_file, $filenames['file'], 'zip');
					
					if (isset($filenames['screen']))
						$this->filestorage->upload($theme_screenshot, $filenames['screen'], array('jpg','jpeg'));
					
					if (isset($filenames['thumb']))
						$this->filestorage->upload($theme_thumbnail, $filenames['thumb'], array('jpg','jpeg'));
				}
				catch (FilestorageException $e)
				{
					if ($e->getCode() == FilestorageException::ERR_FILETYPE)
						$errors .= 'Please check the filetypes of your uploaded files.';
					else
						$errors .= "The file server is currently unavailable, please try again later<br />";
				}
			}
		}
		
		// if everything went well, continue
		if( ! $errors)
		{
			$this->publisher_submit_model->submitTheme($theme_name, $filenames['file'], $theme_description, $filenames['thumb'], $theme_price, $this->user->getId(), $category_id, $filenames['screen'], $this->input->post('paypal_email'), $is_unique);
			
			$content = '<html>
						<body>
							<h1 style="color:#0099cc;font-weight:normal;font-size:16px;">This mail was sent from Emu-Devstore</h1>
							<b style="color:#0099cc;">Name:</b> '.$this->user->getName().'<br />
							<b style="color:#0099cc;">Product name:</b> '.$theme_name.'<br /><br />
							Product has been submitted. Approve it from the Emu-Devstore admin panel.
						</body>
					</html>';
		
			$this->mail($this->config->item('admin_mail'), "[Pending approval] ".$theme_name, $this->config->item('sender_mail'), $content);

		    die(json_encode(array('status' => 'success', 'redirect' => site_url('publisher/products'))));
		}
		else
		{
            die(json_encode(array('status' => 'error', 'errors' => $errors)));
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