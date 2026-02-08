<?php
class Allreport_Model_DbTable_DbAttendanceReport extends Zend_Db_Table_Abstract
{
	public  function getCountingAllClass($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql = "
			SELECT 
				COUNT(g.id)
			FROM 
				rms_group AS g 
			WHERE 1
				AND g.is_use = 1
				AND g.status = 1
		";
		$sql.='';
		if(!empty($search["branch_id"])){
			$sql.=' AND g.branch_id = '.$search["branch_id"];
		}
		if(!empty($search["academic_year"])){
			$sql.=' AND g.academic_year = '.$search["academic_year"];
		}
		if(!empty($search["degree"])){
			$sql.=' AND g.degree = '.$search["degree"];
		}
		if(!empty($search["grade"])){
			$sql.=' AND g.grade = '.$search["grade"];
		}
		$sql .= $dbGb->getAccessPermission('g.branch_id');
		$sql .= $dbGb->getDegreePermission('g.degree');
		$sql .= $dbGb->getSchoolOptionAccess('g.school_option');
		return $_db->fetchOne($sql);
	}
	
	public  function getCountingAllClassHasIssuedAtt($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql = "
			SELECT 
			  COUNT(DISTINCT(att.group_id)) AS groupId
			FROM  rms_student_attendence AS att
				JOIN rms_group AS g  ON g.id = att.group_id
			WHERE 1
		";
		$sql.='';
		if(!empty($search["currentDate"])){
			$date = new DateTime($search['currentDate']);
			$currentDate =  $date->format("Y-m-d");
			$sql.=" AND att.date_attendence = DATE_FORMAT('$currentDate', '%Y/%m/%d') ";
		}
		if(!empty($search["branch_id"])){
			$sql.=' AND g.branch_id = '.$search["branch_id"];
		}
		if(!empty($search["academic_year"])){
			$sql.=' AND g.academic_year = '.$search["academic_year"];
		}
		if(!empty($search["degree"])){
			$sql.=' AND g.degree = '.$search["degree"];
		}
		if(!empty($search["grade"])){
			$sql.=' AND g.grade = '.$search["grade"];
		}
		$sql .= $dbGb->getAccessPermission('g.branch_id');
		$sql .= $dbGb->getDegreePermission('g.degree');
		$sql .= $dbGb->getSchoolOptionAccess('g.school_option');
		return $_db->fetchOne($sql);
	}
	
	public  function getCountingAttedanceSummary($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql = "
			SELECT 
				COUNT(IF(v.`maxAttendenceStatus` = 2,v.maxAttendenceStatus,NULL)) AS totalAbsent
				,COUNT(IF(v.`maxAttendenceStatus` = 3,v.maxAttendenceStatus,NULL)) AS totalPermission
				,COUNT(IF(v.`maxAttendenceStatus` = 4,v.maxAttendenceStatus,NULL)) AS totalLate
				,COUNT(IF(v.`maxAttendenceStatus` = 5,v.maxAttendenceStatus,NULL)) AS totalEarlyLate
			FROM  `v_studentattendancestatusperdate` AS v
			WHERE 1
		";
		$sql.='';
		if(!empty($search["branch_id"])){
			$sql.=' AND v.branchId = '.$search["branch_id"];
		}
		if(!empty($search["currentDate"])){
			$date = new DateTime($search['currentDate']);
			$currentDate =  $date->format("Y-m-d");
			$sql.=" AND v.`dateAttendence` = DATE_FORMAT('$currentDate', '%Y/%m/%d') ";
		}
		if(!empty($search["academic_year"])){
			$sql.=' AND v.academicYear = '.$search["academic_year"];
		}
		if(!empty($search["degree"])){
			$sql.=' AND v.degree = '.$search["degree"];
		}
		if(!empty($search["grade"])){
			$sql.=' AND v.grade = '.$search["grade"];
		}
		$sql .= $dbGb->getAccessPermission('v.branchId');
		$sql .= $dbGb->getDegreePermission('v.degree');
		$sql .= $dbGb->getSchoolOptionAccess('v.schoolOption');
		return $_db->fetchRow($sql);
	}
	
