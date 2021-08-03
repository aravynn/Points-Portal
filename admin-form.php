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
<div id="admin">
<h2> Control Center </h2>

<h3>Add User</h3>
<?php
	if(isset($_GET['nu'])){
		if($_GET['nu'] == 'u'){
			echo '<em class="error">ERROR: Username exists, please select a different username. </em>';
		}
		if($_GET['nu'] == 'p'){
			echo '<em class="error">ERROR: Password is invalid, please ensure the password is at least 8 characters in length and includes: 
					<ul>
						<li> At least 1 lower case letter</li>
						<li> At least 1 upper case letter</li>
						<li> At least 1 number</li>
					</ul></em>';
		}
		if($_GET['nu'] == 'i'){
			echo '<em class="error">ERROR: Username or password is blank, please enter a valid input. </em>';
		}
		if($_GET['nu'] == 'e'){
			echo '<em class="error">ERROR: Email is not correctly formatted, please enter a correct email. </em>';
		}
		if($_GET['nu'] == 's'){
			echo '<em class="success">User successfully added.</em>';
		}
	}
	
	/*
	$retstr = '&user=' . filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)  
 			    . '&email=' . filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) 
 			    . '&first=' . filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) 
 			    . '&last=' . filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
 	*/
?>

<form method="post" action="authenticate.php">
	<label for="username">Username</label><input type="text" id="username" name="username" <?php echo (isset($_GET['user']) ? 'value="' . filter_var($_GET['user'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) . '"' : ""); ?> /><br />
	<label for="password">Password</label><input type="password" id="password" name="password" /><br />
	<label for="email">Email</label><input type="text" id="email" name="email" <?php echo (isset($_GET['email']) ? 'value="' . filter_var($_GET['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) . '"' : ""); ?> /><br />
	<label for="firstname">First Name</label><input type="text" id="firstname" name="firstname" <?php echo (isset($_GET['first']) ? 'value="' . filter_var($_GET['first'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) . '"' : ""); ?> /><br />
	<label for="lastname">Last Name</label><input type="text" id="lastname" name="lastname" <?php echo (isset($_GET['last']) ? 'value="' . filter_var($_GET['last'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) . '"' : ""); ?> /><br />
	<input type="hidden" name="action" value="AddNewUser" />
	<input type="submit" />
</form>

<h3> Users </h3>
<?php 
if(isset($_GET['r'])){
	if($_GET['r'] == 'deleted'){
		echo '<em class="success"> User successfully deleted </em>'; 
	}
	if($_GET['r'] == 'frozen'){
		echo '<em class="success"> User successfully frozen, and will not be able to place orders. </em>'; 
	}
	if($_GET['r'] == 'activated'){
		echo '<em class="success"> User successfully activated, and may place orders. </em>'; 
	}
	if($_GET['r'] == 'points'){
		echo '<em class="success"> User points have been successfully reset to ' . $_GET['v'] . ' points.</em>'; 
	}
}	

loadUserChart(); ?>
<?php if($_SESSION['TYPE'] == 'ADMIN'){ ?>
<h3>Add Products</h3>


<form method="post" action="authenticate.php">
	<label for="itemname">Product Name</label><input type="text" id="itemname" name="itemname" /><br />
	<label for="imagelink">Image Link</label><input type="text" id="imagelink" name="imagelink" /><br />
	<label for="partnumber">Part Number</label><input type="text" id="partnumber" name="partnumber" /><br />
	<label for="pointvalue">Point Value</label><input type="text" id="pointvalue" name="pointvalue" /><br />
	<label for="retail">Retail Value $</label><input type="text" id="retail" name="retail" /><br />
	<label for="option1">Option 1:</label><input type="text" id="option1" name="option1" placeholder="Title" />
										   <input type="text" id="option1value" class="optionvalue" name="option1value" placeholder="Option|Option|Option" /><br />
	<label for="option2">Option 2:</label><input type="text" id="option2" name="option2" placeholder="Title" />
										   <input type="text" id="option2value" class="optionvalue" name="option2value" placeholder="Option|Option|Option" /><br />
	<label for="option3">Option 3:</label><input type="text" id="option3" name="option3" placeholder="Title" />
										   <input type="text" id="option3value" class="optionvalue" name="option3value" placeholder="Option|Option|Option" /><br />
	<input type="hidden" name="action" value="AddProduct" />
	<input type="submit" />
</form>

<h3>Products</h3>

<?php  
	loadProductChart(); 
	if(isset($_POST['action']) && $_POST['action'] == 'UpdateProduct') editProduct($_POST['product']);
	}
?>
</div> <!-- /#admin -->
<?php 
	// functions for this form. 
	function loadUserChart(){
		$admin = new admin();

		$IDList = $admin->getUserIDs(); // get all user ids. 
		
		//var_dump($IDList);
		
		$output = '<div id="userchart">';

		foreach ($IDList as $i){
			// output 
			$user = new user();
			$user->LoadUserByID($i);
			
		/*	public $Username;
			public $Email;
			public $FirstName;
			public $LastName;
			public $Points; */
			
			// create a chart of all collected ID's
			
			
			$output .= '<div class="userchartline">
							<div> <strong>Username:</strong> ' . $user->Username . '</div>
							<div> <strong>Name:</strong> ' . $user->FirstName . ' ' . $user->LastName . '</div>
							<div> <strong>Status:</strong> ' . $user->Status . '</div>
							<div> <strong>Points:</strong> ' . $user->Points . '</div>
						
							<form method="post" action="authenticate.php">
								<label for="points">New Points</label>
								<input type="text" name="points" id="points" value="' . $user->Points . '" />
								<input type="hidden" name="action" value="ResetUserPoints" />
								<input type="hidden" name="user" value="' . $i . '" />
								<input type="submit" value="Reset Points" />
							</form>
							<br />
							<br />
							<label for="">User Control</label>
							<form method="post" action="authenticate.php">
								<input type="hidden" name="action" value="FreezeUser" />
								<input type="hidden" name="user" value="' . $i . '" />
								<input type="submit" value="' . ($user->Status == 'Active' ? 'Freeze' : 'Activate') . '" />
							</form>
						
							<form method="post" action="authenticate.php">
								<input type="hidden" name="action" value="DeleteUser" />
								<input type="hidden" name="user" value="' . $i . '" />
								<input type="submit" value="Delete" />
							</form>
						</div>';
				
			
			
		}
		
		$output .= "</div>";
		
		echo $output;
	}
	
	function loadProductChart(){
		// load list view of products, without dropdowns etc.
		$admin = new admin();
		
		$IDList = $admin->getAllItemIDs();
		
		$output = '<div id="productchart">';
		foreach ($IDList as $i){
			$product = $admin->getItemByID($i);
			

			$output .= '<div class="productchartline">
							<div class="img"><img style="width: 50px;" src="' . $product['Image'] . '" /></div>
							<div>' . $product['Name'] . '</div>
							<div>Part #: ' . $product['PartNumber'] . '</div>
							<div>' . $product['PointValue'] . ' Points</div>
							<div>$' . $product['RetailValue'] . '</div>
							<div class="productoptions">';
			
							if(isset($product['options'])){
								foreach($product['options'] as $key => $option){
									$output .='<label>' .  $key . '</label><select>';
									foreach($option as $o){
										$output .= '<option>' . $o . '</option>';
									}
									$output .= '</select>';
								}	
							}
				$output .= '</div>';
			
				// send to same page, but POST the id so we can load it to edit it. 
				$output .= '<form method="post" action="index.php?p=admin">
							<input type="hidden" name="action" value="UpdateProduct" />
							<input type="hidden" name="product" value="' . $i . '" />
							<input type="submit" value="Edit" /></form>';
			
				$output .= '<form method="post" action="authenticate.php">
							<input type="hidden" name="action" value="DeleteProduct" />
							<input type="hidden" name="product" value="' . $i . '" />
							<input type="submit" value="Delete" /></form>';
			$output .= '</div>';
						
		}
		$output .= "</div>";
		
		echo $output;
	}
	
	function editProduct($id){
	
		$admin = new admin();
	
		$product = $admin->getItemByID($id);
	
	
		
		$output = '<h3>Edit Product</h3>
					<form method="post" action="authenticate.php">
					<label for="itemname">Product Name</label><input type="text" id="itemname" name="itemname" value="' . $product['Name'] . '" /><br />
					<label for="imagelink">Image Link</label><input type="text" id="imagelink" name="imagelink" value="' . $product['Image'] . '" /><br />
					<label for="partnumber">Part Number</label><input type="text" id="partnumber" name="partnumber" value="' . $product['PartNumber'] . '" /><br />
					<label for="pointvalue">Point Value</label><input type="text" id="pointvalue" name="pointvalue" value="' . $product['PointValue'] . '" /><br />
					<label for="retail">Retail Value $</label><input type="text" id="retail" name="retail" value="' . $product['RetailValue'] . '" /><br />';
				
		$i = 1;
		$option = array();
		if(isset($product['options'])){
			foreach($product['options'] as $k => $o){
				$output .=	'<label for="option'. $i .'">Option '. $i .':</label>
							<input type="text" id="option'. $i .'" name="option'. $i .'" placeholder="Title" value="' . $k . '" />
							<input type="text" id="option'. $i .'value" class="optionvalue" name="option'. $i .'value" placeholder="Option|Option|Option" value="' . implode("|", $o) . '" /><br />';
				$i++;
			}
		}
		
		
		if($i < 4){
			for(;$i < 4; $i++){
			$output .=	'<label for="option'. $i .'">Option '. $i .':</label>
						<input type="text" id="option'. $i .'" name="option'. $i .'" placeholder="Title" />
						<input type="text" id="option'. $i .'value" class="optionvalue" name="option'. $i .'value" placeholder="Option|Option|Option" /><br />';
			 
			}
		}
					
		$output .= '<input type="hidden" name="action" value="EditProduct" />
					<input type="hidden" name="product" value="' . $id . '" />
					<input type="submit" />
					</form>';
			
		echo $output;
	
	}
?>