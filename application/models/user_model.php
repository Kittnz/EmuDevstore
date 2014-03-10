<?php

class User_model extends CI_Model
{
	
	public function getUserById($id)
	{
		$query = $this->db->query("SELECT * FROM customers WHERE customer_id=? LIMIT 1", array($id));

		if($query->num_rows())
		{
			$row = $query->result_array();

			return $row[0];
		}
		else
		{
			return false;
		}
	}

	public function getUserByEmail($email)
	{
		$query = $this->db->query("SELECT * FROM customers WHERE email=? LIMIT 1", array($email));

		if($query->num_rows())
		{
			$row = $query->result_array();

			return $row[0];
		}
		else
		{
			return false;
		}
	}

	public function getProducts($id)
	{
		return $this->db->query("
			SELECT 
				c.customer_id author_id, c.real_name, c.website, p.price, p.id, p.name, p.thumbnail, p.is_unique, p.downloads, o.customer_id, o.product_id
			FROM 
				orders o, customers c, products p
			WHERE 
				c.customer_id = p.author_id AND p.id = o.product_id AND o.customer_id=?", array($id))
			->result_array();
	}

	public function saveSettings($id, $data)
	{
		$this->db->where('customer_id', $id);
		$this->db->update('customers', $data);
	}

	public function createUser($email, $password, $real_name)
	{
		$data = array(
			'email' => $email,
			'password_sha1' => $password,
			'real_name' => $real_name,
			'rank' => $this->config->item('register_rank')
		);

		$this->db->insert("customers", $data);
	}

	public function getOrderCount($userId, $id)
	{
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM orders WHERE customer_id=? AND product_id=?", array($userId, $id));
		$row = $query->result_array();

		return $row[0]['total'];
	}

	public function changePassword($email, $password)
	{
		$this->db->where('email', $email);
		$this->db->update("customers", array('password_sha1' => $password));
	}
	
	/**
	 * Use User::isPublisherOf() instead
	 *
	 * @deprecated
	 */
	public function isPublisherOf($id)
	{
		$query = $this->db->query("SELECT * FROM products WHERE author_id = ? AND id = ?", array($this->user->getId(), $id));

		if($query->num_rows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}