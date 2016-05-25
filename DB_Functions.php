<?php
class DB_Functions{
	private $db;
	
	//Constructor.
	function __construct(){
		require_once 'DB_Connect.php';
		//connecting to database.
		$this->db = new DB_Connect();
		$this->db->connect();
	}
	
	//Destructor.
	function __destruct(){
		
	}
	
	//Adding new user to mysql database, and return user details.
	public function storeUser($username,$password){
		$encrypted_password  = $this->hashSSHA($password); //encrypted password.
		
		$result = mysql_query("INSERT INTO user(username,encrypted_passwd,created) VALUES('$username','$encrypted_password', NOW())");
		if($result){
			//get user details
			//$uid = mysql_insert_id(); //last inserted id
			$result = mysql_query("SELECT * FROM user WHERE username = '$username'");
			//print($username);
			//return user details.
			return @mysql_fetch_array($result);
		}else{
			return false;
		}
	}
	
	//Verifies user by username and password.
	public function getUser($username,$password){
		$result = mysql_query("SELECT * FROM user where username = '$username'") or die(mysql_error());
		//Check for result
		$numRows = mysql_num_rows($result);
		//print_r($numRows);
		if($numRows > 0){
			$result = @mysql_fetch_array($result);
			//print_r($result);
			$encrypted_password = $result['encrypted_passwd'];
			//check for password equality.
			if($encrypted_password == crypt($password, $encrypted_password)){
				return $result; //user authentication are correct.
			}else{
				return false;	//user not found.
			}
		}
	}
	
	//Check user is existed or not.
	public function isUserExisted($username){
		$result = mysql_query("SELECT * FROM user WHERE username LIKE '$username'");
		$numRows = mysql_num_rows($result);
		if($numRows > 0){
			return true;	//user existed.
		}else{
			return false;	//user not existed.
		}
	}
	
	//Get user schedule information/
	public function getSchedule($username){
		//$result = mysql_query("SELECT (event_num) id, (event_title) title,location,(s_date) startDate,(e_date) endDate, color FROM event WHERE ev_uname LIKE '$username'") or die(mysql_error());
		$result = mysql_query("SELECT * FROM event WHERE ev_uname LIKE '$username'") or die(mysql_error());
		
		//Check for result
		$numRows = mysql_num_rows($result);
		$array_event = array();
		if($numRows > 0){
			//$result = @mysql_fetch_array($result);
			while($array = mysql_fetch_assoc($result)){			
			$array_event[]=$array;
			}
			//print_r($array_event);
			return $array_event; //user are correct.
			//return $result;
		}else{
			return false;	//user not found.
		}
		//}
	}
	
	//Get user schedule information/
	public function getSingleSchedule($username,$value){
		$data = json_decode($value,true);
		$id = $data['event_num'];
		
		$result = mysql_query("SELECT * FROM event WHERE ev_uname LIKE '$username' and event_num = '$id'") or die(mysql_error());
		$numRows = mysql_num_rows($result);
		if($numRows > 0){
			return true;	//event existed.
		}else{
			return false;	//event not existed.
		}
	}
	
