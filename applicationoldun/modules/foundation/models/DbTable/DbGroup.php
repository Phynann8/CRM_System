<?php

class Foundation_Model_DbTable_DbGroup extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_group';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function checkGroupExits($_data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT id FROM rms_group WHERE branch_id =" . $_data['branch_id'] . " AND academic_year =" . $_data['academic_year'];
		$sql .= " AND group_code='" . $_data['group_code'] . "'";
		$sql .= " AND degree='" . $_data['degree'] . "'";
		$rs = $db->fetchOne($sql);
		if (!empty($rs)) {
			return 1;
		}
		return null;
	}
	public function AddNewGroup($_data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$dbg = new Application_Model_DbTable_DbGlobal();
			$schoolOption = $dbg->getSchoolOptionbyDegree($_data['degree']);

			$_arr = array(
				'branch_id' 	=> $_data['branch_id'],
				'group_code' 	=> $_data['group_code'],
				'room_id' 		=> $_data['room'],
				'academic_year' => $_data['academic_year'],
				'semester' 		=> $_data['semester'],
				'session' 		=> $_data['part_time_list'],
				'time' 			=> $_data['time'],
				'degree' 		=> $_data['degree'],
				'grade' 		=> $_data['grade'],
				'school_option' => $schoolOption,
				'date' 			=> date("Y-m-d"),
				'status'   		=> 1,
				'teacher_id'   	=> $_data['teacher_id'],
				'teacher_assistance' => $_data['teacher_ass'],
				'note'   		=> $_data['notes'],
				'user_id'	 	=> $this->getUserId(),
				'total_score' 		=> $_data['total_max_score'],
				'amount_subject' 	=> $_data['divide_subject'],
				'max_average' 		=> $_data['max_average'],

				'semesterTotalScore' 	=> $_data['semesterTotalScore'],
				'semesterTotalSubject' 	=> $_data['semesterTotalSubject'],
				'semesterTotalAverage' 	=> $_data['semesterTotalAverage'],
				'semesterPercentage' 	=> $_data['semesterPercentage'],

				'is_use' 		=> 0,
				'gradingId' 		=> empty($_data['gradingId']) ? 0 : $_data['gradingId'],
			);
			if (EDUCATION_LEVEL == 1) {
				$_arr['calture'] = $_data['calture'];
			}

			$id = $this->insert($_arr);
			$this->_name = 'rms_group_subject_detail';
			if (!empty($_data['identity1'])) {
				$ids = explode(',', $_data['identity1']);
				foreach ($ids as $i) {
					$_dbmoddel = new Global_Model_DbTable_DbSubjectExam();

					$arr = array(
						'group_id'		=> $id,
						'subject_id'	=> $_data['group_subject_study_' . $i],
						'max_score'		=> $_data['max_score' . $i],
						'score_short'	=> $_data['scoreshort_' . $i],
						'amount_subject' => $_data['amount_subject' . $i],
						'semester_max_score' => $_data['semester_max_score' . $i],
						'amount_subject_sem' => $_data['amount_subject_semester' . $i],
						'teacher'   	=> $_data['teacher_' . $i],
						'note'   		=> $_data['group_note_' . $i],
						'date' 			=> date("Y-m-d"),
						'user_id'		=> $this->getUserId(),
					);
					$this->insert($arr);
				}
			}
			$db->commit();
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}

	public function updateGroup($_data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$dbg = new Application_Model_DbTable_DbGlobal();
			$schoolOption = $dbg->getSchoolOptionbyDegree($_data['degree']);

			$_arr = array(
				'branch_id' 	=> $_data['branch_id'],
				'group_code' 	=> $_data['group_code'],
				'room_id' 		=> $_data['room'],
				'academic_year' => $_data['academic_year'],
				'semester' 		=> $_data['semester'],
				'session' 		=> $_data['part_time_list'],
				'time' 			=> $_data['time'],
				'degree' 		=> $_data['degree'],
				'grade'		 	=> $_data['grade'],
				'school_option' => $schoolOption,
				// 					'start_date' 	=> $_data['start_date'],
				// 					'expired_date'	=> $_data['end_date'],
				'date' 			=> date("Y-m-d"),
				'teacher_id'   	=> $_data['teacher_id'],
				'teacher_assistance' => $_data['teacher_ass'],
				'status'   		=> $_data['status'],
				'note'   		=> $_data['notes'],
				'is_pass'   	=> $_data['is_pass'],
				'total_score' 		=> $_data['total_max_score'],
				'amount_subject' 	=> $_data['divide_subject'],
				'max_average' 		=> $_data['max_average'],

				'semesterTotalScore' 	=> $_data['semesterTotalScore'],
				'semesterTotalSubject' 	=> $_data['semesterTotalSubject'],
				'semesterTotalAverage' 	=> $_data['semesterTotalAverage'],
				'semesterPercentage' 	=> $_data['semesterPercentage'],

				'user_id'	  		=> $this->getUserId(),
				'gradingId' 		=> empty($_data['gradingId']) ? 0 : $_data['gradingId'],
			);

			if (EDUCATION_LEVEL == 1) {
				$_arr['calture'] = $_data['calture'];
			}
			$where = $this->getAdapter()->quoteInto("id=?", $_data['id']);
			$this->_name = 'rms_group';
			$this->update($_arr, $where);

			$this->_name = 'rms_group_subject_detail';
			$where = 'group_id = ' . $_data['id'];
			$this->delete($where);
			$_dbmoddel = new Global_Model_DbTable_DbSubjectExam();
			if (!empty($_data['identity1'])) {
				$ids = explode(',', $_data['identity1']);
				foreach ($ids as $i) {


					$arr = array(
						'group_id'		=> $_data['id'],
						'subject_id'	=> $_data['group_subject_study_' . $i],
						'max_score'		=> $_data['max_score' . $i],
						'score_short'	=> $_data['scoreshort_' . $i],
						'amount_subject' => $_data['amount_subject' . $i],
						'semester_max_score' => $_data['semester_max_score' . $i],
						'amount_subject_sem' => $_data['amount_subject_semester' . $i],
						'teacher'   	=> $_data['teacher_' . $i],
						'note'   		=> $_data['group_note_' . $i],
						'date' 			=> date("Y-m-d"),
						'user_id'		=> $this->getUserId()
					);
					$this->insert($arr);
				}
			}

			return $db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getGroupById($id)
	{
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				g.*
				,it.isExtraCourse
			FROM rms_group as g 
				LEFT JOIN rms_items AS it ON it.type=1 AND it.id = g.degree
			WHERE g.id = " . $db->quote($id);

		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getDegreePermission('g.degree');
		$sql .= $dbp->getAccessPermission('g.branch_id');
		$sql .= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` )');
		$sql .= " LIMIT 1";
		$row = $db->fetchRow($sql);
		return $row;
	}
	public function getGroupSubjectById($id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_group_subject_detail WHERE group_id = " . $db->quote($id);
		$row = $db->fetchAll($sql);
		return $row;
	}
	function getAllGroups($search)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$branch = $dbp->getBranchDisplay();
		
		$colunmname = 'title_en';
		$label = "name_en";
		$titleCol = "title";
		if ($currentLang == 1) {
			$colunmname = 'title';
			$label = "name_kh";
			$titleCol = "titleKh";
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$stringCountStudent = " COALESCE((SELECT COUNT(gds.gd_id) FROM `rms_group_detail_student` AS gds WHERE gds.group_id = g.id AND gds.itemType =1 AND gds.movedType!=1 AND gds.stop_type NOT IN (1,2) LIMIT 1),0) ";
		$sql = "SELECT 
			`g`.`id`
			,b.$branch AS branch_name
			,`g`.`group_code` AS `titleRecord`
			,CONCAT(ac.fromYear,'-',ac.toYear) AS academicYear	
			,p.$titleCol AS session
			,`r`.`room_name`
			,`t`.`teacher_name_kh` AS teacher
			,`time`	
			,v.$label as processingRecord 
			,u.first_name as userName
			,g.status AS statusRecord ";
		$sql .= "
			,CONCAT(COALESCE(i.shortcut,i.$colunmname),' ',COALESCE((SELECT COALESCE(id.shortcut,id.$colunmname)  FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1),''),' ".$tr->translate('AMOUNT_STUDENT')." ',$stringCountStudent ) AS subTitleRecord
			,CASE 
				WHEN g.is_pass = 0 THEN 'bg-label-secondary' 
				WHEN g.is_pass = 1 THEN 'bg-label-success' 
				WHEN g.is_pass = 2 THEN 'bg-label-primary' 
				WHEN g.is_pass = 3 THEN 'bg-label-info' 
				WHEN g.is_pass = 4 THEN 'bg-label-success' 
				ELSE 'bg-label-secondary' 
			END as processingBg ";
		$sql .= " FROM `rms_group` `g` 
				LEFT JOIN `rms_items` i ON i.type=1 AND i.id = `g`.`degree`
				LEFT JOIN `rms_branch` b ON b.br_id = g.branch_id
				LEFT JOIN `rms_academicyear` ac ON ac.id = g.academic_year
				LEFT JOIN rms_parttime_list p ON p.id = g.session
				LEFT JOIN `rms_room` `r` ON `r`.`room_id` = `g`.`room_id`
				LEFT JOIN rms_teacher t ON t.id = g.teacher_id
				LEFT JOIN rms_view v ON v.key_code=g.is_pass AND v.type=9 
				LEFT JOIN rms_users AS u ON u.id= g.`user_id`
		";

		$where = ' WHERE 1 ';
		$from_date = (empty($search['start_date'])) ? '1' : "g.date >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : "g.date <= '" . $search['end_date'] . " 23:59:59'";
		$where .= " AND " . $from_date . " AND " . $to_date;

		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
			$s_where[] = " `g`.`semester` LIKE '%{$s_search}%'";
			$s_where[] = " `r`.`room_name` LIKE '%{$s_search}%'";
			$where .= ' AND (' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['academic_year'])) {
			$where .= ' AND g.academic_year=' . $search['academic_year'];
		}
		if (!empty($search['grade'])) {
			$where .= ' AND g.grade=' . $search['grade'];
		}
		if (!empty($search['degree'])) {
			$where .= ' AND `g`.`degree`=' . $search['degree'];
		}
		if (!empty($search['partTimeList'])) {
			$where .= ' AND g.session=' . $search['partTimeList'];
		}
		if ($search['status']!="" AND $search['status'] > -1) {
			$where .= ' AND g.status=' . $search['status'];
		}
		if (!empty($search['branch_id'])) {
			$where .= ' AND g.branch_id=' . $search['branch_id'];
		}
		if (!empty($search['teacher'])) {
			$where .= ' AND g.teacher_id=' . $search['teacher'];
		}
		if (!empty($search['is_pass']) and $search['is_pass'] > -1) {
			$where .= ' AND g.is_pass=' . $search['is_pass'];
		}
		$where .= $dbp->getAccessPermission('g.branch_id');
		$where .= $dbp->getDegreePermission('g.degree');
		$where .= $dbp->getSchoolOptionAccess('i.schoolOption');
		$order =  ' ORDER BY `g`.`id` DESC ';
		return $db->fetchAll($sql . $where . $order);
	}

	function getAllYears()
	{
		$db = new Application_Model_DbTable_DbGlobal();
		return $db->getAllYear();
	}

	public function getAllSubjectStudy($opt = null, $schoolOption = null)
	{
		$_db = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$rows = $_db->getAllSubjectStudy($schoolOption);
		array_unshift($rows, array('id' => -1, "name" => $tr->translate("ADD_NEW_SUBJECT"), "shortcut" => ""));
		if ($opt != null) {
			return $rows;
		}
		$options = '<option value="0">' . $tr->translate("SELECT_SUBJECT") . '</option>';
		if (!empty($rows)) foreach ($rows as $value) {
			$options .= '<option value="' . $value['id'] . '" >' . htmlspecialchars($value['name'], ENT_QUOTES) . '</option>';
		}
		return $options;
	}

	public function getAllTeacherOption($schoolOption = null, $branch_id = null)
	{
		$_db = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$teacher = $this->getAllTeacher($schoolOption, $branch_id);
		array_unshift($teacher, array('id' => -1, "name" => $tr->translate("ADD_NEW")));
		$teacher_options = '<option value="0">' . $tr->translate("PLEASE_SELECT") . '</option>';
		if (!empty($teacher)) foreach ($teacher as $value) {
			$teacher_options .= '<option value="' . $value['id'] . '" >' . htmlspecialchars($value['name'], ENT_QUOTES) . '</option>';
		}
		return $teacher_options;
	}
	function getParentSubject()
	{
		$db = $this->getAdapter();
		$sql = "select id,subject_titlekh as name from rms_subject where is_parent =1 and status=1 ";
		return $db->fetchAll($sql);
	}
	function getAllYear()
	{
		$db = $this->getAdapter();
		$sql = "select id,CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')')as years from rms_tuitionfee ";
		return $db->fetchAll($sql);
	}
	public function getAllFecultyName()
	{
		$_db = new Application_Model_DbTable_DbGlobal();
		return $_db->getAllItems(1, null);
	}



	public function getDeptSubjectById($id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_dept_subject_detail WHERE dept_id = " . $db->quote($id);
		$row = $db->fetchAll($sql);
		return $row;
	}

	function getAllTeacher($schoolOptin = null, $branch_id = null)
	{
		$db = $this->getAdapter();
		$sql = " SELECT id,
				teacher_name_kh  as name 
			FROM rms_teacher 
			WHERE status=1 and staff_type=1 and teacher_name_kh!='' ";
		if (!empty($schoolOptin)) {
			$sql .= " AND schoolOption =$schoolOptin";
		}
		if (!empty($branch_id)) {
			$sql .= " AND branch_id =$branch_id";
		}
		return $db->fetchAll($sql);
	}
	function getTeacherByID($teacher_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_teacher AS g WHERE g.id=$teacher_id LIMIT 1";
		return $db->fetchRow($sql);
	}

	public function getGroupInfo($id)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$branch = $dbp->getBranchDisplay();

		$label = "name_en";
		$titleCol = "title_en";
		if ($currentLang == 1) {
			
			$label = "name_kh";
			$titleCol = "title";
		}
		$sql = "
			SELECT 
				g.*
				,b.$branch AS branchName
				,it.isExtraCourse
				,it.$titleCol AS degreeName
				,itd.$titleCol AS gradeName
				,v.$label AS processingRecord 
			FROM rms_group as g 
				LEFT JOIN rms_items AS it ON it.type=1 AND it.id = g.degree
				LEFT JOIN rms_itemsdetail AS itd ON itd.id = g.grade
				LEFT JOIN rms_branch AS b ON b.br_id = g.branch_id
				LEFT JOIN rms_view AS v ON v.key_code=g.is_pass AND v.type=9 
			WHERE g.is_use = 0

			AND g.id NOT IN (
                SELECT DISTINCT group_id 
                FROM rms_group_detail_student 
                WHERE group_id IS NOT NULL
            )
			AND g.id NOT IN (
                SELECT DISTINCT group_id 
                FROM rms_group_schedule 
                WHERE group_id IS NOT NULL
            )
			AND g.id = " . $db->quote($id);

		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getDegreePermission('g.degree');
		$sql .= $dbp->getAccessPermission('g.branch_id');
		$sql .= $dbp->getSchoolOptionAccess('it.schoolOption');
		$sql .= " LIMIT 1";
		$row = $db->fetchRow($sql);
		return !empty($row) ? $row:0;
	}

	function deleteGroup($_data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			
			$id= $_data['id'];
			$this->_name = "rms_group";
			$where = " id = $id";
			$this->delete($where);
			$db->commit();
			return 1;
		} catch (exception $e) {
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
}
