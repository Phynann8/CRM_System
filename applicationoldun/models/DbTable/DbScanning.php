<?php

class Application_Model_DbTable_DbScanning extends Zend_Db_Table_Abstract
{
	
	
	public function getAllPlaylistvideo(){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_setting_playlistvideo WHERE status = 1 ";
		$row=$db->fetchAll($sql);
		return $row;
	}
	public function getAllEntrance(){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_entrance_exit WHERE status = 1 ";
		$row=$db->fetchAll($sql);
		return $row;
	}
	public function getEntranceById($id){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_entrance_exit WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function getAllScanSetting(){
		$db = $this->getAdapter();
		$sql = " 
			SELECT 
				scSt.id AS id 
				,scSt.title AS name 
				,scSt.fromTime
				,scSt.toTime
			FROM rms_scan_att_setting AS scSt 
			WHERE scSt.status = 1 ";
		$row=$db->fetchAll($sql);
		return $row;
	}
	public function getSettinInfo($data){
		$db = $this->getAdapter();
		$sql = " 
			SELECT 
				scSt.* 
			FROM 
				rms_scan_att_setting AS scSt 
			WHERE scSt.status = 1 ";
		$ordering=" ORDER BY  scSt.id DESC ";	
		
		
		
		$settingId = empty($data["settingId"]) ? 0 : $data["settingId"]; 
		if(!empty($data["settingId"])){
			$sql.=" AND scSt.id = ".$db->quote($settingId);
		}else{
			
			$branchId = empty($data["branchId"]) ? 0 : $data["branchId"]; 
			$sql.=" AND scSt.branchId = ".$branchId;
		
			$scanDate = empty($data["scanDate"]) ? date('Y-m-d H:i:s') : $data["scanDate"];
			if(!empty($data["nextScaningTime"])){
				$time = date("H:i",strtotime($data["nextScaningTime"]));
				$fromTime = " DATE_FORMAT(scSt.fromTime,'%H:%i:%s') >= '" . $time . ":00'";
				$sql.= " AND " . $fromTime;
				$ordering=" ORDER BY  scSt.fromTime ASC ";
				
			}else{
				$time = date("H:i",strtotime($scanDate));
				$fromTime = " DATE_FORMAT(scSt.fromTime,'%H:%i:%s') <= '" . $time . ":00'";
				$toTime = " DATE_FORMAT(scSt.toTime,'%H:%i:%s')  >= '" . $time . ":00'";
				$sql .= " AND " . $fromTime . " AND " . $toTime;
			}
		}
		
		
		
		
		$sql.=$ordering;
		$sql.=" LIMIT 1 ";
		//Application_Model_DbTable_DbUserLog::writeMessageError("setting".$sql);
		$row=$db->fetchRow($sql);
		return $row;
	}
	
	public function insertPreAttedance($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr=array(
				'branchId'	  		=> $_data['branchId'],
				'gate'	  			=> $_data['gate'],
				'settingId'	  		=> $_data['settingId'],
				'type'	  			=> $_data['type'],
				'groupId'			=> $_data['groupId'],
				'studentId'			=> $_data['studentId'],
				'attendanceStatus'	=> $_data['attendanceStatus'],
				'shift'	=> $_data['shift'],
				'createDate'	 	=> date('Y-m-d H:i:s'),
				'modifyDate' 		=> date('Y-m-d H:i:s'),
			);
			$this->_name = "rms_scan_pre_att";
			$id =  $this->insert($_arr);
			$db->commit();
			return $id;
				
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error");
			$db->rollBack();
		}
	}
	public function checkingPreAtt($data){
		$db = $this->getAdapter();
		
		$studentId = empty($data["studentId"]) ? 0 : $data["studentId"]; 
		$type = empty($data["type"]) ? 0 : $data["type"]; 
		$settingId = empty($data["settingId"]) ? 0 : $data["settingId"]; 
		
		$sql=" 
			SELECT 
				pAtt.* 
				,(SELECT scSt.title FROM rms_scan_att_setting AS scSt WHERE scSt.id = pAtt.settingId LIMIT 1) AS settingTitle
			FROM 
			rms_scan_pre_att AS pAtt ";
		$sql.=" WHERE 1 ";
		$sql.=" AND pAtt.studentId=$studentId ";
		$sql.=" AND pAtt.settingId=$settingId ";
		$sql.=" AND pAtt.type=$type ";
		
		$scanDate = empty($data["scanDate"]) ? date('Y-m-d') : date('Y-m-d',strtotime($data["scanDate"]));
		$sql.=" AND DATE_FORMAT(pAtt.createDate,'%Y-%m-%d') = '".$scanDate."'";
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
	public function getStudent($data)
	{
		$curr = new Application_Model_DbTable_DbGlobal();
		$lang= $curr->currentlang();
		$db = $this->getAdapter();
		$field = 'name_en';
		$colunmname='title_en';
		if ($lang==1){
			$field = 'name_kh';
			$colunmname='title';
		}
		
		$qr = empty($data["studentToken"]) ? "" : $data["studentToken"]; 
		$sql ="
			SELECT 
				CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameEng
				,s.branch_id as branchId
				,s.stu_khname as stuNameKh
				,s.stu_code as stuCode
				,s.sex as sexCode
				,s.stu_id as studentId
				,ds.stop_type AS stopType
				,ds.gd_id AS studyId
				
				
				,s.photo
				,(SELECT $field from rms_view where type=5 and key_code=ds.stop_type LIMIT 1) as stopTypeTitle
				,(SELECT group_code FROM `rms_group` WHERE rms_group.id=ds.group_id AND ds.is_maingrade=1 LIMIT 1) AS groupCode
				,ds.group_id AS groupId
				,(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = ds.degree AND i.type=1 AND ds.is_maingrade=1 LIMIT 1) AS degree
				,(SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = ds.grade AND idd.items_type=1 AND ds.is_maingrade=1 LIMIT 1) AS grade
				,(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academic_year LIMIT 1) AS academicYear
				
			FROM 
				rms_student AS s JOIN rms_group_detail_student AS ds ON ds.itemType=1 AND ds.is_maingrade=1  AND ds.is_current=1  AND s.stu_id=ds.stu_id 
			WHERE  
			   1 
			   AND s.status = 1 
			AND s.customer_type = 1 
		";
		$sql.="  AND s.studentToken = ".$db->quote($qr);
		
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		//Application_Model_DbTable_DbUserLog::writeMessageError("student".$sql);
		return $row;
	}
	
	public function getScanningResult($data)
	{
		$db = $this->getAdapter();
		try{
			$stringHtml="";
			$statusReturn = "0";
			$qrValue = empty($data["keyword"]) ? "" : $data["keyword"]; 
			$str = $qrValue;
			$explString = explode("setting",$str);
			$setting = empty($explString[1]) ? 0 : str_replace("/","",$explString[1]);
			
			$scanDate = date("Y-m-d H:i:s");
			$stuScanChecking=0;
			if($setting!=""){
				$data["settingId"] = $setting;
				$rowSettingInfo = $this->getSettinInfo($data);
			}else{
				
				$explString = explode("token",$str);
				$token = empty($explString[1]) ? "" : str_replace("/","",str_replace("=","",$explString[1]));
				$data["studentToken"] = $token;
				
				$row = $this->getStudent($data);
				
				if(!empty($data["settingId"])){
					$rowSettingInfo = $this->getSettinInfo($data);
				}else{
					$data["branchId"] = empty($row["branchId"]) ? 0 : $row["branchId"];
					$data["scanDate"] = $scanDate;
					$rowSettingInfo = $this->getSettinInfo($data);
				}
				$data["branchId"] = empty($rowSettingInfo["branchId"]) ? 0 : $rowSettingInfo["branchId"];
				$data["settingId"] = empty($rowSettingInfo["id"]) ? 0 : $rowSettingInfo["id"];
				$data["type"] = empty($rowSettingInfo["type"]) ? 0 : $rowSettingInfo["type"];
				$data["shift"] = empty($rowSettingInfo["shift"]) ? 0 : $rowSettingInfo["shift"];
				
				if(!empty($row)){
					if(!empty($rowSettingInfo)){
						
						$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
						$profilePhoto = $baseUrl.'/images/no-profile.png';
						$sex = empty($row["sexCode"]) ? 1 : $row["sexCode"];
						if($sex==2){
							$profilePhoto = $baseUrl.'/images/no-profile-female.png';
						}
						if(!empty($row["photo"])){
							if (file_exists(PUBLIC_PATH."/images/photo/".$row["photo"])){
								$profilePhoto = $baseUrl.'/images/photo/'.$row["photo"];
							}
						}
						$stringHtml.='
								<div class="profile-block text-center">
								  <img id="stuImageProfile" src="'.$profilePhoto.'" alt="" class="img-circle img-responsive">
								</div>
								
								<p class="m-0"><span id="stuNameCode">'.$row["stuCode"].'</span></p>
								<h2 id="stuNameKh" class="m-0">'.$row["stuNameKh"].'</h2>
								<h2 id="stuNameEng" class="m-0">'.$row["stuNameEng"].'</h2>
								<p ></p>
						';
							
						$data["studentId"] = empty($row["studentId"]) ? 0 : $row["studentId"];
						$data["groupId"] = empty($row["groupId"]) ?0 : $row["groupId"];
						$data["attendanceStatus"] = 1;
						$stopType = empty($row["stopType"]) ? 0 : $row["stopType"];
						if($stopType!=0){
							
							$stringHtml.='
								<p ></p>
								<p ><i class="fa fa-qrcode icon-xxl"></i></p>
								<p class="m-0"><span >Inactive Student Unable to Connect</span></p>
								<p ></p>
								
								<div class="success-animation">
									<svg class="checkmark error" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 52 52" ><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" /><path class="checkmark__check" fill="none" d="M 12,12 L 40,40 M 40,12 L 12,40"/></svg>
								</div>
							';
					
						}else{
							if(!empty($rowSettingInfo["condictionTime"])){
								$timeVlue = date("H:i",strtotime($scanDate));
								$datetime1 = new DateTime($timeVlue);
								$datetime2 = new DateTime($rowSettingInfo["condictionTime"]);
								if($rowSettingInfo["type"]==2){
									if($datetime1 < $datetime2){
										$data["attendanceStatus"] = 5;//ចេញមុន
									}
								}else{
									if($datetime1 > $datetime2){
										$data["attendanceStatus"] = 4;//យឺត
									}
								}
							}

							
							
							$data["scanDate"] = $scanDate;
							$checking = $this->checkingPreAtt($data);
							if(!empty($checking)){
								$stringHtml.='
									<p class="m-0"><span >Already Scanned '.$checking["settingTitle"].'</span></p>
								';
								$stuScanChecking=$checking;
							}else{
								$inserted = $this->insertPreAttedance($data);
								$stringHtml.='
									<p class="m-0"><span >'.date('d-M-Y h:i A').'</span></p>
															
									<div class="success-animation">
										<svg class="checkmark success" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" /><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" /></svg>
									</div>
								';
								$statusMessage="✅ បានមកដល់សាលា";
								if($rowSettingInfo["type"]==2){
									$statusMessage="បានចាកចេញពីសាលា";
								}
								$messageStr="
		📆 Date: ".date('d-M-Y')."
		⏰ Time: ".date('h:i A')."
		<strong>$statusMessage</strong>
		🙏 Thank you
		";
								$dbTele = new Api_Model_DbTable_DbTelegramPush();
								$arrPush = array(
									"optNotification"=>2,
									"typeNotify"=>"scanAtt",
									"message"=>$messageStr,
									"studentId"=>$data["studentId"],
								);
								$dbTele->pushMessageTele($arrPush);
							}
							$statusReturn = "1";
						}
						
					}else{
						$data["nextScaningTime"] = $data["scanDate"];
						$settingInfo = $this->getSettinInfo($data);
						$stringHtml.='
								<p ></p>
								<p ><i class="fa fa-qrcode icon-xxl"></i></p>
								<p class="m-0"><span >Scanning Attendance Ended</span></p>
								<p class="m-0"><span >Available on :</span></p>
								
								<p class="m-0"><span ><strong>'.date("h:i A",strtotime($settingInfo["fromTime"])).'</strong> to <strong>'.date("h:i A",strtotime($settingInfo["toTime"])).'</strong> </span></p>
								<p class="m-0"><strong >'.$settingInfo["title"].'</strong></p>
								<p ></p>
								
								<i class="fa fa-exclamation-triangle icon-xl text-warning" ></i>
								
							';
					}
				}else if(empty($token)){
					$stringHtml.='
						<p ></p>
						<p ><i class="fa fa-qrcode icon-xxl"></i></p>
						<p class="m-0"><span >Invalid QR Code</span></p>
						<p ></p>
						
						<div class="success-animation">
							<svg class="checkmark error" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 52 52" ><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" /><path class="checkmark__check" fill="none" d="M 12,12 L 40,40 M 40,12 L 12,40"/></svg>
						</div>
					';
				}
				$data["settingId"] = 0;
				$data["scanDate"] = date("Y-m-d H:i:s");
				$rowSettingInfo = $this->getSettinInfo($data);
			}
			
			$arrReturn = array(
				'content' 	=> $stringHtml,
				'status' 	=> $statusReturn,
				'settingInfo' => $rowSettingInfo,
				'setting' => $setting,
				'scanned' => $stuScanChecking,
			);
			return $arrReturn;
		
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError("scanning : ".$e->getMessage());
			$arrReturn = array(
				'content' 	=> "",
				'status' 	=> 0,
				'settingInfo' => [],
				'setting' => 0,
				'scanned' => 0,
			);
			return $arrReturn;
		}
	}
	
}
?>