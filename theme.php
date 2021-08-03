<?php

/**
 *
 * Miller Theme styling. 
 *
 */
 
 function insert_header($userstatus = false){ 
 	?>
 	
 	<html>
 		<head>
 			<title>Miller Points<?php echo pagetitle($userstatus); ?> </title>
 			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
 			<script src="js/scripts.js"></script>
 			
 			<link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:ital,wght@0,300;0,800;1,300;1,800&display=swap" rel="stylesheet">
 			<link rel="stylesheet" type="text/css" href="css/style.css" />
 			
 			<meta name="viewport" content="width=device-width, initial-scale=1.0">
 		</head> 
 		<body>
 		
 		<div id="allwrap">
 			<header>
 				
 				<img src="img/miller-logo.svg" />
 				<div id="titlebar">
 					<h1>Miller Points PPE Program</h1>
 				</div>
 				<?php if($userstatus){ echo '<img src="img/menu.svg" id="mobilemenubutton" />'; } ?>
 				<ul id="menubar">
					<?php  if($userstatus){ ?>
						<li><a href="index.php?p=order">Home</a></li>
						<li><a href="index.php?p=help">Help</a></li>
						<li><a href="index.php?p=account">Account</a></li>
						
						<?php if($_SESSION['TYPE'] != 'Employee' && $_SESSION['TYPE'] != 'Sizing'){ ?>
							<li><a href="index.php?p=admin">Admin</a></li>
						<?php }  ?>
						<li><a href="index.php?p=orderHistory">Order History</a></li>
						<li><a href="index.php?p=logout">Log Out</a></li>
					<?php }  ?>
 				</ul>
 			</header>
 			<div id="content">
 	<?php		
 	
 }
 
 function insert_footer(){
 	?> 
 				</div><!-- /content -->
 			
				<footer class="brownbg">
					<em>Created by MEP Brothers for Miller Environmental</em>
				</footer>
 			</div><!-- /allwrap -->
 		</body>
 	</html>
 	
 	<?php
 }
 
 ?>
 
 <?php
 
 // theme function
 
function pagetitle($userstatus = false){
 	if(!$userstatus){
 		return '';
 	}
 
 	if(!isset($_GET['p'])){
 		$check = '';
 	} else {
 		$check = $_GET['p'];
 		
 		if($_SESSION['TYPE'] == 'Employee'){
 		// this is an employee and we need to prevent access to admin
 			$check = ($check == 'admin' ? 'order' : $check);
 		} 	
 	}
 	
 	switch($check){
 		case 'orderResult':
 			$title = ' - Order Results';
 			break;
 		case 'help':
 			$title = ' - Help';
 			break;
 		case 'account':
 			$title = ' - Edit Account';
 			break;
 		case 'admin':
 			$title = ' - Administrator Access';
 			break;
 		case 'orderHistory':
 			$title = ' - Order History';
 			break;
 		case 'order':
 			$title = ' - Ordering';
 			break;
 		default:
 			$title = '';
 			break;
 	} 
 	return $title;
}