	//Insert user schedule information
	public function insertSchedule($username,$value){
		$data = json_decode($value,true);
		$id = $data['event_num'];
		$title = $data['event_title'];
		$location = $data['location'];
		$s_date = $data['s_date'];
		$e_date = $data['e_date'];
		$done_date = $data['done_date'];
		$allDay = $data['all_day'];
		$note = $data['notes'];	
		$priority = $data['priority'];
		$repeat = $data['days'];
		$color = $data['color'];
		$course = $data['course'];
		if(array_key_exists('super_event_id',$data)){
			$super_event_id = $data['super_event_id'];
			$sql = "INSERT INTO event (ev_uname,event_num,event_title,location,s_date,e_date,done_date,all_day,notes,priority,days,color,course,super_event_id) VALUES ('$username','$id','$title','$location','$s_date','$e_date','$done_date','$allDay','$note','$priority','$repeat','$color','$course','$super_event_id')";
		}else{
			$sql = "INSERT INTO event (ev_uname,event_num,event_title,location,s_date,e_date,done_date,all_day,notes,priority,days,color,course) VALUES ('$username','$id','$title','$location','$s_date','$e_date','$done_date','$allDay','$note','$priority','$repeat','$color','$course')";
		}
		
		//echo $repeat,'repeat'."<br/>";
		//$result = mysql_query("INSERT INTO event (ev_uname,event_num,event_title,location,s_date,e_date,done_date,all_day,notes,priority,days,color,course) VALUES ('$username','$id','$title','$location','$s_date','$e_date','$done_date','$allDay','$note','$priority','$repeat','$color','$course') ");
		if($repeat == null){
			//echo empty($repeat),"<br/>";
			$result = mysql_query($sql) or die('Invalid query: ' . mysql_error());
		}else{
			
			$sDate = new DateTime($s_date);
			$dDate = new DateTime($done_date);
			$eDate = new DateTime($e_date);
			if($done_date == null){
				$diff = 30;
			}else{				
				$diff = (int)date_diff($sDate,$dDate)->format('%a');
			}	
			//echo $diff, "<br />";
			$result = mysql_query($sql) or die('Invalid query: ' . mysql_error());
			
			for($i = 0; $i < $diff;$i++){
				$sDate = $sDate->add(DateInterval::createFromDateString('1 day'));
				$eDate = $eDate->add(DateInterval::createFromDateString('1 day'));
				//echo $sDate->format('Y-m-d H:i:s'), "<br />";
				//echo $eDate->format('Y-m-d H:i:s'), "<br />";
				//echo $sDate->format('D'), "<br />";
				//echo "increment of $i", "<br />";
				switch($sDate->format('D')){
					case 'Mon':
						$match = 'M';
						break;
					case 'Tue':
						$match = 'T';
						break;
					case 'Wed':
						$match = 'W';
						break;
					case 'Thu':
						$match = 'R';
						break;
					case 'Fri':
						$match = 'F';
						break;
					case 'Sat':
						$match = 'A';
						break;
					case 'Sun':
						$match = 'S';
						break;
				}
				
				//echo $match, "<br />";
				//echo$repeat, "<br />";
				if(strpos($repeat,$match) !== false){
					//echo strpos($repeat,$match), "<br />";
					//echo "MATCH_STRING", "<br />";
					$newEDate = $eDate->format('Y-m-d H:i:s');
					$newSDate = $sDate->format('Y-m-d H:i:s');
					$newId = rand();
					$result = mysql_query("INSERT INTO event (ev_uname,event_num,event_title,location,s_date,e_date,done_date,all_day,notes,priority,days,color,course,super_event_id) VALUES ('$username','$newId','$title','$location','$newSDate','$newEDate','$done_date','$allDay','$note','$priority','$repeat','$color','$course','$id')") or die('Invalid query: ' . mysql_error());
					if($result){
						//echo "SUCCESS", "<br />";
					}else{
						//echo "FAIL TO INSERT", "<br />";
					}
				}
			}			
		}
		
		if($result){
			//get user details
			//$uid = mysql_insert_id(); //last inserted id
			$result = mysql_query("SELECT * FROM event WHERE ev_uname = '$username' and event_num = '$id'");
			//print($username);
			//return user details.
			return @mysql_fetch_array($result);
		}else{
			return false;
		}
		
		
	}
	
	//Update user schedule information
	public function updateSchedule($username,$value){
		$data = json_decode($value,true);
		$id = $data['event_num'];
		$title = $data['event_title'];
		$location = $data['location'];
		$s_date = $data['s_date'];
		$e_date = $data['e_date'];
		$done_date = $data['done_date'];
		$allDay = $data['all_day'];
		$note = $data['notes'];	
		$priority = $data['priority'];
		$repeat = $data['days'];
		$color = $data['color'];
		$course = $data['course'];
		if(array_key_exists('super_event_id',$data)){
			$super_event_id = $data['super_event_id'];
		}else{
			$super_event_id = null;
		}
		
		$result = mysql_query("UPDATE event SET event_title = '$title',location = '$location',s_date = '$s_date',e_date = '$e_date',done_date = '$done_date',all_day = '$allDay',notes = '$note',priority = '$priority',days = '$repeat',color='$color',course='$course' WHERE ev_uname = '$username' and event_num = '$id' ");
		
		if($result){
			//get user details
			//$uid = mysql_insert_id(); //last inserted id
			$result = mysql_query("SELECT * FROM event WHERE ev_uname = '$username' and event_num = '$id'");
			//print($username);
			//return user details.
			return @mysql_fetch_array($result);
		}else{
			return false;
		}
	}
	
	//Update user schedule information
	public function deleteSchedule($username,$id){
		
		$result = mysql_query("DELETE FROM event WHERE ev_uname = '$username' and event_num = '$id' ");
		$check = mysql_query("SELECT * FROM event WHERE ev_uname = '$username' and event_num = '$id'");
		$numRows = mysql_num_rows($check);
		if($numRows > 0){
			return false;	//cannot delete.
		}else{
			return true;	//delete successfully.
		}
	}
	
