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
		
		//CHECK TO SEE IF PROVIDED MID IS IN THE LIST OF POSSIBLE MEETINGS AS A MENTEE
		$userPage_grade_query = "SELECT grade FROM students WHERE student_id = '$sid' LIMIT 1";
		$userPage_grade_result = mysqli_query($db2, $userPage_grade_query);
		$userPage_grade_arr = mysqli_fetch_assoc($userPage_grade_result);
		$userPage_grade = $userPage_grade_arr['grade'];
		
		$userPage_group_query = "SELECT * FROM groups WHERE description = '$userPage_grade' LIMIT 1";
		$userPage_group_result = mysqli_query($db2, $userPage_group_query);
		$userPage_group_arr = mysqli_fetch_assoc($userPage_group_result);
		
		$userPage_group_id = $userPage_group_arr['group_id'];
		
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
		
		$possible_meetings_mentee_of = "SELECT * FROM meetings WHERE group_id = '$userPage_group_id' AND meet_id NOT IN (SELECT meet_id FROM enroll WHERE mentee_id = '$sid') AND (time_slot_id, date) NOT IN (SELECT time_slot_id, date FROM meetings INNER JOIN enroll ON meetings.meet_id = enroll.meet_id WHERE mentee_id = '$sid')" . $append_future_dates_query;
		
		$possible_meetings_mentee_of_result = mysqli_query($db2, $possible_meetings_mentee_of);
		$is_possible_meeting = False;
		while($meeting = mysqli_fetch_assoc($possible_meetings_mentee_of_result)) {
			if (in_array($mid, $meeting)) {
				$is_possible_meeting = True;
				break;
			}
		}
		
		//get type of class this meeting is (Math or English) and its date
		$meeting_name_date_query = "SELECT meet_name, date FROM meetings WHERE meet_id = '$mid' LIMIT 1";
		$meeting_name_date_result = mysqli_query($db2, $meeting_name_date_query);
		$meeting_name_date_arr = mysqli_fetch_assoc($meeting_name_date_result);
		$meet_name = $meeting_name_date_arr['meet_name'];
		$meet_date = $meeting_name_date_arr['date'];
		
		//get weekend dates of this meetings weekend
		if (date('D', strtotime($meet_date)) == 'Sat') {
			$sat_date = $meet_date;
			$sun_date = date('Y-m-d', strtotime($sat_date .' +1 day'));
		}
		else if (date('D', strtotime($meet_date)) == 'Sun') {
			$sun_date = $meet_date;
			$sat_date = date('Y-m-d', strtotime($sun_date .' -1 day'));
		}

		//figure out types of classes enrolled in that weekend
		$types_of_classes_enrolled_in_that_weekend_query = "SELECT meet_name FROM meetings INNER JOIN enroll ON meetings.meet_id = enroll.meet_id WHERE mentee_id = '$sid' AND date <= '$sun_date' AND date >= '$sat_date'";
		$types_of_classes_enrolled_in_that_weekend_result = mysqli_query($db2, $types_of_classes_enrolled_in_that_weekend_query);
		
		//figure out if this meeting is in types of classes we are enrolled in
		$enrolled_in_type_already_that_weekend = False;
		while($enrolled_name = mysqli_fetch_assoc($types_of_classes_enrolled_in_that_weekend_result)) {
			if (in_array($meet_name, $enrolled_name)) {
				$enrolled_in_type_already_that_weekend = True;
				break;
			}
		}
		
		if (($current_id == $sid || ($_SESSION['isParent'] && $in_all_childen_of_parent) || $_SESSION['isAdmin']) && $is_possible_meeting && !($enrolled_in_type_already_that_weekend)) {	
			
			//if there are already 6 mentees, don't add any
			$mentee_count_query = "SELECT count(mentee_id) FROM enroll WHERE meet_id = '$mid' LIMIT 1";
			$mentee_count_result = mysqli_query($db2, $mentee_count_query);
			$mentee_count_arr = mysqli_fetch_assoc($mentee_count_result);
			$mentee_count = $mentee_count_arr['count(mentee_id)'];
			
			if ($mentee_count < 6) {
			
				// add all meetings as a mentee that have the same meeting name; we don't use meeting id because every meeting has a different id even if its the same meeting/section that is meeting 2 weeks/etc. later; we also don't use group_id because group_id is the id of the grade level and we don't want to add all sections of all meetings in that grade at once, only the same sections of meetings	
				
				$add_to_mentees = "INSERT INTO mentees (mentee_id) VALUES ('$sid')";
				mysqli_query($db2, $add_to_mentees);
				
				$add_meeting_as_mentee = "INSERT INTO enroll (meet_id, mentee_id) VALUES ('$mid', '$sid')";
				mysqli_query($db2, $add_meeting_as_mentee);
				
				$select_meetings_with_same_name = "SELECT * FROM meetings WHERE group_id = (SELECT group_id FROM meetings WHERE meet_id = '$mid' LIMIT 1) AND meet_name = (SELECT meet_name FROM meetings WHERE meet_id = '$mid')";
				$result = mysqli_query($db2, $select_meetings_with_same_name);

				while($meeting = mysqli_fetch_assoc($result)) {
					$new_mid = $meeting['meet_id'];
					$new_name = $meeting['meet_name'];
					$new_date = $meeting['date'];
					
					//get weekend dates of this meetings weekend
					if (date('D', strtotime($new_date)) == 'Sat') {
						$sat_date = $new_date;
						$sun_date = date('Y-m-d', strtotime($sat_date .' +1 day'));
					}
					else if (date('D', strtotime($new_date)) == 'Sun') {
						$sun_date = $new_date;
						$sat_date = date('Y-m-d', strtotime($sun_date .' -1 day'));
					}

					//figure out types of classes enrolled in that weekend
					$types_of_classes_enrolled_in_that_weekend_query = "SELECT meet_name FROM meetings INNER JOIN enroll ON meetings.meet_id = enroll.meet_id WHERE mentee_id = '$sid' AND date <= '$sun_date' AND date >= '$sat_date'";
					$types_of_classes_enrolled_in_that_weekend_result = mysqli_query($db2, $types_of_classes_enrolled_in_that_weekend_query);
					
					//figure out if this meeting is in types of classes we are enrolled in
					$enrolled_in_type_already_that_weekend = False;
					while($enrolled_name = mysqli_fetch_assoc($types_of_classes_enrolled_in_that_weekend_result)) {
						if (in_array($new_name, $enrolled_name)) {
							$enrolled_in_type_already_that_weekend = True;
							break;
						}
					}
					
					if (!($enrolled_in_type_already_that_weekend)) {
						print($enrolled_in_type_already_that_weekend);
						$add_meeting_as_mentee = "INSERT INTO enroll (meet_id, mentee_id) VALUES ('$new_mid', '$sid')";
						mysqli_query($db2, $add_meeting_as_mentee);
					}
				}
			}
		}
	}
	
	header('Location: student.php?id='. $sid);	

?>