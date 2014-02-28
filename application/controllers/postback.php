<?php

class Postback extends CI_Controller 
{
	// User values
	private $userId;
	private $productId;
	private $payment_status;
	private $payment_amount;
	private $payment_currency;
	private $txn_id;
	private $receiver_email;
	private $payer_email;

	// Config values
	private $config_paypal;

	// Debug
	private $debug = false;
 
	/**
	 * Initialize and prevent direct access
	 */
	public function __construct()
	{
		parent::__construct();

		$this->config_paypal = $this->config->item('paypal');
		// Prevent direct access
		if(count($_POST) == 0)
		{
			if($this->debug)
			{
				$_POST['custom'] = "10-5";
				$_POST['payment_status'] = "Completed";
				$_POST['mc_gross'] = 10.0; // I'm very generous
				$_POST['mc_currency'] = "USD";
				$_POST['txn_id'] = sha1(uniqid());
				$_POST['receiver_email'] = "paypal@domain.com";
				$_POST['payer_email'] = "test@gmail.com";
			}
			else
			{
				die("No access");
			}
		}
	}
	
	/**
	 * Process the request
	 */
	public function index()
	{
		// Read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		
		// Create our request string
		foreach($_POST as $key => $value)
		{
			$req .= "&$key=".urlencode(stripslashes($value));
		}
		
		// Connect to the PayPal servers
		$fp = fsockopen('ssl://www.'.(($this->config_paypal['sandbox']) ? 'sandbox.': '').'paypal.com', 443, $errno, $errstr, 5);

		// Stop here if we can't connect
		if(!$fp)
		{
			die("Can't connect to PayPal");
		}
		else
		{
			// Gather the values we need
			$data = explode("-", $this->input->post('custom'));

			$this->userId = $data[0];
			$this->productId = $data[1];
			$this->payment_status = $this->input->post('payment_status');
			$this->payment_amount = $this->input->post('mc_gross');
			$this->payment_currency = $this->input->post('mc_currency');
			$this->txn_id = $this->input->post('txn_id');
			$this->receiver_email = $this->input->post('receiver_email');
			$this->payer_email = $this->input->post('payer_email');

			// Make sure the currency is correct
			if($this->payment_currency != $this->config_paypal['donation_currency'])
			{
				$error = "Invalid currency (set to ".$this->payment_currency.")";
			}

			$this->load->model('product_model');
			$product = $this->product_model->getProduct($this->productId);

			$receiver = ($product['paypal_email']) ? $product['paypal_email'] : $this->config_paypal['receiver'];

			// Make sure the receiver email is correct
			if($this->receiver_email != $receiver)
			{
				$error = "Invalid receiver email (set to ".$this->receiver_email.")";
			}

			// Make sure the payment has not already been processed
			if($this->transactionExists($this->txn_id))
			{
				$error = "Payment has already been processed";
			}

			// Make sure payment status is completed
			if($this->payment_status != "Completed")
			{
				$error = "Payment status is not completed (".$this->payment_status.")";
			}

			if(!$product)
			{
				$error = "Product does not exist (".$this->productId.")";
			}
			elseif($product['price'] > $this->payment_amount)
			{
				$error = "User paid too little (USD ".$this->payment_amount.")";
			}

			if($this->user->hasProduct($this->productId, $this->userId))
			{
				$error = "Has already bought this product";
			}

			// Gather our database log datas
			$data = array(
				"payment_status" => $this->payment_status,
				"payment_amount" => $this->payment_amount,
				"payment_currency" => $this->payment_currency,
				"txn_id" => $this->txn_id,
				"receiver_email" => $this->receiver_email,
				"payer_email" => $this->payer_email,
				"user_id" => $this->userId,
				"product_id" => $this->productId,
				"validated" => "0",
				"timestamp" => time(),
				"error" => (isset($error)) ? $error : "",
				"pending_reason" => $this->input->post('pending_reason')
			);

			$this->db->insert("paypal_logs", $data);

			if(isset($error))
			{
				die($error);
			}
			else
			{
				if($this->debug)
				{
					$this->processVerified();
				}

				// Define our request headers
				$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
				$header .= "Host: www.".(($this->config_paypal['sandbox']) ? 'sandbox.': '')."paypal.com\r\n";
				$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

				// Send our validation request to PayPal
				fputs($fp, $header.$req, strlen($header.$req));

				$out = "";

				// Loop through the response
				while(!feof($fp))
				{
					$res = fgets($fp, 1024);
					$out .= $res;

					if(strcmp($res, "VERIFIED") == 0) 
					{*/
						$this->processVerified();
				}
					elseif(strcmp($res, "INVALID") == 0)
					{
						$this->processInvalid();
					}
				}

				$this->processFailed($header, $req, $out);
				fclose($fp);
			}
		}
	}

