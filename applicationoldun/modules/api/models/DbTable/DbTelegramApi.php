<?php

class Api_Model_DbTable_DbTelegramApi extends Zend_Db_Table_Abstract
{
	const BOT_NAME='PSIS Chamkar Doung';
	function getStudentInfo($_data){
		$db = $this->getAdapter();
		$sql="
		SELECT 
			s.`stu_id` AS studentId
			,s.`stu_code` AS studentCode
			,s.`stu_khname` AS stuNameKh
			,s.`sex`
			,CONCAT(COALESCE(s.`last_name`,''),' ',COALESCE(s.`stu_enname`,'')) AS stuNameEn
			,COALESCE(g.`group_code`,'') AS groupCode
			,(SELECT CONCAT(COALESCE(ac.`fromYear`,''),' ',COALESCE(ac.`toYear`,'')) FROM rms_academicyear AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
			FROM `rms_student` AS s 
				JOIN `rms_group_detail_student` AS gd ON gd.`stu_id` = s.`stu_id` AND gd.`itemType` = 1 AND gd.`is_current`=1 AND gd.`is_maingrade`=1
				LEFT JOIN `rms_group` AS g ON g.id = gd.`group_id`
			WHERE s.`status` =1 
			AND s.`customer_type` = 1
			
		";
		$studentId = empty($_data["studentId"]) ? 0 : $_data["studentId"]; 
		$studentCode = empty($_data["studentCode"]) ? 0 : $_data["studentCode"];
		if(!empty($studentId)){
			$sql.=" AND s.`stu_id` = '$studentId' ";
		}else{
			$sql.=" AND s.`stu_code` = '$studentCode' ";
		}
		return $db->fetchRow($sql);
	}
	function getTelegramChatInfo($_data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				s.`id`
				,s.`chatId`
				,s.`studentId`
			FROM `rms_telegram_token` AS s 
			WHERE 1
		";
		$chatId = empty($_data["chatId"]) ? 0 : $_data["chatId"]; 
		$studentId = empty($_data["studentId"]) ? 0 : $_data["studentId"];
		$sql.=" AND s.`chatId` = '$chatId' ";
		$sql.=" AND s.`studentId` = '$studentId' ";
		return $db->fetchRow($sql);
	}
	function getStudentChatIdInfo($_data){
		$db = $this->getAdapter();
		try{
			$sql="
				SELECT 
					t.`id`
					,t.`chatId`
					,t.`studentId`
					,s.`stu_code` AS studentCode
					,s.`stu_khname` AS stuNameKh
					,s.sex
					,CONCAT(COALESCE(s.`last_name`,''),' ',COALESCE(s.`stu_enname`,'')) AS stuNameEn
					,COALESCE(g.`group_code`,'') AS groupCode
					,(SELECT CONCAT(COALESCE(ac.`fromYear`,''),' ',COALESCE(ac.`toYear`,'')) FROM rms_academicyear AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
				FROM `rms_telegram_token` AS t 
					JOIN (`rms_student` AS s  JOIN `rms_group_detail_student` AS gd ON gd.`stu_id` = s.`stu_id` AND gd.`itemType` = 1 AND gd.`is_current`=1 AND gd.`is_maingrade`=1) ON t.studentId = s.`stu_id`
					LEFT JOIN `rms_group` AS g ON g.id = gd.`group_id`
				WHERE 1
			";
			$chatId = empty($_data["chatId"]) ? 0 : $_data["chatId"]; 
			$sql.=" AND t.`chatId` = '$chatId' ";
			$res = $db->fetchAll($sql);
			return $res;	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			
			return array();
		}
	}
	function getAllTokenByChatId($_data){
		$db = $this->getAdapter();
		try{
			$sql="
				SELECT 
					t.`id`
					,t.`chatId`
					,t.`studentId`
				FROM `rms_telegram_token` AS t 
				WHERE 1
			";
			$chatId = empty($_data["chatId"]) ? 0 : $_data["chatId"]; 
			$sql.=" AND t.`chatId` = '$chatId' ";
			$sql.=" ORDER BY t.id ASC ";
			$res = $db->fetchAll($sql);
			return $res;	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return array();
		}
	}
	
	function submitTokenTelegram($_data){
		$db = $this->getAdapter();
		try{
			$chatId = empty($_data['chatId'])?"0":$_data['chatId'];
			$studentCode = empty($_data['studentCode'])?"0":$_data['studentCode'];
			$stuInfo = $this->getStudentInfo($_data);
			$studentId = empty($stuInfo["studentId"]) ? 0 : $stuInfo["studentId"];
			$_data["studentId"] = $studentId;
			$arr = array(
				'chatId'  		=> $chatId,
				'studentId'		=> $studentId,
				'modifyDate'	=> date("Y-m-d H:i:s"),
			);
			$this->_name='rms_telegram_token';
			
			$tokenInfo = $this->getTelegramChatInfo($_data);
			
			$dbGStt = new Setting_Model_DbTable_DbGeneral();
			$telegramBotName = $dbGStt->geLabelByKeyName('telegramBotName');
			$botName = empty($telegramBotName["keyValue"]) ? self::BOT_NAME : $telegramBotName["keyValue"];
			
			if(!empty($chatId)){
				if(!empty($stuInfo)){
					if(!empty($tokenInfo)){
						$where = "id = ".$tokenInfo["id"];
						$this->update($arr,$where);
					}else{
						$_data["studentId"] = 0;
						$tokenInfo = $this->getTelegramChatInfo($_data);
						if(!empty($tokenInfo)){
							$where = "id = ".$tokenInfo["id"];
							$this->update($arr,$where);
						}else{
							$arr["createDate"] = date("Y-m-d H:i:s");
							$this->insert($arr);
						}
					}
					$message = "✅ Sussucefully Connected With Student: ".$studentCode;
					$emoji="🧑‍🎓";
					$gender = empty($stuInfo['sex']) ? 1 : $stuInfo['sex'];
					if($gender==2){
						$emoji="👩‍🎓";
					}
					$studentName = empty($stuInfo['stuNameKh']) ? "" : $stuInfo['stuNameKh'];
					$studentName = empty($stuInfo['stuNameEn']) ? $studentName : $studentName." / ".$stuInfo['stuNameEn'];
					$message.="\r\n".$emoji." <strong>".$studentName."</strong>";
					$message.="\r\n⚜️ ".$stuInfo['academicYear']." ".$stuInfo['groupCode'];
					$message.="\r\n";
					
				}else{
					$message = "🤷‍♂️ Student <strong>{$studentCode}</strong> not found In ".$botName.".";
					if(!empty($tokenInfo)){
						$where = "id = ".$tokenInfo["id"];
						$this->update($arr,$where);
					}else{
						$arr["createDate"] = date("Y-m-d H:i:s");
						$this->insert($arr);
					}
				}
				
				$firstName = empty($_data["firstName"]) ? '' : $_data["firstName"];
				$lastName = empty($_data["lastName"]) ? '' : $_data["lastName"];
				$arrInfo = array(
					
					'firstName'		=> $firstName,
					'lastName'		=> $lastName,
					'modifyDate'	=> date("Y-m-d H:i:s"),
				);
				$whereInfo = "chatId = ".$chatId;
				$this->update($arrInfo,$whereInfo);
							
				$arrResult = array(
					"result" => true,
					"code" => "SUCCESS",
					"message" => $message,
				);
			}else{
				$message = "🤷‍♂️ Student <strong>{$studentCode}</strong> not found In ".$botName.".";
				$arrResult = array(
					"result" => true,
					"code" => "NO_RECORD",
					"message" => $message,
				);
			}
			return $arrResult;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$arrResult = array(
					"result" => false,
					"code" => "ERR_",
					"message" => "Record not found",
				);
			return $arrResult;
		}
	}
	
	function removeTokenTelegram($_data){
		$db = $this->getAdapter();
		try{
			
			$dbGStt = new Setting_Model_DbTable_DbGeneral();
			$telegramBotName = $dbGStt->geLabelByKeyName('telegramBotName');
			$botName = empty($telegramBotName["keyValue"]) ? self::BOT_NAME : $telegramBotName["keyValue"];
			
			
			$chatId = empty($_data['chatId'])?"0":$_data['chatId'];
			$studentCode = empty($_data['studentCode'])?"0":$_data['studentCode'];
			$message="";
			if(!empty($_data['removeall'])){
				if(!empty($chatId)){
					$allChat = $this->getAllTokenByChatId($_data);
					$amtStuAcc = count($allChat);
					if($amtStuAcc>0){
						$recordId = empty($allChat[0]["id"]) ? 0 : $allChat[0]["id"];
						$where =" id !=".$recordId." AND chatId='$chatId' ";
						$this->_name="rms_telegram_token";
						$this->delete($where);
						
						$arr = array(
							"studentId" => 0
						);
						$where = "id = ".$recordId;
						$this->update($arr,$where);
						
						$message="✅ Succeefully disconnected all student account.";
					}else{
						$message="🤷‍♂️ Sorry empty Student to disconnect.";
					}
				}
				
			}else{
				$message= "🤷‍♂️ Sorry Student <strong>$studentCode</strong> not found in ".$botName.".";
				$stuInfo = $this->getStudentInfo($_data);
				if(!empty($stuInfo)){
					$message= "🤷‍♂️ Sorry Student <strong>$studentCode</strong> not found with your connected.";
					$studentId = empty($stuInfo["studentId"]) ? 0 : $stuInfo["studentId"];
					$_data["studentId"] = $studentId;
					$tokenInfo = $this->getTelegramChatInfo($_data);
					if(!empty($tokenInfo)){
						
						$allChat = $this->getAllTokenByChatId($_data);
						$amtStuAcc = count($allChat);
						if($amtStuAcc>1){
							$where ="studentId=".$studentId." AND chatId='$chatId' ";
							$this->_name="rms_telegram_token";
							$this->delete($where);
						}else{
							$arr = array(
								"studentId" => 0
							);
							$this->_name="rms_telegram_token";
							$where = "id = ".$tokenInfo["id"];
							$this->update($arr,$where);
						}
						
						
						$message= "✅ Succeefully disconnected student <strong>$studentCode</strong>";
					}
				}
			}
			
			$arrResult = array(
						"result" => true,
						"code" => "SUCCESS",
						"message" => $message,
					);
			return $arrResult;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$arrResult = array(
					"result" => false,
					"code" => "ERR_",
				);
			return $arrResult;
		}
	}
	
	
	
	
	function getCurrentChatInfoMessage($_data){
		try{
			
			$dbGStt = new Setting_Model_DbTable_DbGeneral();
			$telegramBotName = $dbGStt->geLabelByKeyName('telegramBotName');
			$botName = empty($telegramBotName["keyValue"]) ? self::BOT_NAME : $telegramBotName["keyValue"];
			
			
			$charInfo = $this->getStudentChatIdInfo($_data);
			$string="";
			if(!empty($charInfo)){
				$string="💻 Currently you connected with ".count($charInfo)." Student(s) Of $botName.";
				$string.="\r\n";
				foreach($charInfo AS $key => $row){
					$studentName = empty($row['stuNameKh']) ? "" : $row['stuNameKh'];
					$studentName = empty($row['stuNameEn']) ? $studentName : $studentName." / ".$row['stuNameEn'];
					$emoji="🧑‍🎓";
					$gender = empty($row['sex']) ? 1 : $row['sex'];
					if($gender==2){
						$emoji="👩‍🎓";
					}
					$string.="\r\n".$emoji." <strong>".$row['studentCode']." ".$studentName."</strong>";
					$string.="\r\n⚜️ ".$row['academicYear']." ".$row['groupCode'];
					$string.="\r\n";
				}
			}else{
				$string="💻 Sorry currently you not connected yet with Student Of ".$botName.".";
			}
			$arrResult = array(
					"result" => $string,
					"code" => "SUCCESS",
				);
			return $arrResult;	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$arrResult = array(
					"result" => false,
					"code" => "ERR_",
				);
			return $arrResult;
		}
	}
	
	function getStartBotInfo($_data=array()){
		$db = $this->getAdapter();
		try{
			$dbGStt = new Setting_Model_DbTable_DbGeneral();
			$telegramBotName = $dbGStt->geLabelByKeyName('telegramBotName');
			$botName = empty($telegramBotName["keyValue"]) ? self::BOT_NAME : $telegramBotName["keyValue"];
			
			
			$this->submitTokenTelegram($_data);
			$tokenInfo = $this->getTelegramChatInfo($_data);
			if(!empty($tokenInfo)){
				$sql="You're already Connected with Student of ".$botName.". 
If you want to add more student please command /add like example below.
	👉🏻 Example: /add P00002
				
For get Information of list student's connected with please command /mykids .
				";
			}else{
				$sql="Welcome to ".$botName.". 
Please enter Student Code for Connecting and get Student's activictiy.
	👉🏻 Example: /add P00001
				
If you're new to ".self::BOT_NAME.", Please enter /help for get more comman information.";
			}
			$arrResult = array(
					"result" => $sql,
					"code" => "SUCCESS",
				);
			return $arrResult;	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$arrResult = array(
					"result" => false,
					"code" => "ERR_",
				);
			return $arrResult;
		}
	}
	
	function getHelp($_data=array()){
		$db = $this->getAdapter();
		try{
			
			$sql="You can control us by sending these commands:

⚙️ <strong>General</strong>
/start - Start School's Telegram Bot  

👩‍🎓 <strong>Student Commands</strong>
/add - Add Your Kid(s)
	👉🏻 Example: /add P00002
/remove - Remove Your Kid.
	👉🏻 Example: /remove P00002
/removeall - Remove All Your Kids.
";
			$arrResult = array(
					"result" => $sql,
					"code" => "SUCCESS",
				);
			return $arrResult;	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$arrResult = array(
					"result" => false,
					"code" => "ERR_",
				);
			return $arrResult;
		}
	}
	
	
	public function getCompanyContact($search=array()){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		$sql=" 
			SELECT
				l.*
				,ld.title
				,ld.description
    		FROM `mobile_location` AS l,
				`mobile_location_detail` AS ld
    		WHERE l.id=ld.location_id
    		AND ld.lang= $currentLang ";
    		
    		$rowcontact = $db->fetchRow($sql);
    		$rowabout =array();
    		$all_result = array('aboutUs'=>$rowabout,'contact'=>$rowcontact);
    		return $all_result;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$result = array(
    				'aboutUs' =>array(),
    				'contact' =>array(),
    		);
    		return $result;
    	}
    }
	function removeHttp($url) {
	   $disallowed = array('http://', 'https://');
	   foreach($disallowed as $d) {
		  if(strpos($url, $d) === 0) {
			 return str_replace($d, '', $url);
		  }
	   }
	   return $url;
	}
	function getAboutUs($_data=array()){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$rs = $this->getCompanyContact();
			$contact =$rs["contact"];
			$title=empty($contact["title"]) ? "" : $contact["title"];
			$description=empty($contact["description"]) ? "" : $contact["description"];
			$description=empty($contact["description"]) ? "" : $contact["description"];
			$address = trim(strip_tags($description));
			
			$phone=empty($contact["phone"]) ? "" : $contact["phone"];
			$email=empty($contact["email"]) ? "" : $contact["email"];
			$websiteLink=empty($contact["website"]) ? "" : $contact["website"];
			$website="";
			if(!empty($websiteLink)){
				$website = $this->removeHttp($websiteLink);
				$website='🌎 <a href="'.$websiteLink.'">'.$website.'</a>';
			}
			$facebookLink=empty($contact["facebook"]) ? "" : $contact["facebook"];
			$facebook="";
			if(!empty($facebookLink)){
				$facebook='🌎 <a href="'.$facebookLink.'">Facebook</a>';
			}
			$tiktokLink=empty($contact["tiktok"]) ? "" : $contact["tiktok"];
			$tiktok="";
			if(!empty($tiktokLink)){
				$tiktok='🌎 <a href="'.$tiktokLink.'">TikTok</a>';
			}
			$sql="$title
			
🏫 $address
☎️ $phone
📧 $email
$website
$facebook
$tiktok
";
			$arrResult = array(
					"result" => $sql,
					"code" => "SUCCESS",
				);
			return $arrResult;	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$arrResult = array(
					"result" => false,
					"code" => "ERR_",
				);
			return $arrResult;
		}
	}
}