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
		$id = $data['id'];
		
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
		$id = $data['id'];
		$title = $data['title'];
		$location = $data['location'];
		$s_date = $data['startTime'];
		$e_date = $data['endTime'];
		$done_date = $data['doneDate'];
		$allDay = $data['allDay'];
		$note = $data['note'];	
		$priority = $data['priority'];
		$repeat = $data['days'];
		$color = $data['color'];
		$course = $data['course'];
		
		if($repeat == null){
			//echo empty($repeat),"<br/>";
			$result = mysql_query("INSERT INTO event (ev_uname,event_num,event_title,location,s_date,e_date,done_date,all_day,notes,priority,days,color,course) VALUES ('$username','$id','$title','$location','$s_date','$e_date','$done_date','$allDay','$note','$priority','$repeat','$color','$course')") or die('Invalid query: ' . mysql_error());
		}else{
			
			$sDate = new DateTime($s_date);
			$dDate = new DateTime($done_date);
			$eDate = new DateTime($e_date);
			if($done_date == null){
				$diff = 30;
			}else{				
				$diff = (int)date_diff($sDate,$dDate)->format('%a');
			}	
			echo $diff, "<br />";
			$result = mysql_query("INSERT INTO event (ev_uname,event_num,event_title,location,s_date,e_date,done_date,all_day,notes,priority,days,color,course) VALUES ('$username','$id','$title','$location','$s_date','$e_date','$done_date','$allDay','$note','$priority','$repeat','$color','$course')") or die('Invalid query: ' . mysql_error());
			
			for($i = 0; $i < $diff;$i++){
				$sDate = $sDate->add(DateInterval::createFromDateString('1 day'));
				$eDate = $eDate->add(DateInterval::createFromDateString('1 day'));
				echo $sDate->format('Y-m-d H:i:s'), "<br />";
				echo $eDate->format('Y-m-d H:i:s'), "<br />";
				echo $sDate->format('D'), "<br />";
				echo "increment of $i", "<br />";
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
				
				echo $match, "<br />";
				echo$repeat, "<br />";
				if(strpos($repeat,$match) !== false){
					echo strpos($repeat,$match), "<br />";
					echo "MATCH_STRING", "<br />";
					$newEDate = $eDate->format('Y-m-d H:i:s');
					$newSDate = $sDate->format('Y-m-d H:i:s');
					$newId = rand();
					$result = mysql_query("INSERT INTO event (ev_uname,event_num,event_title,location,s_date,e_date,done_date,all_day,notes,priority,days,color,course,super_event_id) VALUES ('$username','$newId','$title','$location','$newSDate','$newEDate','$done_date','$allDay','$note','$priority','$repeat','$color','$course','$id')") or die('Invalid query: ' . mysql_error());
					if($result){
						echo "SUCCESS", "<br />";
					}else{
						echo "FAIL TO INSERT", "<br />";
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
		$id = $data['id'];
		$title = $data['title'];
		$location = $data['location'];
		$s_date = $data['startTime'];
		$e_date = $data['endTime'];
		$done_date = $data['doneDate'];
		$allDay = $data['allDay'];
		$note = $data['note'];	
		$priority = $data['priority'];
		$repeat = $data['days'];
		$color = $data['color'];
		$course = $data['course'];
		
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
		//$data = json_decode($value,true);
		//$id = $data['id'];
		
		$result = mysql_query("DELETE FROM event WHERE ev_uname = '$username' and event_num = '$id' ");
		//$check = mysql_query("SELECT * FROM event WHERE ev_uname = '$username' and event_num = '$id'");
		//$numRows = mysql_num_rows($check);
		if($result){
			return true;	//delete successfully.
		}else{
			return false;	//cannot delete.
		}
	}
	
	//Update user schedule information
	public function computeSchedule($username,$time){
		
	}
	
	//Compute the study Time
	public function computeTime($result){
		//$name = 'admin';
		//$id = '3388240525438111101';
		//$result =  mysql_query("SELECT * FROM event WHERE ev_uname = '$name' and event_num = '$id'")or die('Invalid query: ' . mysql_error());
		//$result =  mysql_query("SELECT * FROM event WHERE ev_uname = '$name' and course = 1 and super_event_id is null ")or die('Invalid query: ' . mysql_error());
		$event = @mysql_fetch_array($result);
		
		$eDate = new DateTime($event['e_date']);
		$sDate = new DateTime($event['s_date']);
		$diff = date_diff($eDate,$sDate);
		$duration = ($diff->h+$diff->i/60);
		$newTime = round($duration*2);
		return $newTime;
	}
	
	//Encrupting password. Returns salt and encrypted password.
	public function hashSSHA($password){
		return crypt($password);
	}
	
}
?>
