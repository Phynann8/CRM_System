<?php

class Api_Model_DbTable_DbPushNotification extends Zend_Db_Table_Abstract
{
	
	function getPreRegisterByID($registerId){
		$db = $this->getAdapter();
		$sql="SELECT 
						pre.*
						,'0' AS isFromStudentTB 
					FROM  rms_mobile_pre_register AS pre
					WHERE pre.status = 1 AND pre.id = $registerId
					LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
	function getListStudentId($_data){
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				GROUP_CONCAT(gsd.stu_id) 
			FROM `rms_group_detail_student` AS gsd  
			WHERE 1 
				AND gsd.stop_type=0 
				AND gsd.itemType=1
			";
		if(!empty($_data['degreeId'])){
			$sql.=" AND gsd.degree = ".$_data['degreeId'];
		}
		if(!empty($_data['gradeId'])){
			$sql.=" AND gsd.grade = ".$_data['gradeId'];
		}
		if(!empty($_data['groupId'])){
			$sql.=" AND gsd.group_id = ".$_data['groupId'];
		}
		return  $db->fetchOne($sql);
	}
	function getMobileToken($_data)
	{

		$_data['branchId'] = empty($_data['branchId']) ? 0 : $_data['branchId'];
		$_data['groupId'] = empty($_data['groupId']) ? 0 : $_data['groupId'];
		$db = $this->getAdapter();
		$sql = "SELECT DISTINCT mb.`token`
				FROM `mobile_mobile_token` AS mb
				WHERE mb.stu_id != 0 ";
		if( $_data['optNotification']==1 ){
			$sql.=" OR mb.stu_id = 0 ";
		}else if( $_data['optNotification']==2 ){ //By study's class of student
			$_data['groupId'] = empty($_data['groupId']) ? 0 : $_data['groupId'];
			$_data['degreeId'] = 0;
			$_data['gradeId'] = 0;
			$listStudentId = $this->getListStudentId($_data);
			if(!empty($listStudentId)){
				$sql.=" AND mb.stu_id IN (".$listStudentId.")";
			}
		}else if( $_data['optNotification']==3 ){ //specific student
			$sql.=" AND mb.stu_id IN (".$_data['studentId'].")";
		}else if( $_data['optNotification']==4 ){ //By Degree
			$_data['groupId'] = 0;
			$_data['degreeId'] = empty($_data['degreeId']) ? 0 : $_data['degreeId'];
			$_data['gradeId'] = 0;
			$listStudentId = $this->getListStudentId($_data);
			if(!empty($listStudentId)){
				$sql.=" AND mb.stu_id IN (".$listStudentId.")";
			}
		}else if( $_data['optNotification']==5 ){ //For Teacher
			$sql.=" AND mb.tokenType = 3 ";
		}
		return  $db->fetchCol($sql);
	}
	function pushNotificationAPI($_data)
	{
		try{
		
			$_data['branchId'] = empty($_data['branchId']) ? 0 : $_data['branchId'];
			$notificationId = empty($_data['notificationId']) ? 0 : $_data['notificationId'];
			
			$_data['optNotification'] = empty($_data['optNotification']) ? 1 : $_data['optNotification'];
			$notificationTitle = empty($_data['title']) ? "" : $_data['title'];
			$notificationSubTitle = empty($_data['subTitle']) ? "" : $_data['subTitle'];
			$notificationDescription = empty($_data['description']) ? "" : $_data['description'];
			$notificationDescriptionI = empty($_data['descriptionI']) ? "" : $_data['descriptionI'];
			$createDate = empty($_data['createDate']) ? "" : $_data['createDate'];
			
			
			$typeNotify = empty($_data['typeNotify']) ? "successfulRegister" : $_data['typeNotify'];
			
			$androidToken = null;
			if($_data['optNotification']!=1){
				$androidToken = $this->getMobileToken($_data);
			}
			
			
			
			$recordDetail = array();
			if($typeNotify == "successfulRegister"){
				$info = $this->getPreRegisterByID($_data['notificationId']);
				$firstName = $info["firstName"];
				$lastName = $info["lastName"];
				$fullKhName = $info["fullKhName"];
				$androidToken = array($info["deviceToken"]);
				
				$notificationTitle = "ស្នើសុំចុះឈ្មោះធ្វើតេស្ដសិក្សា - Register to Study";
				$notificationSubTitle = "ការស្នើសុំចុះឈ្មោះធ្វើតេស្ដសិក្សាបានដោយជោគជ័យ ការចុះឈ្មោះត្រូវបានដាក់បញ្ជូនដើម្បីធ្វើការត្រួតពិនិត្យ";
				$notificationDescription = "សួស្ដី $fullKhName ការស្នើសុំចុះឈ្មោះធ្វើតេស្ដសិក្សាបានដោយជោគជ័យ សូមធ្វើការរង់ចាំការទំនាក់ទំនងត្រឡប់ទៅវិញ បន្ទាប់ពីក្រុមការងារត្រួតពិនិត្យរួចរាល់។";
				$recordDetail = array($info);
			}else if($typeNotify == "studentScoreTranscript"){
				$_data['scoreId'] = $notificationId;
				
				$info = $this->getTranscriptInfo($_data);
				$groupCode = empty($info["groupCode"]) ? "" : $info["groupCode"];
				$forTypeTitleKh = empty($info["forTypeTitleKh"]) ? "" : $info["forTypeTitleKh"];
				$forMonthTitleKh = empty($info["forMonthTitleKh"]) ? "" : $info["forMonthTitleKh"];
				
				$forTypeTitle = empty($info["forTypeTitle"]) ? "" : $info["forTypeTitle"];
				$forMonthTitle = empty($info["forMonthTitle"]) ? "" : $info["forMonthTitle"];
				$examType = empty($info["exam_type"]) ? "1" : $info["exam_type"];
				
				$notificationTitle = "លទ្ធផលសិក្សាប្រចាំខែ / Monthly Score's Result";
				if($examType==2){
					$notificationTitle = "លទ្ធផលសិក្សាប្រចាំឆមាស / Semester Score's Result";
				}
				$notificationSubTitle = "លទ្ធផលសិក្សា  $forTypeTitleKh $forMonthTitleKh $groupCode";
				$notificationSubTitle.= " / Score's result for $forTypeTitle $forMonthTitle $groupCode";
				
				$notificationDescription = $notificationSubTitle;
				
			}else if($typeNotify == "notificationArticle"){
				$info = $this->getNotificationArticle($_data);
				$title = empty($info["title"]) ? "" : $info["title"];
				$description = empty($info["description"]) ? "" : $info["description"];
				mb_internal_encoding('utf-8');
				$description = strip_tags($description);
				$description = mb_substr($description, 0, 400). '...';
				$description = preg_replace('/\s+/', '', $description);
				$description = str_replace("&nbsp;", "", $description);
				
				
				$info["description"] = $description;
				$notificationTitle = $title;
				$notificationSubTitle = $description;
				$notificationDescription = $description;
				$recordDetail = array($info);
			}else if($typeNotify == "newsAndEvents"){
				$info = $this->getNewsArticle($_data);
				$title = empty($info["title"]) ? "" : $info["title"];
				$description = empty($info["description"]) ? "" : $info["description"];
				mb_internal_encoding('utf-8');
				$description = strip_tags($description);
				$description = mb_substr($description, 0, 400). '...';
				$description = preg_replace('/\s+/', '', $description);
				$description = str_replace("&nbsp;", "", $description);
				
				$info["description"] = $description;
				$notificationTitle = $title;
				$notificationSubTitle = $description;
				$notificationDescription = $description;
				$recordDetail = array($info);
				
			}else if($typeNotify == "criteriaStudentScore"){
				
			}else if($typeNotify == "schoolBusOnline"){
			}
	
	
			$dataNotify = array(
				"notificationId" 	=> $notificationId,
				"title" 			=> $notificationTitle,
				"subTitle" 			=> $notificationSubTitle,
				"description" 		=> $notificationDescription,
				"descriptionI" 		=> $notificationDescriptionI,
				"typeNotify" 		=> $typeNotify,
				"createDate" 		=> $createDate,
				"recordDetail" 		=> array(),
			);
			
			
			
			
			$headings = array(
				"en" => $notificationTitle,
			);
			$content = array(
				"en" => $notificationDescription." ".$notificationDescriptionI,
			);
	
			$apiKey = APP_API_KEY;
			$appId = APP_ID;
			$fields = array(
				'app_id' => $appId,
				'data' => $dataNotify,
				'headings' => $headings,
				'contents' => $content,
				"external_id" => null,
				"ios_badgeType" => "Increase",
				"ios_badgeCount" => 1,
				'ttl' => 259200,
				'priority' => 10,
				'ios_interruption_level' => 'active',
			);
			if($_data['optNotification']==1){
				$fields["target_channel"] = "push";
				$fields["included_segments"] = ["Subscribed Users"];
			}else{
				$fields["include_player_ids"] = $androidToken;
			}
			//$fields = json_encode($fields);
	
	
			$curl = curl_init();
			curl_setopt_array($curl, [
			  CURLOPT_URL => "https://api.onesignal.com/notifications?c=push",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => json_encode($fields),
			  CURLOPT_HTTPHEADER => [
				"Authorization: Key $apiKey",
				"Content-Type: application/json"
			  ],
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			
			$arrReturn = array(
				"typeNotify" 		=>$typeNotify,
				"optNotification" 	=>$_data['optNotification'],
				"notificationId"  	=>$notificationId,
				"isSuccess" 		=>1,
				"message" 			=>$response,
				"pushDate" 			=>date("Y-m-d H:i:s"),
			);
			if ($err) {
				$arrReturn["isSuccess"] = 0;
				$arrReturn["message"] = $err;
			}
			$this->recordPushActivicty($arrReturn);
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function recordPushActivicty($data){
		
		$db = $this->getAdapter();
    	try{
			$arr =  array(
					'typeNotify'		=>$data['typeNotify'],
					'optNotification'	=>$data['optNotification'],
					'notificationId'	=>$data['notificationId'],
					'isSuccess'			=>$data['isSuccess'],
					'message'			=>$data['message'],
					'pushDate'			=>$data['pushDate'],
				);
			$this->_name="mobile_push_activicty";
			$articleId = $this->insert($arr);
			return $articleId;
    	}catch(exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	
	function getTranscriptInfo($_data){
		try{
			$db = $this->getAdapter();
			
			$sql="SELECT
				s.*
				,g.`branch_id`
				,(SELECT b.branch_nameen FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branchName
				,(SELECT b.branch_namekh FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branchNameKh
				,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branchLogo
				,(SELECT b.school_namekh FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameKh
				,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameEng
		   	
				,g.`group_code` AS groupCode
				,`g`.`degree` as degreeId
			
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYearTitle
				
				,(SELECT rms_items.title_en FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degreeTitle
				,(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degreeTitleKh
				,(SELECT rms_itemsdetail.title_en FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS gradeTitle
				,(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS gradeTitleKH
		   
				,(SELECT name_en FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `sessionEn`
				,(SELECT name_kh FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `sessionTitleKh`
			
				,(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherNameKh
				,(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teaccherNameEng
				,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherTel
				,(SELECT name_en FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as forTypeTitle
				,(SELECT name_kh FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as forTypeTitleKh
				,CASE
					WHEN s.exam_type = 2 THEN s.for_semester
				ELSE (SELECT month_en FROM `rms_month` WHERE id=s.for_month  LIMIT 1) 
				END AS forMonthTitle
				,CASE
					WHEN s.exam_type = 2 THEN s.for_semester
				ELSE (SELECT month_kh FROM `rms_month` WHERE id=s.for_month  LIMIT 1) 
				END AS forMonthTitleKh
				
				
			FROM
				`rms_score` AS s
				JOIN  `rms_group` AS g ON g.`id` = s.`group_id`
			WHERE s.status = 1 ";
				
			$scoreId = empty($_data['scoreId'])?0:$_data['scoreId'];
			$sql.=" AND s.id = ".$scoreId;
			$sql.=" LIMIT 1 ";
			 
			$row = $db->fetchRow($sql);
			return $row;
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getNotificationArticle($_data){
		try{
			$db = $this->getAdapter();
			$sql="SELECT 
						acd.id,
						acd.`title`,
						acd.`lang`,
						acd.description 
				FROM 
					`mobile_notice_detail` AS acd 
				WHERE  acd.`lang`=1";
				
			$notificationId = empty($_data['notificationId'])?0:$_data['notificationId'];
			$sql.=" AND acd.`notification_id`= ".$notificationId;
			$sql.=" LIMIT 1 ";
			
			$row = $db->fetchRow($sql);
			return $row;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getNewsArticle($_data){
		try{
			$db = $this->getAdapter();
			$sql="SELECT 
						acd.id,
						acd.`title`,
						acd.`lang`,
						acd.description 
				FROM 
					`mobile_news_event_detail` AS acd 
				WHERE  acd.`lang`=1";
				
			$notificationId = empty($_data['notificationId'])?0:$_data['notificationId'];
			$sql.=" AND acd.`news_id`= ".$notificationId;
			$sql.=" LIMIT 1 ";
			
			$row = $db->fetchRow($sql);
			return $row;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
	}
	
	function getLastActiveDeviceInfo($data){
		
		$apiKey = APP_API_KEY;
		$appId = APP_ID;
		$curl = curl_init();
		$playerId = "928b7d15-22df-44c8-8202-52b4bc6d8b13";
		$playerId = empty($data["mobileToken"]) ? $playerId : $data["mobileToken"];
		//$urlAllPlayer = "https://onesignal.com/api/v1/players?app_id=$appId";
		$urlByOnePlayer = "https://onesignal.com/api/v1/players/$playerId?app_id=$appId";
		curl_setopt_array($curl, [
		  CURLOPT_URL => $urlByOnePlayer,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => [
			"Authorization: Basic $apiKey",
			"Content-Type: application/json; charset=utf-8",
			"accept: application/json"
		  ],
		]);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		if ($err) {
		  //echo "cURL Error #:" . $err;
		  return null;
		} else {
			$convertArr =Zend_Json::decode($response);
			return $convertArr;
		}
	
	}
	function getTokenInfomation($data){
		$db = $this->getAdapter();
		$token = empty($data["mobileToken"]) ? 0 : $data["mobileToken"];
		$sql="
			SELECT 
				mtk.*
			FROM mobile_mobile_token AS mtk
			WHERE mtk.token = '".addslashes($token)."' 
		";
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function updateDeviceInfo($data){
		if(!empty($data["mobileToken"])){
			$tokenInfo = $this->getTokenInfomation($data);
			if(!empty($tokenInfo["device_model_name"])){
				if((date("Y-m-d") == date("Y-m-01")) || (date("Y-m-d") == date("Y-m-15")) || (date("Y-m-d")== date("Y-m-25")) ){
					$result = $this->getLastActiveDeviceInfo($data);
					if(!empty($result)){
						$arr = array(
							'device_os'			=>empty($result["device_os"]) ? "" : $result["device_os"],
							'device_model_name'	=>empty($result["device_model"]) ? "" : $result["device_model"],
							'timezone'			=>empty($result["timezone"]) ? "" : $result["timezone"],
							'last_active'		=>empty($result["last_active"]) ? "" : $result["last_active"],
							'created_at'		=>empty($result["created_at"]) ? "" : $result["created_at"],
						);
						$this->_name="mobile_mobile_token";
						$whereRs=" token= '".addslashes($data["mobileToken"])."'";
						$this->update($arr,$whereRs);
					}
				}else{
					$arr = array(
					'last_active'		=>strtotime("now"),
					);
					$this->_name="mobile_mobile_token";
					$whereRs=" token= '".addslashes($data["mobileToken"])."'";
					$this->update($arr,$whereRs);
				}
			}else{
				$result = $this->getLastActiveDeviceInfo($data);
				if(!empty($result)){
					$arr = array(
						'device_os'				=>empty($result["device_os"]) ? "" : $result["device_os"],
						'device_model_name'		=>empty($result["device_model"]) ? "" : $result["device_model"],
						'timezone'			=>empty($result["timezone"]) ? "" : $result["timezone"],
						'last_active'		=>empty($result["last_active"]) ? "" : $result["last_active"],
						'created_at'		=>empty($result["created_at"]) ? "" : $result["created_at"],
						
					);
					$this->_name="mobile_mobile_token";
					$whereRs=" token= '".addslashes($data["mobileToken"])."'";
					$this->update($arr,$whereRs);
				}
			}
		}
		return 1;
	}
	
	
	function getGradingTmpStudentList($gradingTmpId){
		try{
			$db = $this->getAdapter();
			$sql="
				SELECT 
					grdTmp.`id` AS gradingTmpId
					,grdTmpD.`studentId`
					,grdTmp.`criteriaId`
					,grdTmp.`createDate`
					,g.`group_code` AS groupCode
					,(SELECT CONCAT(COALESCE(ac.fromYear),'-',COALESCE(ac.toYear)) FROM rms_academicyear AS ac WHERE ac.id=g.academic_year LIMIT 1) AS academicYearTitle
					,(SELECT subj.subject_titleen FROM `rms_subject` AS subj WHERE subj.id = grdTmp.`subjectId` LIMIT 1) AS subjectTitle
					,(SELECT subj.subject_titlekh FROM `rms_subject` AS subj WHERE subj.id = grdTmp.`subjectId` LIMIT 1) AS subjectTitleKh
					,(SELECT cri.title_en FROM `rms_exametypeeng` AS cri WHERE cri.id = grdTmp.`criteriaId` LIMIT 1) AS criteriaTitle
					,(SELECT cri.title FROM `rms_exametypeeng` AS cri WHERE cri.id = grdTmp.`criteriaId` LIMIT 1) AS criteriaTitleKh

					,grdTmpD.`totalGrading`
					,grdTmpD.`subCriterialTitleEng`
					,grdTmpD.`subCriterialTitleKh`
					,(SELECT sEnT.`title` FROM `rms_score_entry_setting` AS sEnT WHERE sEnT.id = grdTmp.`settingEntryId` LIMIT 1 ) AS entrySettingTitle

					,(SELECT t.teacher_name_kh  FROM `rms_teacher` AS t WHERE t.id =grdTmp.`teacherId` LIMIT 1) AS teacherName
					
				FROM `rms_grading_tmp` AS grdTmp 
					JOIN `rms_grading_detail_tmp` AS grdTmpD ON grdTmp.`id` = grdTmpD.`gradingId`
					LEFT JOIN `rms_group` AS g ON g.id = grdTmp.groupId
				WHERE 1 
					AND grdTmp.`id` = $gradingTmpId 
					AND grdTmpD.`totalGrading` > 0
			";
			
			$row = $db->fetchAll($sql);
			return $row;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
}
