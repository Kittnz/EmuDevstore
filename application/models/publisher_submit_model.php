<?php

class Publisher_submit_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function submitTheme($themeName, $url, $description, $thumbnail, $price, $author_id, $category_id, $screenshot, $email, $is_unique)
	{
		$this->db->query("INSERT INTO products(author_id, name, price, thumbnail, screenshot, download, category_id, description, paypal_email, is_unique) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($author_id, $themeName, $price, $thumbnail, $screenshot, $url, $category_id, $description, $email, $is_unique));
	}
}