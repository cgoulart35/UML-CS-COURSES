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
						
			$select_meetings_with_same_name = "SELECT meet_id FROM meetings WHERE group_id = (SELECT group_id FROM meetings WHERE meet_id = '$mid' LIMIT 1) AND meet_name = (SELECT meet_name FROM meetings WHERE meet_id = '$mid')";
			$result = mysqli_query($db2, $select_meetings_with_same_name);
			
			while($meeting = mysqli_fetch_assoc($result)) {
				$new_mid = $meeting['meet_id'];
				$drop_meeting_as_mentor = "DELETE FROM enroll2 WHERE mentor_id = '$sid' AND meet_id = '$new_mid'";
				mysqli_query($db2, $drop_meeting_as_mentor);
			}
			
			//check to see if still present in enroll2 table, if so keep in mentors, otherwise delete
			$is_still_mentor_query = "SELECT * FROM enroll2 WHERE mentor_id = '$sid' LIMIT 1";
			$result = mysqli_query($db2, $is_still_mentor_query);
			$is_still_mentor = mysqli_fetch_assoc($result);
			
			if ($is_still_mentor == null) {
				$drop_mentor = "DELETE FROM mentors WHERE mentor_id = '$sid'";
				mysqli_query($db2, $drop_mentor);
			}
		}
	}
	
	header('Location: student.php?id='. $sid);	

?>