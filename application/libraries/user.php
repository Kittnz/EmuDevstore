<?php

class User
{
	private $CI;

	private $id;
	private $email;
	private $name;
	private $online;
	private $rank;
	private $products = null;
	private $website;
	private $password;

	public function __construct()
	{
		$this->CI = &get_instance();

		$this->CI->load->model('user_model');

		if($this->CI->session->userdata('id'))
		{
			$this->getUserData();
		}
		else
		{
			$this->id = false;
			$this->email = false;
			$this->name = false;
			$this->rank = 0;
			$this->website = false;
			$this->password = false;
			$this->products = array();
			$this->online = false;
		}
	}

	/**
	 * Set the user details
	 */
	private function getUserData()
	{
		$id = $this->CI->session->userdata('id');

		$account = $this->CI->user_model->getUserById($id);

		$this->id = $account['customer_id'];
		$this->email = $account['email'];
		$this->name = $account['real_name'];
		$this->rank = $account['rank'];
		$this->website = $account['website'];
		$this->password = $account['password_sha1'];
		$this->online = true;
	}

	/**
	 * Log in using provided information
	 * @param String $email
	 * @param String $password
	 * @return Int
	 */
	public function logIn($username, $password)
	{
		// Check for account
		if(filter_var($username, FILTER_VALIDATE_EMAIL))
		{
			$account = $this->CI->user_model->getUserByEmail($username);
		}
		elseif(is_numeric($username))
		{
			$account = $this->CI->user_model->getUserById($username);
		}
		else
		{
			$account = false;
		}

		// Check for results
		if(!$account)
		{
			return 3;
		}
		elseif(sha1($password) != $account['password_sha1'])
		{
			return 2;
		}
		else
		{
			$data = array(
				"id" => $account['customer_id']
			);

			$this->CI->session->set_userdata($data);

			return 1;
		}
	}

	/**
	 * Whether or not the user is online
	 * @return Boolean
	 */
	public function isOnline()
	{
		return $this->online;
	}

	/**
	 * Whether or not the user is a publisher
	 * @return Boolean
	 */
	public function isPublisher()
	{
		if($this->rank >= 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function isMarketManager()
	{
		if($this->rank >= 2)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Whether or not the user is an administrator
	 * @return Boolean
	 */
	public function isAdmin()
	{
		if($this->rank >= 3)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get id
	 * @return Int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get email
	 * @return String
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Get name
	 * @return String
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get rank
	 * @return Int
	 */
	public function getRank()
	{
		return $this->rank;
	}

	/**
	 * Get website
	 * @return String
	 */
	public function getWebsite()
	{
		return $this->website;
	}

	/**
	 * Get products
	 * @return Array
	 */
	public function getProducts()
	{
		// lazy loading
		if ($this->products === null)
			$this->products = $this->CI->user_model->getProducts($this->getId());
		
		return $this->products;
	}

	/**
	 * Get password (hashed)
	 * @return String
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Check if the current user has bought a specific product
	 * @param Int $id
	 * @param Int $userId
	 * @return Boolean
	 */
	public function hasProduct($id, $userId = false)
	{
		if($userId)
		{
			if($this->CI->user_model->getOrderCount($userId, $id))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			if($this->isOnline() && count($this->getProducts()))
			{
				foreach($this->products as $product)
				{
					if($id == $product['product_id'])
					{
						return true;
					}
				}
			}

			return false;
		}
	}
	
	/**
	 * Checks if the current user is the publisher of 
	 * the given product.
	 * 
	 * @param int $id
	 * @return bool
	 */
	public function isPublisherOf($id)
	{
		$query = $this->CI->db->query("SELECT * FROM products WHERE author_id = ? AND id = ?", array($this->getId(), $id));

		if($query->num_rows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function requireOnline($page = false)
	{
		if(!$this->isOnline())
		{
			die('<script type="text/javascript">alert(\'Please log in to proceed\');window.location=\''.base_url().'home/'.$page.'\'</script>');
		}
	}
}