<?php

// Production config
if(!file_exists("is-development"))
{
	require("application/config/emu-devstore-production.php");
}

// Development config
else
{
	// PayPal settings
	$config['paypal'] = array(
		"sandbox" => false,
		"receiver" => "payment@codelicity.com",
		"postback" => "http://fusion.raxezdev.com/postback",
		"return" => "http://fusion.raxezdev.com/buy/success",
		"donation_currency" => "USD",
		"launch" => true
	);

	//Global
	$config['site-title'] = "Teeest";
	$config['contact-email'] = "mail@example.com";

	// CDN
	$config['ftp_hostname'] = "cdn.raxezdev.com";
	$config['ftp_username'] = "USERNAME";
	$config['ftp_password'] = "PASSWORD";
	$config['sender_mail'] = "no-reply@raxezdev.com";
	$config['admin_mail'] = "raxezdev@gmail.com";
}