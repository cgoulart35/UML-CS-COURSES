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
		
		//CHECK TO SEE IF PROVIDED MID IS IN THE LIST OF POSSIBLE MEETINGS AS A MENTOR
		$userPage_grade_query = "SELECT grade FROM students WHERE student_id = '$sid' LIMIT 1";
		$userPage_grade_result = mysqli_query($db2, $userPage_grade_query);
		$userPage_grade_arr = mysqli_fetch_assoc($userPage_grade_result);
		$userPage_grade = $userPage_grade_arr['grade'];
		
		$userPage_group_query = "SELECT * FROM groups WHERE description = '$userPage_grade' LIMIT 1";
		$userPage_group_result = mysqli_query($db2, $userPage_group_query);
		$userPage_group_arr = mysqli_fetch_assoc($userPage_group_result);
		
		$userPage_mentee_grade_req = $userPage_group_arr['mentee_grade_req'];
		
		$append_future_dates_query = "";
		date_default_timezone_set('America/New_York');
		$current_date = date('Y-m-d');
		$thursday_date = date( 'Y-m-d', strtotime( 'thursday this week' ) );
		
		//if date is before this weeks thurday; show meetings with dates this saturday and on
		if ($current_date < $thursday_date) {
			$this_saturday_date = date( 'Y-m-d', strtotime( 'saturday this week' ) );
			$append_future_dates_query = "AND date >= '$this_saturday_date'";
		}
		
		//if date is after or is this thursday; show meetings with dates next saturday and on
		else {
			$next_saturday_date = date( 'Y-m-d', strtotime( 'saturday next week' ) );
			$append_future_dates_query = "AND date >= '$next_saturday_date'"; 
		}
		
		//possible meetings are all meetings in the future (also meetings this weekend if its before Thursday) where we are not already a mentor for that weekend (specifically that date, and that date + 1 if it's Saturday, and that date - 1 if it's Sunday; we use both date + 1 and date - 1 because meetings are not on Fridays or Mondays
		$possible_meetings_mentor_of = "SELECT meet_id FROM meetings WHERE group_id IN (SELECT group_id FROM groups WHERE description <= '$userPage_mentee_grade_req') AND meet_id NOT IN (SELECT meet_id FROM enroll2 WHERE mentor_id = '$sid') AND date NOT IN ((SELECT date FROM meetings INNER JOIN enroll2 ON meetings.meet_id = enroll2.meet_id WHERE mentor_id = '$sid') UNION (SELECT date - 1 FROM meetings INNER JOIN enroll2 ON meetings.meet_id = enroll2.meet_id WHERE mentor_id = '$sid') UNION (SELECT date + 1 FROM meetings INNER JOIN enroll2 ON meetings.meet_id = enroll2.meet_id WHERE mentor_id = '$sid'))" . $append_future_dates_query;
		
		$possible_meetings_mentor_of_result = mysqli_query($db2, $possible_meetings_mentor_of);
		$is_possible_meeting = False;
		while($meeting = mysqli_fetch_assoc($possible_meetings_mentor_of_result)) {
			if (in_array($mid, $meeting)) {
				$is_possible_meeting = True;
				break;
			}
		}		
		
		if (($current_id == $sid || ($_SESSION['isParent'] && $in_all_childen_of_parent) || $_SESSION['isAdmin']) && $is_possible_meeting) {
			
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