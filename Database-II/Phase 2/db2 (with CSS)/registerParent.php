<html>
	<?php
		$headerOutput = "<h1> Welcome to Database II Project!</h1>
						<h3><p> Register as a parent:</p></h3>";
		include ('header.php'); 
	?>
	<div class="page">
		<form method="post" action="registerParent.php">
			<?php include ('errors.php'); ?>
			<div>
				<h1><a href="index.php"> Home </a></h1>
			</div>
			<div class="input-group">
				<label>Name:			</label>
				<input type="text" name="name">
			</div>
			<div class="input-group">
				<label>Phone:			</label>
				<input type="text" name="phone">
			</div>
			<div class="input-group">
				<label>Email:			</label>
				<input type="email" name="email">
			</div>
			<div class="input-group">
				<label>Password:		</label>
				<input type="password" name="password_1">
			</div>
			<div class="input-group">
				<label>Confirm password:</label>
				<input type="password" name="password_2">
			</div>
			<div class="input-group">
				<button type="submit" class="btn" name="reg_user">Sign Up</button>
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
			// INSERT THE PARENT INFORMATION INTO THE USERS TABLE
			$users_query = "INSERT INTO users (email, password, name, phone) VALUES('$email', '$password_1', '$name', '$phone')";
			$stmt = mysqli_prepare($db2, $users_query);
			mysqli_stmt_execute($stmt);
			$id_of_parent = mysqli_stmt_insert_id($stmt);
			
			// INSERT THE PARENT INFORMATION INTO THE PARENTS TABLE
			$parent_query = "INSERT INTO parents (parent_id) VALUES('$id_of_parent')";
			mysqli_query($db2, $parent_query);
		}
		else {
			// IF THERE ARE ERRORS, DISPLAY THEM
			foreach($errors as $error) {
				print($error . "<br>");
			}
		}
	}

?>