<?php

class Home_Model_DbTable_DbTeacher extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student';
	public function getUserId()
	{
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$userId = $dbGb->getUserId();
		$userId = empty($userId) ? 0 : $userId;
		return $userId;
	}
	public function getAllTeacherInfo($search)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbGb->currentlang();
		
		$db = $this->getAdapter();
		$field = 'name_en';
		$dept = 'depart_nameen';
		if ($lang == 1) {
			$field = 'name_kh';
			$dept = 'depart_namekh';
		}
		$branchLabel = $dbGb->getBranchDisplay();
		$stringJsonQue="
		CONCAT(
			'[',
			GROUP_CONCAT('{',
				'".'"subj"'.":','".'"'."',tCl.`subjectTitle`,'".'"'."',
				'".',"subjSho"'.":','".'"'."',tCl.`shortcut`,'".'"'."',
				'".',"class"'.":','".'"'."',tCl.`groupCode`,'".'"'."',
				'".',"academic"'.":','".'"'."',tCl.`academicYear`,'".'"'."',
				
			'}' ORDER BY tCl.`academicYear` DESC, tCl.`degreeOrdering` ASC,tCl.`gradeOrdering` ASC,tCl.`groupCode` ASC )
			,']'
			)
		";
		$sql = "SELECT 
			    	(SELECT b.$branchLabel FROM rms_branch AS b WHERE b.br_id=tCl.branchId LIMIT 1) AS branchName
			    	,tCl.`teacherId`
					,tCl.`academicYear`
					,tCl.`academicYearTtitle`
					,t.`teacher_code` AS teacherCode
					,t.`teacher_name_en` AS teacherNameEn
					,t.`teacher_name_kh` AS teacherNameKh
					,t.`tel`
					,t.`email`
					,t.`photo`
					,t.`sex`
					,t.`status`
					
					,(SELECT v.$field FROM rms_view v WHERE v.type=21 AND v.key_code=t.nationality LIMIT 1) AS nationality
					,(SELECT v.$field FROM rms_view v WHERE v.type=21 AND v.key_code=t.nation LIMIT 1) AS nation
					,(SELECT v.$field FROM rms_view v WHERE v.type=3  AND v.key_code=t.degree LIMIT 1) AS educationTitle
					,(SELECT v.$field FROM rms_view AS v WHERE v.type=24 AND v.key_code=t.`teacher_type` LIMIT 1 ) AS teacherTypeTitle
					,(SELECT dept.$dept FROM rms_department AS dept WHERE dept.depart_id=t.department LIMIT 1) AS teacherDeptTitle
					,(SELECT GROUP_CONCAT(it.title) FROM `rms_items` AS it WHERE FIND_IN_SET(it.id,COALESCE(t.`degreeList`,'')) LIMIT 1) AS degreeTitle
					,CASE
						WHEN  t.sex = 1 THEN '".$tr->translate("MALE")."'
						WHEN t.sex = 2 THEN '".$tr->translate("FEMALE")."'
			    	END AS sexTitle
			    	,CASE
						WHEN  t.staff_type = 1 THEN '".$tr->translate("TEACHER")."'
						WHEN t.staff_type = 2 THEN '".$tr->translate("STAFF")."'
			    	END AS staff_type_title
			    	
					,(SELECT COUNT(DISTINCT tcl2.groupId) FROM v_teaching_info AS tcl2 WHERE tcl2.teacherId = tCl.`teacherId` AND tcl2.`academicYear` = tCl.`academicYear` LIMIT 1 ) countClass
					,(SELECT COUNT(DISTINCT tcl2.subjectId) FROM v_teaching_info AS tcl2 WHERE tcl2.teacherId = tCl.`teacherId` AND tcl2.`academicYear` = tCl.`academicYear` LIMIT 1 ) countSubject

				";
		$sql.=",".$stringJsonQue." AS jsonData";
		$sql.="
		FROM v_teaching_info AS tCl 
					JOIN `rms_teacher` AS t ON t.id = tCl.`teacherId` 
				WHERE 1
		";
		$where='';
    	if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " REPLACE(tCl.groupCode,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(t.teacher_code,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(t.teacher_name_en,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(t.teacher_name_kh,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(t.tel,' ','') LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['academic_year'])){
    		$where.=' AND tCl.`academicYear` = '.$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND FIND_IN_SET(".$search['degree'].",COALESCE((SELECT GROUP_CONCAT(DISTINCT tcl2.degree) FROM v_teaching_info AS tcl2 WHERE tcl2.teacherId = tCl.`teacherId` AND tcl2.`academicYear` = tCl.`academicYear` LIMIT 1 ),'')) ";
    	}
		if(!empty($search['grade'])){
			$where.=" AND FIND_IN_SET(".$search['grade'].",COALESCE((SELECT GROUP_CONCAT(DISTINCT tcl2.grade) FROM v_teaching_info AS tcl2 WHERE tcl2.teacherId = tCl.`teacherId` AND tcl2.`academicYear` = tCl.`academicYear` LIMIT 1 ),'')) ";
    	}
		if(!empty($search['group'])){
			$where.=" AND FIND_IN_SET(".$search['group'].",COALESCE((SELECT GROUP_CONCAT(DISTINCT tcl2.groupId) FROM v_teaching_info AS tcl2 WHERE tcl2.teacherId = tCl.`teacherId` AND tcl2.`academicYear` = tCl.`academicYear` LIMIT 1 ),'')) ";
    	}
    	
    	if(!empty($search['branch_id'])){
    		$where.=' AND tCl.branchId='.$search['branch_id'];
    	}
    	if(!empty($search['staff_type'])){
    		$where.=' AND t.staff_type='.$search['staff_type'];
    	}
		
    	$order_by=" GROUP BY tCl.branchId,tCl.`academicYear`,tCl.`teacherId` ";
    
    	$where.= $dbGb->getAccessPermission('tCl.branchId');
		
    	return $db->fetchAll($sql.$where.$order_by);
	}
	
	public function getTeacherInfoById($search)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbGb->currentlang();
		
		$db = $this->getAdapter();
		$field = 'name_en';
		$dept = 'depart_nameen';
		
		$village_name = "village_name";
		$commune_name = "commune_name";
		$district_name = "district_name";
		$province = "province_en_name";
			
		if ($lang == 1) {
			$field = 'name_kh';
			$dept = 'depart_namekh';
			
			$village_name = "village_namekh";
    		$commune_name = "commune_namekh";
    		$district_name = "district_namekh";
    		$province = "province_kh_name";
		}
		$branchLabel = $dbGb->getBranchDisplay();
		$stringJsonQue="
		CONCAT(
			'[',
			GROUP_CONCAT('{',
				'".'"subj"'.":','".'"'."',tCl.`subjectTitle`,'".'"'."',
				'".',"subjSho"'.":','".'"'."',tCl.`shortcut`,'".'"'."',
				'".',"class"'.":','".'"'."',tCl.`groupCode`,'".'"'."',
				'".',"academic"'.":','".'"'."',tCl.`academicYear`,'".'"'."',
				
			'}' ORDER BY tCl.`academicYear` DESC, tCl.`degreeOrdering` ASC,tCl.`gradeOrdering` ASC,tCl.`groupCode` ASC )
			,']'
			)
		";
		$sql = "SELECT 
			    	(SELECT b.$branchLabel FROM rms_branch AS b WHERE b.br_id=tCl.branchId LIMIT 1) AS branchName
			    	,tCl.`teacherId`
					,tCl.`academicYear`
					,tCl.`academicYearTtitle`
					,t.`teacher_code` AS teacherCode
					,t.`teacher_name_en` AS teacherNameEn
					,t.`teacher_name_kh` AS teacherNameKh
					,t.`tel`
					,t.`email`
					
					,t.`photo`
					,t.`sex`
					,t.dob
					,t.pob
					,t.position
					,t.skill
					
					,(SELECT v.$field FROM rms_view v WHERE v.type=21 AND v.key_code=t.nationality LIMIT 1) AS nationality
					,(SELECT v.$field FROM rms_view v WHERE v.type=21 AND v.key_code=t.nation LIMIT 1) AS nation
					,(SELECT v.$field FROM rms_view v WHERE v.type=2  AND v.key_code=t.sex LIMIT 1) AS sexTitle
					,(SELECT v.$field FROM rms_view v WHERE v.type=3  AND v.key_code=t.degree LIMIT 1) AS educationTitle
					,(SELECT v.$field FROM rms_view AS v WHERE v.type=24 AND v.key_code=t.`teacher_type` LIMIT 1 ) AS teacherTypeTitle
					,(SELECT dept.$dept FROM rms_department AS dept WHERE dept.depart_id=t.department LIMIT 1) AS teacherDeptTitle
					,(SELECT GROUP_CONCAT(it.title) FROM `rms_items` AS it WHERE FIND_IN_SET(it.id,COALESCE(t.`degreeList`,'')) LIMIT 1) AS degreeTitle
					
			    	,CASE
						WHEN  t.staff_type = 1 THEN '".$tr->translate("TEACHER")."'
						WHEN t.staff_type = 2 THEN '".$tr->translate("STAFF")."'
			    	END AS staff_type_title
					
					,t.`home_num` As houserNo
					,t.`street_num` As streetNo
					,(SELECT v.$village_name FROM `ln_village` AS v WHERE v.vill_id = t.district_name LIMIT 1) AS villageName
					,(SELECT c.$commune_name FROM `ln_commune` AS c WHERE c.com_id = t.commune_name LIMIT 1) AS communeName
					,(SELECT d.$district_name FROM `ln_district` AS d WHERE d.dis_id = t.district_name LIMIT 1) AS districtName
					,(SELECT pro.$province from rms_province AS pro WHERE pro.province_id = t.province_id LIMIT 1) AS provinceName
			    	
					,(SELECT COUNT(DISTINCT tcl2.groupId) FROM v_teaching_info AS tcl2 WHERE tcl2.teacherId = tCl.`teacherId` AND tcl2.`academicYear` = tCl.`academicYear` LIMIT 1 ) countClass
					,(SELECT COUNT(DISTINCT tcl2.subjectId) FROM v_teaching_info AS tcl2 WHERE tcl2.teacherId = tCl.`teacherId` AND tcl2.`academicYear` = tCl.`academicYear` LIMIT 1 ) countSubject

				";
		$sql.=",".$stringJsonQue." AS jsonData";
		$sql.="
		FROM v_teaching_info AS tCl 
					JOIN `rms_teacher` AS t ON t.id = tCl.`teacherId` 
				WHERE 1
		";
		$where='';
		$teacherId = empty($search['teacherId']) ? 0 : $search['teacherId'];
    	$where.=" AND tCl.`teacherId` = ".$teacherId;
		if(!empty($search['academic_year'])){
    		$where.=' AND tCl.`academicYear` = '.$search['academic_year'];
    	}
    	$order_by=" GROUP BY tCl.branchId,tCl.`teacherId` LIMIT 1";
    	$where.= $dbGb->getAccessPermission('tCl.branchId');
		
    	return $db->fetchRow($sql.$where.$order_by);
	}

	
}
