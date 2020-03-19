<?php
	session_start();
	
	if (isset($_GET['id'])) {
		
		//if no url id, then can't display anything
		$userPage_id = (int) $_GET['id'];
		
		if (isset($_SESSION['user']) && $_SESSION['isAdmin']) {
			
			$current_user = $_SESSION['user'];
			$current_name = $current_user['name'];
			
			$db2 = mysqli_connect('localhost', 'root', '', 'db2');
			
			$all_admin_id_query = "SELECT admin_id FROM admins";
			$all_admin_id_result = mysqli_query($db2, $all_admin_id_query);
			$in_all_admin_id = False;
			while($admin_id = mysqli_fetch_assoc($all_admin_id_result)) {
				if (in_array($userPage_id, $admin_id)) {
					$in_all_admin_id = True;
					break;
				}
			}
			
			$all_student_id_query = "SELECT student_id FROM students";
			$all_student_id_result = mysqli_query($db2, $all_student_id_query);
			$in_all_student_id = False;
			while($student_id = mysqli_fetch_assoc($all_student_id_result)) {
				if (in_array($userPage_id, $student_id)) {
					$in_all_student_id = True;
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
			
			//make sure person is admin to see admin page incase URL was changed and userPage_id is no longer the id of logged in user
			if ($in_all_admin_id) {
				$userPage_query = "SELECT * FROM users WHERE id='$userPage_id' LIMIT 1";
				$userPage_result = mysqli_query($db2, $userPage_query);
				$userPage_arr = mysqli_fetch_assoc($userPage_result);
				
				$userPage_name = $userPage_arr['name'];
				$userPage_phone = $userPage_arr['phone'];
				$userPage_email = $userPage_arr['email'];
				
				$headerOutput = "<h1> Welcome $current_name!</h1>
								<h3><p> $userPage_name's admin page:</p></h3>";
				include('header.php');
		
				//if it's Friday, cancel all meetings with less than three mentees and notify participants
				//if it's Friday, admin is the only user who can add mentors to a meeting for current weekend; admin will be prompted to add available mentors to meetings with less than two mentors
				date_default_timezone_set('America/New_York');
				if (date('D') == 'Fri') {
	
					//notify participants
					$next_monday_date = date( 'Y-m-d', strtotime( 'monday next week' ) );
					$notify_participants_query = "SELECT * FROM ((SELECT mentee_id AS id, meet_id FROM enroll GROUP BY meet_id HAVING count(mentee_id) < 3) UNION (SELECT mentor_id AS id, meet_id FROM enroll2 WHERE meet_id NOT IN (SELECT meet_id FROM enroll GROUP BY meet_id HAVING count(mentee_id) >= 3))) AS bigTbl WHERE bigTbl.meet_id IN (SELECT meet_id FROM meetings WHERE date < '$next_monday_date')";
					$notify_participants_result = mysqli_query($db2, $notify_participants_query);
					
					//if there is no one to notify, don't create notifications or cancel any meetings
					if ($notify_participants_result->num_rows != 0) {
						
						//output to notification file canceledMeetings.txt
						$timestamp = date('H:i:s');
						$timestamp = str_replace(":","-",$timestamp);
						$canceledMeetingsFile = fopen("canceledMeetingsNotifications[" . $timestamp . "].txt", "w") or die("Can't open file.");
						
						while($participant = mysqli_fetch_assoc($notify_participants_result)) {
							
							$uid = $participant['id'];
							$mid = $participant['meet_id'];
							
							//get names and emails of participants to notify
							$get_name_email_query = "SELECT name, email FROM users WHERE id = '$uid'";
							$get_name_email_result = mysqli_query($db2, $get_name_email_query);
							$get_name_email_arr = mysqli_fetch_assoc($get_name_email_result);
							
							//get names of meetings
							$get_meeting_name_query = "SELECT meet_name FROM meetings WHERE meet_id = '$mid'";
							$get_meeting_name_result = mysqli_query($db2, $get_meeting_name_query);
							$get_meeting_name_arr = mysqli_fetch_assoc($get_meeting_name_result);
							
							$notify_name = $get_name_email_arr['name'];
							$notify_email = $get_name_email_arr['email'];
							$notify_meeting_name = $get_meeting_name_arr['meet_name'];
							
							$txt = "Notify " . $notify_name . " (" . $notify_email . ")" . " that the meeting '" . $notify_meeting_name . "' has been canceled.\n";
							fwrite($canceledMeetingsFile, $txt);
						}
						fclose($canceledMeetingsFile);
					
					
						//remove meetings with less than three mentees
						$cancel_meetings_query = "DELETE FROM meetings WHERE meet_id NOT IN (SELECT meet_id FROM enroll GROUP BY meet_id HAVING count(mentee_id) >= 3) AND date < '$next_monday_date'";
						mysqli_query($db2, $cancel_meetings_query);
						
						//remove mentees from mentees if they are no longer in enroll
						$remove_mentees_query = "DELETE FROM mentees WHERE mentee_id NOT IN (SELECT mentee_id FROM enroll)";
						mysqli_query($db2, $remove_mentees_query);
						
						//remove mentors from mentors if they are no longer in enroll2
						$remove_mentors_query = "DELETE FROM mentors WHERE mentor_id NOT IN (SELECT mentor_id FROM enroll2)";
						mysqli_query($db2, $remove_mentors_query);
					}
					
					//generate table showing meetings with under two mentors for current weekend; add and notify option for available mentors to add
					?>
					<html>
						<h2>It's Friday! Add mentors to meetings with under two mentors:</h2>
					</html>
					<?php
					
					$meetings_under_two_mentors_query = "SELECT * FROM meetings WHERE meet_id NOT IN (SELECT meet_id FROM enroll2 GROUP BY meet_id HAVING count(mentor_id) >= 2) AND date < '$next_monday_date'";
					$meetings_under_two_mentors_result = mysqli_query($db2, $meetings_under_two_mentors_query);
					
					if ($meetings_under_two_mentors_result->num_rows == 0) {
						?>
						<html>
							<h3>No meetings with under two mentors!</h3>
						</html>
						<?php
					}
					
					while($meeting = $meetings_under_two_mentors_result->fetch_assoc()) {
						$meeting_name = $meeting['meet_name'];
						$meeting_id = $meeting['meet_id'];
						$meeting_date = $meeting['date'];
						
						echo "<table border=\"1\">
								<tr>
									<th>$meeting_id</th>
									<th>$meeting_name</th>
									<th>$meeting_date</th>
								</tr>
								<tr>
									<th>Name:</th>
									<th>Email:</th>
									<th>Add as Mentor:</th>
								</tr>";
						
						//show list of available mentors for this meeting
						//change to professor's idea of available mentors!!!!
						$available_mentors_query = "SELECT * FROM users WHERE id IN (SELECT student_id FROM students WHERE grade >= (SELECT mentor_grade_req FROM groups INNER JOIN meetings ON groups.group_id = meetings.group_id WHERE meet_id = $meeting_id)) AND id NOT IN (SELECT mentor_id FROM enroll2 WHERE meet_id = $meeting_id)";
						$available_mentors_result = mysqli_query($db2, $available_mentors_query);
						while($mentor = $available_mentors_result->fetch_assoc()) {
							$mentor_id = $mentor['id'];
							$mentor_name = $mentor['name'];
							$mentor_email = $mentor['email'];
							echo "<tr>
									<td>$mentor_name</td>
									<td>$mentor_email</td>
									<td><a href=addAsMentorAndNotify.php?mid=$meeting_id&sid=$mentor_id> Add & Notify </a></th>
								</tr>";
						}
						echo "</table>
							<br>";
					}
				}
				
				//update account info
				?>
				<html>
					<div>
						<div>
							<a href="logout.php"><input type="button" value="Sign out" name="logout"></a>
						</div>
						<br>
						<form method="post" action="admin.php?id=<?php echo $userPage_id ?>">
							<?php include ('errors.php'); ?>
							<div>
								<label>Update Name:		</label>
								<input type="text" name="name" placeholder="<?php echo $userPage_name ?>">
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
					$phone = mysqli_real_escape_string($db2, $_POST['phone']);
					$email = mysqli_real_escape_string($db2, $_POST['email']);
					$password_1 = mysqli_real_escape_string($db2, $_POST['password_1']);
					$password_2 = mysqli_real_escape_string($db2, $_POST['password_2']);
					
					// CHECK IF ANY VALUES ARE EMPTY, AND IF PASSWORDS AREN'T EQUAL
					if(empty($name)) { array_push($errors, "Enter a name."); }
					if(empty($phone)) { array_push($errors, "Enter a phone number."); }
					if(empty($email)) { array_push($errors, "Enter a email."); }
					if(empty($password_1)) { array_push($errors, "Enter a password."); }
					if($password_1 != $password_2) { array_push($errors, "Passwords do not match."); }
					
					// CHECK IF EMAIL ALREADY EXISTS AS A USER
					$email_exists_query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
					$result = mysqli_query($db2, $email_exists_query);
					$admin = mysqli_fetch_assoc($result);
					
					if($admin) {
						if($admin['email'] === $email) {
							array_push($errors, "Email already taken.");
						}
					}

					// CHECK TO SEE IF THERE ARE ANY EXISTING ERRORS
					if(count($errors) == 0) {
						// UPDATE THE ADMIN INFORMATION INTO THE USERS TABLE
						$users_query = "UPDATE users SET email='$email', password='$password_1', name='$name', phone='$phone' WHERE id='$userPage_id'";
						mysqli_query($db2, $users_query);
						
						header('Location: admin.php?id=' . $userPage_id);
					}
					else {
						// IF THERE ARE ERRORS, DISPLAY THEM
						foreach($errors as $error) {
							print($error . "<br>");
						}
					}
				}
				
				//show all meetings and their information
				//post materials to meetings on meetings page
				
				?>
				<html>
						<h2>All Meetings:</h2>
						<table border="1">
						  <tr>
							<th>ID:</th>
							<th>Name:</th> 
							<th>Date:</th>
							<th>Time Slot:</th>
							<th>Capacity:</th>
							<th>Announcement:</th>
							<th>Post Materials:</th>
						  </tr>
				</html>
				<?php
				
				$select_all_meetings_query = "SELECT * FROM meetings";
				$select_all_meetings_query_result = mysqli_query($db2, $select_all_meetings_query);
				
				while($row = $select_all_meetings_query_result->fetch_assoc()) {
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
							<td><a href=meeting.php?mid=$row_id> Post </a></th>
						</tr>";
				}
				
				//shown all users; bring you to parent page, or student page to update mentors and mentees of meetings
				$select_all_users_query = "SELECT * FROM users";
				$all_users_result = mysqli_query($db2, $select_all_users_query);
				
				?>
				<html>
						</table>
						<h2>All Users:</h2>
						<table border="1">
						  <tr>
							<th>ID</th>
							<th>Email</th> 
							<th>Password</th>
							<th>Name</th>
							<th>Phone</th>
							<th>Grade</th>
							<th>Edit User</th>
						  </tr>
				</html>
				<?php
				
				//pass the id to the url; on the next page, make sure that id is in the array of users that was listed out on the previous page or person logged in
				
				$arr_id = [];
				while($row = $all_users_result->fetch_assoc()) {
					$row_id = $row['id'];
					$row_email = $row['email'];
					$row_password = $row['password'];
					$row_name = $row['name'];
					$row_phone = $row['phone'];
					
					$select_student_grade = "SELECT grade FROM students WHERE student_id = '$row_id'";
					$select_student_grade_result = mysqli_query($db2, $select_student_grade);
					$row_grade_array = mysqli_fetch_assoc($select_student_grade_result);
					
					if($row_grade_array) {
						$row_grade = $row_grade_array['grade'];
					}
					else {
						$row_grade = 'N/A';
					}
					
					echo "<tr>
							<td>$row_id</th>
							<td>$row_email</th> 
							<td>$row_password</th>
							<td>$row_name</th>
							<td>$row_phone</th>
							<td>$row_grade</th>";
					
					$arr_id[$row_id] = $row_id;

					$is_student_query = "SELECT * FROM students WHERE student_id = '$row_id' LIMIT 1";
					$student_result = mysqli_query($db2, $is_student_query);
					$isUserToUpdateAStudent = mysqli_fetch_assoc($student_result);

					$is_parent_query = "SELECT * FROM parents WHERE parent_id = '$row_id' LIMIT 1";
					$parent_result = mysqli_query($db2, $is_parent_query);
					$isUserToUpdateAParent = mysqli_fetch_assoc($parent_result);
					
					if ($isUserToUpdateAStudent) {
						echo '<td><a href="student.php?' . 'id=' . $row_id . '"> Edit Student </a></th>
							</tr>';
					}
					elseif ($isUserToUpdateAParent) {
						echo '<td><a href="parent.php?' . 'id=' . $row_id . '"> Edit Parent </a></th>
							</tr>';
					}
					else {
						echo '<td><a href="admin.php?' . 'id=' . $row_id . '"> Edit Admin </a></th>
							</tr>';
					}
				}
				$_SESSION['arr_id'] = $arr_id;

				?>
				<html>
						</table>
						<br>
					</div>
				</html>
				<?php

			}
			
			elseif ($in_all_student_id) {
				header('Location: student.php?id=' . $userPage_id);
			}
			
			elseif ($in_all_parent_id) {
				header('Location: parent.php?id=' . $userPage_id);
			}
				
		}
		else {
			if (!(isset($_SESSION['user']))) {
				header('Location: login.php');
			}
			elseif ($_SESSION['isStudent']) {
				header('Location: student.php?id=' . $userPage_id);
			}
			elseif ($_SESSION['isParent']) {
				header('Location: parent.php?id=' . $userPage_id);
			}
		}
	}
?>