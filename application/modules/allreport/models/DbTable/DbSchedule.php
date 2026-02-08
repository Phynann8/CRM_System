<?php

class Allreport_Model_DbTable_DbSchedule extends Zend_Db_Table_Abstract
{

	function getAllGroupSchedule($search=null){
    	$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
		
		$label = "name_en";
		$subject = "subject_titleen";
		$branch = "branch_nameen";
		$teacherRoom = "teacher_name_en";
		$school_name = "school_nameen";
		$dayTitle="dTitleEn";
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$subject = "subject_titlekh";
    		$branch = "branch_namekh";
			$teacherRoom = "teacher_name_kh";
			$school_name = "school_namekh";
			$dayTitle="dTitleKh";
			
    	}
		
		$stringJsonSubject="
		,CONCAT(
			'[',
			GROUP_CONCAT( DISTINCT '{',
				'".'"subjId"'.":','".'"'."',gr.`subject_id`,'".'"'."',
				'".',"dayId"'.":','".'"'."',gr.`day_id`,'".'"'."',
				'".',"frTime"'.":','".'"'."',gr.`from_hour`,'".'"'."',
				'".',"toTime"'.":','".'"'."',gr.`to_hour`,'".'"'."',
				'".',"frTimeT"'.":','".'"'."',COALESCE((SELECT t.title FROM rms_timeseting AS t WHERE t.value =gr.from_hour LIMIT 1),''),'".'"'."',
				'".',"toTimeT"'.":','".'"'."',COALESCE((SELECT t.title FROM rms_timeseting AS t WHERE t.value =gr.to_hour LIMIT 1),''),'".'"'."',
				'".',"sTitleEn"'.":','".'"'."',COALESCE(subj.`subject_titleen`,''),'".'"'."',
				'".',"sTitleKh"'.":','".'"'."',COALESCE(subj.`subject_titlekh`,''),'".'"'."',
				'".',"sShort"'.":','".'"'."',COALESCE(subj.`shortcut`,''),'".'"'."',
				'".',"sLang"'.":','".'"'."',COALESCE(subj.`subject_lang`,''),'".'"'."',
				'".',"tNameEn"'.":','".'"'."',COALESCE(t.`teacher_name_en`,''),'".'"'."',
				'".',"tNameKh"'.":','".'"'."',COALESCE(t.`teacher_name_kh`,''),'".'"'."',
				'".',"tTel"'.":','".'"'."',COALESCE(t.`tel`,''),'".'"'."',
				
			'}' ORDER BY gr.`day_id` ASC,gr.`from_hour` ASC )
			,']'
			) AS jsonSubject
		";
		
