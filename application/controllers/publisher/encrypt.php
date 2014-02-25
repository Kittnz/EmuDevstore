<?php

class Encrypt extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('publisher_submit_model');

		if(!$this->user->isOnline())
		{
			die('<script type="text/javascript">alert(\'You need to be logged in\');window.location=\''.base_url().'\'</script>');
		}
	}
	
	public function index($id)
	{
		if(!$id)
		{
		redirect('publisher/submit');
		die();
		}
		
		$product_info = $this->publisher_submit_model->getProductDetailsByProcessId($id);
		
		if(!$product_info['process_id'])
		{
		die("Wrong process ID!");
		}
		
		$time = time();
		$time_check = $time - $product_info['time'];
		
		if($time_check > 2700) //Just 40 min for encrypting!
		{
		$this->publisher_submit_model->removeProductDetails($id);
		redirect('publisher/submit');
		die();
		}
	
		$output = $this->template->loadPage("publisher/encrypt", array('process_id' => $id));
	
		$this->template->setTitle("Submit a product - ".$this->config->item('site-title'));
		$this->template->setHeadline("Submit a product");
		$this->template->setBigHeader(false);
		$this->template->view($output);
	}
	
	public function process($id)
	{
		if(!$id)
		{
		redirect('publisher/submit');
		die();
		}
		
		$product_info = $this->publisher_submit_model->getProductDetailsByProcessId($id);
		
		if(!$product_info['process_id'])
		{
		redirect('publisher/submit');
		die("Wrong process ID!"); 
		}
		
		$time = time();
		$time_check = $time - $product_info['time'];
		
		if($time_check > 2700) //Just 40 min for encrypting!
		{
		$this->publisher_submit_model->removeProductDetails($id);
		die("The time has expired!"); 
		}
		
		$process_id = $id;	

		$errors = "";
		$password = $this->input->post('password');
		$accepted = $this->input->post('accept');
		
		if(empty($password))
		{
			$errors .= "You must be fill in everything!<br />";
		}
		
		if(!$accepted)
		{
			$errors .= "You must accept it!<br />";
		}
		
		if(strlen($password) > 255)
		{
			$errors .= "Your choicen password is too length!<br />";
		}
		
		if($errors == "")
		{
		
			$zip_dir = "/".$_SERVER['DOCUMENT_ROOT']."files/".$product_info['download'];
			$zip_size = filesize($zip_dir);
			
			$extract_dir = "/".$_SERVER['DOCUMENT_ROOT']."unpack_temp/";
			
			mkdir($extract_dir.$process_id); //create process folder
			
			//Unpack
			$zip = new ZipArchive;
			if ($zip->open($zip_dir) === TRUE)
			{
				$zip->extractTo($extract_dir.$process_id);
				$zip->close();
			} else {
				$errors .= "Something with ZIP extraction went wrong!<br />";
			}
		
			$unzip_folder_size = $this->folderSize($extract_dir.$process_id);
				if($unzip_folder_size < $zip_size)
				{
					$errors .= "The ZIP Archive can't have a password protection!<br />";
				}
			
			if($errors == "")
			{
				sleep(8);
				$this->publisher_submit_model->switchProductDetails($id);
				$this->publisher_submit_model->addPassword($product_info['download'], $password);
				$this->removeFolder($extract_dir.$process_id);
				
				$content = '<html>
							<body>
								<h1 style="color:#0099cc;font-weight:normal;font-size:16px;">This mail was sent from Emu-Devstore</h1>
								<b style="color:#0099cc;">Name:</b> '.$this->user->getName().'<br />
								<b style="color:#0099cc;">Product name:</b> '.$theme_name.'<br /><br />
								Product has been submitted. Approve it from the Emu-Devstore admin panel.
							</body>
						</html>';
			
				$this->mail($this->config->item('admin_mail'), "[Pending approval] ".$theme_name, $this->config->item('sender_mail'), $content);
				
				redirect('publisher/products');
			}
			else
			{
			$this->removeFolder($extract_dir.$process_id);
			$this->publisher_submit_model->removeProductDetails($id);
			}
		
		}
	
		$output = $this->template->loadPage("publisher/encrypt", array('errors' => $errors, 'process_id' => $id));
		
		$this->template->setTitle("Submit a product - ".$this->config->item('site-title'));
		$this->template->setHeadline("Submit a product");
		$this->template->setBigHeader(false);
		$this->template->view($output);
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
	
	private function folderSize($dir)
	{
	$size = 0;
    $contents = glob(rtrim($dir, '/').'/*', GLOB_NOSORT);

    foreach ($contents as $contents_value) {
        if (is_file($contents_value)) {
            $size += filesize($contents_value);
        } else {
            #$size += realFolderSize($contents_value);
        }
    }

    return $size;
	}
	
	private function RemoveFolder($dir)
	{
		if(!$dh = @opendir($dir)) return;
		while (false !== ($obj = readdir($dh))) {
			if($obj=='.' || $obj=='..') continue;
			if (!@unlink($dir.'/'.$obj)) $this->RemoveFolder($dir.'/'.$obj);
		}

		closedir($dh);
			@rmdir($dir);

	}
	

}