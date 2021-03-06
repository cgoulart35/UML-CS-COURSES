<?php

	session_start();
	
	//delete row from enroll with student id and meeting id if logged in as student, student's parent, or admin
	if (isset($_GET['mid']) && isset($_GET['sid']) && isset($_SESSION['user'])) {
		
		$db2 = mysqli_connect('localhost', 'root', '', 'db2');
		
		$current_user = $_SESSION['user'];
		$current_id = $current_user['id'];
		
		$sid = $_GET['sid'];
		$mid = $_GET['mid'];
		
		$all_childen_of_parent_query = "SELECT student_id FROM students WHERE parent_id = '$current_id'";
		$all_childen_of_parent_result = mysqli_query($db2, $all_childen_of_parent_query);
		$in_all_childen_of_parent = False;
		while($child_id = mysqli_fetch_assoc($all_childen_of_parent_result)) {
			if (in_array($sid, $child_id)) {
				$in_all_childen_of_parent = True;
				break;
			}
		}
		
		if ($current_id == $sid || ($_SESSION['isParent'] && $in_all_childen_of_parent) || $_SESSION['isAdmin']) {
			$drop_meeting_as_mentee = "DELETE FROM enroll WHERE mentee_id = '$sid' AND meet_id = '$mid'";
			mysqli_query($db2, $drop_meeting_as_mentee);
			
			//check to see if still present in enroll table, if so keep in mentees, otherwise delete
			$is_still_mentee_query = "SELECT * FROM enroll WHERE mentee_id = '$sid' LIMIT 1";
			$result = mysqli_query($db2, $is_still_mentee_query);
			$is_still_mentee = mysqli_fetch_assoc($result);
			
			if ($is_still_mentee == null) {
				$drop_mentee = "DELETE FROM mentees WHERE mentee_id = '$sid'";
				mysqli_query($db2, $drop_mentee);
			}
		}
	}
	
	header('Location: student.php?id='. $sid);	

?>