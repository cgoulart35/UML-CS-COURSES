<?php

	session_start();
	
	//add row to enroll with student id and meeting id if logged in as student, student's parent, or admin
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
		
			//if there are already 6 mentees, don't add any
			$mentee_count_query = "SELECT count(mentee_id) FROM enroll WHERE meet_id = '$mid' LIMIT 1";
			$mentee_count_result = mysqli_query($db2, $mentee_count_query);
			$mentee_count_arr = mysqli_fetch_assoc($mentee_count_result);
			$mentee_count = $mentee_count_arr['count(mentee_id)'];
			
			if ($mentee_count < 6) {
				$add_to_mentees = "INSERT INTO mentees (mentee_id) VALUES ('$sid')";
				mysqli_query($db2, $add_to_mentees);
				
				$add_meeting_as_mentee = "INSERT INTO enroll (meet_id, mentee_id) VALUES ('$mid', '$sid')";
				mysqli_query($db2, $add_meeting_as_mentee);
			}
		}
	}
	
	header('Location: student.php?id='. $sid);	

?>