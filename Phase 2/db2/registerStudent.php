<html>
	<?php
		$headerOutput = "<h1> Welcome to Database II Project!</h1>
						<h3><p> Register as a student:</p></h3>";
		include ('header.php'); 
	?>
	<div>
		<form method="post" action="registerStudent.php">
			<?php include ('errors.php'); ?>
			<div>
				<h1><a href="index.php"> Home </a></h1>
			</div>
			<div>
				<label>Name:			</label>
				<input type="text" name="name">
			</div>
			<div>
				<label>Grade:			</label>
				<input type="number" name="grade">
			</div>
			<div>
				<label>Phone:			</label>
				<input type="text" name="phone">
			</div>
			<div>
				<label>Email:			</label>
				<input type="email" name="email">
			</div>
			<div>
				<label>Parent Email:	</label>
				<input type="text" name="parent_email">
			</div>
			<div>
				<label>Password:		</label>
				<input type="password" name="password_1">
			</div>
			<div>
				<label>Confirm password:</label>
				<input type="password" name="password_2">
			</div>
			<div>
				<button type="submit" name="reg_user">Sign Up</button>
			</div>
			<br>
			<div>
				Want to login? <a href="login.php"> Sign In </a>
			</div>
		</form>
	</div>
</html>

<?php

	// CONNECT TO DB2
	session_start();
	$db2 = mysqli_connect('localhost', 'root', '', 'db2');

	// IF SUBMIT IS CLICKED ON THE FORM
	if (isset($_POST['reg_user'])) {
		
		// GET THE FORM VALUES
		$name = mysqli_real_escape_string($db2, $_POST['name']);
		$grade = (int) mysqli_real_escape_string($db2, $_POST['grade']);
		$phone = mysqli_real_escape_string($db2, $_POST['phone']);
		$parent_email = mysqli_real_escape_string($db2, $_POST['parent_email']);
		$email = mysqli_real_escape_string($db2, $_POST['email']);
		$password_1 = mysqli_real_escape_string($db2, $_POST['password_1']);
		$password_2 = mysqli_real_escape_string($db2, $_POST['password_2']);
		
		// CHECK IF ANY VALUES ARE EMPTY, AND IF PASSWORDS AREN'T EQUAL
		if(empty($name)) { array_push($errors, "Enter a name."); }
		if(empty($grade)) { array_push($errors, "Enter a grade."); }
		if(empty($phone)) { array_push($errors, "Enter a phone number."); }
		if(empty($parent_email)) { array_push($errors, "Enter a parent email."); }
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
		
		// CHECK TO SEE IF USER WITH THIS EMAIL EXISTS FOR A PARENT
		$parent_email_query = "SELECT * FROM users WHERE email = '$parent_email' LIMIT 1";
		$result2 = mysqli_query($db2, $parent_email_query);
		$parent = mysqli_fetch_assoc($result2);
		
		if(!$parent) {
			array_push($errors, "Email does not exist.");
		}
		else {
			// CHECK TO SEE IF USER WITH THIS EMAIL IS A PARENT IN THE PARENT TABLE
			$id = $parent['id'];
			$parent_id_query = "SELECT * FROM parents WHERE parent_id = '$id' LIMIT 1";
			$result3 = mysqli_query($db2, $parent_id_query);
			$id_of_parent = mysqli_fetch_assoc($result3);
			
			if(!$id_of_parent) {
				array_push($errors, "Parent email does not exist.");
			}
		}
		
		// CHECK TO SEE IF THERE ARE ANY EXISTING ERRORS
		if(count($errors) == 0) {			
			$id_of_parent2 = (int) $id_of_parent['parent_id'];
			
			// INSERT THE STUDENT INFORMATION INTO THE USERS TABLE
			$users_query = "INSERT INTO users (email, password, name, phone) VALUES('$email', '$password_1', '$name', '$phone')";
			$stmt = mysqli_prepare($db2, $users_query);
			mysqli_stmt_execute($stmt);
			$id_of_student = mysqli_stmt_insert_id($stmt);
			
			// INSERT THE STUDENT AND PARENT INFORMATION INTO THE STUDENTS TABLE
			$student_query = "INSERT INTO students (student_id, grade, parent_id) VALUES('$id_of_student', '$grade', '$id_of_parent2')";
			mysqli_query($db2, $student_query);
		}
		else {
			// IF THERE ARE ERRORS, DISPLAY THEM
			foreach($errors as $error) {
				print($error . "<br>");
			}
		}
	}

?>