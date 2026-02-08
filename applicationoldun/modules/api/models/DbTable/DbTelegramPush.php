<?php

class Api_Model_DbTable_DbTelegramPush extends Zend_Db_Table_Abstract
{
	const BOT_NAME='Chamkar Doung';
	const BOT_TOKEN='1915354968:AAG9e_M9E4qPsdsPcADJlJrArBheYwNvkq4';
	
	function sentTelegramMessage($data=array()){
		try{
			$userId = empty($data["chatId"]) ? 0 : $data["chatId"];
			if(!empty($userId)){
				//$botMessage= $data["message"];
				$botMessage= urlencode($data["message"]);
				$parameters = array(
					"chat_id" => $userId,
					"text" => $botMessage,
					"parse_mode" => "html"
				);
				
				$dbGStt = new Setting_Model_DbTable_DbGeneral();
				$telegramBotToken = $dbGStt->geLabelByKeyName('telegramBotToken');
				$botToken = empty($telegramBotToken["keyValue"]) ? self::BOT_TOKEN : $telegramBotToken["keyValue"];
			
				file_get_contents("https://api.telegram.org/bot".$botToken."/sendMessage?chat_id=$userId&parse_mode=HTML&text=".$botMessage);
				
				return true;
			}else{
				return false;
			}
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getAllChatIdStudent($_data){
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
					LEFT JOIN (`rms_student` AS s  JOIN `rms_group_detail_student` AS gd ON gd.`stu_id` = s.`stu_id` AND gd.`itemType` = 1 AND gd.`is_current`=1 AND gd.`is_maingrade`=1) ON t.studentId = s.`stu_id`
					LEFT JOIN `rms_group` AS g ON g.id = gd.`group_id`
				WHERE 1
			";
			
			$groupBy="";
			if($_data['optNotification']==1){// General Announcement  {all chatId}
				$groupBy=" GROUP BY t.`chatId` ";
			}else if($_data['optNotification']==2){// Specfic to Student {chatId connected student}
				$studentId = empty($_data["studentId"]) ? "-1" : $_data["studentId"]; 
				$sql.=" AND t.`studentId` = '$studentId' ";
			}else if($_data['optNotification']==3){// Class Of Student {chatId connected student}
				$groupId = empty($_data["groupId"]) ? "-1" : $_data["groupId"]; 
				$sql.=" AND g.id = '$studentId' ";
			}
			if(!empty($_data['branchId'])){
				$branchId = empty($_data["branchId"]) ? "-1" : $_data["branchId"]; 
				$sql.=" AND s.branch_id = '$branchId' ";
			}
			$res = $db->fetchAll($sql.$groupBy);
			return $res;	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return array();
		}
	}
	function pushMessageTele($_data=array()){
		try{
			
			$_data['optNotification'] = empty($_data['optNotification']) ? 1 : $_data['optNotification'];
			$typeNotify = empty($_data['typeNotify']) ? "general" : $_data['typeNotify'];
			$message = empty($_data['message']) ? "" : $_data['message'];
			$chatRs = $this->getAllChatIdStudent($_data);
			
			if(!empty($chatRs)){
				foreach($chatRs AS $rowChat){
					$messageSent = $message;
					if($typeNotify !="general"){
						$messageSent = $this->getMessageTemplate($typeNotify,$rowChat);
						$messageSent.= $message;
					}
					$arr=array(
						"chatId"=>$rowChat["chatId"],
						"message"=>$messageSent,
					);
					$this->sentTelegramMessage($arr);
				}
			}
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getMessageTemplate($typeNotify,$rowData){
		
		$message = "";
		if($typeNotify =="scanAtt"){
			$emoji="🧑‍🎓";
			$gender = empty($rowData['sex']) ? 1 : $rowData['sex'];
			if($gender==2){
				$emoji="👩‍🎓";
			}
			$studentCode = empty($rowData['studentCode']) ? "" : $rowData['studentCode'];
			$studentName = empty($rowData['stuNameKh']) ? "" : $rowData['stuNameKh'];
			$studentName = empty($rowData['stuNameEn']) ? $studentName : $studentName." / ".$rowData['stuNameEn'];
					
			$message = $emoji." Student: ".$studentCode."
<strong>".$studentName."</strong>
			";
		}
		return $message;
	}
	
}

