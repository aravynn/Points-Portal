<?php

/**
 *
 * Class for order creation and recall. 
 * will manage all orders, as well as getting ids for a user. 
 *
 */
 
 class order {
 	public $ID;
 	public $UserID;
 	public $Date;
 	public $Points;
 	public $Lines;
 	
 	private $userID;
 	
 	function __construct(){
 		// create the object.
 	}
 	
 	private function clear(){
 		// clear variables for reuse, if needed. 
 		$this->ID = '';
 		$this->UserID = '';
 		$this->Date = '';
 	 	$this->Points = '';
 		$this->Lines = array();
 	}
 	
 	public function getOrderByID($id){
 		// get order of user by ID given
 		
 		$this->clear();
 		
 		$sql = new sqlControl();
		$sql->sqlCommand('SELECT UserPK, Timestamp, PointsUsed FROM Orders WHERE PK = :PK', array(':PK' => $id), false);
		
		$results = $sql->returnResults(); // get the results for the user. 
		
		$this->ID = $id;
		$this->UserID = $results['UserPK'];
		$this->Date = $results['Timestamp'];
		$this->Points = $results['PointsUsed'];
		
		$sql->sqlCommand('SELECT ItemPK, Quantity, Variations FROM OrderLines WHERE OrderPK = :PK', array(':PK' => $id), false);
		
		$results = $sql->returnAllResults();
		
		foreach ($results as $r){
			$this->Lines[] = array( 'PK' => $r['ItemPK'], 'Quantity' => $r['Quantity'], 'Variations' => $r['Variations'] );
		}
		
		
		$sql = '';
 	}
 	
 	public function getLastOrder($userID){
 		// get last inserted order of user
 		// this might be an overload function of by id
 		
 		$ids = $this->getAllOrderIDs($userID); //get all IDS, then return the highest one. 
 		
 		$this->getOrderByID($ids[count($ids) - 1]); // get the last item, and return it. 
 		
 	}
 	
 	public function insertOrder($points, $lines, $userID){
 		// insert order for user. 
 		
 		$sql = new sqlControl();
 		$sql->sqlCommand('INSERT INTO Orders (UserPK, PointsUsed) VALUES (:UserPK, :PointsUsed)', array(':UserPK' => $userID, ':PointsUsed' => $points), false);
 		
 		$orderID = $sql->lastInsert();
 		$output = '';
 		foreach ($lines as $l){
 			$sql->sqlCommand('INSERT INTO OrderLines (OrderPK, ItemPK, Quantity, Variations) VALUES (:OrderPK, :ItemPK, :Quantity, :Variations)', array(':OrderPK' => $orderID, ':ItemPK' => $l['ItemPK'], ':Quantity' => $l['Quantity'], ':Variations' => $l['Variation']), false);
 			$output .= " line- PK: " . $l['ItemPK'] . " QTY: " . $l['Quantity'] . " Variation: " . $l['Variation'] . "
 			 ";
 		}
 		
 		$sql->logAction($_SESSION['USER'], "User entered order# " . $orderID . ": 
		UserID: " . $userID . "
		points: " . $points . "
		" . $output);
 		
 		
 		return $sql->lastInsert();
 		
 		$sql = '';
 	}
 	
 	public function getAllOrderIDs($userID = false, $desc = false, $limit = 0, $offset = 0){
 		// full order history. if userID specified, get all users. 
 		
 		$stmt = 'SELECT PK FROM Orders';
 		$arr = array();
 		if($userID != false){
 			$stmt .= ' WHERE UserPK = :PK';
 			$arr[':PK'] = $userID;			
 		}
 		
 		if($desc){
 			$stmt .= ' ORDER BY PK DESC';
 		}
 		
 		if($limit > 0 && is_numeric($limit)){
 			$stmt .= ' LIMIT ' . filter_var($limit, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
 			
 			if($offset > 0 && is_numeric($offset)){
 				$stmt .= ' OFFSET ' . filter_var($offset, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
 			}
 		}
 		
 		
 		$sql = new sqlControl();
		$sql->sqlCommand($stmt, $arr, false);
		
		$results = $sql->returnAllResults(); // get the results for the user. 
		
		$returns = array();
		
		foreach($results as $r){
			$returns[] = $r['PK'];
		}
			
		return $returns;
 	}
 }
 
 ?>