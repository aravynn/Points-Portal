<?php

/** 
 *
 * POST functionality will be accessed here, then returned back to the main page. 
 *
 */
 
 if(empty($_POST)){
 	http_response_code(405);
 	exit("ERROR 405: Method Not Found");
 }
 
 require_once("config.php");
 
if($_POST['action'] != 'login'){
	checkUser();
 }
 
 switch($_POST['action']){
 	case 'login':
 		// login attempt goes here. 
 		
 		$user = new user();
 		$res = $user->authenticateUser($_POST['username'], $_POST['password']);
 		
 		if($res == 0){
 			// redirect back to the correct page. 
 			header('location:https://www.mepbrothers.com/points/index.php?p=order');
 		} elseif($res == -1) {
 			header('location:https://www.mepbrothers.com/points/index.php?r=error');
 		} else {
 			header('location:https://www.mepbrothers.com/points/index.php?r=error-too-many');
 		}
 		break;
 	case 'AddNewUser': // create new user via admin
 		$admin = new admin();
 		
 		$retstr = '&user=' . filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)  
 			    . '&email=' . filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) 
 			    . '&first=' . filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) 
 			    . '&last=' . filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
 		
 		switch($admin->AddUser($_POST['username'], $_POST['password'], BASE_POINTS, $_POST['email'], $_POST['firstname'], $_POST['lastname'])){
 			case 0: 
 				header('location:https://www.mepbrothers.com/points/index.php?p=admin&nu=s');
 				break;
 			case -1: // -1 bad username
 				header('location:https://www.mepbrothers.com/points/index.php?p=admin&nu=u' . $retstr);
 				break;
 			case -2: // -2 bad password
 				header('location:https://www.mepbrothers.com/points/index.php?p=admin&nu=p' . $retstr);
 				break;
 			case -3: // -3 invalid entries
 				header('location:https://www.mepbrothers.com/points/index.php?p=admin&nu=i' . $retstr);
 				break;
 			case -4: // -4 bad email
 				header('location:https://www.mepbrothers.com/points/index.php?p=admin&nu=e' . $retstr);
 				break;
 			default: 
 				break;
 		}
 		
 		$admin = '';		
 		break;
 	case 'DeleteUser':
 		$admin = new admin();
 		$admin->DeleteUserByID($_POST['user']);
 		$admin = '';
 		header('location:https://www.mepbrothers.com/points/index.php?p=admin&r=deleted');		
 		break;
 	case 'ResetUserPoints':
 		$user = new user();
 		$user->loadUserByID($_POST['user']);
 		$user->UpdatePoints($_POST['points']);
 		header('location:https://www.mepbrothers.com/points/index.php?p=admin&r=points&v=' . filter_var($_POST['points'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) );
 		break;
 	case 'FreezeUser':
 		$admin = new admin();
 		$freeze = $admin->FreezeUserByID($_POST['user']);
 		$admin = '';
 		// true activate false freeze
 		header('location:https://www.mepbrothers.com/points/index.php?p=admin&r=' . ($freeze ? 'activated' : 'frozen'));		
 		break;
 	case 'AddProduct':
 		$admin = new admin();
 		
 		$options = array();
 		if(isset($_POST['option1']) && $_POST['option1'] != ''){
 			$options[$_POST['option1']] = explode("|", $_POST['option1value']);
 		}
 		if(isset($_POST['option2']) && $_POST['option2'] != ''){
 			$options[$_POST['option2']] = explode("|", $_POST['option2value']);
 		}
 		if(isset($_POST['option3']) && $_POST['option3'] != ''){
 			$options[$_POST['option3']] = explode("|", $_POST['option3value']);
 		}
 		$admin->AddItem($_POST['itemname'], $_POST['imagelink'], $_POST['partnumber'], $_POST['pointvalue'], $_POST['retail'], $options);
 		header('location:https://www.mepbrothers.com/points/index.php?p=admin');	
 		break;
 	case 'DeleteProduct':
 		$admin = new admin();
 		
 		$admin->deleteItemByID($_POST['product']);
 		
 		header('location:https://www.mepbrothers.com/points/index.php?p=admin');
 		break;
 	case 'EditProduct':
 		$admin = new admin();
 		
 		$options = array();
 		if(isset($_POST['option1']) && $_POST['option1'] != ''){
 			$options[$_POST['option1']] = explode("|", $_POST['option1value']);
 		}
 		if(isset($_POST['option2']) && $_POST['option2'] != ''){
 			$options[$_POST['option2']] = explode("|", $_POST['option2value']);
 		}
 		if(isset($_POST['option3']) && $_POST['option3'] != ''){
 			$options[$_POST['option3']] = explode("|", $_POST['option3value']);
 		}
 		
 		$admin->UpdateItemByID($_POST['product'], $_POST['itemname'], $_POST['imagelink'], $_POST['partnumber'], $_POST['pointvalue'], $_POST['retail'], $options);
 		
 		header('location:https://www.mepbrothers.com/points/index.php?p=admin');
 		break;
 	case 'PlaceOrder':
 		if(isset($_POST['orderuser']) && $_POST['orderuser'] != ''){
 			placeOrder($_POST['orderuser']); // place order for another user.
 		} else {
 			placeOrder(); // place a standard order.
 		}
 		break;
 	case 'UpdateUser':
 		$user = new user();
 		$user->LoadUserByName($_SESSION['USER']);
 		
 		if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
 			// only error case for this. email is wrong.
 			header('location:https://www.mepbrothers.com/points/index.php?p=account&ae=email');
 			break;
 		}
 		
 		$user->EditUser($_POST['email'], $_POST['firstname'], $_POST['lastname']);
 		
 		header('location:https://www.mepbrothers.com/points/index.php?p=account&ae=success');
 		break;
 	case 'UpdatePassword':
 		$user = new user();
 		$user->LoadUserByName($_SESSION['USER']);
 		
 		if(count($user->CheckPasswordStrength($_POST['newpassword'])) > 0){
 			error_log("pass strength");
 			header('location:https://www.mepbrothers.com/points/index.php?p=account&r=password-strength');
 			break;
 		}
 			
 		
 		if($user->ResetPassword($_POST['oldpassword'], $_POST['newpassword'])){
 			header('location:https://www.mepbrothers.com/points/index.php?p=account');
 		} else {
 			header('location:https://www.mepbrothers.com/points/index.php?p=account&r=incorrect-password');
 		}
 		break;
 	case 'SendHelpEmail':
 		$admin = new admin();
 		
 		$user = new user();
 		$user->LoadUserByName($_SESSION['USER']);
 		
 		//filter_var($orderID, FILTER_SANITIZE_FULL_SPECIAL_CHARS)
 		
 		
 		
 		
 		$emails = filter_var($_POST['messagetype'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		
		if($_POST['messagetype'] == 'Admin'){
			$subject = 'Admin Help: ' . filter_var($_POST['subject'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		} else {
			$subject = 'Technical Help: ' . filter_var($_POST['subject'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		}
		
		$message = 'Email From: ' . $user->FirstName . ' ' . $user->LastName . '(' . $user->Username . ')' . '<br />' . filter_var($_POST['message'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
 		
 		$results = $admin->sendEmail($emails, $subject, $message, $user->UserID);
 		
 		if($results){
 			header('location:https://www.mepbrothers.com/points/index.php?p=help&r=success');
 		} else {
 			header('location:https://www.mepbrothers.com/points/index.php?p=help&r=failure');
 		}
 		break;
 	default:
 		break;
 }
 
 
 function checkUser(){
 	// check for persistent user allowability. 
 	$user = new user();
 	if(!$user->CheckLoggedIn()){
 		// Not logged in. Get OUT!
 		header('location:https://www.mepbrothers.com/points/index.php');
 		die();
 		
 	} 
 }
 
 function placeOrder($userid){
 	// full place order script, it is more complicated so we'll need more space. 
 	
 	$admin = new admin();	
	$IDList = $admin->getAllItemIDs();	
	$user = new user();
	
	if($userid == -1){
		$user->LoadUserByName($_SESSION['USER']);
 	} else {
 		$user->LoadUserByID($userid);
 	}
 	
 	if($user->Status == 'Frozen'){
		header('location:https://www.mepbrothers.com/points/index.php?p=order&r=frozen');
 		return false;
 	}
 	
 	$points = 0;
 	$lines = array();
 	$userID = $user->UserID;
 	
 	$failstring = ''; // for IDs and quantities if the order failed to process.
 	
 	$messagelines = '';
 	
 	$numberfail = false;
 	
 	foreach ($IDList as $i){
		$product = $admin->getItemByID($i);	
		// we need the options as well as the ID for each product to determine what was selected, as well as the point value of each. 
		
		
		if($_POST['QTY' . $i] > 0){
			// use this line, there is product added. 
			
			$varia = '';
			if($user->UserType != "Sizing"){
				$failstring .= '&Q' . $i . '=' . $_POST['QTY' . $i]; // in case we need an abort.
		
				
				if(isset($product['options'])){
					foreach($product['options'] as $k => $o){
						$varia .= $_POST[$k . $i] . ', ';
			
						$failstring .= '&' . $k . $i . '=' . $_POST[$k . $i]; // in case we need an abort.
					}
				}
				$varia = rtrim($varia, ", ");
			}
			///////////////////////////////////////////////////////////////////////////////
			// code for checking if discount.
			
			if($user->UserType == "Sizing"){
				// this is a sizing account, and we'll need to get the information special for "varia"
				
				$varia = '';
				foreach($product['options'] as $k => $o){
					// for each option under each product, check for checked values and send them back a list. 
					
					if($varia != ''){
						$varia .= " | ";
					}
					$varia .= implode(", ", $_POST[$k . $i]);
					
					//error_log("LOG2: " . $varia);
					
				}
				
			} elseif($product['PartNumber'] == 'DAKOTA15'){
				// this is a retail value of an item. We'll set a point value here, then output the new point value. 
				$points += round(floatval($varia) * 0.85 * 10 * $_POST['QTY' . $i]);
				$varia = "$" . $varia . " Voucher";
			} elseif($product['PartNumber'] == 'CSAFOOTWEAR10'){
				// this is a retail value of an item. We'll set a point value here, then output the new point value. 
				$points += round(floatval($varia) * 0.90 * 10 * $_POST['QTY' . $i]);
				$varia = "$" . $varia . " Voucher";
			}
			
			
			
			
			///////////////////////////////////////////////////////////////////////////////
			
			$lines[] = array(
							'ItemPK' => $i,
							'Quantity' => $_POST['QTY' . $i],
							'Variation' => $varia);
		
			
			$messagelines .= '#' . filter_var($i, FILTER_SANITIZE_FULL_SPECIAL_CHARS) . ' ' . filter_var($product['Name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) . '(' . filter_var($varia, FILTER_SANITIZE_FULL_SPECIAL_CHARS) . ') QTY: ' . filter_var($_POST['QTY' . $i], FILTER_SANITIZE_FULL_SPECIAL_CHARS) . ' PTS: ' . filter_var(round($points / $_POST['QTY' . $i]), FILTER_SANITIZE_FULL_SPECIAL_CHARS) . '<br />';
		
			$points += $product['PointValue'] * $_POST['QTY' . $i];
		
		} 
		
		if(!is_numeric($_POST['QTY' . $i])){
			$numberfail = true;
			
			$varia = '';
			if(isset($product['options'])){
				foreach($product['options'] as $k => $o){
					$varia .= $_POST[$k . $i] . ', ';
			
					$failstring .= '&' . $k . $i . '=' . $_POST[$k . $i]; // in case we need an abort.
				}
			}
			$varia = rtrim($varia, ",");
		}
	}	
	
	if($userid != -1){
		$failstring .= '&uid=' . $userid;
	}
	
	//error_log("Used: " . $points . ' user: ' . $user->Points);
	
	if($numberfail){
		header('location:https://www.mepbrothers.com/points/index.php?p=order&r=validation' . $failstring);
 		return false;
 	}
	
	/* We're disabling the overage for points, as per Jeff/Dan request.
 	if($points > $user->Points && $user->UserType != "Sizing"){
 		// we gotta problem, kick them back cause they ordered too much.
 		header('location:https://www.mepbrothers.com/points/index.php?p=order&r=points' . $failstring);
 		return false;
 		
 	}
	*/
	
	
	// we can place the order. 
	$order = new order();	
	$orderID = $order->insertOrder($points, $lines, $userID);
	$user->UpdatePoints($user->Points - $points);
	
	// send an email. 
	$admin = new admin();
	
	$message = 'Order id: ' . filter_var($orderID, FILTER_SANITIZE_FULL_SPECIAL_CHARS) . ' Total Points: ' . filter_var($points, FILTER_SANITIZE_FULL_SPECIAL_CHARS) . ' User ID: ' . filter_var($userID, FILTER_SANITIZE_FULL_SPECIAL_CHARS) . ' ' . filter_var($user->FirstName, FILTER_SANITIZE_FULL_SPECIAL_CHARS) . ' ' . filter_var($user->LastName, FILTER_SANITIZE_FULL_SPECIAL_CHARS) . '<br />' . $messagelines;
	
	$admin->sendEmail('Order', 'points Points: Order placed by ' . filter_var($user->FirstName, FILTER_SANITIZE_FULL_SPECIAL_CHARS) . ' ' . filter_var($user->LastName, FILTER_SANITIZE_FULL_SPECIAL_CHARS), $message, $userID);
	
	if($userid > -1){
		$retuser = '&u=' . $userid;
	} else {
		$retuser = '';
	}
	
	
	header('location:https://www.mepbrothers.com/points/index.php?p=orderResult' . $retuser);
	
 }
 	
 ?>