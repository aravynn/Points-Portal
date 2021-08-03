<?php

/**
 * 
 * Upload functionality for miller, to quickly add users. 
 * no UI, will DIE(); at any chance of other use. 
 *
 */

require_once("config.php");

die("See Administrator for Details");

$inserts = array(
			array("first","last","password"),
			);
			
foreach($inserts as $i){
	// insert each user.
	$u = new user();
	
	$ID = $u->NewUser($i[0] . substr($i[1], 0, 1), $i[2], 4500, 'Employee');
 		
 	// load user data from DB. 
 	$u->LoadUserByID($ID);
 		
 	// update the user information
 	$u->EditUser('', $i[0], $i[1]);
 	
 	echo 'Added user: ' . $i[0] . ' ' . $i[1] . '<br />';
}

?>
