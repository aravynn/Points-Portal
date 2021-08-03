<?php

/**
 *
 * Admin Class functions
 * Will manage: 
 * users
 * items
 * 
 *
 */
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
 
 class admin {
 	function __construct(){
		if(!isset($_SESSION)) session_start();
 	}
 	
 	private function CheckPrivilage(){
 		// check users privilage level. return a numerical value to determine 
 		// what level of control required to access function.
 		switch($_SESSION['TYPE']){
 			case 'ADMIN': 
 				return 3;
 			case 'Manager':
 			case 'Sizing':
 				return 2;
 			case 'Employee':
 				return 1;
 			default:
 				return 0;		
 		}
 	}
 	
 	public function AddUser($username, $password, $Points, $Email, $FirstName, $LastName){
 		$sql = new sqlControl();
 		if($this->CheckPrivilage() < 2){ 
 			$sql->logAction($_SESSION['USER'], "User attempted to create new user, but did not have permission.");
 			return -100; 
 		}
 		
 		// return results: 
 		// -100 bad access.
 		// 0 OK
 		// -1 bad username
 		// -2 bad password
 		// -3 invalid entries
 		
 		$UserType = 'Employee'; // always add employee, then adjust direct for managers and admin
 		
 		if($username == '' || $password == '' || $Points == ''){
 			// this is not a valid entry. 
 			$sql->logAction($_SESSION['USER'], "User attempted to create a new user, but had incomplete information. 
 			Username: " . $username . " 
 			Points: " . $Points . " 
 			Email: " . $Email . " 
 			Name: " . $FirstName . ' ' . $LastName);
 			return -3; // 
 		}
 		
 		
		$sql->sqlCommand('SELECT PK FROM Users WHERE User = :User', array(':User' => $username), false);
		
		$results = count($sql->returnAllResults()); // get the results for the user. 
 		
 		if($results > 0){
 			$sql->logAction($_SESSION['USER'], "User attempted to create a new user, but had chose an existing username. 
 			Username: " . $username . " 
 			Points: " . $Points . " 
 			Email: " . $Email . " 
 			Name: " . $FirstName . ' ' . $LastName);
 			return -1; // this username exists, get a new one! 
 		}
 		
 		
 		// add user to file.
 		$newUser = new user();
 		
 		if(count($newUser->CheckPasswordStrength($password)) > 0){
 		$sql->logAction($_SESSION['USER'], "User attempted to create a new user, but set an insecure password. 
 			Username: " . $username . " 
 			Points: " . $Points . " 
 			Email: " . $Email . " 
 			Name: " . $FirstName . ' ' . $LastName);
 			return -2;
 		}
 		if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
 			$sql->logAction($_SESSION['USER'], "User attempted to create a new user, but entered a non-valid email. 
 			Username: " . $username . " 
 			Points: " . $Points . " 
 			Email: " . $Email . " 
 			Name: " . $FirstName . ' ' . $LastName);
 			return -4;
 		}
 		
 		
 		$ID = $newUser->NewUser($username, $password, $Points, $UserType);
 		
 		// load user data from DB. 
 		$newUser->LoadUserByID($ID);
 		
 		// update the user information
 		$newUser->EditUser($Email, $FirstName, $LastName);
 		
 		$sql->logAction($_SESSION['USER'], "User Successfully created: 
 			Username: " . $username . " 
 			Points: " . $Points . " 
 			Email: " . $Email . " 
 			Name: " . $FirstName . ' ' . $LastName);
 		return 0;
 	}
 	
 	public function DeleteUserByID($id){
 		$sql = new sqlControl();
 		if($this->CheckPrivilage() < 3){ 
 		$sql->logAction($_SESSION['USER'], "Attempted to delete user, but did not have permission. 
 		ID: " . $id);
 		return false; }
 		
		$sql->sqlCommand('UPDATE Users SET Status = :Status WHERE PK = :PK', array(':Status' => 'Deleted', ':PK' => $id), false);
		
 		$sql->logAction($_SESSION['USER'], "User successfully deleted: 
 		ID: " . $id);
 		$sql = '';
 		return true;
 	}
 	
 	public function FreezeUserByID($id){
 		if($this->CheckPrivilage() < 2){ return false; }
 		// freeze user by ID
 		$sql = new sqlControl();
 		
 		$user = new user();
 		$user->loadUserByID($id);
 		
 		$freeze = ($user->Status == 'Frozen' ? true : false);
 		
		$sql->sqlCommand('UPDATE Users SET Status = :Status WHERE PK = :PK AND Status != \'Deleted\'', array(':Status' => ($freeze ? 'Active' : 'Frozen'), ':PK' => $id), false);
		
		$sql->logAction($_SESSION['USER'], "User successfully " . ($freeze ? "activated " : "frozen ") . "
 			ID: " . $id);
		
		$sql = '';
 		
 		return $freeze;
 		
 	}
 	
 	public function getUserIDs(){
 		if($this->CheckPrivilage() < 2){ return false; }
 		// gets all Employee Level users.
 		
 		//echo "working...";
 		
 		$sql = new sqlControl();
		$sql->sqlCommand('SELECT PK FROM Users WHERE UserType = \'Employee\' AND Status != \'Deleted\'', array(), false);
		
		$results = $sql->returnAllResults(); // get the results for the user. 
		
		$returns = array();
		
		foreach($results as $r){
			$returns[] = $r['PK'];
		}
		
		return $returns;
		
		$sql = '';
 	}
 	
 	public function AddItem($Name, $Image, $PartNumber, $PointValue, $RetailValue, $Options){
 		$sql = new sqlControl();
 		if($this->CheckPrivilage() < 3){ 
 			$sql->logAction($_SESSION['USER'], "User attempted to add item, but did not have permissions: 
			Name: " . $Name . "
			Image: " . $Image . " 
			PartNumber: " . $PartNumber . "
			PointValue: " . $PointValue . "
			RetailValue: " . $RetailValue);
 			return false; 
 		}
 		
 		
 		
 		
		$sql->sqlCommand('INSERT INTO Product (Name, Image, PartNumber, PointValue, RetailValue) VALUES (:Name, :Image, :PartNumber, :PointValue, :RetailValue)', 
					array(':Name'  => $Name,
						  ':Image'  => $Image,
						  ':PartNumber'  => $PartNumber,
						  ':PointValue'  => $PointValue,
						  ':RetailValue' => $RetailValue), false);
		
		$ID = $sql->lastInsert();
		
		foreach($Options as $key => $val){
			foreach($val as $v){
				// for each item, add to the variations table DB.
				$sql->sqlCommand('INSERT INTO ProductVariations (ItemPK, Parent, Name) VALUES (:ItemPK, :Parent, :Name)', 
					array(':ItemPK'  => $ID, ':Parent'  => $key, ':Name'  => $v), false);
			}
		}
		
		$sql->logAction($_SESSION['USER'], "User successfully added item: 
		Name: " . $Name . "
	  	Image: " . $Image . " 
	    PartNumber: " . $PartNumber . "
	    PointValue: " . $PointValue . "
	    RetailValue: " . $RetailValue);
 			
		
		$sql = '';
		return true;
 	}
 	
 	public function UpdateItemByID($id, $Name, $Image, $PartNumber, $PointValue, $RetailValue, $Options){
 		$sql = new sqlControl();
 		if($this->CheckPrivilage() < 3){ 
			$sql->logAction($_SESSION['USER'], "User attempted to update item, but did not have permissions: 
			Name: " . $Name . "
			Image: " . $Image . "
			PartNumber: " . $PartNumber . "
			PointValue: " . $PointValue . "
			RetailValue: " . $RetailValue . "
			PK: " . $id);
			return false; 
 		}
 		
		$sql->sqlCommand('UPDATE Product SET Name = :Name, Image = :Image, PartNumber = :PartNumber, PointValue = :PointValue, RetailValue = :RetailValue WHERE PK = :PK', 
					array(':Name'  => $Name,
						  ':Image'  => $Image,
						  ':PartNumber'  => $PartNumber,
						  ':PointValue'  => $PointValue,
						  ':RetailValue' => $RetailValue, 
						  ':PK' => $id), false);
		
		// we'll start these from scratch.
		
		$sql->sqlCommand('DELETE FROM ProductVariations WHERE ItemPK = :PK', array(':PK' => $id), false);
		
		foreach($Options as $key => $val){
			foreach($val as $v){
				// for each item, add to the variations table DB.
				$sql->sqlCommand('INSERT INTO ProductVariations (ItemPK, Parent, Name) VALUES (:ItemPK, :Parent, :Name)', 
					array(':ItemPK'  => $id, ':Parent'  => $key, ':Name'  => $v), false);
			}
		}				  
		$sql->logAction($_SESSION['USER'], "User updated item: 
		Name: " . $Name . "
		Image: " . $Image . "
		PartNumber: " . $PartNumber . "
		PointValue: " . $PointValue . "
		RetailValue: " . $RetailValue . "
		PK: " . $id);
						  
 		$sql = '';
 		return true;		
 	}
 	
 	public function deleteItemByID($id){
 		$sql = new sqlControl();
 		if($this->CheckPrivilage() < 3){ 
 		$sql->logAction($_SESSION['USER'], "User attempted to deleted item, but did not have permission: 
			ID: " . $id);
 		return false; }
 		
 		
		$sql->sqlCommand('UPDATE Product SET Status = \'Deleted\' WHERE PK = :PK', array(':PK' => $id), false);
 		
 		$sql->logAction($_SESSION['USER'], "User deleted item: 
			ID: " . $id);
 		
 		$sql = '';
 		return true;	
 	}
 	
 	public function getItemByID($id){
 		if($this->CheckPrivilage() < 1){ return false; }
 		
 		$sql = new sqlControl();
		$sql->sqlCommand('SELECT Name, Image, PartNumber, PointValue, RetailValue FROM Product WHERE PK = :PK AND Status != \'Deleted\'', array(':PK' => $id), false);
		
		$results = $sql->returnResults(); // get the results for the product. 
		
		$sql->sqlCommand('SELECT Parent, Name FROM ProductVariations WHERE ItemPK = :PK', array(':PK' => $id), false);
		
		$res2 = $sql->returnAllResults();
		
		foreach($res2 as $r){
			// add each to the DB. 
			$results['options'][$r['Parent']][] = $r['Name'];
		}
		
		return $results;
		
 	}
 	
 	public function getAllItemIDs(){
 		if($this->CheckPrivilage() < 1){ return false; }
 		
 		$sql = new sqlControl();
		$sql->sqlCommand('SELECT PK FROM Product WHERE Status != \'Deleted\'', array(), false);
		
		$results = $sql->returnAllResults(); // get the results for the user. 
		
		$returns = array();
		
		foreach($results as $r){
			$returns[] = $r['PK'];
		}
		
		return $returns;
 		
 	}
 	
 	public function sendEmail($emails, $subject, $message, $userID){
 		if($this->CheckPrivilage() < 1){ return false; }
 		
 		//global $mail;
		global $mailCFG;
		///////////////////////
		// NOTE: UPDATE THESE EMAIL ADDRESSES, ALSO, ADD CODE TO LOAD THE 
		// CURRENT USER'S EMAIL AS WELL TO SEND ALONG WITH THE REST!
		///////////////////////
		
		 $mail = new PHPMailer(true);
 
 //Server settings
			$mail->isSMTP();                                // Send using SMTP
			$mail->Host       = $mailCFG['mailHost'];       // Set the SMTP server to send through
			$mail->SMTPAuth   = true;                     	// Enable SMTP authentication
			$mail->Username   = $mailCFG['mailUsername'];   // SMTP username
			$mail->Password   = $mailCFG['mailPassword'];   // SMTP password
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;// Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
			$mail->Port       = $mailCFG['mailPort'];       // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
		
		$email = array();
		
		error_log("Emails set to: " . $emails);
		
		switch($emails){
			case 'Tech':
				// emails me, for site issues.
				$email['admin@mail.com'] = 'Kevin Jones';
				break;
			case 'Admin':
			case 'Order':
				// email the miller admin as well as jeff, minimalized for now.
				$email = array('admin@mail.com' => 'Kevin Jones');
				$sql = new sqlControl();
				$sql->sqlCommand("SELECT Email, FirstName, LastName FROM Users WHERE UserType = 'Manager'", array(), false);	
				$returnvars = $sql->returnAllResults();
				foreach($returnvars as $r){
					$email[$r['Email']] = $r['FirstName'] . ' ' . $r['LastName'];
				}
				$sql='';
				break;
		}
		
		
		try {
			
			//Recipients
			$mail->setFrom('info@mail.com', 'Points');
			
			$mails = '';
			foreach($email as $k => $a){			
				$mail->addAddress($k, $a);     // Add a recipient
				$mails .= $k . ', ';
				//error_log('Send to: ' . $k . ' ' . $e);
			}
			
			$user = new user();
 			$user->LoadUserByID($userID);
			
			if(isset($user->Email) && $user->Email != ''){
				$mail->addAddress($user->Email, $user->FirstName . ' ' . $user->LastName);
			}
			
			// Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = filter_var($subject, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$mail->Body    = $message;
			$mail->AltBody = filter_var($message, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

			$mail->send();
			//echo 'Message has been sent';
			$sql = new sqlControl();
			$sql->logAction($_SESSION['USER'], "User sent email:
			To: " . $mails . "
			Subject: " . $subject . "
			message: " . $message);
			$sql='';
			return true;
		} catch (Exception $e) {
			//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			$sql = new sqlControl();
			$sql->logAction($_SESSION['USER'], "User attempted to send email, but could not be sent. email:
			To: " . $mails . "
			Subject: " . $subject . "
			message: " . $message . " 
			error: " . $mail->ErrorInfo);
			$sql='';
			
			return false;
		}
 	}
 }