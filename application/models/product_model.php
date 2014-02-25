<?php
	
class Product_model extends CI_Model
{
	public function getUrl($product)
	{
		return 'Product/'.url_title($product['name']).'-'.$product['id'];
	}
	
	public function approve($id)
	{
		$this->db->query("UPDATE products SET validated=1 WHERE id=?", array($id));

		return true;
	}

	public function deny($id)
	{
		$this->db->query("UPDATE products SET validated=2 WHERE id=?", array($id));

		return true;
	}

	public function addDownload($id)
	{
		$this->db->query("UPDATE products SET downloads = downloads + 1 WHERE id=?", array($id));
	}
	
	public function getProduct($id, $validated = null)
	{
		$query = $this->db->query("SELECT p.*, c.website, c.real_name, c.customer_id, c.email FROM products p, customers c WHERE c.customer_id = p.author_id AND p.id=? ".($validated !== null ? 'AND p.validated = 1' : '')." LIMIT 1", array($id));

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

	public function getNotValidateProduct($id)
	{
		$query = $this->db->query("SELECT p.*, c.website, c.real_name, c.customer_id, c.email FROM products p, customers c WHERE c.customer_id = p.author_id AND p.id=? LIMIT 1", array($id));

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

	public function updateProduct($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update('products', $data);
	}

	public function getProductsByCategoryId($category_id)
	{
		$query = $this->db->query("
			SELECT 
				p.*, c.website, c.real_name, c.customer_id 
			FROM 
				products p
			LEFT JOIN
				customers c ON c.customer_id = p.author_id
			WHERE 
				p.validated = 1 
				AND p.category_id=? 
			ORDER BY p.downloads DESC
		", $category_id);
		
		return $query->result_array();
	}

	public function getProductsByUserId($id)
	{
		$query = $this->db->query("SELECT * FROM products WHERE author_id = ?", array($id));
		
		if($query->num_rows() > 0)
		{
			return $query->result_array();
		}
		else
		{
			return false;
		}
	}

	public function deleteProduct($id)
	{
		$query = $this->db->query("DELETE FROM products WHERE id = ?", array($id));
	}
	
	public function validate($validateId)
	{
		$query = $this->db->query("UPDATE products SET validated = 1 WHERE id = ?", array($validateId));
		if($query)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function getNotValidated()
	{
		$query = $this->db->query("SELECT * FROM customers c, products p WHERE c.customer_id = p.author_id AND p.validated = 0");

		if($query->num_rows())
		{
			$row = $query->result_array();

			return $row;
		}
		else
		{
			return false;
		}
	}

	public function getSales($id)
	{
		$query = $this->db->query("SELECT downloads FROM products WHERE id=?", array($id));

		if($query->num_rows())
		{
			$row = $query->result_array();

			return $row[0]['downloads'];
		}
		else
		{
			return false;
		}
	}
}