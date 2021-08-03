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

<h2> Order History </h2>


<?php loadOrderHistory(); ?>


<?php

function loadOrderHistory(){
	// load the last order entered. Since this page is a one-off, 
	// it will always show the last order entered by the user. 
	
	$user = new user();
	$user->LoadUserByName($_SESSION['USER']);
	
	$order = new order();
	
	if(isset($_GET['perpage']) && is_numeric($_GET['perpage'])){
		$limit = $_GET['perpage'];
	} else {
		$limit = 5;
	}
	
	
	
	if(isset($_GET['page']) && is_numeric($_GET['page'])){
		// there is a page number, -1 and mult by 10 for offset. 
		
		$offset = ($_GET['page'] -1) * $limit;
		
	} else {
		$offset = 0;
	}
	
	// determine full list or singular.
	if($_SESSION['TYPE'] == 'Employee' || $_SESSION['TYPE'] == 'Sizing'){
		$ordercount = count($order->getAllOrderIDs($user->UserID));
		$ids = $order->getAllOrderIDs($user->UserID, true, $limit, $offset);
	} else {
		$ordercount = count($order->getAllOrderIDs());
		$ids = $order->getAllOrderIDs(false, true, $limit, $offset);
		echo '<em class="warning"> This order history is for all users of the Miller points system. </em>';
	}
	
	$admin = new admin();
	
	$output = '';
	
	$output .=  pageButtons($ordercount, $limit, $offset);
	
	foreach($ids as $id){
		// for each order, go through and output data string. 
		$order->getOrderByID($id);
		
		// get the correct user.
		$orderUser = new user();
		$orderUser->LoadUserByID($order->UserID);
		
		$output .= '<div class="orderHistoryLine">
						<div class="orderhistdetails">
							<div class="histid"><strong>ID:</strong> ' . $id . '</div>
							<div class="histdate"><strong>Date:</strong> ' . $order->Date . '</div>
							<div class="histpoints"><strong>Points Used:</strong> ' . $order->Points . ' Points</div>
							<div class=""><strong>User:</strong> ' . $orderUser->Username . ' (' . $orderUser->FirstName . ' ' . $orderUser->LastName . ')</div>
						</div>';
		
		$grey = true;
		foreach($order->Lines as $lines){
			$product = $admin->getItemByID($lines['PK']);
		
			$output .= '<div class="orderhistlineitem' . ($grey ? ' grey' : '') . '">
							<div class="histlinename">' . $product['Name'];
		
			if($lines['Variations'] != ''){
				$output .= ' - ' . $lines['Variations'];
			}
				
				$output .= '</div>
							<div class="histlinepart">' . $product['PartNumber'] . '</div>';
				
				
			if($product['PartNumber'] == "DAKOTA15"){
				$output .= '<div class="histlinepts">' . (ltrim(rtrim($lines['Variations'], " Voucher"), "$") * 0.85 * 10) . ' Points Per</div>';
			} elseif($product['PartNumber'] == "CSAFOOTWEAR10"){
				$output .= '<div class="histlinepts">' . (ltrim(rtrim($lines['Variations'], " Voucher"), "$") * 0.90 * 10) . ' Points Per</div>';
			} else {
				$output .= '<div class="histlinepts">' . $product['PointValue'] . ' Points Per</div>';
			}
			
				
				
				
				$output .= '<div class="histlineqty">Quantity: ' . $lines['Quantity'] . '</div>
						</div>';
			$grey = ($grey ? false : true);
		}
		$output .= '</div>';
	}		
	
	$output .= pageButtons($ordercount, $limit, $offset);
				
	echo $output;			
	
}

function pageButtons($ordercount, $limit, $offset){
	// take the total orders, divide by limit for total pages. round up. 
	$pages = ceil($ordercount / $limit); 
	
	$output = '<div class="historycontrol">
				<div class="historycontrolbuttons">';
	// for each page, output a button that links back to page with a set limit and offset. 
	// back and forward buttons are set with +limit and -limit to current values, unless on the first or last page. 
	// input area allows user to set number of results per page. 
	
	if($offset >= $limit){
		$output .= '<a href="index.php?p=orderHistory&perpage=' . $limit . '&page=' . (ceil($offset/$limit)) . '">Prev</a> ';
	}
	
	$currentpage = $_GET['page'] - 1;
	
	if($currentpage - 2 < 0){
		$minpage = 0;
	} else {
		$minpage = $currentpage - 2;
	}
	
	if($currentpage + 2 >= $pages){
		$maxpage = $pages;
	} else {
		$maxpage = $currentpage + 3;
	}
	
	for($i = $minpage; $i < $maxpage; $i++){
		
		$output .= '<a ' . ($currentpage == $i ? 'class="activepage" ' : '') . 'href="index.php?p=orderHistory&perpage=' . $limit . '&page=' . ($i+1) . '">' . $i . '</a> ';
		
	}
	if($offset < $ordercount - $limit){
		$output .= '<a href="index.php?p=orderHistory&perpage=' . $limit . '&page=' . (ceil($offset/$limit) + 2) . '">Next</a> ';
	}
	
	
	
	$output .= '</div>
				<form method="get" action="index.php" class="ordersperpage">
					<input type="text" name="perpage" value="' . $limit . '" />
					<input type="hidden" name="page" value="' . ($offset / $limit + 1) . '" />
					<input type="hidden" name="p" value="orderHistory" />
					<input type="submit" value="Update" />
				</form>
				</div>';
	return $output;
}