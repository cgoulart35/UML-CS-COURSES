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
					</div>
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
				
				
				
				
				
				//TO DO:
				//show meetings student is mentor of
				//drop option for each
				//show all mentors in each meeting mentor of (names & emails)
				//show all mentees in each meeting mentor of (names & emails)
				//show materials (use assign) & time_slot
				
				//show meetings student is mentee of
				//drop option for each
				//show materials (use assign) & time_slot
				
				//show possible meetings for student to be mentor of
				//add option for each (says rest of year option, but can only have 1 meeting at a time with same ID???)
				
				//show possible meetings for student to be mentee of
				//add option for each (says rest of year option, but can only have 1 meeting at a time with same ID???)
				
				
					
					
				
				
				
				
				
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