<?php

class Allreport_Model_DbTable_DbRptStudentDrop extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_drop';
    public function getAllStudentDrop($search){
    	$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
    	$branch= $dbp->getBranchDisplay();
		$viewShort = $dbp->getViewLabelDisplay("shortcut");
		
    	$str='name_en';
    	$grade="title_en";
		$stu_name="CONCAT(st.last_name,' ',st.stu_enname)";
    	if($lang==1){//for kh
    		$str = 'name_kh';
    		$grade="title";
			$stu_name="st.stu_khname";
    	}
    	$sql = "SELECT 
					st.stu_code AS stu_id,
					(SELECT b.$branch 
					FROM rms_branch AS b 
					WHERE b.br_id = gds.branch_id 
					LIMIT 1) AS branch_name,

					st.stu_khname,
					st.stu_enname,
					st.last_name,
					$stu_name AS stu_name,
					st.tel,
					CONCAT(ac.fromYear, '-', ac.toYear) AS academic_year,
					CONCAT(COALESCE(acEr.fromYear, ''), '-', COALESCE(acEr.toYear, '')) AS academicYearEnroll,
				
					(SELECT v.$viewShort 
					FROM rms_view AS v 
					WHERE v.type = 2 
					AND v.key_code = st.sex 
					LIMIT 1) AS sex,
					
					(SELECT v.$str 
					FROM rms_view AS v 
					WHERE v.type = 5 
					AND v.key_code = gds.stop_type 
					LIMIT 1) AS type,

					gds.grade,
					ids.shortcut AS gradeTitle,
					(SELECT g.group_code 
					FROM rms_group AS g 
					WHERE g.id = gds.group_id 
					LIMIT 1) AS group_name,

					stdp.note,
					stdp.date_stop,
					stdp.reason,

					(SELECT v.$str 
					FROM rms_view AS v 
					WHERE v.type = 43 
					AND v.key_code = stdp.reasonId 
					LIMIT 1) AS reasonTitle

				FROM `rms_group_detail_student` AS gds
				LEFT JOIN  rms_student_drop AS stdp  ON  stdp.stu_id = gds.stu_id
				LEFT JOIN rms_student AS st ON gds.stu_id= st.stu_id
				LEFT JOIN rms_academicyear AS ac ON ac.id = gds.academic_year
				LEFT JOIN rms_academicyear AS acEr ON acEr.id = st.academicYearEnroll
				LEFT JOIN rms_itemsdetail AS ids ON ids.id = gds.grade
	
			WHERE gds.is_maingrade = 1  AND gds.stop_type != 0 ";
    		$where="";
    	$where.=$dbp->getAccessPermission("gds.branch_id");
    	$where.=$dbp->getDegreePermission("gds.degree");
    	
    	$order=" ORDER BY ids.ordering ASC, gds.grade ASC, gds.group_id ASC, st.stu_code ASC";
    	if(empty($search)){
    		return $db->fetchAll($sql.$where.$order);
    	}
    	$from_date =(empty($search['start_date']))? '1': " stdp.date_stop >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " stdp.date_stop <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " st.last_name LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = "  (SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=`stdp`.`type` LIMIT 1) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND gds.branch_id='.$search['branch_id'];
    	}
		if(!empty($search['branchList'])){
			$branchList = implode(",", $search['branchList']);
			$where.= " AND FIND_IN_SET(gds.branch_id,'" . $branchList . "' ) ";
	   	}
    	if(!empty($search['academic_year'])){
    		$where.=' AND gds.academic_year='.$search['academic_year'];
    	}
    	if(!empty($search['grade'])){
	   		$where.=' AND gds.grade='.$search['grade'];
	   	}
	   	if($search['degree']>0){
	   		$where.=' AND `gds`.`degree`='.$search['degree'];
	   	}
    	if($search['stop_type']!=''){
    		$where.=" AND gds.stop_type =".$search['stop_type'];
    	}
	//	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
   
    function getAllRescheduleGroup($search){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
		
		$branch= $_db->getBranchDisplay();
		$label = "name_en";
		$subject = "subject_titleen";
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$subject = "subject_titlekh";
    	}
    	$sql = "SELECT 
			    	gr.group_id,
			    	(SELECT $branch FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_name,	
			    	(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gr.year_id LIMIT 1) AS years,	
			    	
			    	
			    	(SELECT group_code FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1) AS group_code,
			    	(SELECT $label FROM rms_view WHERE rms_view.key_code=gr.day_id AND rms_view.type=18 LIMIT 1)AS days,
			    	gr.from_hour,
					gr.to_hour,
					(SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.from_hour LIMIT 1) AS fromHourTitle,
					(SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.to_hour LIMIT 1) AS toHourTitle,
			    	(SELECT rms_group.session FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1 )AS session_id,
			    	(SELECT $label FROM rms_view AS v WHERE v.key_code=(SELECT rms_group.session FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1) AND v.type=4 LIMIT 1)AS `session`,
			    	(SELECT $subject FROM `rms_subject` WHERE is_parent=1 AND rms_subject.id = gr.subject_id AND subject_titlekh!='' LIMIT 1) AS subject_name,
			    	(SELECT CONCAT(teacher_name_kh,'-',teacher_name_en) FROM rms_teacher WHERE rms_teacher.id=gr.techer_id AND teacher_name_kh!='' LIMIT 1) AS teacher_name,
			    	
			    	DATE_FORMAT(gr.create_date,'%d-%m-%Y')As createDate,
					gr.create_date,
					(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = gr.user_id) AS USER,
			    	(SELECT $label FROM rms_view WHERE rms_view.key_code=gr.status AND rms_view.type=1 LIMIT 1) AS STATUS
    		FROM 
    			rms_group_reschedule AS gr  
    		WHERE gr.status=1 ";
    	$where =' ';
    			$from_date =(empty($search['start_date']))? '1': "gr.create_date >= '".$search['start_date']." 00:00:00'";
    			$to_date = (empty($search['end_date']))? '1': "gr.create_date <= '".$search['end_date']." 23:59:59'";
    			$where.= " AND ".$from_date." AND ".$to_date;
    	$order =  ' ORDER BY `gr`.`group_id` DESC ,gr.day_id ASC ,from_hour ASC ' ;
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " gr.`note` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND gr.branch_id='.$search['branch_id'];
    	}
		if(!empty($search['branchList'])){
			$branchList = implode(",", $search['branchList']);
			$where.= " AND FIND_IN_SET(gr.branch_id,'" . $branchList . "' ) ";
		}
    	if(!empty($search['academic_year'])){
    		$where.=' AND gr.year_id='.$search['academic_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND gr.group_id='.$search['group'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND  CAST(gr.from_hour AS UNSIGNED) '.$search['session'];
    	}
    	if(!empty($search['subject'])){
    		$where.=' AND  gr.subject_id ='.$search['subject'];
    	}
    	if(!empty($search['teacher'])){
    		$where.=' AND  gr.techer_id ='.$search['teacher'];
    	}
    	if(!empty($search['day'])){
    		$where.=' AND  gr.day_id ='.$search['day'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("gr.branch_id");
    	return $db->fetchAll($sql.$where.$order);
    }
    
    //reschedule by group
    function getAllReschedulebygroup($search=null){
    	$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
		$branch= $_db->getBranchDisplay();
		
		$label = "name_en";
		$subject = "subject_titleen";
		$teacherRoom = "teacher_name_en";
		$school_name = "school_nameen";
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$subject = "subject_titlekh";
			$teacherRoom = "teacher_name_kh";
			$school_name = "school_namekh";
    	}
    	$sql="
			SELECT 
				gr.id
				,gr.year_id
				,gr.branch_id
				,gr.group_id
				,g.degree as degree
				,g.group_code as group_code
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id=gr.year_id LIMIT 1) AS academicYear
				,(SELECT fromYear FROM `rms_academicyear` WHERE id=gr.year_id LIMIT 1) AS from_year
				,(SELECT toYear FROM `rms_academicyear` WHERE id=gr.year_id LIMIT 1) AS to_year
				
				,(SELECT photo FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_logo
				,(SELECT $branch FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_name
				,(SELECT $school_name FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS school_name
				,(SELECT school_namekh FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS school_namekh
				,(SELECT school_nameen FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS school_nameen
				
				,(SELECT $teacherRoom  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_id )AS teacher_room
				,(SELECT tel  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_id )AS teacher_tel
				,(SELECT $teacherRoom  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_assistance )AS teacher_ta
				,(SELECT tel  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_assistance )AS ta_tel
				,gr.day_id
				,gr.from_hour
				,gr.to_hour
				,(SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.from_hour LIMIT 1) AS fromHourTitle
				,(SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.to_hour LIMIT 1) AS toHourTitle
				,gr.subject_id
				,gr.techer_id
				,(SELECT room_name AS NAME FROM `rms_room` WHERE is_active=1 AND room_name!='' AND rms_room.room_id=g.room_id )AS room_name
				,CONCAT(itd.title,' (',ite.title,')') AS grade_name
				,REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times
			FROM 
				rms_group_reschedule AS gr 
				JOIN rms_group AS g ON g.id = gr.group_id
					LEFT JOIN rms_items AS ite ON ite.id  = g.degree
					LEFT JOIN rms_itemsdetail AS itd ON itd.id  = g.grade AND itd.title!=''
		
		";
    	 
    	$where =' WHERE 1';
    	// $from_date =(empty($search['start_date']))? '1': "gr.create_date >= '".$search['start_date']." 00:00:00'";
    	// $to_date = (empty($search['end_date']))? '1': "gr.create_date <= '".$search['end_date']." 23:59:59'";
    	// $where.= " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " gr.`note` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    
    	$order=" 
		GROUP BY gr.year_id,gr.group_id
    	ORDER BY gr.year_id,COALESCE(ite.ordering,'0'),COALESCE(itd.ordering,'0'),g.group_code,times ASC 
		";
    	if(!empty($search['branch_id'])){
    		$where.=' AND gr.branch_id='.$search['branch_id'];
    	}
		if(!empty($search['branchList'])){
			$branchList = implode(",", $search['branchList']);
			$where.= " AND FIND_IN_SET(gr.branch_id,'" . $branchList . "' ) ";
		}
    	if(!empty($search['academic_year'])){
    		$where.=' AND gr.year_id='.$search['academic_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND gr.group_id='.$search['group'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND (SELECT rms_group.degree FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1)='.$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=' AND (SELECT rms_group.grade FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1)='.$search['grade'];
    	}
    	if(!empty($search['room'])){
    		$where.=' AND (SELECT rms_group.room_id FROM rms_group WHERE rms_group.id=gr.group_id LIMIT 1)='.$search['room'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("gr.branch_id");
    	return $db->fetchAll($sql.$where.$order);
    	 
    }
 
    function getTimeSchelduleByYGS($year,$group){ /* get Time for show in schedule VD*/
    	$db=$this->getAdapter();
    	$sql="
    		SELECT 
    		gr.from_hour,
    		gr.to_hour,
			(SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.from_hour LIMIT 1) AS fromHourTitle,
			(SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.to_hour LIMIT 1) AS toHourTitle,
			REPLACE(CONCAT((SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.from_hour LIMIT 1),'-',(SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.to_hour LIMIT 1)),' ','') AS times

			FROM rms_group_reschedule AS gr 
			WHERE gr.year_id=$year AND gr.group_id=$group
			GROUP BY REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','')
			ORDER BY gr.from_hour ASC ";
    	$row = $db->fetchAll($sql);
    	return $row;
    }
    function getSubjectTeacherByScheduleAndGroup($year,$group,$time,$day){
    	$db=$this->getAdapter();
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
		$subjecct = "subject_titleen";
		$teacher = "teacher_name_en";
    	if($lang==1){// khmer
    		$subjecct = "subject_titlekh";
    		$teacher = "teacher_name_kh";
    	}
    	$sql="SELECT 
				gr.from_hour
				,REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times
				,(SELECT s.$subjecct FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name
				,(SELECT s.subject_titlekh FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name_kh
				,(SELECT s.subject_titleen FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name_en
				,(SELECT s.subject_lang FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_lang
				,(SELECT t.$teacher FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_name
				,(SELECT t.tel FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_phone
			FROM 
				rms_group_reschedule AS gr 
			WHERE 
				gr.year_id=$year 
				AND gr.group_id=$group
				AND REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') ='$time'
				AND gr.`day_id` =$day 
			LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
    function getSubjectListByYG($year,$group){
	    $db=$this->getAdapter();
	    $_db  = new Application_Model_DbTable_DbGlobal();
	    $lang = $_db->currentlang();
		$subjecct = "subject_titleen";
		$teacher = "teacher_name_en";
	    if($lang==1){// khmer
	    	$subjecct = "subject_titlekh";
	    	$teacher = "teacher_name_kh";
	    }
	    $sql="SELECT 
	    		gr.id,
	    		gr.year_id,
	    		gr.group_id,
	    		gr.day_id,
	    		gr.from_hour,
	    		gr.to_hour,
	    		gr.subject_id,
	    		gr.techer_id,
			    REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times,
			    (SELECT s.$subjecct FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name,
				(SELECT s.subject_titlekh FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name_kh,
				(SELECT s.subject_titleen FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name_en,
				(SELECT s.subject_lang FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_lang,
			    (SELECT t.$teacher FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_name,
			    (SELECT t.tel FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_phone,
			    COUNT(*)AS total_hour
		    FROM 
		    	rms_group_reschedule AS gr
		    WHERE 
		    	gr.year_id=$year
		    	AND gr.group_id=$group
		    GROUP BY 
		    	gr.group_id,
		    	gr.subject_id
		    ORDER BY 
		    	subject_name,gr.subject_id DESC 
	    ";
	    return $db->fetchAll($sql);
    }
    //end reschedule by group
    
    
    function getSubjectForCalculateTime($year_id,$group_id,$subject_id){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    				gr.id,
    				gr.year_id,
    				gr.group_id,
    				gr.day_id,
    				gr.from_hour,
    				gr.to_hour,
    				gr.subject_id,
    				gr.techer_id,
			    	REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times,
			    	(SELECT s.subject_titleen FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name,
			    	(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_name,
			    	(SELECT t.tel FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_phone
    			FROM 
    				rms_group_reschedule AS gr
    			WHERE 
    				gr.year_id=$year_id
			    	AND gr.group_id=$group_id
			    	AND gr.subject_id =$subject_id
    			ORDER BY 
    				subject_name,gr.subject_id DESC ";
    	$row = $db->fetchAll($sql);
		if(!empty($row)){
			$hour=count($row);  // section count
		}else{
			$hour=0;
		}
    	$min=0;
    	// if (!empty($row)){
	    // 	foreach ($row as $rs){
	    // 		$fromHour = explode(".", $rs['from_hour']);
	    // 		$to_hour = explode(".", $rs['to_hour']);
	    		
	    // 		$HourFro = $fromHour[0]; 
	    // 		$HourTo = $to_hour[0];	
	    		
	    // 		$MinFro = end($fromHour);
	    // 		$MinTo = end($to_hour);
	    		
	    // 		$hour = $hour + ($HourTo - $HourFro);
	    // 		$min = $min+($MinTo - $MinFro);
		
	    // 		if(($MinTo - $MinFro)<0 && $min<60){
	    // 			$hour = $hour -1;
	    // 			$min = 60 + $min;
	    // 			if($min>=60){
	    // 				$min = $min%60;
	    // 				$hour = $hour+1;
	    // 			}
	    // 		}else if($min>=60){
	    // 			$min = $min%60;
	    // 			$hour = $hour+1;
	    // 		}else if(($MinTo - $MinFro)<0){
	    // 			$hour = $hour -1;
	    // 			$min = 60 + $min;
	    // 		}
	    // 	}
    	// }
		// if($min>0){
		// 	$hour=$hour+1;
		// 	$min =0;
		// }

    	$lblHour="Hr";
    	if ($hour>1){
    		$lblHour="Hrs";
    	}
    	$hourStudy = $hour." ".$lblHour." ";
    	if ($hour==0){
    		$hourStudy = "";
    	}
    	$minStudy=$min." min";
    	if ($min==0){
    		$minStudy ="";
    	}
    	return $hourStudy.$minStudy;
    }
	
	
	public function getAllStudentDropReturn($search){
    	
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		$branch= $dbp->getBranchDisplay();
		
    	$str='name_en';
    	$colunmname="title_en";
    	if($lang==1){//for kh
    		$str = 'name_kh';
    		$colunmname="title";
    	}
    	
    	$sql = "
			SELECT  
				sdr.*,
				(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = sdr.branch_id LIMIT 1) AS branch_name,			
				(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1) AS stu_code,
				(SELECT s.tel FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1) AS tel,
				(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1) AS stu_khname,
				(SELECT CONCAT(COALESCE(s.stu_enname,''),' ',COALESCE(s.last_name.s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1) AS student_name,
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = sdr.academic_year LIMIT 1) AS academic,
				
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=sdr.degree AND type=1 LIMIT 1) AS degree,
				(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=sdr.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.group LIMIT 1 ) AS group_name,
				
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=sdr.degree_id_return AND type=1 LIMIT 1) AS degreeReturn,
				(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=sdr.grade_id_return AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeReturn,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.group_id_return LIMIT 1 ) AS groupReturn,
				sdr.return_date,
				
				(SELECT $str FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=(SELECT stdp.`type` FROM rms_student_drop AS stdp WHERE stdp.id =sdr.drop_id LIMIT 1  ) LIMIT 1 ) as typeStopTitle,
				(SELECT first_name FROM `rms_users` WHERE id=sdr.user_id LIMIT 1) AS user_name
			";
			$sql.=" FROM `rms_student_drop_return` AS sdr WHERE sdr.status=1 ";
    	$where="";
    	
    	$where.=$dbp->getAccessPermission("sdr.branch_id");
    	$where.=$dbp->getDegreePermission("sdr.degree_id_return");
    	
    	$order=" ORDER BY sdr.id DESC";
    	
    	$from_date =(empty($search['start_date']))? '1': " sdr.return_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sdr.return_date <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " REPLACE((SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1),' ','')LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT CONCAT(COALESCE(s.stu_enname,''),' ',COALESCE(s.last_name,'')) FROM `rms_student` AS s WHERE s.stu_id=sdr.stu_id LIMIT 1),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.group LIMIT 1 ),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.group_id_return LIMIT 1 ),' ','') LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
    	if(!empty($search['branch_id'])){
    		$where.=' AND sdr.branch_id='.$search['branch_id'];
    	}
		if(!empty($search['branchList'])){
			$branchList = implode(",", $search['branchList']);
			$where.= " AND FIND_IN_SET(sdr.branch_id,'" . $branchList . "' ) ";
		}
    	if(!empty($search['academic_year'])){
    		$where.=' AND sdr.academic_year='.$search['academic_year'];
    	}
    	if(!empty($search['grade'])){
	   		$where.=' AND sdr.grade_id_return='.$search['grade'];
	   	}
	   	if($search['degree']>0){
	   		$where.=' AND `sdr`.`degree_id_return`='.$search['degree'];
	   	}
		
		if($search['type']!=''){
    		$where.=" AND (SELECT stdp.`type` FROM rms_student_drop AS stdp WHERE stdp.id =sdr.drop_id LIMIT 1)=".$search['type'];
    	}
		
    	return $db->fetchAll($sql.$where.$order);
    }
}