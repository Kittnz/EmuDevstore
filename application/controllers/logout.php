<?php

class Logout extends CI_Controller
{
	public function index()
	{
		$this->session->sess_destroy();

		die('<script type="text/javascript">window.location = \''.base_url().'\'</script>');
	}
}