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
				
				$userPage_name_query = "SELECT name FROM users WHERE id='$userPage_id' LIMIT 1";
				$userPage_name_result = mysqli_query($db2, $userPage_name_query);
				$userPage_name_arr = mysqli_fetch_assoc($userPage_name_result);
				$userPage_name = $userPage_name_arr['name'];
				
				$headerOutput = "<h1> Welcome $current_name!</h1>
								<h3><p> $userPage_name's student page:</p></h3>";
				include('header.php');
				
				//update account info
				?>
				<html>
					<div class="page">
						<div class ="logout_btn">
							<a href="logout.php"><input type="button" value="Sign out" name="logout"></a>
						</div>
						<br>
						<form method="post" action="student.php?id=<?php echo $userPage_id ?>">
							<?php include ('errors.php'); ?>
							<div class="input-group">
								<label>Update Name:		</label>
								<input type="text" name="name">
							</div>
							<div class="input-group">
								<label>Update Grade:	</label>
								<input type="number" name="grade">
							</div>
							<div class="input-group">
								<label>Update Phone:	</label>
								<input type="text" name="phone">
							</div>
							<div class="input-group">
								<label>Update Email:	</label>
								<input type="email" name="email">
							</div>
							<div class="input-group">
								<label>Update Password:	</label>
								<input type="password" name="password_1">
							</div>
							<div class="input-group">
								<label>Confirm password:</label>
								<input type="password" name="password_2">
							</div>
							<div class="input-group">
								<button type="submit" class="btn" name="update_user">Submit</button>
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
				
				//show meetings student is mentor of
				//view & drop option for each
				
				?>
				<html>
						<h2>Meetings <?php echo $userPage_name ?> is a mentor of:</h2>
						<table border="1" style="width:75%">
						  <tr>
							<th>ID:</th>
							<th>Name:</th> 
							<th>Date:</th>
							<th>Time Slot:</th>
							<th>Capacity:</th>
							<th>Announcement:</th>
							<th>View Meeting:</th>
							<th>Drop Meeting:</th>
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
						</tr>";
				}
				
				?>
				<html>
						</table>
						<br>
				</html>
				<?php
				
				//show meetings student is mentee of
				//view & drop option for each
				
				?>
				<html>
						<h2>Meetings <?php echo $userPage_name ?> is a mentee of:</h2>
						<table border="1" style="width:75%">
						  <tr>
							<th>ID:</th>
							<th>Name:</th> 
							<th>Date:</th>
							<th>Time Slot:</th>
							<th>Capacity:</th>
							<th>Announcement:</th>
							<th>View:</th>
							<th>Drop:</th>
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
						</tr>";
				}
				
				?>
				<html>
						</table>
						<br>
					</div>
				</html>
				<?php
				
				
				
				
				//assert grade level requirements!
				
				//show possible meetings for student to be mentor of
				//add option for each (says rest of year option, but can only have 1 meeting at a time with same ID???)
				
				//get grade lvl and use it to get group student is in and that is where description = student grade
				//display meetings with group ids that have a description of my mentee_grade_req or below
				
				//$possible_meetings_mentor_of = "SELECT * FROM meetings INNER JOIN enroll2 ON enroll2.meet_id = meetings.meet_id WHERE mentor_id != '$userPage_id'";
				
				
				
				
				
				//show possible meetings for student to be mentee of
				//add option for each (says rest of year option, but can only have 1 meeting at a time with same ID???)
				
				//get grade lvl and use it to get group student is in and that is where description = student grade
				//display meetings with that group id
				
				//$possible_meetings_mentor_of = "SELECT * FROM meetings INNER JOIN enroll ON enroll.meet_id = meetings.meet_id WHERE mentee_id != '$userPage_id'";
				
				
					
					
				
				
				
				
				
			}
			else {
				?>
				<html>
					<div class="page">
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