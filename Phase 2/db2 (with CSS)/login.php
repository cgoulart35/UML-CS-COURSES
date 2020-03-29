<html>
	<?php
		$headerOutput = "<h1> Welcome to Database II Project!</h1>
						<h3><p> Login Page:</p></h3>";
		include ('header.php'); 
	?>
	<div class="page">
		<form method="post" action="login.php">
			<div>
				<h1><a href="index.php"> Home </a></h1>
			</div>
			<?php include ('errors.php'); ?>
			<div class="input-group">
				<label>Email:	</label>
				<input type="email" name="email">
			</div>
			<div class="input-group">
				<label>Password:</label>
				<input type="password" name="password">
			</div>
			<div class="input-group">
				<button type="submit" class="btn" name="sign_in">Sign In</button>
			</div>
			<br>
			<div>
				Want to register as a student? <a href="registerStudent.php"> Sign Up Student </a>
			</div>
			<div>
				Want to register as a parent? <a href="registerParent.php"> Sign Up Parent </a>
			</div>
			<div>
				Want to register as an admin? <a href="registerAdmin.php"> Sign Up Admin </a>
			</div>
		</form>
	</div>
</html>

<?php
	session_start();
	$db2 = mysqli_connect('localhost', 'root', '', 'db2');
	
	// IF SUBMIT IS CLICKED ON THE FORM
	if (isset($_POST['sign_in'])) {
		$email = mysqli_real_escape_string($db2, $_POST['email']);
		$password = mysqli_real_escape_string($db2, $_POST['password']);
		
		if(empty($email)) { array_push($errors, "Enter a email."); }
		if(empty($password)) { array_push($errors, "Enter a password."); }
		
		$email_exists_query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
		$result = mysqli_query($db2, $email_exists_query);
		$user = mysqli_fetch_assoc($result);
		
		if(!$user) {
				array_push($errors, "User does not exist.");
		}
		else {
			if(!($user['password'] === $password)) {
				array_push($errors, "Password is incorrect.");
			}
		}
		
		// CHECK TO SEE IF THERE ARE ANY EXISTING ERRORS
		if(count($errors) == 0) {			
			
			// SAVE THE USER AS A SESSION VARIABLE
			$_SESSION['user'] = $user;
			//$_SESSION['userToUpdate'] = $user;
			$id = (int) $user['id'];
			
			// CHECK TO SEE WHETHER THE USER IS A STUDENT, PARENT, OR AN ADMIN AND NAVIGATE TO THE CORRECT PAGE
			$is_student_query = "SELECT * FROM students WHERE student_id = '$id' LIMIT 1";
			$student_result = mysqli_query($db2, $is_student_query);
			$isStudent = mysqli_fetch_assoc($student_result);
			$_SESSION['isStudent'] = $isStudent;
			
			$is_parent_query = "SELECT * FROM parents WHERE parent_id = '$id' LIMIT 1";
			$parent_result = mysqli_query($db2, $is_parent_query);
			$isParent = mysqli_fetch_assoc($parent_result);
			$_SESSION['isParent'] = $isParent;
			
			$is_admin_query = "SELECT * FROM admins WHERE admin_id = '$id' LIMIT 1";
			$admin_result = mysqli_query($db2, $is_admin_query);
			$isAdmin = mysqli_fetch_assoc($admin_result);
			$_SESSION['isAdmin'] = $isAdmin;
			
			if ($isStudent) {
				header('Location: student.php?id=' . $id);
			}
			elseif ($isParent) {
				header('Location: parent.php?id=' . $id);
			}
			elseif ($isAdmin) {
				header('Location: admin.php?id=' . $id);
			}
			
		}
		else {
			// IF THERE ARE ERRORS, DISPLAY THEM
			foreach($errors as $error) {
				print($error . "<br>");
			}
		}
	}
?>