<?php

class Publisher_themes_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getThemes($userId)
	{
		//Type = 1 since its a theme, 0 is a module.
		$query = $this->db->query("SELECT * FROM products, customers WHERE products.author_id = customers.customer_id AND author_id = ? AND type = 1", array($userId));
		if($query->num_rows() > 0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}
	
	public function isMyTheme($userId, $moduleId)
	{
		$query = $this->db->query("SELECT * FROM products WHERE author_id = ? AND id = ? AND type = 1", array($userId, $moduleId));
		if($query->num_rows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function deleteTheme($themeId)
	{
		$query = $this->db->query("DELETE FROM products WHERE id = ?", array($themeId));
	}
}