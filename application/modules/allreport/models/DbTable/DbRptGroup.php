<?php

class Allreport_Model_DbTable_DbRptGroup extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_group';
	public function getStudentGroupReport($id,$search,$type){
		$_db  = new Application_Model_DbTable_DbGlobal();
	    $lang = $_db->currentlang();

		$gender_str=($lang==1)?'name_kh':'name_en';
		
	   	$db = $this->getAdapter();
	   	$sql="SELECT
					 g.gd_id,
					 gr.branch_id,
					 gr.academic_year,
					 `g`.`group_id` AS `group_id`,
					 `g`.`stu_id`   AS `stu_id`,
				  	 `s`.`stu_code` AS `stu_code`,
				     `s`.`stu_khname` AS `kh_name`,
				     `s`.`stu_enname` AS `en_name`,
				     `s`.`last_name` AS `last_name`,
				      CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS fullName,
				     `s`.`address` AS `address`,
				      s.pob,
				     `s`.`tel` AS `tel`,
				     `s`.`sex` AS `gender`,
				     DATE_FORMAT(`s`.`dob`,'%d-%m-%Y') AS `dob`
					 
					,fam.fatherNameKh AS father_khname 
					,fam.fatherName AS father_name  
					,fam.fatherNation AS father_nation
					,fam.fatherPhone AS father_phone
					
					,fam.motherNameKh AS mother_khname 
					,fam.motherName AS mother_name  
					,fam.motherPhone AS mother_phone  
					
					,fam.guardianNameKh AS guardian_khname 
					,fam.guardianName AS guardian_enname 
					,fam.guardianPhone AS guardian_tel
				   
				    ,ns.name_kh AS nationality
    				,ng.name_kh AS nation
					
					,occ.occu_name AS father_job
					,occm.occu_name AS mother_job
				  	,vt.$gender_str AS `sex`,
					`g`.`status`   AS `status`,
					`g`.`is_current`   AS `is_current`,
					`g`.`is_pass`   AS `is_pass`,
					`g`.`is_maingrade`   AS `is_maingrade`,
					`g`.`movedType`   AS `movedType`,
					s.home_num,
					s.street_num,
					v.village_namekh AS village_name,
					c.commune_namekh AS commune_name,
					d.district_namekh AS district_name,
					p.province_kh_name AS province,
				    t.teacher_name_kh AS teacher
				FROM 
					rms_student as s JOIN `rms_group_detail_student` AS g ON g.itemType=1 AND g.stu_id = s.stu_id AND g.is_maingrade=1 AND `g`.`status` = 1 
					LEFT JOIN `rms_view` vt ON vt.type=2 AND vt.key_code=s.sex
					LEFT JOIN rms_view AS ng ON ng.type=21 AND ng.key_code=s.nation
					LEFT JOIN rms_view AS ns ON ns.type=21 AND ns.key_code=s.nationality
					LEFT JOIN rms_group AS gr ON gr.id = g.group_id 
					LEFT JOIN rms_teacher AS t ON t.id = gr.teacher_id
					LEFT JOIN rms_family AS fam ON fam.id = s.familyId
					LEFT JOIN rms_occupation AS occ ON occ.occupation_id = fam.fatherJob
					LEFT JOIN rms_occupation AS occm ON occm.occupation_id = fam.motherJob
					LEFT JOIN `ln_village` AS v ON v.vill_id = s.village_name
					LEFT JOIN `ln_commune` AS c ON c.com_id = s.commune_name
					LEFT JOIN `ln_district` AS d ON d.dis_id = s.district_name
					LEFT JOIN `rms_province` AS p ON p.province_id = s.province_id
				WHERE 
					1 
					AND `g`.`movedType` !=1
		   			 ";
			if(!empty($search['group'])){
				$id= $search['group'] ;
				if (!empty($id)){
					$sql.=' AND g.group_id='.$id;
				}
			}else{
				if (!empty($id)){
					$sql.=' AND g.group_id='.$id;
				}
			}
			
			$search['study_type'] = empty($search['study_type'])?0:$search['study_type'];
			if($search['study_type']>-1){
				if($search['study_type']==1){
					$sql.=' AND (g.stop_type=2 OR g.stop_type= '.$search['study_type'].")";
				}else{
					$sql.=' AND g.stop_type= '.$search['study_type'];
				}
			}  
			
			$stuOrderBy = empty($search['stuOrderBy'])?0:$search['stuOrderBy'];
			
			$order= ' ORDER BY s.stu_khname ASC ';
			if ($stuOrderBy==1){
				$order= " ORDER By  `s`.`stu_code` ASC ";
			}elseif($stuOrderBy==2){
				$order= ' ORDER BY s.stu_khname ASC ';
			}elseif($stuOrderBy==3){
				$order= " ORDER BY CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) ASC ";
			}
			
			$dbp = new Application_Model_DbTable_DbGlobal();
			$sql.=$dbp->getAccessPermission("gr.branch_id");
			$sql.=$dbp->getDegreePermission('gr.degree');
			
		   	if(!empty($search['adv_search'])){
		   		$s_where = array();
		   		$s_search = addslashes(trim($search['adv_search']));
			   		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			   		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
		   			$s_where[] = " stu_code LIKE '%{$s_search}%'";
		   		$sql .=' AND ( '.implode(' OR ',$s_where).')';
		   	}
		   	if(!empty($search['branch_id'])){
		   		$sql.=' AND gr.branch_id = '.$search['branch_id'];
		   	}
			if(!empty($search['branchList'])){
				$branchList = implode(",", $search['branchList']);
				$sql.= " AND FIND_IN_SET(gr.branch_id,'" . $branchList . "' ) ";
			}
		   	if(!empty($search['academic_year'])){
		   		$sql.=' AND gr.academic_year = '.$search['academic_year'];
		   	}
		   	if(!empty($search['group'])){
		   		$sql.=' AND gr.id = '.$search['group'];
		   	}
		 return $db->fetchAll($sql.$order);
	}
	public function getGroupDetailReport($search){//using
	   	$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
	   	$lang = $dbp->currentlang();
		$branch= $dbp->getBranchDisplay();
		
		$label = "name_en";
		$grade = "title_en";
		$degree = "title_en";
		$titleCol = "title";
	   	if($lang==1){// khmer
	   		$label = "name_kh";
	   		$grade = "title";
	   		$degree = "title";
			$titleCol = "titleKh";

	   	}
	   	//(SELECT	$label FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`,
	   	$sql = "SELECT
				   	`g`.`id`
					,b.$branch AS branch_name
					,`g`.`group_code` AS `group_code`
					,CONCAT(ac.fromYear,'-',ac.toYear) AS academic
					,`g`.`semester` AS `semester`
					,it.$degree AS degree
					,itd.$grade as grade
					,p.$titleCol as `session`
					,`r`.`room_name` AS `room_name`
					,(SELECT $label FROM `rms_view` WHERE `rms_view`.`type` = 9 AND `rms_view`.`key_code` = `g`.`is_pass` LIMIT 1) AS `status`
					,COUNT(DISTINCT CASE WHEN sg.itemType = 1 AND sg.movedType != 1 AND s.status = 1 AND sg.is_newstudent = 1 THEN sg.stu_id END ) AS New_Student
					,COUNT(DISTINCT CASE WHEN sg.itemType = 1 AND sg.movedType != 1 AND s.status = 1 THEN sg.stu_id END ) AS Num_Student
					,COUNT(DISTINCT CASE WHEN sg.itemType = 1 AND sg.movedType != 1 AND s.status = 1 AND sg.stop_type != 0 THEN sg.stu_id END ) AS student_drop
					
				FROM 
	   				`rms_group` `g`
					LEFT JOIN `rms_branch` AS b  ON b.br_id = g.branch_id
					LEFT JOIN `rms_academicyear` AS ac ON ac.id = g.academic_year
					LEFT JOIN rms_items it ON it.id = g.degree AND it.type = 1
					LEFT JOIN `rms_itemsdetail` itd ON `itd`.`id`=`g`.`grade` AND `itd`.`items_type`=1
					LEFT JOIN `rms_parttime_list` AS p ON p.id = `g`.`session`
					LEFT JOIN `rms_room` `r` ON `r`.`room_id` = `g`.`room_id`
					LEFT JOIN rms_group_detail_student sg ON sg.group_id = g.id
					LEFT JOIN rms_student s ON s.stu_id = sg.stu_id
	   			WHERE 
	   				 group_code != '' AND g.status=1 ";
	   	$where=" ";
	   	if(!empty($search['adv_search'])){
	   		$s_where = array();
	   		$s_search = addslashes(trim($search['adv_search']));
	   		$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
	   		$s_where[] = " `g`.`semester` LIKE '%{$s_search}%'";
	   		$sql .=' AND ( '.implode(' OR ',$s_where).')';
	   	}
	   	if(!empty($search['branch_id'])){
	   		$where.=' AND g.branch_id='.$search['branch_id'];
	   	}
		if(!empty($search['branchList'])){
			$branchList = implode(",", $search['branchList']);
			$where.= " AND FIND_IN_SET(g.branch_id,'" . $branchList . "' ) ";
		}
	   	if(!empty($search['academic_year'])){
	   		$where.=' AND g.academic_year='.$search['academic_year'];
	   	}
	   	if(!empty($search['teacher'])){
	   		$where.=' AND g.teacher_id='.$search['teacher'];
	   	}
	   	if(!empty($search['grade'])){
	   		$where.=' AND g.grade='.$search['grade'];
	   	}
	   	if($search['room']>0){
	   		$where.=' AND `g`.`room_id`='.$search['room'];
	   	}
	   	if($search['degree']>0){
	   		$where.=' AND `g`.`degree`='.$search['degree'];
	   	}
	   	if(!empty($search['school_option'])){
	   		$where.=' AND g.school_option='.$search['school_option'];
	   	}
	   	if(!empty($search['group'])){
	   		$where.=' AND g.id='.$search['group'];
	   	}
	   	if($search['study_status']>=0){
	   		$where.=' AND g.is_pass='.$search['study_status'];
	   	}
		if(!empty($search['branchList'])){
			$branchList = implode(",", $search['branchList']);
			$where.= " AND (FIND_IN_SET(g.branch_id,'" . $branchList . "' ) ) ";
	   	}
	   
	   	$where.=$dbp->getAccessPermission('g.branch_id');
	   	$where.=$dbp->getDegreePermission('g.degree');
	   	$where.= $dbp->getSchoolOptionAccess('sg.school_option');
	   	$order = ' GROUP BY g.id ORDER BY g.branch_id , g.academic_year DESC ,`g`.`degree`,`g`.`grade`,`g`.`is_pass` ASC ,`g`.`group_code` ASC ';
	   	return $db->fetchAll($sql.$where.$order);
	}

	public function getGroupDetailByID($id){
	   	$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
	   	$lang = $dbp->currentlang();
		$branch= $dbp->getBranchDisplay();
		
		$grade = "title_en";
		$degree = "title_en";
	   	if($lang==1){// khmer
	   		$grade = "title";
	   		$degree = "title";
	   	}
	   	$sql = "
			SELECT
				`g`.`id`
				,`g`.`branch_id`
				,g.academic_year
				,b.$branch AS branch_name
				,b.school_nameen AS school_nameen
				,b.photo AS branch_logo
				,`g`.`group_code`    AS `group_code`
				,CONCAT(ac.fromYear,'-',ac.toYear) AS academic
				,`g`.`semester` AS `semester`
				,`g`.`degree` as degree_id
				,i.$degree as degree
				,it.$grade as grade
				,v.`name_en` AS `session`
				,r.`room_name` AS `room_name`
				,`g`.`start_date`
				,`g`.`expired_date`
				,`g`.`note`
				,`g`.`time`
				,te.teacher_name_en AS teacher_name_en
				,te.teacher_name_kh AS teacher_name_kh
				,vs.`name_en` AS `status`
				,(SELECT COUNT(`stu_id`) FROM `rms_group_detail_student` WHERE itemType=1 AND `group_id`=`g`.`id`) AS Num_Student
			FROM 
				`rms_group` `g`
				LEFT JOIN rms_branch AS b ON b.br_id=g.branch_id
				LEFT JOIN rms_academicyear ac ON ac.id=g.academic_year
				LEFT JOIN rms_items i ON i.id=g.degree AND i.type=1
				LEFT JOIN rms_itemsdetail it ON it.id=g.grade AND it.items_type=1
				LEFT JOIN rms_view v ON v.type=4 AND v.key_code=g.session
				LEFT JOIN rms_view AS vs ON vs.type=1 AND vs.key_code=g.status
				LEFT JOIN rms_room r ON r.room_id=g.room_id
				LEFT JOIN rms_teacher te ON te.id=g.teacher_id
			WHERE 
				`g`.`id`=".$id." ";
	   
	   	$sql.=$dbp->getAccessPermission("g.branch_id");
		$sql.=$dbp->getDegreePermission('g.degree');

	   	$sql.="  LIMIT 1 ";
	   	return $db->fetchRow($sql);
	}
	
	function getAllTeacherByGroup($group_id){
		$db = $this->getAdapter();
		$sql=" 
			SELECT 
				t.id
				,t.`teacher_name_kh` AS name
			FROM
				rms_group_subject_detail AS gsd
				JOIN rms_teacher AS t ON gsd.teacher = t.id
			WHERE 
				1
				AND t.teacher_name_kh!=''
				AND gsd.group_id =  $group_id	
			";
		$sql.=" GROUP BY t.teacher_name_kh";
		return $db->fetchAll($sql);
	}
	
	function getAllSubjectByGroup($group_id){
		$db = $this->getAdapter();
		$sql=" SELECT
					s.id,
					CONCAT(s.`subject_titlekh`,'-',s.`subject_titleen`) AS name
				FROM
					rms_group_subject_detail AS gsd,
					rms_subject AS s
				WHERE
					gsd.subject_id = s.id
					AND gsd.group_id =  $group_id
			";
		return $db->fetchAll($sql);
	}
	function UpdateAmountStudent($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$ids = explode(",", $data['identity']);
			$iddetail ="";
			if (!empty($ids)) foreach ($ids as $id){
				if (empty($iddetail)){
					if (!empty($data['gd_id'.$id])){
						$iddetail=$data['gd_id'.$id];
					}
				}else{
					if (!empty($data['gd_id'.$id])){
						$iddetail=$iddetail.",".$data['gd_id'.$id];
					}
				}
			}
			$this->_name="rms_group_detail_student";
			$where1=" group_id=".$data['group_id'];
			if(!empty($iddetail)){
				$where1.=" AND gd_id NOT IN (".$iddetail.")";
			}
			$this->delete($where1);
			$db->commit();
		}catch(exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	function getScoreSettingIdByGroup($group_id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `rms_score_eng` AS s WHERE s.group_id = $group_id
			AND s.status=1
			GROUP BY s.score_setting
			ORDER BY s.id DESC 
			LIMIT 1";
		return $db->fetchRow($sql);
	}
	function checkScorePolicyMoreThanOne($group_id){
		$db = $this->getAdapter();
		$sql="SELECT s.score_setting FROM `rms_score_eng` AS s WHERE s.group_id = $group_id 
				AND s.status=1
				GROUP BY s.score_setting
				ORDER BY s.id DESC ";
		return $db->fetchAll($sql);
	}
	function getScoreEngByStuAndType($group_id,$stu_id,$typescore){
		$db = $this->getAdapter();
		$sql="SELECT sed.* FROM `rms_score_eng_detail` AS sed,`rms_score_eng` AS se
			WHERE sed.score_id=se.id
			AND se.group_id=$group_id AND sed.student_id=$stu_id
			AND se.exame_type=$typescore 
			AND se.status=1
			ORDER BY sed.id DESC
		LIMIT 1";
		return $db->fetchRow($sql);
	}
	
       
}