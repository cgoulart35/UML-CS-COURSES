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
				
				//update account info
				?>
				<html>
					<div class="page">
						<div class ="logout_btn">
							<a href="logout.php"><input type="button" value="Sign out" name="logout"></a>
						</div>
						<br>
						<form method="post" action="admin.php?id=<?php echo $userPage_id ?>">
							<?php include ('errors.php'); ?>
							<div class="input-group">
								<label>Update Name:		</label>
								<input type="text" name="name" placeholder="<?php echo $userPage_name ?>">
							</div>
							<div class="input-group">
								<label>Update Phone:	</label>
								<input type="text" name="phone" placeholder="<?php echo $userPage_phone ?>">
							</div>
							<div class="input-group">
								<label>Update Email:	</label>
								<input type="email" name="email" placeholder="<?php echo $userPage_email ?>">
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
						<table border="1" style="width:75%">
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
						<table border="1" style="width:75%">
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