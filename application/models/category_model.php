<?php
	
class Category_model extends CI_Model
{
	public function getCategoryById($id)
	{
		return $this->db->select('*')
			->from('categories')
			->where('id', $id)
			->get()
			->row_array();
	}
	
	public function categoryHasChilds($id)
	{
		return $this->db->query('SELECT COUNT(*) count FROM '.$this->db->dbprefix('categories').' WHERE parent_id = ?', $id)
			->row()
			->count > 0;
	}
	
	public function getAllCategories()
	{
		return $this->db->query('SELECT * FROM categories ORDER BY parent_id, id')->result_array();
	}
	
	public function getMainCategories()
	{
		return $this->db->query('SELECT * FROM '.$this->db->dbprefix('categories').' WHERE has_childs >0')->result_array();
	}
	
	public function getSubcatsByParentId($parent_id, $count = true)
	{
		if ($count) {
			$count = '
				,
				(
					SELECT COUNT(*) 
					FROM products p
					WHERE 
						(
							p.category_id = c.id
							OR p.category_id IN (SELECT id FROM categories WHERE parent_id = c.id)
						) 
						AND p.validated = 1
				) AS num_products
			';
		}
		else {
			$count = '';
		}
		
		return $this->db->query('
			SELECT 
				c.*,
				IF(
					(SELECT COUNT(*) FROM '.$this->db->dbprefix('categories').' c1 WHERE c1.parent_id = c.id) > 0,
					1, 
					0
				) AS has_childs
				'.$count.'
			FROM '.$this->db->dbprefix('categories').' c
			WHERE c.parent_id = '.$this->db->escape($parent_id).'
			')
			->result_array();
	}
	
	public function getUrl($category, $parent = null)
	{
		return ($parent ? url_title($parent['title']).'/' : '').url_title($category['title']).'-'.$category['id'];
	}
	
	public function getProductsUrl($category, $parent = null)
	{
		return ($parent ? url_title($parent['title']).'/' : '').url_title($category['title']).'/Products-'.$category['id'];
	}
}