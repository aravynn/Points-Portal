<?php

/**
 * configuration data. 
 */ 
 
  // PHP Mailer
require_once("PHPMailer/PHPMailer/PHPMailer.php");
require_once("PHPMailer/PHPMailer/Exception.php");
require_once("PHPMailer/PHPMailer/SMTP.php");

// above root, the config for mail.
require_once("../../config.php");
 
 

 
 define("BASE_POINTS", 4500);
 
 require_once("class-sql.php");
 require_once("class-user.php");
 require_once("class-admin.php");
 require_once("class-order.php");

 require_once("theme.php");
 



 ?>