<?php

class Issue_Model_DbTable_DbStuReqPermission extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_request_permission';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllReqPermission($search=null){
    	$db=$this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$currentLang = $dbp->currentlang();
    	$colunmname='title_en';
    	$label="name_en";
    	if ($currentLang==1){
    		$colunmname='title';
    		$label="name_kh";
    	}
		$branch = $dbp->getBranchDisplay();
    	$sql="
		SELECT 
			sad.`id`
			,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id = sad.branchId LIMIT 1) AS branch_name
			,(SELECT stu_code FROM rms_student AS s WHERE s.stu_id = sad.studentId LIMIT 1) AS stu_code
			,(SELECT stu_khname FROM rms_student AS s WHERE s.stu_id = sad.studentId LIMIT 1) AS stu_name
			,g.group_code AS group_name
			,(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academic_id
			
			,sad.`amountDay`
			,CASE 
				WHEN sad.`amountDay` > 1 
					THEN CONCAT(DATE_FORMAT(sad.`fromDate`,'%d/%m/%Y'),' - ',DATE_FORMAT(sad.`toDate`,'%d/%m/%Y')) 
				ELSE DATE_FORMAT(sad.`fromDate`,'%d/%m/%Y')
			END AS permisssionDate
			,sad.`createDate`
			";
	$sql.=$dbp->caseStatusShowImage("sad.`status`");
	$sql.="	 FROM rms_student_request_permission AS sad 
				LEFT JOIN `rms_group` AS g ON sad.`groupId`=g.`id` 
			WHERE sad.`inputFrom`=1 
		";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': " sad.`createDate` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sad.`createDate` <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['branch_id'])){
    		$where.= " AND sad.branchId  =".$search['branch_id'];
    	}
    	if(!empty($search['group'])){
    		$where.= " AND sad.`groupId` =".$search['group'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND g.academic_year  =".$search['study_year'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND  g.grade =".$search['grade'];
    	}
    	$order=" ORDER BY sad.id DESC ";
    	$where.=$dbp->getAccessPermission('sad.branchId');
    	$where.=$dbp->getDegreePermission('`g`.`degree`');
		
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addStuReqPermission($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$arr = array(
				'branchId' 			=> $_data['branch_id'],
				'groupId' 			=> $_data['group'],
				'studentId' 		=> $_data['stu_name'],
				'amountDay' 		=> $_data['amountDay'],
				'fromDate' 			=> $_data['fromDate'],
				'toDate' 			=> $_data['toDate'],
				'reason' 			=> $_data['reason'],
				'sessionType' 		=> 1,
				'requestStatus' 	=> 1, // approved
				
				'createDate' 		=> date("Y-m-d H:i:s"),
				'modifyDate' 		=> date("Y-m-d H:i:s"),
				'status' 			=> 1,
				'inputFrom' 		=> 1,
				'userId' 		=> $this->getUserId(),
			);
    		$this->_name='rms_student_request_permission';
    		$requestId = $this->insert($arr);
			
			$amount_day = $_data['amountDay'];
			$date = $_data['fromDate'];
			if (!empty($amount_day)) {
				for ($i = 0; $i < $amount_day; $i++) {
					$att_date = date('Y-m-d', strtotime($date . ' + ' . $i . ' days'));

					$sDate = new DateTime($att_date);
					$dayNum = $sDate->format('N');
					if ($dayNum != 7) {
						$arr = array(
							'studentRequestId' 	=> $requestId,
							'attendence_id'	   	=> 0,
							'stu_id'		   	=> $_data['stu_name'],
							'attendence_status' => 3, //3=Permission
							'description'	   	=> $_data['reason'],
							'type'			   	=> 2, //from one student 
							'branchId'		   	=> $_data['branch_id'],
							'groupId'		   	=> $_data['group'],
							
							'attendanceDate'   	=> $att_date,
							'createDate'	   	=> date("Y-m-d H:i:s"),
							'modifyDate'	   	=> date("Y-m-d H:i:s"),
						);
						$this->_name = 'rms_student_attendence_detail';
						$this->insert($arr);
					}
				}
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
			$db->rollBack();
		}
   	}
	
	public function editStuReqPermission($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$status = empty($_data['status']) ? 0 : 1;
			$arr = array(
				'branchId' 			=> $_data['branch_id'],
				'groupId' 			=> $_data['group'],
				'studentId' 		=> $_data['stu_name'],
				'amountDay' 		=> $_data['amountDay'],
				'fromDate' 			=> $_data['fromDate'],
				'toDate' 			=> $_data['toDate'],
				'reason' 			=> $_data['reason'],
				'sessionType' 		=> 1,
				'requestStatus' 	=> 1, // approved
				'modifyDate' 		=> date("Y-m-d H:i:s"),
				
				'status' 			=> $status,
				'inputFrom' 		=> 1,
				'userId' 		=> $this->getUserId(),
			);
    		$this->_name='rms_student_request_permission';
    		$requestId = $_data['id'];
			$where=" id= ".$requestId;
			$this->update($arr, $where);
			
			$_data['requestId'] = $requestId;
			$amount_day = $_data['amountDay'];
			$date = $_data['fromDate'];
			if (!empty($amount_day)) {
				
				$oldDetailId = "";
				for ($i = 0; $i < $amount_day; $i++) {
					$att_date = date('Y-m-d', strtotime($date . ' + ' . $i . ' days'));
					$_data["requestAttDate"] = $att_date;
					$sDate = new DateTime($att_date);
					$dayNum = $sDate->format('N');
					if ($dayNum != 7) {
						$checking = $this->checkingAttReq($_data);
						if(!empty($checking)){
							if($oldDetailId ==""){
								$oldDetailId = $checking["id"];
							}else{
								$oldDetailId = $oldDetailId.",".$checking["id"];
							}
						}
					}
				}
				
				$whereDelete = " type = 2 AND studentRequestId = ".$requestId;
				if($status==1){
					if( $oldDetailId != "" ){
						$whereDelete.=" AND id NOT IN ($oldDetailId) ";
					}
				}
				$this->_name='rms_student_attendence_detail';
				$this->delete($whereDelete);
				
				if($status==1){
					for ($i = 0; $i < $amount_day; $i++) {
						$att_date = date('Y-m-d', strtotime($date . ' + ' . $i . ' days'));
						$_data["requestAttDate"] = $att_date;
						$sDate = new DateTime($att_date);
						$dayNum = $sDate->format('N');
						if ($dayNum != 7) {
							$checking = $this->checkingAttReq($_data);
							if(!empty($checking)){
								$arrDetail = array(
									'studentRequestId' 	=> $requestId,
									'attendence_id'	   	=> 0,
									'stu_id'		   	=> $_data['stu_name'],
									'attendence_status' => 3, //3=Permission
									'description'	   	=> $_data['reason'],
									'type'			   	=> 2, //from one student 
									'branchId'		   	=> $_data['branch_id'],
									'groupId'		   	=> $_data['group'],
									
									'attendanceDate'   	=> $att_date,
									'modifyDate'	   	=> date("Y-m-d H:i:s"),
								);
								$this->_name = 'rms_student_attendence_detail';
								$whereDetail=" type = 2 AND studentRequestId= ".$requestId;
								$whereDetail.=" AND id= ".$checking["id"];
								$this->update($arrDetail, $whereDetail);
							}else{
								$arr = array(
									'studentRequestId' 	=> $requestId,
									'attendence_id'	   	=> 0,
									'stu_id'		   	=> $_data['stu_name'],
									'attendence_status' => 3, //3=Permission
									'description'	   	=> $_data['reason'],
									'type'			   	=> 2, //from one student 
									'branchId'		   	=> $_data['branch_id'],
									'groupId'		   	=> $_data['group'],
									
									'attendanceDate'   	=> $att_date,
									'createDate'	   	=> date("Y-m-d H:i:s"),
									'modifyDate'	   	=> date("Y-m-d H:i:s"),
								);
								$this->_name = 'rms_student_attendence_detail';
								$this->insert($arr);
							}
						}
					}
				}
			
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
			$db->rollBack();
		}
   	}
	
	public function checkingAttReq($_data=array())
	{
		$db = $this->getAdapter();
		$requestId = empty($_data['requestId']) ? 0 : $_data['requestId'];
		if(!empty($_data['requestAttDate'])){
			$requestAttDate = empty($_data['requestAttDate']) ? "" : $_data['requestAttDate'];
			$sql = "
				SELECT 
					id
					,groupId
					,stu_id
					,attendence_status
					,description
				FROM 
				`rms_student_attendence_detail` 
				WHERE type=2 
				AND studentRequestId = $requestId 
				AND attendanceDate = '".$requestAttDate."' 
				AND stu_id = '".$_data['stu_name']."' 
				AND groupId = '".$_data['group']."' 
			";
			$sql.= " LIMIT 1 ";
			$row = $db->fetchRow($sql);
			return $row;
		}else{
			return array();
		}
		
	}
	
	public function getReqPermission($id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `rms_student_request_permission` WHERE id = " . $id;
		$sql.= " LIMIT 1 ";
		$row = $db->fetchRow($sql);
		return $row;
	}
	public function getAttendacenDetailById($id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `rms_student_attendence_detail` WHERE studentRequestId= " . $id . " ORDER BY isCompleted DESC limit 1";
		$row = $db->fetchRow($sql);
		return $row;
	}
	
	
   	
	
}