		$stringJsonDay="
		,CONCAT(
			'[',
			GROUP_CONCAT( DISTINCT '{',
				'".'"dTitleEn"'.":','".'"'."',COALESCE(dsch.`dTitleEn`,''),'".'"'."',
				'".',"dTitleKh"'.":','".'"'."',COALESCE(dsch.`dTitleKh`,''),'".'"'."',
				'".',"dTitle"'.":','".'"'."',COALESCE(dsch.$dayTitle,''),'".'"'."',
				'".',"dayShort"'.":','".'"'."',COALESCE(dsch.`dayShort`,''),'".'"'."',
				'".',"dayId"'.":','".'"'."',COALESCE(dsch.`dayId`,''),'".'"'."',
			'}' ORDER BY dsch.`dayId` ASC )
			,']'
			) AS jsonDay
		";
    	$sql="
			SELECT 
			gr.id
			,gr.year_id
			,gr.branch_id
			,gr.main_schedule_id as mainScheduleId
			,gr.group_id AS groupId
			,g.degree as degree
			,g.group_code as group_code
			,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id=gr.year_id LIMIT 1) AS academicYear
			
			,(SELECT photo FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_logo
			,(SELECT branch_nameen FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_name
			,(SELECT $school_name FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS school_name
			,(SELECT school_namekh FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS school_namekh
			,(SELECT school_nameen FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS school_nameen
			
			,(SELECT $teacherRoom  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_id )AS teacher_room
			,(SELECT tel  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_id )AS teacher_tel
			,(SELECT $teacherRoom  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_assistance )AS teacher_ta
			,(SELECT tel  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_assistance )AS ta_tel
			
			
			,(SELECT room_name AS NAME FROM `rms_room` WHERE is_active=1 AND room_name!='' AND rms_room.room_id=g.room_id ) AS room_name
			,CONCAT(itd.title,' (',ite.title,')') AS grade_name
			,REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times
			
			,sch.status as scheduleStatus
			,du.positionkh AS positionkh
			,du.stamp
			,du.signature
			,du.duty_namekh
    	
		
		";
		$sql.=$stringJsonDay;
		$sql.=$stringJsonSubject;
		$sql.="
			FROM 
			rms_group_reschedule AS gr 
			JOIN rms_group AS g ON g.id = gr.group_id
				LEFT JOIN rms_group_schedule AS sch ON sch.id  = gr.main_schedule_id
				LEFT JOIN rms_items AS ite ON ite.id  = g.degree
				LEFT JOIN rms_itemsdetail AS itd ON itd.id  = g.grade AND itd.title!=''
				LEFT JOIN rms_teacher AS t ON t.id = gr.`techer_id`
				LEFT JOIN `rms_subject` AS subj ON subj.id = gr.`subject_id`
				LEFT JOIN v_sch_daybygroup AS dsch ON  dsch.`groupId`  = gr.`group_id` AND gr.`branch_id` = dsch.`branchId`
				LEFT JOIN rms_duty AS du ON du.`degree` = g.`degree` AND du.`type` = 1
		";
    	 
    	$where =' WHERE 1';
		
		if(!empty($search['scheduleId'])){
    		$where .=' AND gr.main_schedule_id IN ('.$search['scheduleId'].')';
    	}
		if($search['status']>-1){
    		$where .=' AND sch.status ='.$search['status'];
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " gr.`note` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    
    	$order=" 
		GROUP BY gr.year_id,gr.group_id,gr.main_schedule_id
    	ORDER BY gr.year_id,COALESCE(ite.ordering,'0'),COALESCE(itd.ordering,'0'),g.group_code,times ASC 
		";
    	if(!empty($search['branch_id'])){
    		$where.=' AND gr.branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=' AND gr.year_id='.$search['academic_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND gr.group_id='.$search['group'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND g.degree='.$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=' AND g.grade='.$search['grade'];
    	}
    	if(!empty($search['room'])){
    		$where.=' AND g.room_id='.$search['room'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("gr.branch_id");
    	return $db->fetchAll($sql.$where.$order);
    }
	
	function getCountingSubjectByGroup($search=array()){
		
		$db=$this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
    	$lang = $dbp->currentlang();
		
		$subject = "subject_titleen";
    	if($lang==1){// khmer
    		$subject = "subject_titlekh";
    	}
		$sql="
			SELECT 
				gr.id
				,gr.year_id AS academicYear
				,gr.group_id AS groupId
				,gr.subject_id
				,gr.main_schedule_id as mainScheduleId
				,CASE 
					WHEN s.subject_lang =1 THEN s.subject_titlekh
					ELSE s.subject_titleen END AS subjectName
				,s.subject_titlekh AS subjectNameKh
				,s.subject_titleen AS subjectNameEn
				,s.subject_lang AS subjectLang
				,COUNT(*)AS totalHour
			FROM 
				(rms_group_reschedule AS gr  JOIN rms_group AS g ON g.id = gr.group_id) 
				LEFT JOIN rms_subject AS s ON s.id=gr.subject_id
				LEFT JOIN rms_group_schedule AS sch ON sch.id  = gr.main_schedule_id
		";
    	 
    	$where =' WHERE 1';
		if(!empty($search['scheduleId'])){
    		$where .=' AND gr.main_schedule_id IN ('.$search['scheduleId'].')';
    	}
		if($search['status']>-1){
    		$where .=' AND sch.status ='.$search['status'];
    	}
		if(!empty($search['branch_id'])){
    		$where.=' AND gr.branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=' AND gr.year_id='.$search['academic_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND gr.group_id='.$search['group'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND g.degree='.$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=' AND g.grade='.$search['grade'];
    	}
    	if(!empty($search['room'])){
    		$where.=' AND g.room_id='.$search['room'];
    	}
    	$where.=$dbp->getAccessPermission("gr.branch_id");
		$order="
		GROUP BY  gr.group_id, gr.subject_id,gr.main_schedule_id
		ORDER BY  s.subject_lang  ASC , CASE 
					WHEN s.subject_lang =1 THEN s.subject_titlekh
					ELSE s.subject_titleen END ASC ";
    	return $db->fetchAll($sql.$where.$order);
	}
	
}