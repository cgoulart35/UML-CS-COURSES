<?php

	session_start();
	
	//add row to enroll2 with student id and meeting id if logged in as student, student's parent, or admin
	if (isset($_GET['mid']) && isset($_GET['sid']) && isset($_SESSION['user'])) {
		
		$db2 = mysqli_connect('localhost', 'root', '', 'db2');
		
		$current_user = $_SESSION['user'];
		$current_id = $current_user['id'];
		
		$sid = $_GET['sid'];
		$mid = $_GET['mid'];
		
		//CHECK TO SEE IF PROVIDED MID IS IN THE LIST OF POSSIBLE MEETINGS AS A MENTOR
		$userPage_grade_query = "SELECT grade FROM students WHERE student_id = '$sid' LIMIT 1";
		$userPage_grade_result = mysqli_query($db2, $userPage_grade_query);
		$userPage_grade_arr = mysqli_fetch_assoc($userPage_grade_result);
		$userPage_grade = $userPage_grade_arr['grade'];
		
		$userPage_group_query = "SELECT * FROM groups WHERE description = '$userPage_grade' LIMIT 1";
		$userPage_group_result = mysqli_query($db2, $userPage_group_query);
		$userPage_group_arr = mysqli_fetch_assoc($userPage_group_result);
		
		$userPage_mentee_grade_req = $userPage_group_arr['mentee_grade_req'];
		
		$possible_meetings_mentor_of = "SELECT meet_id FROM meetings WHERE group_id IN (SELECT group_id FROM groups WHERE description <= '$userPage_mentee_grade_req') AND meet_id NOT IN (SELECT meet_id FROM enroll2 WHERE mentor_id = '$sid')";
		$possible_meetings_mentor_of_result = mysqli_query($db2, $possible_meetings_mentor_of);
		$is_possible_meeting = False;
		while($meeting = mysqli_fetch_assoc($possible_meetings_mentor_of_result)) {
			if (in_array($mid, $meeting)) {
				$is_possible_meeting = True;
				break;
			}
		}		
		
		if (($current_id == $sid || $_SESSION['isAdmin']) && $is_possible_meeting) {
			
			//get names and emails of participants to notify
			$get_name_email_query = "SELECT name, email FROM users WHERE id = '$sid'";
			$get_name_email_result = mysqli_query($db2, $get_name_email_query);
			$get_name_email_arr = mysqli_fetch_assoc($get_name_email_result);
			
			//get names of meetings
			$get_meeting_name_query = "SELECT meet_name FROM meetings WHERE meet_id = '$mid'";
			$get_meeting_name_result = mysqli_query($db2, $get_meeting_name_query);
			$get_meeting_name_arr = mysqli_fetch_assoc($get_meeting_name_result);
			
			$notify_name = $get_name_email_arr['name'];
			$notify_email = $get_name_email_arr['email'];
			$notify_meeting_name = $get_meeting_name_arr['meet_name'];
			
			//notify sid that they were added to meeting mid in a text file
			$timestamp = date('H:i:s');
			$timestamp = str_replace(":","-",$timestamp);
			$addedMentorNotificationFile = fopen("addedMentorNotificationUser" . $sid . "[". $timestamp . "].txt", "w") or die("Can't open file.");
			$txt = "Notify " . $notify_name . " (" . $notify_email . ")" . " that they have been added as a mentor to the meeting '" . $notify_meeting_name . "'.\n";
			fwrite($addedMentorNotificationFile, $txt);
			fclose($addedMentorNotificationFile);

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
	
	header('Location: admin.php?id='. $current_id);

?>