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
		
	}else if($tag == 'compute_event'){
		//Request type is add user schedule information
		$username = $_POST['username'];
		$time = $_POST['event'];
		//echo new DateTime();
		$rset = $db->computeSchedule($username,$time);
		 //print_r($rset);
		 //$num = count($rset);
		 //check for correct user's schedule
		 if($rset != false){
			 $response["success"] = 1;
			 ///$response["event"] = $rset;
			 echo json_encode($response);
		 }else{
			$response["error"] = 1;
            $response["error_msg"] = "JSON Error occured. Cannot Compute the event";
            echo json_encode($response);
		}	
		
	}else if($tag == 'delete_event'){
		//Request type is add user schedule information
		$username = $_POST['username'];
		$id = $_POST['event'];
		
		$rset = $db->deleteSchedule($username,$id);
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
	echo "Time Management ", "<br />";
	//$a['id'] = '123';
	//echo 'study for '.$a['id'];
	/**
	//date('D',$date1)
	//$date1 = new DateTime();
	$date1 = new DateTime("2016-05-01 09:15:00");
	$date2 = new DateTime("2016-05-10 10:30:00");
	//$diff = $date1->diff($date2);
	$diff = date_diff($date1,$date2);
	//echo $diff->h+$diff->i/60;
	echo $diff->format('%a'), "<br />";
	if((int)$diff->format('%a') < 10)
		echo "yes";
	for($i = 1 ; $i < 8;$i++){
	echo $date1->add(DateInterval::createFromDateString('1 day'))->format('Y-m-d H:i:s'), "<br />";
		if($date1->format('D') == 'Tue'){
			echo "yes: ".$i;
		}
		echo rand(),"<br />";
	}
	echo (string)$date1->format('D'), "<br />";
	//$letter = "MWF";
	//$m = "W";
	//if(strpos($letter,$m)){
	//	echo "yes", "<br />";
	//}
	//ini_set("precision",25);
	//$id = 4381979113977629696;
	//$va = 9223372036854775808;
	//echo $id, "<br />";
	//echo $va, "<br />";
	//echo uniqid(rand());
	//$d = '';
	//echo empty($d);
	//$time = '17:00';
	//$d56 = new DateTime($time);
	///echo $d56->format('H:i:s'),"<br/>";
	//if($date2->format('H:i:s')<$d56->format('H:i:s')){
		
	//	echo $date2->format('H:i:s'),"<br/>";
	//	echo $d56->format('H:i:s'),"<br/>";
	//	echo true,"<br/>";
	//else{
	//	echo false;
	//}
	//Include Databse handler
	 //require_once 'include/DB_Functions.php';
	 //$db = new DB_Functions();
	//$count = $db->computeTime();
	//echo $count;
	//$j = 1.2;
	//echo round($j);
	//$k = 2;
	//echo $date1->setTime($d56->format('H'),$d56->format('i'))->format('Y-m-d H:i:s'),"<br/>";
	//echo $date1->setTime($d56->format('H')+$k,$d56->format('i'))->format('Y-m-d H:i:s'),"<br/>";
	*/
 }	
?>