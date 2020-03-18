<?php

	session_start();

	if (isset($_GET['mid']) && isset($_SESSION['user'])) {
		
		$current_user = $_SESSION['user'];
		$current_id = $current_user['id'];
		$current_name = $current_user['name'];
		
		$mid = $_GET['mid'];
		
		$db2 = mysqli_connect('localhost', 'root', '', 'db2');
		
		$select_mentor_users_of_meeting = "SELECT * FROM users INNER JOIN enroll2 ON enroll2.mentor_id = users.id WHERE meet_id = '$mid'";
		$select_mentor_users_of_meeting_result = mysqli_query($db2, $select_mentor_users_of_meeting);
		$is_mentor_of_meeting = False;
		while($mentor_id = mysqli_fetch_assoc($select_mentor_users_of_meeting_result)) {
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
		
		$select_mentee_users_of_meeting = "SELECT * FROM users INNER JOIN enroll ON enroll.mentee_id = users.id WHERE meet_id = '$mid'";
		$select_mentee_users_of_meeting_result = mysqli_query($db2, $select_mentee_users_of_meeting);
		$is_mentee_of_meeting = False;
		while($mentee_id = mysqli_fetch_assoc($select_mentee_users_of_meeting_result)) {
			if (in_array($current_id, $mentee_id)) {
				$is_mentee_of_meeting = True;
				break;
			}
		}
		
		$select_parents_of_mentees = "SELECT parent_id FROM students WHERE student_id IN (SELECT mentee_id FROM enroll WHERE meet_id = '$mid')";
		$select_parents_of_mentees_result = mysqli_query($db2, $select_parents_of_mentees);
		$is_parent_of_mentee = False;
		while($parent_id = mysqli_fetch_assoc($select_parents_of_mentees_result)) {
			if (in_array($current_id, $parent_id)) {
				$is_parent_of_mentee = True;
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
				<h2>Meeting Information:</h2>
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
			</tr>
			</table>";
			
		//if use is an admin, show option to post materials
		if ($_SESSION['isAdmin']) {
			?>
			<html>
					<h2>Post Materials:</h2>
					<form method="post" action="meeting.php?mid=<?php echo $mid ?>">
						<?php include ('errors.php'); ?>
						<div class="input-group">
							<label>Type:	</label>
							<input type="text" name="type">
						</div>
						<div class="input-group">
							<label>Notes:	</label>
							<input style="height:100px" type="text" name="notes">
						</div>
						<div class="input-group">
							<label>Title:	</label>
							<input type="text" name="title">
						</div>
						<div class="input-group">
							<label>Author:	</label>
							<input type="text" name="author">
						</div>
						<div class="input-group">
							<label>URL:		</label>
							<input type="url" name="url">
						</div>
						<div class="input-group">
							<button type="submit" class="btn" name="post_materials">Post</button>
						</div>
					</form>
			</html>
			<?php
			
			// IF POST IS CLICKED ON THE FORM
			if (isset($_POST['post_materials'])) {
				
				// GET THE FORM VALUES
				$type = mysqli_real_escape_string($db2, $_POST['type']);
				$notes = mysqli_real_escape_string($db2, $_POST['notes']);
				$title = mysqli_real_escape_string($db2, $_POST['title']);
				$author = mysqli_real_escape_string($db2, $_POST['author']);
				$url = mysqli_real_escape_string($db2, $_POST['url']);
				date_default_timezone_set('America/New_York');
				$assigned_date = date('Y-m-d');
				
				// CHECK IF ANY VALUES ARE EMPTY, AND IF PASSWORDS AREN'T EQUAL
				if(empty($type)) { array_push($errors, "Enter a type."); }
				if(empty($notes)) { array_push($errors, "Enter notes."); }
				if(empty($title)) { array_push($errors, "Enter a title."); }

				// CHECK TO SEE IF THERE ARE ANY EXISTING ERRORS
				if(count($errors) == 0) {
					
					// INSERT THE MATERIAL INFORMATION INTO THE MATERIAL TABLE
					$add_material_query = "INSERT INTO material (title, author, type, url, assigned_date, notes) VALUES('$title', '$author', '$type', '$url', '$assigned_date', '$notes')";
					$stmt = mysqli_prepare($db2, $add_material_query);
					mysqli_stmt_execute($stmt);
					$id_of_material = mysqli_stmt_insert_id($stmt);
					
					// INSERT THE MATERIAL ID AND MEETING ID INTO THE ASSIGN TABLE
					$assign_query = "INSERT INTO assign (meet_id, material_id) VALUES('$mid', '$id_of_material')";
					mysqli_query($db2, $assign_query);
					
					header('Location: meeting.php?mid=' . $mid);
				}
				else {
					// IF THERE ARE ERRORS, DISPLAY THEM
					foreach($errors as $error) {
						print($error . "<br>");
					}
				}
			}
			
		}
		
		//show material of the meeting if logged in as mentor, mentee, parent of mentee, parent of mentor, or admin
		if ($is_mentor_of_meeting || $is_mentee_of_meeting || ($_SESSION['isParent'] && ($is_parent_of_mentor || $is_parent_of_mentee)) || $_SESSION['isAdmin']) {
			
			?>
			<html>
					<h2>Meeting Material:</h2>
					<table border="1" style="width:75%">
					  <tr>
						<th>Type:</th>
						<th>Notes:</th> 
						<th>Title:</th> 
						<th>Author:</th> 
						<th>URL:</th> 
						<th>Assigned Date:</th> 
					  </tr>
			</html>
			<?php
			
			$meeting_materials_query = "SELECT * FROM material WHERE material_id IN (SELECT material_id FROM assign WHERE meet_id = $mid)";
			$meeting_materials_result = mysqli_query($db2, $meeting_materials_query);
			
			while($row = $meeting_materials_result->fetch_assoc()) {
				$row_title = $row['title'];
				$row_author = $row['author'];
				$row_type = $row['type'];
				$row_url = $row['url'];
				$row_assigned_date = $row['assigned_date'];
				$row_notes = $row['notes'];
			
				echo "<tr>
						<td>$row_type</th>
						<td>$row_notes</th> 
						<td>$row_title</th> 
						<td>$row_author</th> 
						<td><a href=$row_url>$row_url</a></th> 
						<td>$row_assigned_date</th> 
					</tr>";
			}
		}		
		
		//show mentors and mentees (name & email) if logged in as mentor or parent of mentor or admin
		if ($is_mentor_of_meeting || ($_SESSION['isParent'] && $is_parent_of_mentor) || $_SESSION['isAdmin']) {
			?>
			<html>
					</table>
					<h2>Mentors:</h2>
					<table border="1" style="width:75%">
					  <tr>
						<th>Name:</th>
						<th>Email:</th> 
					  </tr>
			</html>
			<?php
			
			mysqli_data_seek($select_mentor_users_of_meeting_result, 0);
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
					<h2>Mentees:</h2>
					<table border="1" style="width:75%">
					  <tr>
						<th>Name:</th>
						<th>Email:</th> 
					  </tr>
			</html>
			<?php
			
			mysqli_data_seek($select_mentee_users_of_meeting_result, 0);
			while($row = $select_mentee_users_of_meeting_result->fetch_assoc()) {
				$row_name = $row['name'];
				$row_email = $row['email'];
			
				echo "<tr>
						<td>$row_name</th>
						<td>$row_email</th> 
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