	/**
	 * Check if a transaction exists
	 * @param String $txn_id
	 * @return Boolean
	 */
	private function transactionExists($txn_id)
	{
		$query = $this->db->query("SELECT COUNT(*) as `total` FROM paypal_logs WHERE txn_id=? AND payment_status = 'Completed'", array($txn_id));

		if($query->num_rows() > 0)
		{
			$row = $query->result_array();

			if($row[0]['total'] > 0)
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
			return false;
		}
	}

	/**
	 * The payment was successful: give the user his donation points
	 */
	private function processVerified()
	{
		if($this->productId == 8 && $this->config_paypal['launch'])
		{
			// Give access to item
			$order_data = array(
				"customer_id" => $this->userId,
				"product_id" => 3
			);

			$this->db->insert("orders", $order_data);

			// Give access to item
			$order_data = array(
				"customer_id" => $this->userId,
				"product_id" => 1
			);

			$this->db->insert("orders", $order_data);
		}
		else
		{
			// Give access to item
			$order_data = array(
				"customer_id" => $this->userId,
				"product_id" => $this->productId
			);

			$this->db->insert("orders", $order_data);
		}

		// Update the transaction log and set validated to 1
		$data = array("validated" => "1");
		$this->db->where('txn_id', $this->txn_id);
		$this->db->update('paypal_logs', $data);

		$this->db->query("UPDATE products SET downloads = downloads + 1, validated = IF(is_unique=1, 3, 1) WHERE id=?", array($this->productId));

		$this->updateDailyIncome(); 
	}

	private function updateDailyIncome()
	{
		if($this->receiver_email == $this->config_paypal['receiver'])
		{
			$query = $this->db->query("SELECT COUNT(*) AS `total` FROM daily_income WHERE day=?", array(date("Y-m-d")));

			$row = $query->result_array();

			if($row[0]['total'])
			{
				$this->db->query("UPDATE daily_income SET amount = amount + ".floor($this->payment_amount)." WHERE day=?", array(date("Y-m-d")));
			}
			else
			{
				$this->db->query("INSERT INTO daily_income(day, amount) VALUES(?, ?)", array(date("Y-m-d"), floor($this->payment_amount)));
			}
		}

		die("Success");
	}

	/**
	 * The payment was invalid: log the error for manual investigation
	 */
	private function processInvalid()
	{
		$data = array("error" => "PayPal validation failed: invalid transaction");

		$this->db->where('txn_id', $this->txn_id);
		$this->db->update('paypal_logs', $data); 

		die("PayPal validation failed: invalid transaction");
	}

	private function processFailed($header, $req, $response)
	{
		$f = fopen("paypal_failed_log.txt", "w");
		fwrite($f, $header."\n\n\n");
		fwrite($f, $req."\n\n\n");
		fwrite($f, $response."\n\n\n");
		fwrite($f, print_r($_POST, true));
		fclose($f);

		die($header."\n\n\n".$req."\n\n\n".print_r($_POST, true)."\n\n\n".$response);
	}
}
