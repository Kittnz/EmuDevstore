<?php

class Publisher_modules_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getModules($userId)
	{
		//Type = 1 since its a theme, 0 is a module.
		$query = $this->db->query("SELECT * FROM products, customers WHERE products.author_id = customers.customer_id AND author_id = ? AND type = 0", array($userId));
		if($query->num_rows() > 0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	
	public function isMyModule($userId, $moduleId)
	{
		$query = $this->db->query("SELECT * FROM products WHERE author_id = ? AND id = ? AND type = 0", array($userId, $moduleId));
		if($query->num_rows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function deleteModule($moduleId)
	{
		$query = $this->db->query("DELETE FROM products WHERE id = ?", array($moduleId));
	}
}