<?php
	session_start();
	
	if (isset($_GET['id'])) {
		
		//if no url id, then can't display anything
		$userPage_id = (int) $_GET['id'];
		
		$db2 = mysqli_connect('localhost', 'root', '', 'db2');
		
		$all_parent_id_query = "SELECT parent_id FROM parents";
		$all_parent_id_result = mysqli_query($db2, $all_parent_id_query);
		$in_all_parent_id = False;
		while($parent_id = mysqli_fetch_assoc($all_parent_id_result)) {
			if (in_array($userPage_id, $parent_id)) {
				$in_all_parent_id = True;
				break;
			}
		}
		
		if (isset($_SESSION['user']) && ($_SESSION['isParent'] || $_SESSION['isAdmin']) && $in_all_parent_id) {
			
			$current_user = $_SESSION['user'];
			$current_name = $current_user['name'];
			$current_id = $current_user['id'];
			
			if ($userPage_id == $current_id || $_SESSION['isAdmin']) {
				//if id is the logged in parent, show parents page; or if logged in as admin and we know its a parent id
				
				$userPage_name_query = "SELECT name FROM users WHERE id='$userPage_id' LIMIT 1";
				$userPage_name_result = mysqli_query($db2, $userPage_name_query);
				$userPage_name_arr = mysqli_fetch_assoc($userPage_name_result);
				$userPage_name = $userPage_name_arr['name'];
				
				$headerOutput = "<h1> Welcome $current_name!</h1>
								<h3><p> $userPage_name's parent page:</p></h3>";
				include('header.php');
				
				//update account info
				?>
				<html>
					<div class="page">
						<div class ="logout_btn">
							<a href="logout.php"><input type="button" value="Sign out" name="logout"></a>
						</div>
						<br>
						<form method="post" action="parent.php?id=<?php echo $userPage_id ?>">
							<?php include ('errors.php'); ?>
							<div class="input-group">
								<label>Update Name:		</label>
								<input type="text" name="name">
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
					$parent = mysqli_fetch_assoc($result);
					
					if($parent) {
						if($parent['email'] === $email) {
							array_push($errors, "Email already taken.");
						}
					}

					// CHECK TO SEE IF THERE ARE ANY EXISTING ERRORS
					if(count($errors) == 0) {
						// UPDATE THE PARENT INFORMATION INTO THE USERS TABLE
						$users_query = "UPDATE users SET email='$email', password='$password_1', name='$name', phone='$phone' WHERE id='$userPage_id'";
						mysqli_query($db2, $users_query);
						
						header('Location: parent.php?id=' . $userPage_id);
					}
					else {
						// IF THERE ARE ERRORS, DISPLAY THEM
						foreach($errors as $error) {
							print($error . "<br>");
						}
					}
				}
				
				//show all children as hrefs and direct to the students page
				$select_all_children_query = "SELECT * FROM users WHERE id IN (SELECT student_id FROM students WHERE parent_id = '$userPage_id')";
				$all_children_result = mysqli_query($db2, $select_all_children_query);
				
				?>
				<html>
						<h2><?php echo "$userPage_name's " ?>Children:</h2>
						<table border="1" style="width:75%">
						  <tr>
							<th>ID:</th>
							<th>Email:</th> 
							<th>Password:</th>
							<th>Name:</th>
							<th>Phone:</th>
							<th>Grade:</th>
							<th>Edit:</th>
						  </tr>
				</html>
				<?php
				
				//pass the id to the url; on the next page, make sure that id is in the array of users that was listed out on the previous page or person logged in
				
				$arr_id = [];
				while($row = $all_children_result->fetch_assoc()) {
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
					
					if ($isUserToUpdateAStudent) {
						echo '<td><a href="student.php?' . 'id=' . $row_id . '"> Edit Student </a></th>
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
			elseif ($_SESSION['isStudent']) {
				header('Location: student.php?id=' . $userPage_id);
			}
			elseif (!($in_all_parent_id)) {
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
				
				if ($in_all_admin_id) {
					header('Location: admin.php?id=' . $userPage_id);
				}
				elseif ($in_all_student_id) {
					header('Location: student.php?id=' . $userPage_id);
				}
			}
		}		
	}
?>