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
<div id="order">
<h2>Order Form</h2>
<?php
	if(isset($_GET['r'])){
	 	if($_GET['r'] == 'points'){
			echo '<em class="error">ERROR: Selected point value exceeds current point balance, please reduce your selection.</em>';
		}
		if($_GET['r'] == 'validation'){
			echo '<em class="error">ERROR: Quantities must be numerical.</em>';
		}
		if($_GET['r'] == 'frozen'){
			echo '<em class="error">ERROR: Account frozen.</em>';
		}
	}	
	
	$u = new user();
	$u->LoadUserByName($_SESSION['USER']);
	$points = $u->Points;
	if($u->UserType == 'Sizing'){
		echo '<p class="pointsrem brownbg">Please make your selection below by checking off all variations required, then click "Place Order".</p>';
	}elseif($u->UserType == 'Manager' || $u->UserType == 'ADMIN'){
		echo '<p class="pointsrem brownbg">Hello ' . $u->FirstName . ', Please make your selection below by entering the quantity of each item desired as well as variations if required, then click "Place Order". <br /><br /><span id="uchose"></span> has <span id="upoints"></span> points remaining.</p>';
	} else {
		echo '<p class="pointsrem brownbg">Hello ' . $u->FirstName . ', You have ' . $points . ' points remaining. Please make your selection below by entering the quantity of each item desired as well as variations if required, then click "Place Order".</p>';
	}
	//$u = '';
	
?>
<form method="post" action="authenticate.php">
<?php 
	if($u->UserType == 'Manager' || $u->UserType == 'ADMIN'){ 
		loadUserPointArray();
?>

<div id="user-select">
	<label for="orderuser">Choose Employee</label>
	<select name="orderuser" id="orderuser">
		<option value="<?php echo $u->UserID; ?>"><?php echo $u->FirstName . ' ' . $u->LastName; ?></option>
		<?php loadUsers(); ?>
	</select>
</div>
<?php } ?>

<div id="order-products">
<?php loadProductView(); ?>
</div>
<input type="hidden" name="action" value="PlaceOrder" />
	<div class="order-tally">

<?php if($u->UserType != 'Sizing'){ ?>
		<div id="userpoints">
			<span class="label">Starting Points</span>
			<span class="totalpoints"><?php echo $points ?></span>
		</div>
		<div id="tallypoints">
			<span class="label">Points Total</span>
			<span class="ordertallypoints">0</span>
		</div>
		<div id="remainingpoints">
			<span class="label">Points Left</span>
			<span class="userremainingpoints"><?php echo $points ?></span>
		</div>
<?php } ?>
		<input type="submit" value="Place Order" />
	</div>
</form>

</div> <!-- /#order -->
<?php

// functions for this form

function loadProductView(){
	
	global $u;
	
	$admin = new admin();
	$IDList = $admin->getAllItemIDs();
	
	$output ='';	
	$count = 0;
	foreach ($IDList as $i){
		$product = $admin->getItemByID($i);	
		
		$output .= '<div class="itemline">';
		
		// image area
		$output .= '<div class="img"><img src="' . $product['Image'] . '" /></div>';
		
		// text area
		$output .= '<div class="order-line-content">
						<p class="title">' . $product['Name'] . '</p>
						<p class="detail">
							<span id="itempoints' . $count . '" class="points">' . $product['PointValue'] . ' Points</span>
							<span class="retail">Retail: $' . $product['RetailValue'] . '</span>
							<span class="partnumber">' . $product['PartNumber'] . '</span> 
						</p>
						<div class="order-line-dropdowns">';
		
		if(isset($product['options'])){
				
				foreach($product['options'] as $key => $option){
					//echo $key . ' ' . $option . '<br />';
					// check here if the KEY is Value and the OPTION is INPUT
					if($u->UserType == 'Sizing'){
						$output .= '<p><strong>' . $key . '</strong></p>';
						foreach($option as $o){
							$output .= '<span class="checkbox"><input type="checkbox" name="' . $key . $i . '[]" id="' . $key . $i . $o . '" value="' . $o . '" /><label for="' . $key . $i . $o . '">' . $o . '</label></span>';
						}
					} else {
						if($key == "Value" && $option[0] == "INPUT"){
							// this is an input instead.
							$output .= '<label for="' . $key . $i . '">Enter Retail Value:</label><input type="text" class="percentboot" name="' . $key . $i . '" />';
						} else {
							// instead, this is a normal item.
					
							$output .= '<label for="' . $key . $i . '">' . $key . '</label><select name="' . $key . $i . '">';
							foreach($option as $o){
								$output .= '<option value="' . $o . '"';
						
								if(isset($_GET[$key . $i]) && $_GET[$key . $i] == $o){
									// this is selected.
									$output .= ' selected="selected" ';
								}
						
								$output .= '>' . $o . '</option>';
							}
							$output .= '</select>';
						}
					}
				
				}	
			}					
		$output .= '</div></div>';
		
		
		// quantity area
		$output .= '<div class="order-line-quantity">
					<label for="QTY' . $i . '">Quantity</label>
					<input type="textarea" name="QTY' . $i . '" class="orderquantity orderqty' . $count . '" id="QTY' . $i . '" value="' . (isset($_GET['Q' . $i]) ? $_GET['Q' . $i] : 0) . '" />
					</div></div>';
		$count++;
	}
		
	echo $output;
}

function loadUsers(){
	// load options for all users. 
	
	
	$sql = new sqlControl(); // create the SQL object.
	$sql->sqlCommand("SELECT PK, FirstName, LastName FROM Users WHERE (UserType = :usertypea OR UserType = :usertypeb) AND Status = :status", array(":usertypea" => 'Employee', ":usertypeb" => 'Manager', ":status" => 'Active'), false);	
	
	$returnvars = $sql->returnAllResults();
	$output = '';
	foreach($returnvars as $ret){
		// output each 
		$s = '';
		
		if(isset($_GET['uid']) && $_GET['uid'] != ''){
			$s = ' selected="selected"';
		}
		
		$output .= '<option value="' . $ret['PK'] . '" ' . $s . '>' . $ret['FirstName'] . ' ' . $ret['LastName'] . '</option>';
	}
	
	echo $output;
	
}

function loadUserPointArray(){

	global $u;
	
	$sql = new sqlControl(); // create the SQL object.
	$sql->sqlCommand("SELECT PK, FirstName, LastName, PointsRemaining FROM Users WHERE (UserType = :usertypea OR UserType = :usertypeb) AND Status = :status", array(":usertypea" => 'Employee', ":usertypeb" => 'Manager', ":status" => 'Active'), false);	
	
	$returnvars = $sql->returnAllResults();
	$output = '<script type="text/javascript">
	var userPoints = new Array(); 
	';
	
	$output .= 'userPoints[' . $u->UserID . '] = new Array("' .  $u->FirstName . ' ' . $u->LastName . '", ' . $u->Points . '); 
	';
	
	$i = 0;
	foreach($returnvars as $ret){
		// output each 
		$output .= 'userPoints[' . $ret['PK'] . '] = new Array("' .  $ret['FirstName'] . ' ' . $ret['LastName'] . '", ' . $ret['PointsRemaining'] . '); 
	';
		$i++;
	}
	$output .= "</script>";
	echo $output;
}
