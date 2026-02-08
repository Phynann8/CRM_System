<?php
class Foundation_Model_DbTable_DbChangeProgram extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_change_program';
	public function getUserId(){
		$dbp = new Application_Model_DbTable_DbGlobal();
		$userId = $dbp->getUserId();
		$userId= empty($userId) ? 0 : $userId;
		return $userId;
	}
function getAllChangeProgram($search)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$branch = $dbp->getBranchDisplay();
		$titleCol = "titleEn";
		if ($currentLang == 1) {
			$titleCol = "title";
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT 
				chp.id
				,(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = chp.`branchId` LIMIT 1) AS branchName
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = chp.`academicYear` LIMIT 1) AS academicYear	
				,(SELECT pt.$titleCol FROM `rms_program_type` AS pt WHERE pt.id  = chp.`programType` LIMIT 1) AS fromProgramTypeTitle
				,(SELECT pt.$titleCol FROM `rms_program_type` AS pt WHERE pt.id  = chp.`toProgramType` LIMIT 1) AS toProgramTypeTitle
				,chp.`chagneDate`
				,(SELECT COUNT(chpd.id) FROM `rms_change_program_detail` AS chpd WHERE chpd.changeId = chp.`id` AND chpd.recordType=1 LIMIT 1) amtStudent
				,(SELECT COUNT(chpd.id) FROM `rms_change_program_detail` AS chpd WHERE chpd.changeId = chp.`id` AND chpd.recordType=2 LIMIT 1) amtNewClass
				,(SELECT u.first_name FROM rms_users AS u WHERE u.id= chp.`userId` LIMIT 1 ) AS userName
		";

		$sql .= ", chp.status AS statusRecord ";
		
		$sql .= " FROM `rms_change_program` AS chp
		";

		$where = ' WHERE 1 ';
		$from_date = (empty($search['start_date'])) ? '1' : " chp.`chagneDate` >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : " chp.`chagneDate` <= '" . $search['end_date'] . " 23:59:59'";
		$where .= " AND " . $from_date . " AND " . $to_date;

		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " (SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = chp.`academicYear` LIMIT 1) LIKE '%{$s_search}%'";
			
			$where .= ' AND (' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['branch_id'])) {
			$where .= ' AND chp.`branchId`=' . $search['branch_id'];
		}
		if (!empty($search['academic_year'])) {
			$where .= ' AND chp.`academicYear`=' . $search['academic_year'];
		}
		$where .= $dbp->getAccessPermission('chp.`branchId`');
		$order =  ' ORDER BY `chp`.`id` DESC ';
		return $db->fetchAll($sql . $where . $order);
	}	
	
	public function addStudentChangeProgram($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$academicYear = $_data['academicYear'];
			$_arr= array(
				'branchId'		=>$_data['branch_id'],
				'academicYear' 	=>$_data['academicYear'],	
				'programType' 	=>$_data['programType'],
				'toProgramType' =>$_data['toProgramType'],
				'chagneDate' 	=>$_data['chagneDate'],
				'note' 			=>$_data['note'],
				
				'status'		=>1,
				'userId'		=>$this->getUserId(),
				'createDate' 	=> date("Y-m-d H:i:s"),
				'modifyDate' 	=> date("Y-m-d H:i:s"),
			);
			$this->_name='rms_change_program';
			$changeId = $this->insert($_arr);
			
			$isSingleToProgram = empty($_data['isSingleToProgram']) ? 1 : $_data['isSingleToProgram'];
			
			$dbGroup = new Foundation_Model_DbTable_DbGroup();
			$programType = empty($_data['toProgramType']) ? 1 : $_data['toProgramType'];
			if(!empty($_data['identity_student'])){
				$studentList = explode(',', $_data['identity_student']);
				foreach ($studentList as $st){
					$studentId = $_data['studentId'.$st];
					$_arr = array(
							'changeId'			=>$changeId,
							'academicYear'		=>$academicYear,
							'recordType'		=>1,
							'studentId'			=>$_data['studentId'.$st],
							'degree'			=>$_data['studentDegree'.$st],
							'grade'				=>$_data['studentGrade'.$st],
							'groupId'			=>$_data['studentGroup'.$st],
					);
					$this->_name="rms_change_program_detail";
					$this->insert($_arr);
					
					$this->_name = 'rms_group_detail_student';
					$dataMaiGrade = array(
							'programType'		=>$programType,
							'modify_date'		=>date("Y-m-d H:i:s"),
					);
					$whereMainGrade = ' stu_id = '.$studentId;
					$whereMainGrade.= ' AND itemType = 1 AND is_current = 1 AND is_maingrade=1 ';
					$this->update($dataMaiGrade, $whereMainGrade);
					
					if($isSingleToProgram==1){
						$this->_name = 'rms_group_detail_student';
						$dataSubGrade = array(
								'stop_type'			=>1,//may change to 7 as new stop type = change program
								'programType'		=>$programType,
								'modify_date'		=>date("Y-m-d H:i:s"),
						);
						$whereSubGrade = ' stu_id = '.$studentId;
						$whereSubGrade.= ' AND itemType = 1 AND is_current = 1 AND is_maingrade=0 ';
						$this->update($dataSubGrade, $whereSubGrade);
					}else{
						if(!empty($_data['identity_study'])){
							$studyList = explode(',', $_data['identity_study']);
							foreach ($studyList as $k){
								$groupId = empty($_data['group_'.$k])?0:$_data['group_'.$k];
								$isSetGroup = empty($groupId)?0:1;
								$groupInfo = $dbGroup->getGroupById($groupId);
								$_arr = array(
										'stu_id'			=>$studentId,
										'branch_id'			=>$_data['branch_id'],
										'itemType'			=>1,
										'status'			=>1,
										'group_id'			=>$groupId,
										'degree'			=>$_data['degree_'.$k],
										'grade'				=>$_data['grade_'.$k],
										'is_current'		=>1,
										'is_setgroup'		=>$isSetGroup,
										'is_maingrade'		=>0,
										'entryFrom'			=>7,
										'note'				=>"Study Info From Change Study Program Type",
										'create_date'		=>date("Y-m-d H:i:s"),
										'modify_date'		=>date("Y-m-d H:i:s"),
										'user_id'			=>$this->getUserId(),
										'programType'		=>$programType,
								);
								$academic_year = $academicYear;
								if (!empty($groupInfo)){
									$_arr['session'] = $groupInfo['session'];
									$academic_year = $groupInfo['academic_year'];
								}
								$_arr['academic_year'] = $academic_year;
								$this->_name="rms_group_detail_student";
								$this->insert($_arr);
								
								if($groupId>0){
									$this->_name = 'rms_group';
									$dataGro = array(
											'is_use'=> 1,//ប្រើប្រាស់
											'is_pass'=> 2,//កំពុងសិក្សា
									);
									$whereGroup = 'id = '.$groupId;
									$this->update($dataGro, $whereGroup);
								}
							}
						}
					}
				}
			}
			
			if(!empty($_data['identity_study'])){
				$ids = explode(',', $_data['identity_study']);
				foreach ($ids as $i){
					$_arr = array(
							'changeId'			=>$changeId,
							'academicYear'		=>$academicYear,
							'recordType'		=>2,
							'studentId'			=>0,
							'degree'			=>$_data['degree_'.$i],
							'grade'				=>$_data['grade_'.$i],
							'groupId'			=>$_data['group_'.$i],
					);
					$this->_name="rms_change_program_detail";
					$this->insert($_arr);
				}
			}
			$_db->commit();
			return $changeId;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
	}
	
	
	public function getChangeProgramById($id){
		$db = $this->getAdapter();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
		$branch = $dbg->getBranchDisplay();
		$titleCol = "titleEn";
		if ($currentlang == 1) {
			$titleCol = "title";
		}
		$sql = "
			SELECT 
				chp.*
				,(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = chp.`branchId` LIMIT 1) AS branchName
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = chp.`academicYear` LIMIT 1) AS academicYear	
				,(SELECT pt.$titleCol FROM `rms_program_type` AS pt WHERE pt.id  = chp.`programType` LIMIT 1) AS fromProgramTypeTitle
				,(SELECT pt.$titleCol FROM `rms_program_type` AS pt WHERE pt.id  = chp.`toProgramType` LIMIT 1) AS toProgramTypeTitle
				,chp.`chagneDate`
			FROM rms_change_program AS chp WHERE chp.id =$id 
		";
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
	function getChangeProgramDetail($data=array()){
		$db = $this->getAdapter();
		
		$id  = empty($data["id"]) ? 0 : $data["id"];
		$recordType  = empty($data["recordType"]) ? 1 : $data["recordType"];
		$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
		$branch = $dbg->getBranchDisplay();
		$titleCol = "titleEn";
		$colunmName = "title_en";
		$viewCol = "name_en";
		if ($currentlang == 1) {
			$titleCol = "title";
			$colunmName = "title";
			$viewCol = 'name_kh';
		}
		$sql = "
			SELECT 
				chp.*
				,(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = chp.`branchId` LIMIT 1) AS branchName
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = chp.`academicYear` LIMIT 1) AS academicYear	
				,(SELECT pt.$titleCol FROM `rms_program_type` AS pt WHERE pt.id  = chp.`programType` LIMIT 1) AS fromProgramTypeTitle
				,(SELECT pt.$titleCol FROM `rms_program_type` AS pt WHERE pt.id  = chp.`toProgramType` LIMIT 1) AS toProgramTypeTitle
				,chp.`chagneDate`
				
				,g.group_code AS groupCode
				,(SELECT r.room_name FROM rms_room AS r WHERE r.room_id = g.room_id LIMIT 1) AS roomName
				,s.stu_code AS stuCode
				,s.stu_khname AS stuNameKh
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameEn
				,(SELECT v.$viewCol FROM rms_view AS v WHERE v.type=2 and v.key_code=s.sex LIMIT 1) AS genderTitle
				,(SELECT i.$colunmName FROM `rms_items` AS i WHERE i.id=chpD.degree AND i.type=1 LIMIT 1) as degreeTitle
				,(SELECT idd.$colunmName FROM `rms_itemsdetail` AS idd WHERE idd.id=chpD.grade AND idd.items_type=1 LIMIT 1) as gradeTitle
				
			FROM rms_change_program AS chp 
				 JOIN rms_change_program_detail AS chpD ON chp.id = chpD.changeId 
				 LEFT JOIN rms_student AS s ON s.stu_id = chpD.studentId
				 LEFT JOIN rms_group AS g ON g.id = chpD.groupId
			WHERE chp.id =$id 
			AND chpD.recordType = $recordType
		";
		$row = $db->fetchAll($sql);
		return $row;
	}
	
}

