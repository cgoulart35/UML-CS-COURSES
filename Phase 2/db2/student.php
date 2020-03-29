<?php
	session_start();
	
	if (isset($_GET['id'])) {
		
		//if no url id, then can't display anything
		$userPage_id = (int) $_GET['id'];
		
		$db2 = mysqli_connect('localhost', 'root', '', 'db2');
		
		$all_student_id_query = "SELECT student_id FROM students";
		$all_student_id_result = mysqli_query($db2, $all_student_id_query);
		$in_all_student_id = False;
		while($student_id = mysqli_fetch_assoc($all_student_id_result)) {
			if (in_array($userPage_id, $student_id)) {
				$in_all_student_id = True;
				break;
			}
		}
		
		if (isset($_SESSION['user']) && $in_all_student_id) {
			
			$current_user = $_SESSION['user'];
			$current_name = $current_user['name'];
			$current_id = $current_user['id'];
			
			$all_childen_of_parent_query = "SELECT student_id FROM students WHERE parent_id = '$current_id'";
			$all_childen_of_parent_result = mysqli_query($db2, $all_childen_of_parent_query);
			$in_all_childen_of_parent = False;
			while($child_id = mysqli_fetch_assoc($all_childen_of_parent_result)) {
				if (in_array($userPage_id, $child_id)) {
					$in_all_childen_of_parent = True;
					break;
				}
			}
			
			if ($userPage_id == $current_id || ($_SESSION['isParent'] && $in_all_childen_of_parent) || $_SESSION['isAdmin']) {
				//if id is the logged in student, show student page; or if logged in as admin or if logged in as parent of child
				
				$userPage_query = "SELECT * FROM users WHERE id='$userPage_id' LIMIT 1";
				$userPage_result = mysqli_query($db2, $userPage_query);
				$userPage_arr = mysqli_fetch_assoc($userPage_result);
				
				$userPage_grade_query = "SELECT grade FROM students WHERE student_id='$userPage_id' LIMIT 1";
				$userPage_grade_result = mysqli_query($db2, $userPage_grade_query);
				$userPage_grade_arr = mysqli_fetch_assoc($userPage_grade_result);
				
				$userPage_name = $userPage_arr['name'];
				$userPage_grade = $userPage_grade_arr['grade'];
				$userPage_phone = $userPage_arr['phone'];
				$userPage_email = $userPage_arr['email'];
				
				$headerOutput = "<h1> Welcome $current_name!</h1>
								<h3><p> $userPage_name's student page:</p></h3>";
				include('header.php');
				
				//update account info
				?>
				<html>
					<div>
						<div>
							<a href="logout.php"><input type="button" value="Sign out" name="logout"></a>
						</div>
						<br>
						<form method="post" action="student.php?id=<?php echo $userPage_id ?>">
							<?php include ('errors.php'); ?>
							<div>
								<label>Update Name:		</label>
								<input type="text" name="name" placeholder="<?php echo $userPage_name ?>">
							</div>
							<div>
								<label>Update Grade:	</label>
								<input type="number" name="grade" placeholder="<?php echo $userPage_grade ?>">
							</div>
							<div>
								<label>Update Phone:	</label>
								<input type="text" name="phone" placeholder="<?php echo $userPage_phone ?>">
							</div>
							<div>
								<label>Update Email:	</label>
								<input type="email" name="email" placeholder="<?php echo $userPage_email ?>">
							</div>
							<div>
								<label>Update Password:	</label>
								<input type="password" name="password_1">
							</div>
							<div>
								<label>Confirm password:</label>
								<input type="password" name="password_2">
							</div>
							<div>
								<button type="submit" name="update_user">Submit</button>
							</div>
						</form>
				</html>
				<?php
				
				// IF SUBMIT IS CLICKED ON THE FORM
				if (isset($_POST['update_user'])) {
					
					// GET THE FORM VALUES
					$name = mysqli_real_escape_string($db2, $_POST['name']);
					$grade = (int) mysqli_real_escape_string($db2, $_POST['grade']);
					$phone = mysqli_real_escape_string($db2, $_POST['phone']);
					$email = mysqli_real_escape_string($db2, $_POST['email']);
					$password_1 = mysqli_real_escape_string($db2, $_POST['password_1']);
					$password_2 = mysqli_real_escape_string($db2, $_POST['password_2']);
					
					// CHECK IF ANY VALUES ARE EMPTY, AND IF PASSWORDS AREN'T EQUAL
					if(empty($name)) { array_push($errors, "Enter a name."); }
					if(empty($grade)) { array_push($errors, "Enter a grade."); }
					if(empty($phone)) { array_push($errors, "Enter a phone number."); }
					if(empty($email)) { array_push($errors, "Enter a email."); }
					if(empty($password_1)) { array_push($errors, "Enter a password."); }
					if($password_1 != $password_2) { array_push($errors, "Passwords do not match."); }
					
					// CHECK IF EMAIL ALREADY EXISTS AS A USER
					$email_exists_query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
					$result = mysqli_query($db2, $email_exists_query);
					$student = mysqli_fetch_assoc($result);
					
					if($student) {
						if($student['email'] === $email) {
							array_push($errors, "Email already taken.");
						}
					}
					
					// CHECK TO SEE IF THERE ARE ANY EXISTING ERRORS
					if(count($errors) == 0) {			
						
						// UPDATE THE STUDENT INFORMATION INTO THE USERS TABLE
						$users_query = "UPDATE users SET email='$email', password='$password_1', name='$name', phone='$phone' WHERE id='$userPage_id'";
						mysqli_query($db2, $users_query);
						
						// UPDATE THE STUDENT AND PARENT INFORMATION INTO THE STUDENTS TABLE
						$student_query = "UPDATE students SET grade='$grade' WHERE student_id='$userPage_id'";
						mysqli_query($db2, $student_query);
						
						header('Location: student.php?id=' . $userPage_id);
					}
					else {
						// IF THERE ARE ERRORS, DISPLAY THEM
						foreach($errors as $error) {
							print($error . "<br>");
						}
					}
				}
				
				$userPage_grade_query = "SELECT grade FROM students WHERE student_id = '$userPage_id' LIMIT 1";
				$userPage_grade_result = mysqli_query($db2, $userPage_grade_query);
				$userPage_grade_arr = mysqli_fetch_assoc($userPage_grade_result);
				$userPage_grade = $userPage_grade_arr['grade'];
				
				$userPage_group_query = "SELECT * FROM groups WHERE description = '$userPage_grade' LIMIT 1";
				$userPage_group_result = mysqli_query($db2, $userPage_group_query);
				$userPage_group_arr = mysqli_fetch_assoc($userPage_group_result);
				
				$userPage_group_id = $userPage_group_arr['group_id'];
				$userPage_mentor_grade_req = $userPage_group_arr['mentor_grade_req'];
				$userPage_mentee_grade_req = $userPage_group_arr['mentee_grade_req'];
				
				//ONLY SHOW POSSIBLE MEETINGS THAT ARE IN THE FUTURE BY THURSDAY
				$append_future_dates_query = "";
				date_default_timezone_set('America/New_York');
				$current_date = date('Y-m-d');
				$next_monday_date = date( 'Y-m-d', strtotime( 'monday next week' ) );
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
				
				if ($userPage_mentee_grade_req != null) {
					
					//show meetings student is mentor of
					//view & drop option for each
					
					?>
					<html>
							<h2>Meetings <?php echo $userPage_name ?> is a mentor of:</h2>
							<table border="1">
							  <tr>
								<th>ID:</th>
								<th>Name:</th> 
								<th>Date:</th>
								<th>Time Slot:</th>
								<th>Capacity:</th>
								<th>Announcement:</th>
								<th>View Meeting:</th>
								<th>Drop Meeting:</th>
								<th>Drop All Future Meetings (by name):</th>
							  </tr>
					</html>
					<?php
					
					$select_meetings_mentor_of = "SELECT * FROM meetings INNER JOIN enroll2 ON enroll2.meet_id = meetings.meet_id WHERE mentor_id = '$userPage_id'";
					$select_meetings_mentor_of_result = mysqli_query($db2, $select_meetings_mentor_of);
					
					while($row = $select_meetings_mentor_of_result->fetch_assoc()) {
						$row_id = $row['meet_id'];
						$row_name = $row['meet_name'];
						$row_date = $row['date'];
						$row_time_id = $row['time_slot_id'];
						$row_capacity = $row['capacity'];
						$row_announcement = $row['announcement'];
						
						$time_slot_query = "SELECT * FROM time_slot WHERE time_slot_id = '$row_time_id' LIMIT 1";
						$time_slot_result = mysqli_query($db2, $time_slot_query);
						$time_slot_arr = mysqli_fetch_assoc($time_slot_result);
						$row_time_slot_day = $time_slot_arr['day_of_the_week'];
						$row_time_slot_start = $time_slot_arr['start_time'];
						$row_time_slot_end = $time_slot_arr['end_time'];
						
						echo "<tr>
								<td>$row_id</th>
								<td>$row_name</th> 
								<td>$row_date</th>
								<td>$row_time_slot_day $row_time_slot_start - $row_time_slot_end</th>
								<td>$row_capacity</th>
								<td>$row_announcement</th>
								<td><a href=meeting.php?mid=$row_id> View </a></th>
								<td><a href=dropAsMentor.php?mid=$row_id&sid=$userPage_id> Drop </a></th>
								<td><a href=dropMeetingsAsMentor.php?mid=$row_id&sid=$userPage_id> Drop Future Meetings </a></th>
							</tr>";
					}
					
					echo "<tr>
							<a href=dropAllAsMentor.php?sid=$userPage_id> Drop All Meetings as Mentor </a>
						</tr>";
					
					//show meetings student can be mentor of
					//view & add option for each
					
					?>
					<html>
							</table>
							<h2>Possible meetings for <?php echo $userPage_name ?> to be mentor of:</h2>
							<h5>Your request will not go through if there are already 3 mentors. You can only be a mentor of one meeting per weekend.</h5>
							<table border="1">
							  <tr>
								<th>ID:</th>
								<th>Name:</th> 
								<th>Date:</th>
								<th>Time Slot:</th>
								<th>Capacity:</th>
								<th>Announcement:</th>
								<th>View Meeting:</th>
								<th>Add Meeting:</th>
								<th>Add All Future Meetings (by name):</th>
							  </tr>
					</html>
					<?php
					
					//possible meetings are all meetings in the future (also meetings this weekend if its before Thursday) where we are not already a mentor for that weekend (specifically that date, and that date + 1 if it's Saturday, and that date - 1 if it's Sunday; we use both date + 1 and date - 1 because meetings are not on Fridays or Mondays
					$possible_meetings_mentor_of = "SELECT * FROM meetings WHERE group_id IN (SELECT group_id FROM groups WHERE description <= '$userPage_mentee_grade_req') AND meet_id NOT IN (SELECT meet_id FROM enroll2 WHERE mentor_id = '$userPage_id') AND date NOT IN ((SELECT date FROM meetings INNER JOIN enroll2 ON meetings.meet_id = enroll2.meet_id WHERE mentor_id = '$userPage_id') UNION (SELECT date - 1 FROM meetings INNER JOIN enroll2 ON meetings.meet_id = enroll2.meet_id WHERE mentor_id = '$userPage_id') UNION (SELECT date + 1 FROM meetings INNER JOIN enroll2 ON meetings.meet_id = enroll2.meet_id WHERE mentor_id = '$userPage_id'))" . $append_future_dates_query;
					$possible_meetings_mentor_of_result = mysqli_query($db2, $possible_meetings_mentor_of);
					
					while($row = $possible_meetings_mentor_of_result->fetch_assoc()) {
						$row_id = $row['meet_id'];
						$row_name = $row['meet_name'];
						$row_date = $row['date'];
						$row_time_id = $row['time_slot_id'];
						$row_capacity = $row['capacity'];
						$row_announcement = $row['announcement'];
						
						$time_slot_query = "SELECT * FROM time_slot WHERE time_slot_id = '$row_time_id' LIMIT 1";
						$time_slot_result = mysqli_query($db2, $time_slot_query);
						$time_slot_arr = mysqli_fetch_assoc($time_slot_result);
						$row_time_slot_day = $time_slot_arr['day_of_the_week'];
						$row_time_slot_start = $time_slot_arr['start_time'];
						$row_time_slot_end = $time_slot_arr['end_time'];
						
						echo "<tr>
								<td>$row_id</th>
								<td>$row_name</th> 
								<td>$row_date</th>
								<td>$row_time_slot_day $row_time_slot_start - $row_time_slot_end</th>
								<td>$row_capacity</th>
								<td>$row_announcement</th>
								<td><a href=meeting.php?mid=$row_id> View </a></th>
								<td><a href=addAsMentor.php?mid=$row_id&sid=$userPage_id> Add </a></th>
								<td><a href=addMeetingsAsMentor.php?mid=$row_id&sid=$userPage_id> Add Future Meetings </a></th>
							</tr>";
					}
				}
				
				if ($userPage_mentor_grade_req != null) {
						
					//show meetings student is mentee of
					//view & drop option for each
					
					?>
					<html>
							</table>
							<h2>Meetings <?php echo $userPage_name ?> is a mentee of:</h2>
							<table border="1">
							  <tr>
								<th>ID:</th>
								<th>Name:</th> 
								<th>Date:</th>
								<th>Time Slot:</th>
								<th>Capacity:</th>
								<th>Announcement:</th>
								<th>View Meeting:</th>
								<th>Drop Meeting:</th>
								<th>Drop All Future Meetings (by name):</th>
							  </tr>
					</html>
					<?php
					
					
					$select_meetings_mentee_of = "SELECT * FROM meetings INNER JOIN enroll ON enroll.meet_id = meetings.meet_id WHERE mentee_id = '$userPage_id'";
					$select_meetings_mentee_of_result = mysqli_query($db2, $select_meetings_mentee_of);
					
					while($row = $select_meetings_mentee_of_result->fetch_assoc()) {
						$row_id = $row['meet_id'];
						$row_name = $row['meet_name'];
						$row_date = $row['date'];
						$row_time_id = $row['time_slot_id'];
						$row_capacity = $row['capacity'];
						$row_announcement = $row['announcement'];
						
						$time_slot_query = "SELECT * FROM time_slot WHERE time_slot_id = '$row_time_id' LIMIT 1";
						$time_slot_result = mysqli_query($db2, $time_slot_query);
						$time_slot_arr = mysqli_fetch_assoc($time_slot_result);
						$row_time_slot_day = $time_slot_arr['day_of_the_week'];
						$row_time_slot_start = $time_slot_arr['start_time'];
						$row_time_slot_end = $time_slot_arr['end_time'];
						
						echo "<tr>
								<td>$row_id</th>
								<td>$row_name</th> 
								<td>$row_date</th>
								<td>$row_time_slot_day $row_time_slot_start - $row_time_slot_end</th>
								<td>$row_capacity</th>
								<td>$row_announcement</th>
								<td><a href=meeting.php?mid=$row_id> View </a></th>
								<td><a href=dropAsMentee.php?mid=$row_id&sid=$userPage_id> Drop </a></th>
								<td><a href=dropMeetingsAsMentee.php?mid=$row_id&sid=$userPage_id> Drop Future Meetings </a></th>
							</tr>";
					}
					
					echo "<tr>
							<a href=dropAllAsMentee.php?sid=$userPage_id> Drop All Meetings as Mentee</a>
						</tr>";
						
					//show meetings student can be mentee of
					//view & add option for each
					
					?>
					<html>
							</table>
							<h2>Possible meetings for <?php echo $userPage_name ?> to be mentee of:</h2>
							<h5>Your request will not go through if there are already 6 mentees. You can only be a mentee of meetings with different times, and you can only have one meeting per subject each weekend.</h5>
							<table border="1">
							  <tr>
								<th>ID:</th>
								<th>Name:</th> 
								<th>Date:</th>
								<th>Time Slot:</th>
								<th>Capacity:</th>
								<th>Announcement:</th>
								<th>View Meeting:</th>
								<th>Add Meeting:</th>
								<th>Add All Future Meetings (by name):</th>
							  </tr>
					</html>
					<?php
					
					//possible meetings are meetings that are not on the same date and time-slot as enrolled meetings, and are math classes a certain weekend if not yet enrolled in a math class that weekend, and english classes a weekend if not yet enrolled in an english class that weekend
					$possible_meetings_mentee_of = "SELECT * FROM meetings WHERE group_id = '$userPage_group_id' AND meet_id NOT IN (SELECT meet_id FROM enroll WHERE mentee_id = '$userPage_id') AND (time_slot_id, date) NOT IN (SELECT time_slot_id, date FROM meetings INNER JOIN enroll ON meetings.meet_id = enroll.meet_id WHERE mentee_id = '$userPage_id')" . $append_future_dates_query;
					$possible_meetings_mentee_of_result = mysqli_query($db2, $possible_meetings_mentee_of);
					
					while($row = $possible_meetings_mentee_of_result->fetch_assoc()) {
						
						//get type of class this meeting is (Math or English) and its date
						$row_name = $row['meet_name'];
						$row_date = $row['date'];
						
						//get weekend dates of this meetings weekend
						if (date('D', strtotime($row_date)) == 'Sat') {
							$sat_date = $row_date;
							$sun_date = date('Y-m-d', strtotime($sat_date .' +1 day'));
						}
						else if (date('D', strtotime($row_date)) == 'Sun') {
							$sun_date = $row_date;
							$sat_date = date('Y-m-d', strtotime($sun_date .' -1 day'));
						}

						//figure out types of classes enrolled in that weekend
						$types_of_classes_enrolled_in_that_weekend_query = "SELECT meet_name FROM meetings INNER JOIN enroll ON meetings.meet_id = enroll.meet_id WHERE mentee_id = '$userPage_id' AND date <= '$sun_date' AND date >= '$sat_date'";
						$types_of_classes_enrolled_in_that_weekend_result = mysqli_query($db2, $types_of_classes_enrolled_in_that_weekend_query);
						
						//figure out if this meeting is in types of classes we are enrolled in
						$enrolled_in_type_already_that_weekend = False;
						while($enrolled_name = mysqli_fetch_assoc($types_of_classes_enrolled_in_that_weekend_result)) {
							if (in_array($row_name, $enrolled_name)) {
								$enrolled_in_type_already_that_weekend = True;
								break;
							}
						}

						//again, possible meetings are math classes a certain weekend if not yet enrolled in a math class that weekend, and english classes a weekend if not yet enrolled in an english class that weekend, etc.
						if (!($enrolled_in_type_already_that_weekend)) {
							
							$row_id = $row['meet_id'];
							$row_time_id = $row['time_slot_id'];
							$row_capacity = $row['capacity'];
							$row_announcement = $row['announcement'];
							
							$time_slot_query = "SELECT * FROM time_slot WHERE time_slot_id = '$row_time_id' LIMIT 1";
							$time_slot_result = mysqli_query($db2, $time_slot_query);
							$time_slot_arr = mysqli_fetch_assoc($time_slot_result);
							$row_time_slot_day = $time_slot_arr['day_of_the_week'];
							$row_time_slot_start = $time_slot_arr['start_time'];
							$row_time_slot_end = $time_slot_arr['end_time'];
							
							echo "<tr>
									<td>$row_id</th>
									<td>$row_name</th> 
									<td>$row_date</th>
									<td>$row_time_slot_day $row_time_slot_start - $row_time_slot_end</th>
									<td>$row_capacity</th>
									<td>$row_announcement</th>
									<td><a href=meeting.php?mid=$row_id> View </a></th>
									<td><a href=addAsMentee.php?mid=$row_id&sid=$userPage_id> Add </a></th>
									<td><a href=addMeetingsAsMentee.php?mid=$row_id&sid=$userPage_id> Add Future Meetings </a></th>
								</tr>";
						}	
					}
				}
				
				?>
				<html>
						</table>
						<br>
					</div>
				</html>
				<?php
			
			}
			else {
				?>
				<html>
					<div>
						<a href="logout.php"><input type="button" value="Sign out" name="logout"></a>
					</div>
				</html>
				<?php
			}			
		}
		else {
			if (!(isset($_SESSION['user']))) {
				header('Location: login.php');
			}
			elseif (!($in_all_student_id)) {
				$all_admin_id_query = "SELECT admin_id FROM admins";
				$all_admin_id_result = mysqli_query($db2, $all_admin_id_query);
				$in_all_admin_id = False;
				while($admin_id = mysqli_fetch_assoc($all_admin_id_result)) {
					if (in_array($userPage_id, $admin_id)) {
						$in_all_admin_id = True;
						break;
					}
				}
				
				$all_parent_id_query = "SELECT parent_id FROM parents";
				$all_parent_id_result = mysqli_query($db2, $all_parent_id_query);
				$in_all_parent_id = False;
				while($parent_id = mysqli_fetch_assoc($all_parent_id_result)) {
					if (in_array($userPage_id, $parent_id)) {
						$in_all_parent_id = True;
						break;
					}
				}
				
				if ($in_all_admin_id) {
					header('Location: admin.php?id=' . $userPage_id);
				}
				elseif ($in_all_parent_id) {
					header('Location: parent.php?id=' . $userPage_id);
				}
			}	
		}
	}
?>