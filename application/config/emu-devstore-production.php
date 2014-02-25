<?php

// PayPal settings
$config['paypal'] = array(
"sandbox" => false,
"receiver" => "paypal@domain.com",
"postback" => "http://localhost/postback",
"return" => "http://localhost/buy/success",
"donation_currency" => "USD",
"launch" => true
);

//Global
$config['site-title'] = "Your site title";
$config['contact-email'] = "mail@example.com";

// CDN
$config['ftp_hostname'] = "ftp";
$config['ftp_username'] = "username";
$config['ftp_password'] = "password";
$config['sender_mail'] = "no-reply@domain.com";
$config['admin_mail'] = "admin@domain.com";

?>