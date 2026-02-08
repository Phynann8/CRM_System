<?php
class Foundation_Model_DbTable_DbChangeBranch extends Zend_Db_Table_Abstract
{
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}	
	function getAllBranch($br_id=null){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$branch_id = $dbgb->getAccessPermission('br_id');
		$sql="select br_id as id, CONCAT(branch_nameen) as name from rms_branch WHERE branch_nameen!='' AND  status=1  $branch_id ";
		if (!empty($br_id)){
			$sql.=" AND br_id != $br_id ";
		}
		return $db->fetchAll($sql);
	}
	function getAllToBranch($br_id=null){
		$db = $this->getAdapter();
		$sql="select br_id as id, CONCAT(branch_nameen) as name from rms_branch WHERE branch_nameen!='' AND  status=1  ";
		if (!empty($br_id)){
			$sql.=" AND br_id != $br_id ";
		}
		return $db->fetchAll($sql);
	}
	public function getAllStudentID(){
		$_db = $this->getAdapter();
		$sql = "SELECT st.stu_id as id,st.stu_code FROM `rms_student` as st,
			rms_group_detail_student as gds 
			where 
			gds.itemType=1 
			AND gds.is_pass=0 
			and gds.stu_id=st.stu_id 
			and is_setgroup=1 
			
			and st.status=1 group by gds.stu_id";
		return $_db->fetchAll($sql);		
	}
	
	
	
	public function getAllGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT group_code,id FROM `rms_group` where status = 1 and is_pass IN (0,2) ";

		return $db->fetchAll($sql);
	}
	
	public function selectAllStudentChangeGroup($search){
		$_db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		$branch = $dbgb->getBranchDisplay();
		$sql = "
		SELECT 
			scg.id
			,(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = scg.branch_id LIMIT 1) AS branch_name
			,(SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` LIMIT 1) AS code
			,(SELECT stu_khname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` LIMIT 1) AS kh_name
			,(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=2 AND `rms_view`.`key_code`=st.sex limit 1)AS sex
			,g.group_code as groupCode
			,(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = scg.to_branch LIMIT 1) AS to_branch_name
			,scg.moving_date
			,CONCAT(COALESCE(i.`shortcut`,i.$colunmname),' ',COALESCE(itd.`shortcut`,itd.$colunmname)) AS toGradeTitle
		";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("scg.status");
		
		
		$lastestYear = 0;
		$last = $dbp->getLatestAcadmicYear();
		if(!empty($last)){
			$lastestYear = empty($last["id"]) ? 0 : $last["id"];
		}
		$dbUser = new Application_Model_DbTable_DbUsers();
		$permission = $dbUser->getAccessUrl("foundation","changebranch","revert");
		if (empty($permission)){
			$sql.=" 
				,'' AS slqButton
			";
		}else{
			$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
			$urlEdit = $base_url."/foundation/changebranch/revert/id/";
			$arr=[
				"id"=>"scg.id",
				"urlEdit"=>$urlEdit,
				"title"=>"ROLLBACK",
				"btnIcon"=>"fa-repeat",
				"action"=>"onClick",
			];
			$sqlBtn=$dbp->slqRowButton($arr);
			$sql.=" 
				,CASE 
					WHEN scg.status = 0 THEN '' 
					ELSE 
						CASE 
							WHEN scg.academicYear < $lastestYear 
								THEN ''
							ELSE ".$sqlBtn." 
						END
				END AS slqButton
			";
		}
		
		$sql.="
			,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = scg.academicYear LIMIT 1) AS groupCodeSmallInfo
		    , (SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = scg.toAcademic LIMIT 1) AS toGradeTitleSmallInfo 
		";
		$sql.=" 
			FROM 
					`rms_student_change_branch` AS scg
					JOIN (rms_student AS st JOIN rms_group_detail_student AS gds ON gds.stu_id=st.stu_id AND gds.itemType=1 AND gds.is_maingrade=1 AND gds.itemType=1 AND gds.`is_current`=1 )
					ON scg.stu_id=st.stu_id 
					LEFT JOIN rms_group AS g ON g.id = scg.from_group
					LEFT JOIN  `rms_items` AS i ON i.type=1 AND i.id = scg.toDegree
					LEFT JOIN  `rms_itemsdetail` AS itd ON itd.items_type=1 AND itd.id = scg.toGrade
			WHERE  1
					 ";
		$order_by=" ORDER BY scg.id DESC";
		$where=' ';
		$from_date =(empty($search['start_date']))? '1': "scg.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "scg.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " (SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_khname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_enname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=" AND scg.branch_id=".$search['branch_id'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('scg.branch_id');
		return $_db->fetchAll($sql.$where.$order_by);
	}
	public function getStudentChangeBranchById($id){
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				sc.* 
				 ,s.`stu_id` newStudentId
				 ,COALESCE(sp.id,0) AS pmtId
			FROM 
				rms_student_change_branch AS sc 
				LEFT JOIN `rms_student` AS s ON s.`oldStudentId` =  sc.`stu_id` 
				LEFT JOIN `rms_student_payment` AS sp ON sp.`student_id` = s.`stu_id`
			WHERE sc.id =".$id." AND sc.status = 1 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('sc.branch_id');
		return $db->fetchRow($sql);
	}
	public function getDegreeAndGradeToGroup($group){
		$db = $this->getAdapter();
		$sql = "SELECT academic_year,degree,grade,session,room_id FROM rms_group WHERE id =".$group;
		return $db->fetchRow($sql);
	}
	
	
	function getStudentChangeGroup1ById($id){
		$db = new Application_Model_DbTable_DbGlobal();
		return $db->getStudentGroupInfoById($id);
	}
	function getStudentInfoById($stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT 	
		(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE st.stu_khname END) AS name,
		 st.`sex`,gds.`group_id` FROM 
		 `rms_student` AS st,
		 rms_group_detail_student AS gds 
		 WHERE 
		 gds.itemType=1 
		 AND gds.is_pass=0 and  st.stu_id=$stu_id AND st.stu_id=gds.stu_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getAllStudyInfoByStudentId($_data){
		$db = $this->getAdapter();
		$stu_id = empty($_data['stu_id'])?0:$_data['stu_id'];
		$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
		$sql="
		SELECT
			gds.*,
			gds.academic_year,
			gds.group_id,
			gds.degree,
			gds.grade,
			gds.session,
			(SELECT g.room_id FROM `rms_group` AS g WHERE g.id = gds.group_id LIMIT 1) AS room
		
			FROM
				rms_group_detail_student AS gds
			WHERE
				gds.itemType=1 
				AND gds.is_current =1
				AND gds.stu_id = $stu_id
				AND (SELECT g.branch_id FROM `rms_group` AS g WHERE g.id = gds.group_id LIMIT 1) = $branch_id
		";
		
		return $db->fetchAll($sql);
	}
	public function addStudentChangeBranch($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			
			$dbGb = new Application_Model_DbTable_DbGlobal();
			$dbstu = new Foundation_Model_DbTable_DbStudent();
			$stuInfo = $dbstu->getStudentStudyInfo($_data['studentid']);
			if (!empty($stuInfo)){
				$stu_id = $stuInfo['stu_id'];
				
				$_arr= array(
						'branch_id'		=>$_data['branch_id'],
						'stu_id'		=>$stu_id,
						
						'to_branch'		=>$_data['to_branch'],
						'moving_date'	=>$_data['moving_date'],
						'reason'		=>$_data['reason'],
						'note'			=>$_data['note'],
						
						'academicYear'	=>$stuInfo['academic_year'],
						'from_group'	=>$stuInfo['group_id'],
						'degree'		=>$stuInfo['degree'],
						'grade'			=>$stuInfo['grade'],
						
						'toAcademic'	=>$_data['academic_year'],
						'toDegree'		=>$_data['degree'],
						'toGrade'		=>$_data['grade'],
						'user_id'		=>$this->getUserId(),
						'status'		=>1,
						'create_date' 	=> date("Y-m-d H:i:s"),
						'modify_date' 	=> date("Y-m-d H:i:s")
				);
				$this->_name='rms_student_change_branch';
				$id = $this->insert($_arr);
				
				$array = array(
						'branch_id'	 => $_data['branch_id'],
						'stu_id'	=>$stu_id,
				);
				$studyStudent = $this->getAllStudyInfoByStudentId($array);
				if (!empty($studyStudent)){
					$arrData = [
						"studentId"	=>$stu_id,
						"branchId"	=>$_data['to_branch'],
						"academic_year"	=>$_data['academic_year'],
						"degree"	=>$_data['degree'],
						"grade"		=>$_data['grade'],
					];
					$newStuId = $this->duplicateStudent($arrData);
					if(!empty($newStuId)){
						$dbGg = new Application_Model_DbTable_DbGlobal();
						$stuCode = $dbGg->getnewStudentId($_data['to_branch'],$_data['degree']);
						
						$_arrStu=[
							'branch_id'		=>$_data['to_branch'],
							'stu_code'		=>$stuCode,
						];
						$this->_name = 'rms_student';
						$whereStu=" stu_id=".$newStuId;
						$this->update($_arrStu, $whereStu);
						
						$_dbStCode = new Application_Model_DbTable_DbStudentCode();
						$arrUp =[
							"referenceType" =>5,
							"referenceId" 	=>$id,
							"stuCode" 		=>$stuCode,
							"stuId" 		=>$newStuId,
							"branchId" 		=>$_data['to_branch'],
							"acadedmicYear"	=>$_data['academic_year'],
							"degree" 		=>$_data['degree'],
						];
						$_dbStCode->insertStudentCode($arrUp);
					}
					
					
					foreach ($studyStudent as $st){
						$this->_name='rms_group_detail_student';
						$arr= array(
								'is_pass'	=>1,
								'stop_type'	=>1,
								'movedType'	=>7, //Student Change Branch
								'note'=>'Change Branch'
						);
						$where = " gd_id=".$st['gd_id'];
						$this->update($arr, $where);
						
						
						$degree	=	$st['degree'];
						$grade	=	$st['grade'];
						if($st['is_maingrade']==1){ 
							$degree	=	$_data['degree'];
							$grade	=	$_data['grade'];
						}
						$arrNewBranch = array(
							'branch_id'		=>$_data['to_branch'],
							'studentId'		=>$newStuId ,
							'itemType'		=>1,
							'groupId'		=>0,
							'oldGroup'		=>$st['group_id'],
							'academicYear'	=>$_data['academic_year'],
							'feeId'			=>0,
							'oldFeeId'		=>0,
							'schoolOption'  =>$st['school_option'],
							'degree'		=>$degree,
							'grade'			=>$grade,
							'session'		=>0,
							'startDate'		=>"",
							'endDate'		=>"",
							'balance'		=>$st['balance'],
							'discountType'	=>$st['discount_type'],
							'discountAmount'=>$st['discount_amount'],
							'user_id'		=>$this->getUserId(),
							'status'		=>1,
							'create_date'	=>date('Y-m-d H:i:s'),
							'modify_date'	=>date('Y-m-d H:i:s'),
							'old_group'		=>$st['group_id'],
							'isSetGroup'	=>0,
							'stopType'		=>0,
							'isCurrent'		=>1,
							'isNewStudent'	=>0,
							'isMaingrade'	=>$st['is_maingrade'],
							'entryFrom'		=>5,//not sure
							'remark'		=>'Change Branch',
							'programType'	=>$st['programType'],
						);
						$dbGb->AddItemToGroupDetailStudent($arrNewBranch);
					
					/*
					$arr= array(
							'stu_id'		=>$stu_id,
							'old_group'		=>$st['group_id'],
							'academic_year'	=>$st['academic_year'],
							'degree'		=>$st['degree'],
							'grade'			=>$st['grade'],
							'is_maingrade'	=>$st['is_maingrade'],
							'status'	=>1,
							'is_current'	=>1,
							'is_newstudent'	=>1,
							'create_date' 	=> date("Y-m-d H:i:s"),
							'modify_date' 	=> date("Y-m-d H:i:s"),
							'user_id'		=>$this->getUserId(),
					);
					$this->insert($arr);
					
					$this->_name='rms_student';
					$array = array(
							'branch_id'		=>$_data['to_branch'],
					);
					$where = " stu_id=".$stu_id;
					$this->update($array, $where);
					*/
					
				}

				}
			}
			return $_db->commit();
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
	}
	
	function duplicateStudent($data=[]){
		$db = $this->getAdapter();
		
		$stu_id = empty($data["studentId"]) ? 0 : $data["studentId"];
		$branchId = empty($data["branchId"]) ? 0 : $data["branchId"];
		$academicYear = empty($data["academic_year"]) ? 0 : $data["academic_year"];
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$degreeId = empty($data["degree"]) ? 0 : $data["degree"];
		$stuCode = $dbGb->getnewStudentId($branchId,$degreeId);

		$userId = $this->getUserId();
		
		$sql="INSERT INTO rms_student(
					branch_id,
					stu_code,
					stu_khname,
					last_name,
					stu_enname,
					sex,
					nationality,
					nation,
					dob,
					tel,
					primary_phone,
					pob,
					home_num,
					street_num,
					village_name,
					commune_name,
					district_name,
					province_id,
					studentType,
					familyId,
					
					lang_level,
					from_school,
					know_by,
					sponser,
					sponser_phone,
					status,
					remark,
					photo,
					customer_type,
					date_bacc,
					province_bacc,
					center_bacc,
					room_bacc,
					table_bacc,
					grade_bacc,
					score_bacc,
					certificate_bacc,
					calture,
					
					create_date,
					is_setstudentid,
					street,
					vill_id,
					comm_id,
					dis_id,
					pro_id,
					audioTitle,
					studentToken,
					is_vaccined,
					is_covidTested,
					dateUpdatedCovidFeature,
					setBy,
					crm_degree,
					crm_grade,
					crm_id,
					email,
					emergency_name,
					emergency_tel,
					
					is_studenttest,
					modify_date,
					
					password,
					relationship_to_student,
					serial,
					student_option,
					student_status,
					test_id,
					test_setting_id,
					test_type,
					academicYearEnroll,
					goHomeType,
					oldStudentId,
					user_id				
				)
				SELECT
					$branchId,
					'$stuCode' AS stu_code,
					stu_khname,
					last_name,
					stu_enname,
					sex,
					nationality,
					nation,
					dob,
					tel,
					primary_phone,
					pob,
					home_num,
					street_num,
					village_name,
					commune_name,
					district_name,
					province_id,
					studentType,
					familyId,
					
					lang_level,
					from_school,
					know_by,
					sponser,
					sponser_phone,
					status,
					remark,
					photo,
					customer_type,
					date_bacc,
					province_bacc,
					center_bacc,
					room_bacc,
					table_bacc,
					grade_bacc,
					score_bacc,
					certificate_bacc,
					calture,
					
					create_date,
					is_setstudentid,
					street,
					vill_id,
					comm_id,
					dis_id,
					pro_id,
					audioTitle,
					studentToken,
					is_vaccined,
					is_covidTested,
					dateUpdatedCovidFeature,
					setBy,
					crm_degree,
					crm_grade,
					crm_id,
					email,
					emergency_name,
					emergency_tel,
					
					is_studenttest,
					modify_date,
					
					password,
					relationship_to_student,
					serial,
					student_option,
					student_status,
					test_id,
					test_setting_id,
					test_type,
					$academicYear,
					goHomeType,
					$stu_id,
					$userId
		FROM rms_student WHERE stu_id=$stu_id LIMIT 1";
		 $db->query($sql);
		return $db->lastInsertId();
		
	}
	
	public function revertChangeBranch($id){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$dbstu = new Foundation_Model_DbTable_DbStudent();
			$row = $this->getStudentChangeBranchById($id);
			if (!empty($row)){
				
				$_arr= array(
					'user_id'	=> $this->getUserId(),
					'status'	=> 0,
					'modify_date'=> date("Y-m-d H:i:s")
				);
				$this->_name='rms_student_change_branch';
				$where = "id = ".$id;
				$this->update($_arr, $where);
				
				if(!empty($row["newStudentId"])){
					$this->_name = 'rms_group_detail_student';
					$whereDelete= " stu_id =  ".$row["newStudentId"];
					$whereDelete.= " AND branch_id =  ".$row["to_branch"];
					$this->delete($whereDelete);
					
					$this->_name = 'rms_student';
					$whereDeleteSt= " stu_id =  ".$row["newStudentId"];
					$whereDeleteSt.= " AND branch_id =  ".$row["to_branch"];
					$this->delete($whereDeleteSt);
					
					$_dbStCode = new Application_Model_DbTable_DbStudentCode();
					$arrUp =[
						"referenceType" =>5,
						"referenceId" 	=>$id ,
						"stuId" 		=>$row["newStudentId"],
						"branchId" 		=>$row["to_branch"],
					];
					$_dbStCode->reverseStudCodeOfStudent($arrUp);
				}
						
				$array = array(
						'branch_id'	 => $row['branch_id'],
						'stu_id'	=>$row['stu_id'],
				);
				$studyStudent = $dbstu->getAllStudyByStudent($array);//revert old
				if (!empty($studyStudent)) foreach ($studyStudent as $st){
					$this->_name='rms_group_detail_student';
					$arrGrDetail= array(
							'is_pass'	=> 0,
							'stop_type'	=> 0,
							'note'=> 'Rollback Student Change Branch'
					);
					$whereGrDetail = " gd_id=".$st['gd_id'];
					$this->update($arrGrDetail, $whereGrDetail);
				}
			}
			return $_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
	
		}
	}
	
	
}