	//Update user schedule information
	public function computeSchedule($username,$time){
		$timeBlock = new DateTime($time);
		$timeEndOfDay = new DateTime('23:00');
		$numRows = 0;
		$result =  mysql_query("SELECT * FROM event WHERE ev_uname = '$username' and course = 1 and super_event_id is null ")or die('Invalid query1: ' . mysql_error());
		//print_r($result);//test
		if(mysql_num_rows($result)>0){

			//Check if there is old study schedule
			$check = $check = mysql_query("SELECT * FROM event WHERE ev_uname = '$username' and course = 0 and super_event_id in (SELECT event_num FROM event where ev_uname = '$username' and course = 1 and super_event_id is null) ")or die('Invalid query2: ' . mysql_error());
			$numRows = mysql_num_rows($check);
			
			if(mysql_num_rows($check) > 0){
				
				//Delete all the old study event and create new schedule
				$oldStudySchedule = mysql_query("DELETE FROM event WHERE ev_uname = '$username' and course = 0 and super_event_id in (SELECT event_num FROM (SELECT event_num FROM event where ev_uname = '$username' and course = 1 and super_event_id is null) as id) ")or die('Invalid query3: ' . mysql_error());
				if(mysql_affected_rows()>0){	//If successful delete, then update new study schedule
					while($row = mysql_fetch_assoc($result)){
						$newEvent = $this->computeTime($row,$timeBlock);
						$this->insertSchedule($username,$newEvent);
					}
					return true;
				}else{
					return false; //cannot update new study schedule
				}
				
			}else{	//If there is none any old schedule, create a new one
				echo "ready  to insert2","<br/>";
				while($row = mysql_fetch_assoc($result)){
					$newEvent = $this->computeTime($row,$timeBlock);
					$this->insertSchedule($username,$newEvent);
				}
				return true;
			}	
			
		}else{
			return false;
		}
	}
	//Compute the study Time
	public function computeTime1($event,$timeBlock){
		$copy = $timeBlock;
		while($row = mysql_fetch_assoc($event)){
			$next = mysql_fetch_assoc($event);
			while(new DateTime($row['s_date'])->format('Y-m-d') === new DateTime($next['s_date'])->format('Y-m-d')){
				if($row['priority'] > $next['priority']){					
					$this->computeTime($row,$copy);
					$copy = $copy->setTime(new DateTime($row['s_date'])->format('H')
				}
			}
			switch($event['event_num']){
				
			}
			$newEvent = $this->computeTime($row,$timeBlock);
		}
		$username = $event['ev_uname'];
		$event['super_event_id'] = $event['event_num'];
		
		$eDate = new DateTime($event['e_date']);
		$sDate = new DateTime($event['s_date']);
		$diff = date_diff($eDate,$sDate);
		$duration = ($diff->h+$diff->i/60);
		$newTime = round($duration*2);
		
		/** If there is empty slot after $timeBlock. */
		
		//Generat new study event.		
		$newTitle = 'Study for '.$event['event_title'];
		$newId = rand();
		$newSDate = $sDate->setTime($timeBlock->format('H'),$timeBlock->format('i'))->format('Y-m-d H:i:s');
		$newEDate = $eDate->setTime($timeBlock->format('H')+$newTime,$timeBlock->format('i'))->format('Y-m-d H:i:s');
		
		$event['event_title'] = $newTitle;
		$event['event_num'] = $newId;
		$event['s_date'] = $newSDate;
		$event['e_date'] = $newEDate;
		$event['location'] = '';
		$event['course'] = 0;
		
		
		print_r($event);
		$newEvent = json_encode($event);
		//echo $newEvent;
		
		return $newEvent;
	}
	
	//Compute the study Time
	public function computeTime($event,$timeBlock){
		$username = $event['ev_uname'];
		$event['super_event_id'] = $event['event_num'];
		
		$eDate = new DateTime($event['e_date']);
		$sDate = new DateTime($event['s_date']);
		$diff = date_diff($eDate,$sDate);
		$duration = ($diff->h+$diff->i/60);
		$newTime = round($duration*2);
		
		/** If there is empty slot after $timeBlock. */
		
		//Generat new study event.		
		$newTitle = 'Study for '.$event['event_title'];
		$newId = rand();
		$newSDate = $sDate->setTime($timeBlock->format('H'),$timeBlock->format('i'))->format('Y-m-d H:i:s');
		$newEDate = $eDate->setTime($timeBlock->format('H')+$newTime,$timeBlock->format('i'))->format('Y-m-d H:i:s');
		
		$event['event_title'] = $newTitle;
		$event['event_num'] = $newId;
		$event['s_date'] = $newSDate;
		$event['e_date'] = $newEDate;
		$event['location'] = '';
		$event['course'] = 0;
		
		
		print_r($event);
		$newEvent = json_encode($event);
		//echo $newEvent;
		
		return $newEvent;
	}
	
	//Encrupting password. Returns salt and encrypted password.
	public function hashSSHA($password){
		return crypt($password);
	}
	
}
?>