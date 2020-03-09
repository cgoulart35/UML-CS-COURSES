<?php

	session_start();
	
	if (isset($_GET['mid']) && isset($_SESSION['user'])) {
		
		$current_user = $_SESSION['user'];
		$current_id = $current_user['id'];
		$current_name = $current_user['name'];
		
		$mid = $_GET['mid'];
		
		$db2 = mysqli_connect('localhost', 'root', '', 'db2');
		
		$select_mentors_of_meeting = "SELECT mentor_id FROM enroll2 WHERE meet_id = '$mid'";
		$select_mentors_of_meeting_result = mysqli_query($db2, $select_mentors_of_meeting);
		$is_mentor_of_meeting = False;
		while($mentor_id = mysqli_fetch_assoc($select_mentors_of_meeting_result)) {
			if (in_array($current_id, $mentor_id)) {
				$is_mentor_of_meeting = True;
				break;
			}
		}
		
		$select_parents_of_mentors = "SELECT parent_id FROM students WHERE student_id IN (SELECT mentor_id FROM enroll2 WHERE meet_id = '$mid')";
		$select_parents_of_mentors_result = mysqli_query($db2, $select_parents_of_mentors);
		$is_parent_of_mentor = False;
		while($parent_id = mysqli_fetch_assoc($select_parents_of_mentors_result)) {
			if (in_array($current_id, $parent_id)) {
				$is_parent_of_mentor = True;
				break;
			}
		}
		
		$headerOutput = "<h1> Welcome $current_name!</h1>
						<h3><p> Meeting $mid:</p></h3>";
		include('header.php');
		
		?>
		<html>
			<div class="page">
				<div class = "logout_btn">
					<a href="logout.php"><input type="button" value="Sign out" name="logout"></a>
				</div>
				<h2>Meeting information:</h2>
					<table border="1" style="width:75%">
					  <tr>
						<th>ID:</th>
						<th>Name:</th> 
						<th>Date:</th>
						<th>Time Slot:</th>
						<th>Capacity:</th>
						<th>Announcement:</th>
		</html>
		<?php
		
		$select_meeting = "SELECT * FROM meetings WHERE meet_id = '$mid'";
		$select_meeting_result = mysqli_query($db2, $select_meeting);
		$select_meeting_arr = mysqli_fetch_assoc($select_meeting_result);
		
		$meeting_id = $select_meeting_arr['meet_id'];
		$meeting_name = $select_meeting_arr['meet_name'];
		$meeting_date = $select_meeting_arr['date'];
		$meeting_time_id = $select_meeting_arr['time_slot_id'];
		$meeting_capacity = $select_meeting_arr['capacity'];
		$meeting_announcement = $select_meeting_arr['announcement'];
		
		$time_slot_query = "SELECT * FROM time_slot WHERE time_slot_id = '$meeting_time_id' LIMIT 1";
		$time_slot_result = mysqli_query($db2, $time_slot_query);
		$time_slot_arr = mysqli_fetch_assoc($time_slot_result);
		$meeting_time_slot_day = $time_slot_arr['day_of_the_week'];
		$meeting_time_slot_start = $time_slot_arr['start_time'];
		$meeting_time_slot_end = $time_slot_arr['end_time'];
		
		echo "<tr>
				<td>$meeting_id</th>
				<td>$meeting_name</th> 
				<td>$meeting_date</th>
				<td>$meeting_time_slot_day $meeting_time_slot_start - $meeting_time_slot_end</th>
				<td>$meeting_capacity</th>
				<td>$meeting_announcement</th>
			</tr>";
		
		//show mentors and mentees (name & email) if logged in as mentor or parent of mentor or admin
		if ($is_mentor_of_meeting || ($_SESSION['isParent'] && $is_parent_of_mentor) || $_SESSION['isAdmin']) {
			?>
			<html>
					</table>
					<br>
					<h2>Mentors:</h2>
					<table border="1" style="width:75%">
					  <tr>
						<th>Name:</th>
						<th>Email:</th> 
					  </tr>
			</html>
			<?php
			
			$select_mentor_users_of_meeting = "SELECT * FROM users INNER JOIN enroll2 ON enroll2.mentor_id = users.id WHERE meet_id = '$mid'";
			$select_mentor_users_of_meeting_result = mysqli_query($db2, $select_mentor_users_of_meeting);
			while($row = $select_mentor_users_of_meeting_result->fetch_assoc()) {
				$row_name = $row['name'];
				$row_email = $row['email'];
			
				echo "<tr>
						<td>$row_name</th>
						<td>$row_email</th> 
					</tr>";
			}
			
			?>
			<html>
					</table>
					<br>
					<h2>Mentees:</h2>
					<table border="1" style="width:75%">
					  <tr>
						<th>Name:</th>
						<th>Email:</th> 
					  </tr>
			</html>
			<?php
			
			$select_mentee_users_of_meeting = "SELECT * FROM users INNER JOIN enroll ON enroll.mentee_id = users.id WHERE meet_id = '$mid'";
			$select_mentee_users_of_meeting_result = mysqli_query($db2, $select_mentee_users_of_meeting);
			while($row = $select_mentee_users_of_meeting_result->fetch_assoc()) {
				$row_name = $row['name'];
				$row_email = $row['email'];
			
				echo "<tr>
						<td>$row_name</th>
						<td>$row_email</th> 
					</tr>";
			}
		}
		
		
		

		//show material info
		
		
		
		
		
	}
		?>
		<html>
					</table>
					<br>
			</div>
		</html>