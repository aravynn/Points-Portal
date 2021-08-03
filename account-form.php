<?php

/**
 *
 * Order Form Page
 * load via INCLUDE at page load.
 *
 */
 
 if(count(get_included_files()) ==1){
 	 http_response_code(403);
 	 exit("ERROR 403: Direct access not permitted.");
}

require_once("config.php");

?>
<div id="account">
<h2> Account Information </h2>

<h3> Edit Profile </h3>

<?php 
if(isset($_GET['ae'])){
	if($_GET['ae'] == 'email'){
		echo '<em class="error">ERROR: Email is not correctly formatted, please enter a correct email. </em>';
	}
	if($_GET['ae'] == 'success'){
		echo '<em class="success"> Account successfully updated. </em>';
	}
}


loadAccountEdit(); ?>


<h3> Reset Password </h3> 

<?php
	if(isset($_GET['r'])){
		if($_GET['r'] == 'incorrect-password'){
			echo '<em class="error">ERROR: Please enter correct password </em>';	
		}
		if($_GET['r'] == 'password-strength'){
			echo '<em class="error">ERROR: Password is invalid, please ensure the password is at least 8 characters in length and includes: 
					<ul>
						<li> At least 1 lower case letter</li>
						<li> At least 1 upper case letter</li>
						<li> At least 1 number</li>
					</ul></em>';
		}
		if($_GET['r'] == 'success'){
			echo '<em class="success">Password has been successfully updated.</em>';
		}
	}
?>
<form method="post" action="authenticate.php">
	<label for="oldpassword">Old Password</label><input type="password" id="oldpassword" name="oldpassword" /><br />
	<label for="newpassword">New Password</label><input type="password" id="newpassword" name="newpassword" /><br />
	<input type="hidden" name="action" value="UpdatePassword" />
	<input type="submit" />
</form>
</div>
<?php

// form functions for orders page. 

function loadAccountEdit(){
	// load a user that can be edited. they can only update their name and email.
	// only uses active user to prevent spoofing issues.
	
	$user = new user();
	$user->LoadUserByName($_SESSION['USER']);
	
	$output = '<form method="post" action="authenticate.php">
		<label for="email">Email</label><input type="text" id="email" name="email" value="' . $user->Email . '" /><br />
		<label for="firstname">First Name</label><input type="text" id="firstname" name="firstname" value="' . $user->FirstName . '" /><br />
		<label for="lastname">Last Name</label><input type="text" id="lastname" name="lastname" value="' . $user->LastName . '" /><br />
		<input type="hidden" name="action" value="UpdateUser" />
		<input type="submit" />
	</form>';
	
	echo $output;

}
