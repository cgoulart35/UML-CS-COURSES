<?php

	session_start();
	
	//add row to enroll2 with student id and meeting id if logged in as student, student's parent, or admin
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
			
			//if there are already 3 mentors, don't add any
			$mentor_count_query = "SELECT count(mentor_id) FROM enroll2 WHERE meet_id = '$mid' LIMIT 1";
			$mentor_count_result = mysqli_query($db2, $mentor_count_query);
			$mentor_count_arr = mysqli_fetch_assoc($mentor_count_result);
			$mentor_count = $mentor_count_arr['count(mentor_id)'];
			
			if ($mentor_count < 3) {
				$add_to_mentors = "INSERT INTO mentors (mentor_id) VALUES ('$sid')";
				mysqli_query($db2, $add_to_mentors);
				
				$add_meeting_as_mentor = "INSERT INTO enroll2 (meet_id, mentor_id) VALUES ('$mid', '$sid')";
				mysqli_query($db2, $add_meeting_as_mentor);
			}
		}
	}
	
	header('Location: student.php?id='. $sid);

?>