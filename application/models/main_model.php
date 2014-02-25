<?php

class Main_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		
		$this->updateDailyIncome();
	}

	public function getAllDailyIncome()
	{
		$query = $this->db->query("SELECT * FROM daily_income ORDER BY day ASC");

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

	public function getIncome($id)
	{
		$query = $this->db->query("SELECT price*downloads total FROM products WHERE id=?", array($id));

		if($query->num_rows())
		{
			$row = $query->result_array();

			return $row[0]['total'];
		}
		else
		{
			return false;
		}
	}

	private function updateDailyIncome()
	{
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM daily_income WHERE day=?", array(date("Y-m-d")));

		$row = $query->result_array();

		if(!$row[0]['total'])
		{
			$this->db->query("INSERT INTO daily_income(day, amount) VALUES(?, ?)", array(date("Y-m-d"), 0));
		}
	}
}