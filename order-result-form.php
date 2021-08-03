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

?>
<div id="orderresult">
<?php loadLastOrder(); ?>
</div>

<?php

function loadLastOrder(){
	// load the last order entered. Since this page is a one-off, 
	// it will always show the last order entered by the user. 
	
	$user = new user();
	
	if(isset($_GET['u']) && $_SESSION['TYPE'] != 'Employee'){
		$user->LoadUserByID($_GET['u']);
	} else {
		$user->LoadUserByName($_SESSION['USER']);
	}
	$order = new order();
	// get the proper user ID that was last ordered for managers etc. 
	// How can we choose that by user? should we POST or GET?
	
	$order->getLastOrder($user->UserID);
	
	$admin = new admin();
	
	// now that we have order information, we can return the values for output. 
	
	$output = '<div class="resline">
					<h2 class="result_name">Order Complete!</h2>
					<p class="userFullName"><strong>Order For:</strong> ' . $user->FirstName . ' ' . $user->LastName  . '</p>
					<p class="pointsused"><strong>Points Used:</strong> ' . $order->Points . '</p>
					<p class="datetime"><strong>Date:</strong> ' . $order->Date . '</p>
				</div>';		
	foreach($order->Lines as $lines){
		// output each product. 
		$product = $admin->getItemByID($lines['PK']);
		
		$output .= '<div class="orderResultLine">
					<div class="img">
						<img src="' . $product['Image'] . '" />
					</div>
					<div class="resdetails">
					<div class="resulttitle">' . $product['Name'];
		
		if($lines['Variations'] != ''){
			$output .= ' - ' . rtrim($lines['Variations'], ", ");
		}
		
		$output .= '</div>';
		
			
			if($product['PartNumber'] == "DAKOTA15"){
				$output .= '<div class="respoints"><strong>Points:</strong> ' . (ltrim(rtrim($lines['Variations'], " Voucher"), "$") * 0.85 * 10 * $lines['Quantity']) . '</div>';
			} elseif($product['PartNumber'] == "CSAFOOTWEAR10"){
				$output .= '<div class="respoints"><strong>Points:</strong> ' . (ltrim(rtrim($lines['Variations'], " Voucher"), "$") * 0.90 * 10 * $lines['Quantity']) . '</div>';	
			} else {
				$output .= '<div class="respoints"><strong>Points:</strong>' . ($product['PointValue'] * $lines['Quantity']) . '</div>';
			}
			
			
			$output .= '	<div class="respartnumber"><strong>Part #:</strong> ' . $product['PartNumber'] . '</div>
							<div class="resquantity"><strong>Quantity:</strong> ' . $lines['Quantity'] . '</div>
						</div>
					</div>';
	}			
				
	echo $output;			
	
}