	public  function getCountingAllStudentByDegree($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql = "
			SELECT
				COUNT(DISTINCT(gd.`stu_id`)) AS totalStudent
				,g.`group_code` AS groupCode
				,it.`title` 
				,it.`shortcut`
				,(SELECT COUNT(v.`studentId`) FROM `v_studentattendancestatusperdate` AS v WHERE v.`group_id` = g.id AND v.`maxAttendenceStatus` = 2 AND v.`dateAttendence` = DATE_FORMAT('2024-06-28', '%Y/%m/%d') ) AS totalAbsent
				,(SELECT COUNT(v.`studentId`) FROM `v_studentattendancestatusperdate` AS v WHERE v.`group_id` = g.id AND v.`maxAttendenceStatus` = 3 AND v.`dateAttendence` = DATE_FORMAT('2024-06-28', '%Y/%m/%d') ) AS totalPermission
				,(SELECT COUNT(v.`studentId`) FROM `v_studentattendancestatusperdate` AS v WHERE v.`group_id` = g.id AND v.`maxAttendenceStatus` = 4 AND v.`dateAttendence` = DATE_FORMAT('2024-06-28', '%Y/%m/%d') ) AS totalLate
				,(SELECT COUNT(v.`studentId`) FROM `v_studentattendancestatusperdate` AS v WHERE v.`group_id` = g.id AND v.`maxAttendenceStatus` = 5 AND v.`dateAttendence` = DATE_FORMAT('2024-06-28', '%Y/%m/%d') ) AS totalEarlyLate
				
			FROM 
				(rms_group AS g JOIN `rms_group_detail_student` AS gd ON g.id = gd.`group_id` AND gd.`itemType` = 1 )
				LEFT JOIN `rms_items` AS it ON it.`id` = gd.`degree` AND it.`type` = 1
			WHERE 1
				AND g.is_use = 1
				AND g.status = 1
				AND it.`status` = 1
		";
		$sql.='';
		if(!empty($search["branch_id"])){
			$sql.=' AND g.branch_id = '.$search["branch_id"];
		}
		if(!empty($search["academic_year"])){
			$sql.=' AND g.academic_year = '.$search["academic_year"];
		}
		if(!empty($search["degree"])){
			$sql.=' AND g.degree = '.$search["degree"];
		}
		$sql .= $dbGb->getAccessPermission('g.branch_id');
		$sql .= $dbGb->getDegreePermission('g.degree');
		$sql .= $dbGb->getSchoolOptionAccess('g.school_option');
		
		$sql.=" GROUP BY g.id ";
		$sql.=" ORDER BY g.`school_option` ASC,it.`ordering` ASC,it.`id` ASC ";
		return $_db->fetchAll($sql);
	}
	
	
	public  function getStudentAbsenteeReport($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT
				att.id AS attendanceId
				,g.`group_code` AS groupCode
				,att.`date_attendence` AS attendanceDate
				,attd.`stu_id` AS studentId 
				,attd.`fromHour`
				,attd.`toHour`
				,attd.`subjectId`
				,(SELECT b.branch_namekh FROM rms_branch AS b WHERE b.br_id=g.branch_id LIMIT 1) AS branchNameKh
				,(SELECT b.branch_nameen FROM rms_branch AS b WHERE b.br_id=g.branch_id LIMIT 1) AS branchNameEng
				,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
				,(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = attd.`subjectId` LIMIT 1) AS subjectTitleKh
				,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = attd.`subjectId` LIMIT 1) AS subjectTitleEn
				,(SELECT sj.shortcut FROM `rms_subject` AS sj WHERE sj.id = attd.`subjectId` LIMIT 1) AS subjectShortcut
				,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =attd.fromHour LIMIT 1) AS fromHourTitle
				,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =attd.toHour LIMIT 1) AS toHourTitle
				
				,s.`stu_code` AS studentCode
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameInLatin
				,s.`stu_khname` AS stuNameKh
				,s.`sex`
				,(SELECT v.`name_kh` FROM `rms_view` AS v WHERE v.type=2 AND v.key_code = s.sex LIMIT 1) AS genderTitle
				
				,attd.`byTeacherId`
				,attd.`attendence_status` AS attendenceStatus
				,attd.`description` AS reason
				,(SELECT t.`teacher_name_en` FROM `rms_teacher` AS t WHERE t.id = attd.`byTeacherId` LIMIT 1) AS teacherNameEn
				,(SELECT t.`teacher_name_kh` FROM `rms_teacher` AS t WHERE t.id = attd.`byTeacherId` LIMIT 1) AS teacherNameKh
				,(SELECT sj.shortcut FROM `rms_subject` AS sj WHERE sj.id = attd.`subjectId` LIMIT 1) AS subjectShortcut
				,attd.`createDate`
				,attd.`modifyDate`
				,CASE 
						WHEN attd.`attendence_status` = 2 THEN '".$tr->translate("ABSENT")."' 
						WHEN attd.`attendence_status` = 3 THEN '".$tr->translate("PERMISSION")."' 
						WHEN attd.`attendence_status` = 4 THEN '".$tr->translate("LATE")."'
						WHEN attd.`attendence_status` = 5 THEN '".$tr->translate("EARLY_LEAVE")."'
						ELSE '&#10004;'
					END AS attendenceStatusTitle
				,(SELECT CONCAT(u.first_name) FROM rms_users AS u WHERE u.id = att.`user_id`) AS byUserName
				
			FROM 
				(`rms_student_attendence` AS att JOIN `rms_student_attendence_detail` AS attd ON att.id = attd.`attendence_id`)
				JOIN `rms_student` AS s ON s.`stu_id` = attd.`stu_id` 
				LEFT JOIN rms_group AS g ON g.id = att.`group_id` 
			WHERE 1
				AND att.`status`=1
				AND att.type=1 
		";
		$sql.='';
		$from_date =(empty($search['start_date']))? '1': " att.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " att.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$sql.= " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " REPLACE(s.`stu_code`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(s.`last_name,' ','')` LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(s.`stu_enname`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')),' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(g.`group_code`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(attd.`description`,' ','') LIKE '%{$s_search}%'";
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
		
		if(!empty($search["branch_id"])){
			$sql.=' AND g.branch_id = '.$search["branch_id"];
		}
		if(!empty($search["academic_year"])){
			$sql.=' AND g.academic_year = '.$search["academic_year"];
		}
		if(!empty($search["degree"])){
			$sql.=' AND g.degree = '.$search["degree"];
		}
		if(!empty($search["grade"])){
			$sql.=' AND g.grade = '.$search["grade"];
		}
		$sql .= $dbGb->getAccessPermission('g.branch_id');
		$sql .= $dbGb->getDegreePermission('g.degree');
		$sql .= $dbGb->getSchoolOptionAccess('g.school_option');
		
		$sql.=" ORDER BY att.`date_attendence` DESC,att.`id` DESC,attd.`id` DESC ";
		return $_db->fetchAll($sql);
	}
	
	
	
	//function for Old Report Action
	function getStudentAttendence($search){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    		$branch = "b.branch_namekh";
			$sessionTitle = "titleKh";
    	}else{ // English
    		$label = "name_en";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    		$branch = "b.branch_nameen";
			$sessionTitle = "title";
    	}
		
		$fromDate =(empty($search['start_date']))? '1': "mAtt.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$toDate = (empty($search['end_date']))? '1': "mAtt.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$whereAttDay = " AND ".$fromDate." AND ".$toDate;
		
    	$sql=' SELECT 
				g.id AS group_id,
				g.`group_code`,
				g.`branch_id`,
				(SELECT CONCAT(ac.fromYear,"-",ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
				(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
				(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
				(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`, `g`.`semester` AS `semester`,
				prt.'.$sessionTitle.' AS `session`,
				gsd.`stu_id`, 
				st.`stu_code`,
				st.`stu_enname`,
				st.`stu_khname`,
				st.`last_name`,
				st.`sex` ,
				(SELECT 
				CONCAT("[",
					GROUP_CONCAT(
						CONCAT(
							"{",
							"\"dateAttendance\":\"",
							v_att.dateAttendance,
							"\""
							",",
							"\"attendanceStatus\":",
							v_att.attendanceStatus,
							
							"}"
						)
					),
				"]"
				) FROM `v_daily_stu_attendance` AS v_att 
					WHERE v_att.`groupId`= g.`id`
						AND v_att.type=1 
						AND v_att.`studentId`=gsd.`stu_id` 
					)  AS attendanceStatusList
				
				FROM `rms_student` AS st 
					INNER JOIN `rms_group_detail_student` AS gsd 
					ON (st.`stu_id` = gsd.`stu_id` AND gsd.itemType=1 )
					LEFT JOIN `rms_group` AS g ON (g.`id` = gsd.`group_id` AND g.is_pass!=1) 
					INNER JOIN rms_student_attendence AS sta ON (sta.group_id = g.id) 
					LEFT JOIN `rms_parttime_list` AS prt ON prt.`id` = g.session 
				WHERE 
					sta.type=1
					AND sta.status=1 
					AND gsd.status=1
					AND gsd.stop_type=0
				 	AND st.customer_type=1';
		
    	$from_date =(empty($search['start_date']))? '1': "sta.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sta.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;

    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND g.academic_year =".$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND `g`.`grade`=".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('`g`.`branch_id`');
    	
    	$order =" GROUP BY sta.group_id,gsd.stu_id 
    		ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ,g.id DESC,st.stu_khname ASC ";
		
    	return $db->fetchAll($sql.$where.$order);
    }
	
	
	/*
	function getReportStudentMistake($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
            g.id AS group_id,
            g.group_code,
            (SELECT CONCAT(ac.fromYear, '-', ac.toYear) 
             FROM rms_academicyear AS ac 
             WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
            (SELECT r.room_name 
             FROM rms_room r 
             WHERE r.room_id = g.room_id LIMIT 1) AS room_name, 
            (SELECT rms_view.name_kh 
             FROM rms_view 
             WHERE rms_view.type = 4 AND rms_view.key_code = g.session 
             LIMIT 1) AS session,
            st.stu_id, 
            st.stu_code, 
            st.stu_enname,
            st.last_name,
            st.stu_khname,
            st.sex, 
            IFNULL(
                (SELECT CONCAT(
                    '[', 
                    GROUP_CONCAT(
                        CONCAT(
                            '{\"disciplineStatus\":\"', 
                            attendence_status, 
                            '\",\"totalDiscipline\":\"', 
                            totalDiscipline, 
                            '\"}'
                        )
                    ), 
                    ']'
                ) 
                FROM v_totaldiscipline AS v_disc 
                WHERE v_disc.stu_id = st.stu_id
				AND v_disc.groupId=g.id LIMIT 1
                ), '[]'
            ) AS jsonDisciplineResult,

			IFNULL(
                (SELECT CONCAT(
                    '[', 
                    GROUP_CONCAT(
                        CONCAT(
                            '{\"attendenceStatus\":\"', 
                            v_att.attendence_status, 
                            '\",\"totalAttendance\":\"', 
                            v_att.totalAttendance, 
                            '\"}'
                        )
                    ), 
                    ']'
                ) 
                FROM v_totalattendance AS v_att 
                WHERE v_att.stu_id = st.stu_id
				AND v_att.groupId=g.id LIMIT 1
                ), '[]'
            ) AS jsonAttendanceResult
			
        FROM 
            rms_student AS st
            JOIN rms_group_detail_student AS gds ON st.stu_id = gds.stu_id
            LEFT JOIN rms_group AS g ON gds.group_id = g.id 
        WHERE 
			st.status=1
            AND gds.is_maingrade = 1 
            AND gds.itemType = 1 
            AND gds.stop_type = 0";

    	// $from_date =(empty($search['start_date']))? '1': "sd.`date_attendence` >= '".$search['start_date']." 00:00:00'";
    	// $to_date = (empty($search['end_date']))? '1': "sd.`date_attendence` <= '".$search['end_date']." 23:59:59'";
    	// $where = " AND ".$from_date." AND ".$to_date;
		$where = "";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
		
    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND `g`.`branch_id`=".$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND g.academic_year =".$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND `g`.`grade`=".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("g.branch_id");
		$where .= $dbp->getDegreePermission('g.degree');
    	$order =" ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ,g.id DESC ";
		
    	return $db->fetchAll($sql.$where.$order);
    }
	function getDailyStudentMistake($search){
    	$db = $this->getAdapter();
		
		$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
		$sessionTitle = "title";
		if($lang==1){// khmer
    		$sessionTitle = "titleKh";
    	}
    	$sql = "SELECT 
				g.id AS group_id,
				g.group_code,
				ac.fromYear, ac.toYear, 
				CONCAT(ac.fromYear, '-', ac.toYear) AS academic_year,
				r.room_name, 
				prt.$sessionTitle AS `session`,
				st.stu_id, 
				st.stu_code, 
				st.stu_enname,
				st.last_name,
				st.stu_khname,
				st.sex,
				st.tel,
				sd.date_attendence disciplineDate,
				v1.name_kh AS disciplineType,
				sad.description
			FROM 
				 rms_student_attendence AS sd
				JOIN rms_student_attendence_detail AS sad ON sad.attendence_id = sd.id AND sd.type=2
				LEFT JOIN rms_student AS st ON sad.stu_id = st.stu_id
				LEFT JOIN rms_group_detail_student AS gds ON (sad.stu_id = gds.stu_id AND gds.`group_id`=sd.`group_id`)
				LEFT JOIN rms_group AS g ON sd.group_id = g.id 
				LEFT JOIN rms_academicyear AS ac ON ac.id = g.academic_year
				LEFT JOIN rms_room AS r ON r.room_id = g.room_id
				LEFT JOIN `rms_parttime_list` AS prt ON prt.`id` = g.session 
				LEFT JOIN rms_view AS v1 ON v1.type = 44 AND v1.key_code = sad.attendence_status
			WHERE 
				st.status = 1
				AND gds.is_maingrade = 1 
				AND gds.itemType = 1 
				AND gds.stop_type = 0 ";

    	$from_date =(empty($search['start_date']))? '1': "sd.`date_attendence` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sd.`date_attendence` <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
		
    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND `g`.`branch_id`=".$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND g.academic_year =".$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND `g`.`grade`=".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("g.branch_id");
		$where .= $dbp->getDegreePermission('g.degree');
    	$order =" ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ";
    	return $db->fetchAll($sql.$where.$order);
    }
	*/
	function getReportStudentMistake($search){
    	$db = $this->getAdapter();
		
		$from_date =(empty($search['start_date']))? '1': "v_disc.`dateAttendance` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "v_disc.`dateAttendance` <= '".$search['end_date']." 23:59:59'";
    	$jsonwhere = " AND ".$from_date." AND ".$to_date;
		
		
    	$sql = "SELECT 
            g.id AS group_id,
            g.group_code,
            (SELECT CONCAT(ac.fromYear, '-', ac.toYear) 
             FROM rms_academicyear AS ac 
             WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
            (SELECT r.room_name 
             FROM rms_room r 
             WHERE r.room_id = g.room_id LIMIT 1) AS room_name, 
            (SELECT rms_view.name_kh 
             FROM rms_view 
             WHERE rms_view.type = 4 AND rms_view.key_code = g.session 
             LIMIT 1) AS session,
            st.stu_id, 
            st.stu_code, 
            st.stu_enname,
            st.last_name,
            st.stu_khname,
            st.sex
			
			,COUNT(DISTINCT IF(v_disc.`attendanceStatus` = 1 AND v_disc.type=2,v_disc.`attendenceId`, NULL)) AS minor
			,COUNT(DISTINCT IF(v_disc.`attendanceStatus` = 2 AND v_disc.type=2,v_disc.`attendenceId`, NULL)) AS moderate
			,COUNT(DISTINCT IF(v_disc.`attendanceStatus` = 3 AND v_disc.type=2,v_disc.`attendenceId`, NULL)) AS major
			,COUNT(DISTINCT IF(v_disc.`attendanceStatus` = 4 AND v_disc.type=2,v_disc.`attendenceId`, NULL)) AS other

			,COUNT(DISTINCT IF(v_disc.`attendanceStatus` = 2 AND v_disc.type=1,v_disc.`attendenceId`, NULL)) AS absent
			,COUNT(DISTINCT IF(v_disc.`attendanceStatus` = 3 AND v_disc.type=1,v_disc.`attendenceId`, NULL)) AS permission
			,COUNT(DISTINCT IF(v_disc.`attendanceStatus` = 4 AND v_disc.type=1,v_disc.`attendenceId`, NULL)) AS late
			,COUNT(DISTINCT IF(v_disc.`attendanceStatus` = 5 AND v_disc.type=1,v_disc.`attendenceId`, NULL)) AS early_leave
			
        FROM 
            rms_student AS st
            JOIN rms_group_detail_student AS gds ON st.stu_id = gds.stu_id 
            LEFT JOIN rms_group AS g ON gds.group_id = g.id 
			LEFT JOIN  `v_daily_stu_attendance` AS v_disc ON v_disc.studentId = st.stu_id AND v_disc.groupId=g.id 
				$jsonwhere

        WHERE 
			st.status=1
            AND gds.is_maingrade = 1 
            AND gds.itemType = 1 
            AND gds.stop_type = 0";

    	// $from_date =(empty($search['start_date']))? '1': "sd.`date_attendence` >= '".$search['start_date']." 00:00:00'";
    	// $to_date = (empty($search['end_date']))? '1': "sd.`date_attendence` <= '".$search['end_date']." 23:59:59'";
    	// $where = " AND ".$from_date." AND ".$to_date;
		$where = "";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
		
    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND `g`.`branch_id`=".$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND g.academic_year =".$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND `g`.`grade`=".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("g.branch_id");
		$where .= $dbp->getDegreePermission('g.degree');
    	$order =" GROUP BY gds.group_id,gds.stu_id ";
    	$order.=" ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ,g.id DESC ";
		
    	return $db->fetchAll($sql.$where.$order);
    }
	function getDailyStudentMistake($search){
    	$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sessionTitle = "title";
		if($lang==1){// khmer
    		$sessionTitle = "titleKh";
    	}
		
    	$sql = "
			SELECT 
				v.`groupId` AS group_id
				, g.group_code
				, r.room_name
				, prt.$sessionTitle AS `session`
				, ac.fromYear, ac.toYear, CONCAT(ac.fromYear, '-', ac.toYear) AS academic_year
				, st.stu_id
				, st.stu_code
				, st.stu_enname
				, st.last_name, st.stu_khname, st.sex, st.tel
				,v.`description`
				,v.`dateAttendance` AS disciplineDate
				,v.`attendanceStatus`
				,CASE 
					WHEN v.type = 2 THEN v1.name_kh
					WHEN v.`attendanceStatus`= 2  THEN '".$tr->translate("ABSENT")."'
					WHEN v.`attendanceStatus`= 3  THEN '".$tr->translate("PERMISSION")."'
					WHEN v.`attendanceStatus`= 4  THEN '".$tr->translate("LATE")."'
					WHEN v.`attendanceStatus`= 5  THEN '".$tr->translate("EARLY_LEAVE")."'
					ELSE '' 
				END AS disciplineType 
			FROM `v_daily_stu_attendance` AS v 
				LEFT JOIN rms_student AS st ON v.`studentId` = st.stu_id 
				LEFT JOIN rms_group AS g ON g.id = v.`groupId`
				LEFT JOIN rms_academicyear AS ac ON ac.id = g.academic_year 
				LEFT JOIN rms_room AS r ON r.room_id = g.room_id 
				LEFT JOIN `rms_parttime_list` AS prt ON prt.`id` = g.session 
				LEFT JOIN rms_view AS v1 ON v1.type = 44 AND v1.key_code = v.`attendanceStatus` AND v.type = 2 
				LEFT JOIN rms_items AS it ON it.`id` = g.degree 
				LEFT JOIN rms_itemsdetail AS itd ON itd.`id` = g.grade 
			WHERE 1 
		";

    	$from_date =(empty($search['start_date']))? '1': "v.`dateAttendance` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "v.`dateAttendance` <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
		
    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND `g`.`branch_id`=".$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND g.academic_year =".$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND `g`.`grade`=".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("g.branch_id");
		$where .= $dbp->getDegreePermission('g.degree');
    	$order =" ORDER BY v.`dateAttendance` DESC,it.ordering ASC, itd.ordering ASC, g.group_code ";
		$rowData = $db->fetchAll($sql.$where.$order);
		
		return $rowData;
    }
	
	public  function getStudentPreAttendanceReport($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$last = $dbGb->getLatestAcadmicYear();
		$latestAcademicYear = empty($last["id"]) ? 0 : $last["id"];
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$unScan = empty($search["unScan"]) ? 0 : $search["unScan"];
		
		$stringJsonQue="
		CONCAT(
			'[',
			GROUP_CONCAT('{',
				'".'"sttId"'.":','".'"'."',COALESCE(scp.`settingId`,0),'".'"'."',
				'".',"type"'.":','".'"'."',COALESCE(scp.`type`,0),'".'"'."',
				'".',"date"'.":','".'"'."',COALESCE(scp.`createDate`,''),'".'"'."',
				'".',"status"'.":','".'"'."',COALESCE(scp.`attendanceStatus`,0),'".'"'."',
				'".',"gate"'.":','".'"'."',COALESCE(scp.`gate`,0),'".'"'."',
				'".',"by"'.":','".'"'."',COALESCE(scp.`userId`,0),'".'"'."',
				
			'}' ORDER BY scp.`createDate` ASC )
			,']'
			)
		";
		
		$sql = "
				SELECT 
					(SELECT b.branch_namekh FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branchNameKh 
					,(SELECT b.branch_nameen FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branchNameEng 
					,scp.`studentId` 
					,s.`stu_id`
					,g.`group_code` AS groupCode 
					,g.`id` AS groupId 
					,g.`session` AS sessionId 
					,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear 
					,s.`stu_code` AS studentCode ,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameInLatin 
					,s.`stu_khname` AS stuNameKh 
					,s.`sex` 
					,(SELECT v.`name_kh` FROM `rms_view` AS v WHERE v.type=2 AND v.key_code = s.sex LIMIT 1) AS genderTitle
					 
					,scp.createDate AS attendanceDate 
					,COALESCE(ss.shiftStr,0) AS groupShiftStr 
					
		";
		$sql.=",CASE 
					WHEN scp.`studentId` IS NULL THEN CONCAT( '[',']')
					ELSE $stringJsonQue 
				END AS jsDetail ";
		
		$toDate = (empty($search['end_date']))? '1' : " DATE_FORMAT(scp.createDate,'%Y-%m-%d')  = '".date("Y-m-d",strtotime($search['end_date']))."'";
		$sql.= "
				FROM  (`rms_student` AS s JOIN `rms_group_detail_student` AS gd ON gd.stu_id = s.`stu_id` AND gd.`itemType` = 1 AND gd.`stop_type` = 0 AND gd.`movedType` != 1 ) 
					LEFT JOIN `rms_scan_pre_att` AS scp  ON s.`stu_id` = scp.`studentId` AND $toDate
					LEFT JOIN rms_group AS g ON g.id = gd.`group_id`
					LEFT JOIN `rms_parttime_list` AS ss ON ss.id = g.session
					LEFT JOIN `rms_itemsdetail` AS ite ON ite.`id` = g.`grade` AND ite.`items_type` = 1 
					LEFT JOIN `rms_items` AS it ON it.`id` = g.degree AND it.`type` = 1 
			";
		
		$sql.=' WHERE 
				1 
				
		';
		
		if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " REPLACE(g.`group_code`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(s.`stu_code`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(s.`last_name`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(s.`stu_enname`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(CONCAT(s.`last_name`,' ',s.`stu_enname`),' ','') LIKE '%{$s_search}%'";
    		
    		$sql.=' AND ( '.implode(' OR ',$s_where).')';
    	}
		if(!empty($search["queryOption"])){
			if($search["queryOption"]==1){
				$sql.=' AND scp.`studentId` IS NOT NULL ';
				
				if(!empty($search["shift"])){
					$sql.=' AND scp.shift = '.$search["shift"];
				}else{
					$sql.=' AND FIND_IN_SET(scp.`shift`,ss.shiftStr) ';
				}
			}else if($search["queryOption"]==2){
				$sql.=' AND scp.`studentId` IS NULL ';
			}
		}
		if(!empty($search["branch_id"])){
			$sql.=' AND g.branch_id = '.$search["branch_id"];
		}
		if(!empty($search["academic_year"])){
			$sql.=' AND g.academic_year = '.$search["academic_year"];
		}
		if(!empty($search["group"])){
			$sql.=' AND g.id = '.$search["group"];
		}
		if(!empty($search["degree"])){
			$sql.=' AND g.degree = '.$search["degree"];
		}
		if(!empty($search["grade"])){
			$sql.=' AND g.grade = '.$search["grade"];
		}
		
		$sql .= $dbGb->getAccessPermission('g.branch_id');
		$sql .= $dbGb->getDegreePermission('g.degree');
		$sql .= $dbGb->getSchoolOptionAccess('g.school_option');
		
		$sql.=" GROUP BY gd.group_id,gd.stu_id  ";
		$sql.=" ORDER BY it.`ordering` ASC,ite.`ordering` ASC,g.group_code ASC,g.id ASC,scp.createDate ASC ";
		
		return $_db->fetchAll($sql);
	}
	
	public  function getStudentRequestPermission($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sqlSelect= "
				SELECT 
					ad.`stu_id`
					,s.`stu_code` AS stuCode
					,s.`stu_khname` AS stuNameKh
					,CONCAT(COALESCE(s.`last_name`,''),' ',COALESCE(s.`stu_enname`,'')) AS stuNameEn
					,s.`sex`
					,g.`group_code` AS groupCode
					,(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academicYear
					,ad.`attendanceDate`
					,CASE
						WHEN ad.`attendence_status`=2 THEN '".$tr->translate("ABSENT")."'
						WHEN ad.`attendence_status`=3 THEN '".$tr->translate("PERMISSION")."'
						WHEN ad.`attendence_status`=4 THEN '".$tr->translate("LATE")."'
						WHEN ad.`attendence_status`=5 THEN '".$tr->translate("EARLY_LEAVE")."'
						ELSE ''
					END AS attStatus
					,req.`amountDay`
					,req.`fromDate`
					,req.`toDate`
					,req.`reason`
					,req.createDate
					
		";		
		$sqlFrom= "
				FROM `rms_student_attendence_detail` AS ad 
						JOIN `rms_student_request_permission` AS req ON req.`id` = ad.`studentRequestId`
						LEFT JOIN `rms_student` AS s ON s.`stu_id` = ad.`stu_id`
						LEFT JOIN `rms_group` AS g ON g.id = req.`groupId`
			";
		$sqlWhere=' WHERE ad.`type` =2 ';
		$sqlGroupBy =" GROUP BY ad.`studentRequestId` ";
		if(!empty($search["forReport"])){
			$from_date =(empty($search['start_date']))? '1': "req.`createDate` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "req.`createDate` <= '".$search['end_date']." 23:59:59'";
			$sqlWhere.= " AND ".$from_date." AND ".$to_date;
			$sqlGroupBy =" GROUP BY ad.`studentRequestId` ";
		}
		
		if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " REPLACE(g.`group_code`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(s.`stu_code`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(s.`last_name,' ','')` LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(s.`stu_enname`,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')),' ','') LIKE '%{$s_search}%'";
    		
    		$sqlWhere .=' AND ( '.implode(' OR ',$s_where).')';
    	}
		if(!empty($search["branch_id"])){
			$sqlWhere.=' AND req.`branchId` = '.$search["branch_id"];
		}
		if(!empty($search["academic_year"])){
			$sqlWhere.=' AND g.academic_year = '.$search["academic_year"];
		}
		if(!empty($search["group"])){
			$sqlWhere.=' AND req.`groupId` = '.$search["group"];
		}
		if(!empty($search["degree"])){
			$sqlWhere.=' AND g.degree = '.$search["degree"];
		}
		if(!empty($search["grade"])){
			$sqlWhere.=' AND g.grade = '.$search["grade"];
		}
		
		$sqlWhere .= $dbGb->getAccessPermission('req.`branchId`');
		$sqlWhere .= $dbGb->getDegreePermission('g.degree');
		$sqlWhere .= $dbGb->getSchoolOptionAccess('g.school_option');
		
		$limit="";
		if(!empty($search["limitRecord"])){
			$limit=" LIMIT ".$search["limitRecord"];
		}
		$sqlOrder=" ORDER BY req.id DESC,s.`stu_code` ASC";
		
		$sql=$sqlSelect.$sqlFrom.$sqlWhere.$sqlGroupBy.$sqlOrder;
		$result = $_db->fetchAll($sql.$limit);
		
		$totalRecord = 0;
		if(empty($search["forReport"])){
			$sqlCount = "SELECT  COUNT(ad.`stu_id`) AS amtRecord ";
			$sqlCount.= $sqlFrom;
			$sqlCount.= $sqlWhere;
			if(!empty($search["countToday"])){
				$sqlCount.=" AND ad.`attendanceDate` = '".date("Y-m-d")."' ";
			}
			$totalRecord = $_db->fetchOne($sqlCount);
		}
		
		$arr = [
			'result'=>$result,
			'totalRecord'=>$totalRecord,
		];
		return $arr;
	}
	
	public  function getStudentScanActivity($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbGb->currentlang();
		
		$colSett = "titleEn";
    	if($lang==1){
    		$colSett = "title";
    	}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sqlSelect= "
				SELECT 
					pre.`id`
					,pre.`studentId`
					,pre.`createDate`
					,pre.`attendanceStatus`
					,pre.`type`
					,pre.`groupId`
					,g.`group_code` AS groupCode
					,(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academicYear
					,sctt.$colSett AS titleSetting
					,CASE
						WHEN pre.`attendanceStatus`=2 THEN '".$tr->translate("ABSENT")."'
						WHEN pre.`attendanceStatus`=3 THEN '".$tr->translate("PERMISSION")."'
						WHEN pre.`attendanceStatus`=4 THEN '".$tr->translate("LATE")."'
						WHEN pre.`attendanceStatus`=5 THEN '".$tr->translate("EARLY_LEAVE")."'
						ELSE '".$tr->translate("PRESENT")."'
					END AS attStatus
					
					,s.`stu_code` AS stuCode
					,s.`stu_khname` AS stuNameKh
					,CONCAT(COALESCE(s.`last_name`,''),' ',COALESCE(s.`stu_enname`,'')) AS stuNameEn
					,s.`sex`
					
		";		
		$sqlFrom= "
				FROM `rms_scan_pre_att` AS pre 
					JOIN `rms_student` AS s ON s.`stu_id` = pre.`studentId`
					LEFT JOIN `rms_group` AS g ON g.id = pre.`groupId` 
					LEFT JOIN `rms_scan_att_setting` AS sctt ON sctt.`id` = pre.`settingId`
			";
		$sqlWhere=' WHERE 1 ';
		if(!empty($search["branch_id"])){
			$sqlWhere.=' AND pre.`branchId` = '.$search["branch_id"];
		}
		$sqlWhere .= $dbGb->getAccessPermission('pre.`branchId`');
		$sqlWhere .= $dbGb->getDegreePermission('g.degree');
		$sqlWhere .= $dbGb->getSchoolOptionAccess('g.school_option');
		
		$limit="";
		if(!empty($search["limitRecord"])){
			$limit=" LIMIT ".$search["limitRecord"];
		}
		$sqlOrder=" ORDER BY pre.id DESC";
		
		$sql=$sqlSelect.$sqlFrom.$sqlWhere.$sqlOrder;
		$result = $_db->fetchAll($sql.$limit);
		
		$sqlCount = "SELECT  COUNT(DISTINCT pre.`studentId`) AS amtRecord ";
		$sqlCount.= $sqlFrom;
		$sqlCount.= $sqlWhere;
		if(!empty($search["countToday"])){
			$sqlCount.=" AND DATE_FORMAT(pre.`createDate`,'%Y-%m-%d') = '".date("Y-m-d")."' ";
		}
		$totalRecord = $_db->fetchOne($sqlCount);
		
		$arr = [
			'result'=>$result,
			'totalRecord'=>$totalRecord,
		];
		return $arr;
	}
	
	//New
	function getStudentAttendenceWithScheule($search){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    		$branch = "b.branch_namekh";
			$sessionTitle = "titleKh";
    	}else{ // English
    		$label = "name_en";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    		$branch = "b.branch_nameen";
			$sessionTitle = "title";
    	}
		
		$fromDate =(empty($search['start_date']))? '1': "mAtt.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$toDate = (empty($search['end_date']))? '1': "mAtt.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$whereAttDay = " AND ".$fromDate." AND ".$toDate;
		
		$fromDate =(empty($search['start_date']))? '1': "v_att.dateAttendance >= '".$search['start_date']." 00:00:00'";
    	$toDate = (empty($search['end_date']))? '1': "v_att.dateAttendance <= '".$search['end_date']." 23:59:59'";
    	$whereAttStt = " AND ".$fromDate." AND ".$toDate;
		
    	$sql=' SELECT 
				g.id AS group_id,
				g.`group_code`,
				g.`branch_id`,
				(SELECT CONCAT(ac.fromYear,"-",ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
				(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
				(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
				(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`, `g`.`semester` AS `semester`,
				prt.'.$sessionTitle.' AS `session`,
				gsd.`stu_id`, 
				st.`stu_code`,
				st.`stu_enname`,
				st.`stu_khname`,
				st.`last_name`,
				st.`sex` ,
				(SELECT 
				CONCAT("[",
					GROUP_CONCAT(
						CONCAT(
							"{",
							"\"dateAttendance\":\"",
							v_att.dateAttendance,
							"\""
							
							",",
							"\"attendanceStatus\":",
							"\"",
							v_att.attendanceStatus,
							"\"",
							",",
							
							"\"scheduleId\":",
							"\"",
							v_att.scheduleId,
							"\"",
							
							",",
							"\"subjectId\":",
							"\"",
							v_att.subjectId,
							"\"",
							
							",",
							"\"time\":",
							"\"",
							v_att.fromHour,
							"\"",
							
							",",
							"\"to\":",
							"\"",
							v_att.toHour,
							"\"",
							
							",",
							"\"index\":",
							"\"",
							CONCAT(v_att.dateAttendance,v_att.scheduleId,v_att.subjectId,v_att.fromHour),
							"\"",
							
							"}"
						)
					),
				"]"
				) FROM `v_daily_stu_attendance_subj` AS v_att 
					WHERE v_att.`groupId`= g.`id`
						AND v_att.type=1 
						AND v_att.`studentId`=gsd.`stu_id` 
						'.$whereAttStt.'
					)  AS attendanceStatusList
				,(SELECT 
				CONCAT("[",
					GROUP_CONCAT(
						CONCAT(
							"{",
							"\"dateAttendance\":\"",
							"\"",
							mAtt.date_attendence,
							"\"",
							"\""
							",",
							"\"scheduleId\":",
							"\"",
							mAtt.scheduleId,
							"\"",
							"}"
						)
					),
				"]"
				) FROM `rms_student_attendence` AS mAtt 
					WHERE mAtt.`group_id`= g.`id`
						AND mAtt.type=1 
						'.$whereAttDay.'
					)  AS attDayList
				,COALESCE((SELECT sch.id FROM rms_group_schedule AS sch WHERE sch.status=1 AND sch.group_id =sta.group_id LIMIT 1 ),0) AS defaultSchId

				
				FROM `rms_student` AS st 
					INNER JOIN `rms_group_detail_student` AS gsd 
					ON (st.`stu_id` = gsd.`stu_id` AND gsd.itemType=1 )
					LEFT JOIN `rms_group` AS g ON (g.`id` = gsd.`group_id` AND g.is_pass!=1) 
					INNER JOIN rms_student_attendence AS sta ON (sta.group_id = g.id) 
					LEFT JOIN `rms_parttime_list` AS prt ON prt.`id` = g.session 
				WHERE 
					sta.type=1
					AND sta.status=1 
					AND gsd.status=1
					AND gsd.stop_type=0
				 	AND st.customer_type=1';
		
    	$from_date =(empty($search['start_date']))? '1': "sta.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sta.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;

    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND g.academic_year =".$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND `g`.`grade`=".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('`g`.`branch_id`');
    	
    	$order =" GROUP BY sta.group_id,gsd.stu_id 
    		ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ,g.id DESC,st.stu_khname ASC ";
    	return $db->fetchAll($sql.$where.$order);
    }
	function getAttGroupSchedule($search){
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		
		$jsonData = "
				,(SELECT 
				CONCAT('[',
					GROUP_CONCAT(
						DISTINCT CONCAT(
							'{',
								'\"short\":','\"',subj.`shortcut`,'\"',
								',\"day\":', '\"',gsch.`day_id`,'\"',
								',\"time\":', '\"',gsch.`from_hour`,'\"',
								',\"to\":', '\"',gsch.`to_hour`,'\"',
								',\"schId\":', '\"',gsch.`main_schedule_id`,'\"',
								',\"subjId\":', '\"',gsch.`subject_id`,'\"',
								',\"index\":', '\"',CONCAT(gsch.`main_schedule_id`,gsch.`subject_id`,gsch.`from_hour`),'\"',
								
							'}'
						)
						ORDER BY gsch.`main_schedule_id` ASC,gsch.`day_id`,gsch.`from_hour`
					),
				']'
				) FROM `rms_group_reschedule` AS gsch 
					JOIN `rms_subject` AS subj ON subj.id = gsch.`subject_id`
					WHERE gsch.group_id = sta.`group_id`
				)  AS schDay
			";
		$sql="
			SELECT 
				sta.`group_id` as groupId
			";
		$sql.=$jsonData;
		$sql.= "	FROM `rms_student_attendence` AS sta 
				 LEFT JOIN `rms_group` AS g ON g.`id` = sta.`group_id`
		";
		//AND gsch.`day_id` = (DAYOFWEEK(sta.`date_attendence`) -1) 
		$sql.= "WHERE 
					COALESCE(sta.`scheduleId`,0) != 0 
			";
		$from_date =(empty($search['start_date']))? '1': "sta.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sta.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;

    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND g.academic_year =".$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND `g`.`grade`=".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('`g`.`branch_id`');
    	
    	$order =" GROUP BY sta.`group_id` ";
    	$order.=" ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ,g.id DESC";
		
		
    	return $db->fetchAll($sql.$where.$order);
	}
	
	function getStuConnectedTelegramBot($search){
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$sql="
			SELECT 
				DISTINCT gd.`stu_id` AS `studentId`
				,s.`stu_code` AS stuCode
				,s.`stu_khname` AS stuNameKh
				,CONCAT(COALESCE(s.`last_name`,''),' ',COALESCE(s.`stu_enname`,'')) AS stuNameEn
				,s.`sex`
				,g.`group_code` AS groupCode
				,(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academicYear
				,COALESCE(te.`id`,0) AS connectedTele	
			";
		$sql.= "
			FROM (`rms_student` AS s JOIN  `rms_group_detail_student` AS gd ON gd.`stu_id` = s.`stu_id` AND gd.`itemType` = 1 AND gd.`is_current` = 1 AND gd.`is_maingrade` = 1) 
					LEFT JOIN `rms_group` AS g ON g.`id` = gd.`group_id` 
		";
		$sql.= " LEFT JOIN `rms_telegram_token` AS te ON te.`studentId` = s.`stu_id` 
		";
		$sql.= "WHERE 1 ";
		$where="";
    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND g.academic_year =".$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND `g`.`grade`=".$search['grade'];
    	}
    	
		if(!empty($search['connectedTelegram'])){
			if($search['connectedTelegram']==1){
				$where.=" AND COALESCE(te.`id`,0) > 1 ";
			}else if($search['connectedTelegram']==2){
				$where.=" AND COALESCE(te.`id`,0) = 0 ";
			}
    		
    	}
		
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('`gd`.`branch_id`');
    	
    	$order =" GROUP BY gd.`stu_id` ";
    	$order.=" ORDER BY `g`.`degree`,`g`.`grade`,s.`stu_code` ASC ";
		
		
    	return $db->fetchAll($sql.$where.$order);
	}
	
	public  function getTelegramInfoReport($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$last = $dbGb->getLatestAcadmicYear();
		$latestAcademicYear = empty($last["id"]) ? 0 : $last["id"];
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$reportLayout = empty($search["reportLayout"]) ? 1 : $search["reportLayout"];
		
		if($reportLayout==1){
			$stringJsonQue="
			CONCAT(
				'[',
				GROUP_CONCAT(
					'{\"stuId\":','\"',te.`studentId`,'\"'
					',\"code\":','\"',s.`stuCode`,'\"'
					',\"nameKh\":','\"',REPLACE(s.`stuNameKh`,'​',''),'\"'
					',\"nameEn\":','\"',REPLACE(s.`stuNameEn`,'​',''),'\"'
					',\"gCode\":','\"',COALESCE(s.`groupCode`,''),'\"'
					,'}' 
					ORDER BY s.`stuCode` ASC
				)
				,']'
				)
			";
			
			$sql = "
					SELECT
						te.*
						,$stringJsonQue  AS jsDetail 
					";
			
			$sql.= "
					FROM `rms_telegram_token` te 
						LEFT JOIN v_stu_study_info AS s ON s.`studentId` = te.`studentId` AND NULLIF(te.`studentId`,'') IS NOT NULL
				";
			
			$sql.=' WHERE 
					1 
					
			';
			
			if(!empty($search['adv_search'])){
				$s_where = array();
				$s_search = addslashes(trim($search['adv_search']));
				$s_where[] = " REPLACE(te.`firstName`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(te.`lastName`,' ','') LIKE '%{$s_search}%'";
				
				$sql.=' AND ( '.implode(' OR ',$s_where).')';
			}
			
			$sql.=" GROUP BY te.`chatId` ";
			$sql.=" ORDER BY te.`firstName` ASC,te.`lastName` ASC ";
		}else{
			$stringJsonQue="
			CONCAT(
				'[',
				GROUP_CONCAT(
					'{\"chatId\":','\"',te.`chatId`,'\"'
					',\"fName\":','\"',REPLACE(te.`firstName`,'​',''),'\"'
					',\"lName\":','\"',REPLACE(te.`lastName`,'​',''),'\"'
					',\"jDate\":','\"',DATE_FORMAT(te.`createDate`,'%d/%m/%Y'),'\"'
					,'}'
					ORDER BY te.`firstName` ASC, te.`lastName` ASC
				)
				,']'
				)
			";
			
			$sql = "
					SELECT
						te.studentId
						,s.`stuNameKh`
						,s.`stuNameEn`
						,s.`stuCode` as studentCode
						,s.`groupCode`
						,(SELECT v.`name_kh` FROM `rms_view` AS v WHERE v.type=2 AND v.key_code = s.`sex` LIMIT 1) AS genderTitle
						,$stringJsonQue  AS jsDetail 
					";
			
			$sql.= "
					FROM `rms_telegram_token` te 
						LEFT JOIN v_stu_study_info AS s ON s.`studentId` = te.`studentId` 
				";
			
			$sql.=' WHERE  1 
					AND COALESCE(te.`studentId`,0) !=0						
					
			';
			
			if(!empty($search['adv_search'])){
				$s_where = array();
				$s_search = addslashes(trim($search['adv_search']));
				$s_where[] = " REPLACE(s.`stuNameKh`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(s.`stuNameEn`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(s.`groupCode`,' ','') LIKE '%{$s_search}%'";
				
				$sql.=' AND ( '.implode(' OR ',$s_where).')';
			}
			
			$sql.=" GROUP BY te.`studentId` ";
			$sql.=" ORDER BY s.`degreeOrdering` ASC, s.`gradeOrdering`,s.`groupCode`,s.`stuCode` ";
		}
		
		return $_db->fetchAll($sql);
	}
	
}