<?php 
/**
 PHP API for Login, Register
 **/
 
 if(isset($_POST['tag']) && $_POST['tag'] != ''){
	 //Get the tag
	 $tag = $_POST['tag'];
	 
	 //Include Databse handler
	 require_once 'include/DB_Functions.php';
	 $db = new DB_Functions();
	 //Response Array
	 $response = array("tag" => $tag, "success" => 0, "error" => 0);
	 
	 //Check for tag type
	 if($tag == 'login'){
		 //Request type is check login
		 $username = $_POST['username'];
		 $password = $_POST['password'];
		 
		 //check for user_error
		 $user = $db->getUser($username, $password);
		 //print_r($user);
		 if($user != false){
			 //user found
			 //echo json with success = 1
			 $response["success"] = 1;
			 $response["user"]["username"] = $user["username"];
			 //$response["user"]["password"] = $user["encrypted_passwd"];	
			 $response["user"]["created"] = $user["created"];	
			//print_r($response);
			 echo json_encode($response);
		 }else{
			 //user not found
			 //echo json with error = 1.
			 $response["error"] = 1; 
			 $response["error_msg"] = "Incorrect username or password!";
			 //print_r($response);
			 echo json_encode($response);
		 }		 
	 }else if ($tag == 'register'){
		 //Request type is Register new user
		 $username = $_POST['username'];
		 $password = $_POST['password'];
		 
		 $subject = "Registration";
		 $message = "Hello $username,nnYou have sucessfully registered to our service.nnRegards,nAdmin.";
         $from = "balah balah";
         $headers = "From:" . $from;
		 
		 //Check if user is already existed 
		 if($db->isUserExisted($username)){
			// user is already existed - error response
            $response["error"] = 2;
            $response["error_msg"] = "User already existed";
            echo json_encode($response);
		 }else{
			 //Store user
			 $user = $db->storeUser($username,$password);
			 //print_r($user);
			 if($user){
				 //user store successfully
				 $response["success"] = 1;
				 $response["user"]["username"] = $user["username"];
				 //$response["user"]["password"] = $user["encrypted_passwd"];
				 $response["user"]["created"] = $user["created"];	
				 //mail($email,$subject,$message,$headers);
				 
				 echo json_encode($response);
			 } else {
                // user failed to store
                $response["error"] = 1;
                $response["error_msg"] = "JSON Error occured in Registartion";
                echo json_encode($response);
			 }
		 }
	 }else if($tag == 'get_event'){
		 //Request type is get user schedule information
		 $username = $_POST['username'];
		 
		 $rset = $db->getSchedule($username);
		 //print_r($rset);
		 $num = count($rset);
		 //check for correct user's schedule
		 if($rset != false){
			 $response["success"] = 1;
			 $response["event"] = $rset;
			 //print_r($response);
			 echo json_encode($response);
		 }else{
			$response["error"] = 1;
            $response["error_msg"] = "JSON Error occured. No Event Found";
            echo json_encode($response);
		 }		 
	 
	}else if($tag == 'add_event'){
		//Request type is add user schedule information
		$username = $_POST['username'];
		$JSONEvent = $_POST['event'];
		
		if($db->getSingleSchedule($username,$JSONEvent)){
			//event exit
			$rset = $db->updateSchedule($username,$JSONEvent);
		}else{
			$rset = $db->insertSchedule($username,$JSONEvent);
		}
			//check for correct store user's schedule
		if($rset != false){
			$response["success"] = 1;
			$response["eventAdded"] = $rset;
			//print_r($response);
			echo json_encode($response);
		}else{
			$response["error"] = 1;
			$response["error_msg"] = "JSON Error occured. Cannot adding the event";
			echo json_encode($response);
		}
		
	}else if($tag == 'compute'){
		//Request type is add user schedule information
		$username = $_POST['username'];
		$time = $_POST['time'];
		
		$rset = $db->computeSchedule($username);
		 //print_r($rset);
		 $num = count($rset);
		 //check for correct user's schedule
		 if($rset != false){
			 $response["success"] = 1;
			 $response["event"] = $rset;
			 echo json_encode($response);
		 }else{
			$response["error"] = 1;
            $response["error_msg"] = "JSON Error occured. Cannot Compute the event";
            echo json_encode($response);
		}	
	}else if($tag == 'delete_event'){
		//Request type is add user schedule information
		$username = $_POST['username'];
		$JSONEvent = $_POST['event'];
		
		$rset = $db->deleteSchedule($username,$JSONEvent);
		 //print_r($rset);

		 //check for correct user's schedule
		 if($rset != false){
			 $response["success"] = 1;
			 $response["event"]["msg"] = "Successfully Delete";
			 echo json_encode($response);
		 }else{
			$response["error"] = 1;
            $response["error_msg"] = "JSON Error occured. Cannot Delete the event";
            echo json_encode($response);
		}	
	}		
 }else{
	echo "Time Management";
 }	
?>