<?php
	error_reporting(E_ALL ^ E_WARNING);
	$db2 = mysqli_connect('localhost', 'root', '', 'db2');
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		$query = $decoded['query'];
		
		$result = mysqli_query($db2, $query);
		
		$stack = array();
		
		if (gettype($result) == boolean) {
			array_push($stack, array('status' => $result));
			exit(json_encode($stack));
		}
		else {
			
			while($row = mysqli_fetch_assoc($result)) {
				array_push($stack, $row);
			}
	
			exit(json_encode($stack));
		}
	}
	else {
		array_push($stack, array('status' => 'failed'));
		exit(json_encode($stack));
	}
?>