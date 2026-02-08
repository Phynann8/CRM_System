<?php

class Home_Model_DbTable_DbStudent extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getAllStudentFronDesk($search)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbGb->currentlang();
		
		$_db = $this->getAdapter();
		$from_date = (empty($search['start_date'])) ? '1' : "s.create_date >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : "s.create_date <= '" . $search['end_date'] . " 23:59:59'";
		$where = " AND " . $from_date . " AND " . $to_date;
		$field = 'name_en';
		$colunmname = 'title_en';
		if ($lang == 1) {
			$field = 'name_kh';
			$colunmname = 'title';
		}
		$branchLabel = $dbGb->getBranchDisplay();
		
		$sql = "SELECT  
					b.$branchLabel branchName,
					s.stu_id,
					s.stu_code,
					s.stu_khname,
					s.stu_enname,
					s.last_name,
					CASE 
						WHEN s.primary_phone = 1 THEN s.tel
						WHEN s.primary_phone = 2 THEN COALESCE(NULLIF(fam.fatherPhone,''),s.tel)
						WHEN s.primary_phone = 3 THEN COALESCE(NULLIF(fam.motherPhone,''),s.tel)
						WHEN s.primary_phone = 4 THEN COALESCE(NULLIF(fam.guardianPhone,''),s.tel)
						ELSE ''
					END AS tel,
					ds.stop_type AS is_subspend,
					s.sex as sexcode,
					s.status,
					s.photo,
					s.familyId,
					v.$field studentType,
					CONCAT(ac.fromYear,'-',ac.toYear) academicYearEnroll,
					v1.$field status_student,
					g.group_code group_name,
					i.$colunmname degree,
					idd.$colunmname grade,
					ds.group_id,
					ds.startDate,
					ds.endDate,
					CONCAT(acy.fromYear,'-',acy.toYear) academic_year,
					CASE
							WHEN ds.is_maingrade = 1 THEN '".$tr->translate("MAIN_CLASS")."'
							WHEN ds.is_maingrade = 0 THEN '".$tr->translate("SUB_CLASS")."'
							ELSE ''
						END as mainGradeTypeTitle
					,CASE
							WHEN s.goHomeType = 1 THEN '".$tr->translate("BY_THEMSELVES")."'
							WHEN s.goHomeType = 2 THEN '".$tr->translate("BY_PARENTS")."'
							WHEN s.goHomeType = 3 THEN '".$tr->translate("BY_SCHOOL_BUS")."'
							ELSE 'N/A'
						END as goHomeTypeTitle,
					tf.generation feeTitle,
					(SELECT d.discountTitle FROM `rms_dis_setting` AS d INNER JOIN `rms_discount_student` AS dd ON d.id = dd.discountGroupId WHERE dd.isCurrent=1 AND dd.studentId=ds.stu_id LIMIT 1) AS discountTitle
				FROM 
					rms_student AS s JOIN rms_group_detail_student AS ds ON ds.itemType=1 AND s.stu_id=ds.stu_id AND ds.is_current=1 
					LEFT JOIN rms_family AS fam ON fam.id = s.familyId
					LEFT JOIN `rms_branch` b ON b.br_id = s.branch_id
					LEFT JOIN rms_view v ON v.type=40 AND v.key_code=s.studentType
					LEFT JOIN rms_view v1 ON v1.type=5 AND v1.key_code=ds.stop_type
					LEFT JOIN `rms_academicyear` ac ON ac.id =s.academicYearEnroll
					LEFT JOIN `rms_group`g ON g.id=ds.group_id
					LEFT JOIN `rms_items` i ON i.id = ds.degree AND i.type=1
					LEFT JOIN `rms_itemsdetail` AS idd ON idd.id = ds.grade AND idd.items_type=1
					LEFT JOIN rms_academicyear acy ON acy.id=ds.academic_year
					LEFT JOIN rms_tuitionfee tf ON tf.id=ds.feeId
				WHERE s.status = 1 
						AND s.customer_type = 1 ";
		$orderby = " ORDER BY s.stu_khname ASC ";
		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$lastName=" REPLACE(last_name,' ','')";
			$studentName=" REPLACE(stu_enname,' ','')";
			$s_where[]=" $studentName LIKE '%{$s_search}%'";
			$s_where[]=" $lastName LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT($lastName,$studentName) LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.tel,' ','')  			LIKE '%{$s_search}%'";
			
			$s_where[]=" REPLACE(COALESCE(fam.fatherPhone,''),' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(COALESCE(fam.motherPhone,''),' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(COALESCE(fam.guardianPhone,''),' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(COALESCE(fam.fatherName,''),' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(COALESCE(fam.fatherNameKh,''),' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(COALESCE(fam.motherName,''),' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(COALESCE(fam.guardianName,''),' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(COALESCE(fam.guardianNameKh,''),' ','') LIKE '%{$s_search}%'";
			
			$s_where[] = " REPLACE((SELECT group_code FROM `rms_group` WHERE rms_group.id=ds.group_id AND ds.is_maingrade=1 LIMIT 1),' ','')  LIKE '%{$s_search}%'";
			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['branch_id'])) {
			$where .= " AND s.branch_id=" . $search['branch_id'];
		}
		if (!empty($search['academic_year'])) {
			$where .= " AND ds.academic_year=" . $search['academic_year'];
		}
		if (!empty($search['group'])) {
			$where .= " AND ds.group_id=" . $search['group'];
		}
		if (!empty($search['degree'])) {
			$where .= " AND ds.degree=" . $search['degree'];
		}
		if (!empty($search['grade_all'])) {
			$where .= " AND ds.grade=" . $search['grade_all'];
		}
		if (!empty($search['session'])) {
			$where .= " AND ds.session=" . $search['session'];
		}
		if (!empty($search['goHomeType'])) {
			$where .= " AND s.goHomeType=" . $search['goHomeType'];
		}
		if ($search['study_type']!="") {
			$where .= " AND ds.stop_type=" . $search['study_type'];
		}
		$where .= $dbGb->getAccessPermission('s.branch_id');
		$where .= $dbGb->getDegreePermission('ds.degree');
		return $_db->fetchAll($sql . $where . $orderby);
	}

	public function getStudentFrontDeskDetailById($stu_id)
	{
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$branchLabel = $dbgb->getBranchDisplay();
		$colunmname = 'title_en';
		$vill = 'village_name';
		$comm = 'commune_name';
		$dist = 'district_name';
		$prov = 'province_en_name';
		$view = 'name_en';
		$occuTitle = 'occu_enname';
		$sessionTitle = 'title';
		if ($currentLang == 1) {
			$colunmname = 'title';
			$vill = 'village_namekh';
			$comm = 'commune_namekh';
			$dist = 'district_namekh';
			$prov = 'province_kh_name';
			$view = 'name_kh';
			$occuTitle = 'occu_name';
			$sessionTitle = 'titleKh';
		}

		$sql = "SELECT 
				
				
				s.oldStudentId,
				s.walletBalance,
				s.stu_khname,
				s.last_name,
				s.stu_enname,
				s.stu_code,
				s.sex,
				s.pob,
				s.tel,
				s.email,
				s.home_num,
				s.street_num,
				s.from_school,
				s.sponser,
				s.sponser_phone,
				s.familyId,
				s.photo,
				CASE 
					WHEN s.dob IS NULL THEN '' 
					WHEN s.dob = '1970-01-01' THEN ''
					ELSE  DATE_FORMAT(s.dob,'%d/%M/%Y') 
				END	As dob
				,CASE 
					WHEN s.dob IS NULL THEN '' 
					WHEN s.dob = '1970-01-01' THEN ''
					ELSE  s.dob
				END	As studentDob,

				
				os.stu_code as old_stu_code,
				bo.br_id AS oBranchId,
				bo.$branchLabel oBranchName,
				b.$branchLabel branchName,
			
 				(SELECT $view FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
    			(SELECT $view FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
    			(SELECT $view FROM rms_view where type=21 and key_code=fam.fatherNation LIMIT 1) AS father_nation,
    			(SELECT $view FROM rms_view where type=21 and key_code=fam.motherNation LIMIT 1) AS mother_nation,
    			(SELECT $view FROM rms_view where type=21 and key_code=fam.guardianNation LIMIT 1) AS guardian_nation,
    			
				vl.$vill AS village_name,
				c.$comm AS commune_name,
		    	(SELECT $dist FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
				(SELECT $prov FROM rms_province WHERE province_id=s.province_id LIMIT 1) AS province_name

				,fam.fatherNameKh AS father_khname 
				,fam.fatherName AS father_enname  
				,fam.fatherNation AS father_nation
				,fam.fatherPhone AS father_phone
				
				,fam.motherNameKh AS mother_khname 
				,fam.motherName AS mother_enname  
				,fam.motherPhone AS mother_phone  
				
				,fam.guardianNameKh AS guardian_khname 
				,fam.guardianName AS guardian_enname 
				,fam.guardianPhone AS guardian_tel
				,ds.group_id
				,g.group_code AS group_name
				,pt.$sessionTitle AS sessionTitle
				,r.room_name roomName
				,CONCAT(ac.fromYear,'-',ac.toYear) AS academicYear
				,i.$colunmname AS degreeTitle
			    ,idd.$colunmname AS gradeTitle
				,k.title AS know_by
				,(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.fatherJob LIMIT 1) fath_job
				,(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.motherJob LIMIT 1) moth_job
				,(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.guardianJob LIMIT 1) guard_job
				,(SELECT l.title FROM `rms_degree_language` AS l WHERE l.id = s.lang_level LIMIT 1) AS lang_level
				,pre.schoolName AS prevSchoolName
				
				FROM 
					rms_student as s JOIN rms_group_detail_student AS ds ON ds.itemType=1  AND s.stu_id = ds.stu_id  AND ds.is_maingrade=1 AND ds.is_current=1 
					LEFT JOIN rms_student os ON os.stu_id = s.oldStudentId
					LEFT JOIN `rms_branch` bo ON bo.br_id = os.branch_id
					LEFT JOIN `rms_branch` b ON b.br_id = s.branch_id
					LEFT JOIN rms_family AS fam ON fam.id = s.familyId
					LEFT JOIN rms_group AS g ON g.id = ds.group_id
					LEFT JOIN rms_parttime_list AS pt ON pt.id=g.session
					LEFT JOIN rms_room r ON r.room_id=g.room_id
					LEFT JOIN rms_academicyear ac ON ac.id=ds.academic_year
					LEFT JOIN `rms_items` AS i ON i.id = ds.degree AND i.type=1
					LEFT JOIN `rms_itemsdetail` AS idd ON idd.id = ds.grade AND idd.items_type=1
					LEFT JOIN `ln_village` AS vl ON vl.vill_id = s.village_name
					LEFT JOIN `ln_commune` AS c ON c.com_id = s.commune_name
					LEFT JOIN `rms_know_by` AS k ON k.id = s.know_by 
					LEFT JOIN `rms_previous_school` AS pre ON pre.id = s.from_school 
				WHERE  s.stu_id=$stu_id  ";
		$where = '';
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where .= $dbp->getAccessPermission("s.`branch_id`");
		$where .= $dbp->getDegreePermission('ds.degree');
		$where .= " LIMIT 1";
		return $db->fetchRow($sql . $where);
	}
	public function getStudentByIdToken($stToken)
	{ //will combine with above
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname = 'title_en';
		$vill = 'village_name';
		$comm = 'commune_name';
		$dist = 'district_name';
		$prov = 'province_en_name';
		$view = 'name_en';
		$occuTitle = 'occu_enname';
		if ($currentLang == 1) {
			$colunmname = 'title';
			$vill = 'village_namekh';
			$comm = 'commune_namekh';
			$dist = 'district_namekh';
			$prov = 'province_kh_name';
			$view = 'name_kh';
			$occuTitle = 'occu_name';
		}

		$sql = "
		SELECT 
			
			fam.fatherNameKh AS father_khname 
			,fam.fatherName AS father_enname  
			,fam.fatherNation AS father_nation
			,fam.fatherPhone AS father_phone
			
			,fam.motherNameKh AS mother_khname 
			,fam.motherName AS mother_enname  
			,fam.motherPhone AS mother_phone  
			
			,fam.guardianNameKh AS guardian_khname 
			,fam.guardianName AS guardian_enname 
			,fam.guardianPhone AS guardian_tel
			,s.*
			
			,DATE_FORMAT(`s`.`dob`,'%d-%m-%Y') AS `dob`,
			(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS branch_name,
			(SELECT school_namekh FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS school_namekh,
			(SELECT school_nameen FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS school_nameen,
			(SELECT photo FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS photo_branch,
			(SELECT br_address FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS br_address,
			(SELECT branch_tel FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS branch_tel,
			(SELECT email FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS email_branch,
			(SELECT website FROM `rms_branch` WHERE br_id=s.`branch_id` LIMIT 1) AS website,
			(SELECT $view from rms_view where type=5 and key_code=ds.stop_type LIMIT 1) as status_student,
			
			(SELECT $view FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
			(SELECT $view FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
			(SELECT $view FROM rms_view where type=21 and key_code=fam.fatherNation LIMIT 1) AS father_nation,
			(SELECT $view FROM rms_view where type=21 and key_code=fam.motherNation LIMIT 1) AS mother_nation,
			(SELECT $view FROM rms_view where type=21 and key_code=fam.guardianNation LIMIT 1) AS guardian_nation,
		 
			(SELECT $vill FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
			(SELECT $comm FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
			(SELECT $dist FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
			(SELECT $prov FROM rms_province WHERE province_id=s.province_id LIMIT 1) AS province_name,
			ds.group_id,
			(SELECT g.group_code FROM rms_group AS g WHERE g.id=ds.group_id LIMIT 1) AS group_name,
			(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academic_year LIMIT 1) AS year_name,
			(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = ds.degree AND i.type=1 LIMIT 1) AS degree_name,
			(SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = ds.grade AND idd.items_type=1 LIMIT 1) AS grade_name,
			
	
			(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.fatherJob LIMIT 1) fath_job,
			(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.motherJob LIMIT 1) moth_job,
			(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.guardianJob LIMIT 1) guard_job,
			
			(SELECT k.title FROM `rms_know_by` AS k WHERE k.id = s.know_by LIMIT 1) AS know_by,
			(SELECT l.title FROM `rms_degree_language` AS l WHERE l.id = s.lang_level LIMIT 1) AS lang_level
	
		FROM
			rms_student as s 
			JOIN rms_group_detail_student AS ds ON ds.itemType=1 AND s.stu_id = ds.stu_id AND ds.is_maingrade=1
			LEFT JOIN rms_family fam ON s.familyId=fam.id
		WHERE s.studentToken='" . addslashes($stToken) . "'";
		$where = '';
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where .= $dbp->getAccessPermission("s.`branch_id`");
		$where .= " LIMIT 1";
		return $db->fetchRow($sql . $where);
	}
	function getAllStudentStudyRecord($stu_id)
	{
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();

		$titleCol = "title";
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
			$titleCol = "titleKh";
		}

		$sql = " SELECT 
	 				ds.group_id
					,g.group_code group_name
					,CONCAT(fromYear,'-',toYear) academic_year
					,i.$colunmname degree_name
					,idd.$colunmname grade_name
					,room_name
					,p.$titleCol `session`
					,DATE_FORMAT(ds.create_date, '%d-%m%-y') AS entryDate
				FROM 
					rms_group_detail_student AS ds
					JOIN rms_group AS g ON g.id = ds.group_id
					LEFT JOIN rms_academicyear ac ON ac.id=ds.academic_year
					LEFT JOIN rms_items i ON i.id = ds.degree
					LEFT JOIN `rms_itemsdetail` AS idd ON idd.id = ds.grade AND idd.items_type=1
					LEFT JOIN rms_room r ON r.room_id=g.room_id
					LEFT JOIN rms_parttime_list p ON p.id = g.session
 			WHERE 
				ds.itemType=1 
				AND ds.stu_id = $stu_id 
				AND ds.is_current=1 ";
		return $db->fetchAll($sql);
	}


public function homeGetStudentPaymentDetail($stu_id,$oldStudentId)
	{
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();

		$currentLang = $_db->currentlang();
		$colunmname = 'title_en';
		$label = "name_en";
		if ($currentLang == 1) {
			$colunmname = 'title';
			$label = "name_kh";
		}
		$sql = " SELECT
					spd.id,	
					spd.payment_id, 
					spd.fee,
					spd.qty,
					spd.subtotal,
					spd.extra_fee,
					spd.discount_percent,
					spd.discount_amount,	
					spd.paidamount,		
					spd.note,
					spd.start_date,
					spd.validate,
					spd.is_start,	
					spd.is_onepayment,	
					sp.branch_id,
					sp.student_id,
					sp.receipt_number,
					sp.create_date,
					sp.is_void,
					sp.balance_due as balance,
					s.stu_code,
					s.stu_khname,
					s.stu_enname,
					p.$colunmname title,
					ds.discountCode as discount_type,
					it.$colunmname as category,
					v1.$label AS payment_term, 	
					v2.$label as void_status,	 
					u.first_name as user,
					st.title  term_installment
				FROM
					rms_student_payment AS sp join rms_student_paymentdetail AS spd ON sp.id=spd.payment_id 
					JOIN rms_student AS s ON s.stu_id = sp.student_id AND s.stu_id IN($stu_id,$oldStudentId)
					LEFT JOIN rms_itemsdetail AS p ON p.id = spd.itemdetail_id
					LEFT JOIN rms_dis_setting ds ON ds.id=spd.discount_type
					LEFT JOIN rms_items AS it ON it.id = p.items_id
					LEFT JOIN rms_view v1 ON v1.type=6 AND v1.key_code=spd.payment_term
					LEFT JOIN rms_users u ON u.id = sp.user_id
					LEFT JOIN rms_view v2 ON v2.type=10 AND v2.key_code=sp.is_void
					LEFT JOIN `rms_startdate_enddate` st ON st.id=spd.academicFeeTermId
				WHERE
					 s.customer_type=1 
				ORDER BY 
					sp.id DESC ,
					p.items_id ASC ";
			return $db->fetchAll($sql);
			//ds.dis_name as discount_type,
			//LEFT JOIN rms_discount ds ON ds.disco_id=spd.discount_type
	}
	/*
	public function getStudentServiceUsing($stu_id, $search, $order_no)
	{
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();

		$currentLang = $_db->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}

		$sql = " SELECT
					spd.id,
					spd.fee,
					spd.qty,
					spd.subtotal,		
					spd.extra_fee,
					spd.discount_percent,	
					spd.paidamount,
					spd.note,
					spd.start_date,
					spd.validate,
					spd.is_start,
					sp.receipt_number,
					sp.create_date,
					sp.is_void,
					s.stu_code,
					s.stu_khname,
					s.stu_enname,
					p.title AS service_name,
			 		(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = p.items_id  LIMIT 1) AS category,		
					(SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = spd.itemdetail_id LIMIT 1) AS items_name,			  
					(SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user,
					(SELECT name_kh FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term LIMIT 1) AS payment_term,
					(SELECT name_en FROM rms_view WHERE TYPE=10 AND key_code=sp.is_void LIMIT 1) AS void_status
				FROM
					rms_student_payment AS sp,
					rms_student_paymentdetail AS spd,
					rms_student AS s,
					rms_itemsdetail AS p
				WHERE
					s.stu_id = sp.student_id
					AND sp.id=spd.payment_id
					AND p.id = spd.itemdetail_id
					AND p.items_type=2
					AND spd.is_suspend=0 
					AND s.customer_type=1
					AND s.stu_id=$stu_id
				group by spd.itemdetail_id
			";
			
		return $db->fetchAll($sql);
	}
	*/
	
	function getStudentServiceUsing($search = array()){
		$_db = $this->getAdapter();
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbGb->currentlang();
		
		$branch = $dbGb->getBranchDisplay();
		$titleCol = "title_en";
		$titleColGrade = "gradeTitleEn";
		$titleColDegree = "degreeTitleEn";
		$view = "name_en";
		if($lang==1){
			$titleCol = "title";
			$titleColGrade = "gradeTitleKh";
			$titleColDegree = "degreeTitleKh";
			$view = "name_kh";
		}
		
		$sqlDistinctQue = "";
		$sqlDistinctCondictionQue = "";
		if(!empty($search['distinctStudent'])){
			$sqlDistinctQue = ",COUNT(gds.`gd_id`) AS amtStuService";
			$sqlDistinctCondictionQue = " GROUP BY gds.`stu_id` ";
		}
		$sql="SELECT
				gds.`gd_id` AS id
				,gds.`stu_id` AS studentId
				,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=vs.branchId LIMIT 1) AS branchName
				,(SELECT CONCAT(aca.fromYear,'-',aca.toYear) FROM rms_academicyear AS aca WHERE aca.id=vs.`academicYear` LIMIT 1) AS academicYearTitle
				,vs.`stuCode`
				,vs.`stuNameKh`
				,vs.`stuNameEn`
				,vs.`tel`
				,vs.`sex`
				,vs.`groupCode`
				,vs.`photo`
				
				,COALESCE(vs.`degreeShortcut`,vs.$titleColDegree) AS degreeTitle
				,COALESCE(vs.`gradeShortcut`,vs.$titleColGrade) AS gradeTitle
				,vs.`academicYear`
				,gds.`itemType`
				,gds.`gd_id` AS detailId
				,gds.`grade`
				,itd.`title` AS itemTitle
				,gds.`degree`
				,it.`title` AS categoryTitle
				,gds.`stop_type`
				,gds.`startDate`
				,gds.`endDate`
				,COALESCE(gds.`feeId`,0) AS feeId
				,gds.`balance`
				,gds.`discount_type`
				,gds.`discount_amount`
				,gds.note
				,gds.`is_current`
				,(SELECT v.$view FROM rms_view AS v WHERE v.type=5 and v.key_code=gds.stop_type LIMIT 1) as stopTypeTitle
			";
		$sql.=$sqlDistinctQue;
		$sql.="
			FROM (`rms_group_detail_student` AS gds  JOIN `v_stu_study_info` AS vs  ON vs.`studentId` = gds.`stu_id` AND vs.`itemType` =1)
				JOIN `rms_itemsdetail` AS itd ON itd.`id` = gds.`grade`  AND itd.`is_onepayment` = 0
				LEFT JOIN `rms_items` AS it ON it.`id` = gds.`degree`
		";
		$sql.="WHERE 1 
			AND gds.`itemType` !=1 
			AND gds.`is_current` = 1 
			AND gds.`endDate` !='0000-00-00'
		";
		//AND gds.`stop_type` = 0
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			
			$s_where[]= " REPLACE(vs.`stuCode`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`stuNameKh`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`stuNameEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`stuCode`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`degreeTitleKh`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`degreeTitleEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`gradeTitleEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`gradeTitleKh`,' ','') LIKE '%{$s_search}%'";
			
			$sql.=' AND ('.implode(' OR ', $s_where).')';
		}
		if(!empty($search['academic_year'])){
    		$sql.= " AND vs.`academicYear` = ".$_db->quote($search['academic_year']);
    	}
		if(!empty($search['studentId'])){
    		$sql.= " AND gds.`stu_id` = ".$_db->quote($search['studentId']);
    	}
		if(!empty($search['branch_id'])){
    		$sql.= " AND vs.`branchId` = ".$_db->quote($search['branch_id']);
    	}
		if(!empty($search['groupId'])){
    		$sql.= " AND vs.`groupId` = ".$_db->quote($search['groupId']);
    	}
		if(!empty($search['category'])){
    		$sql.= " AND gds.`degree` = ".$_db->quote($search['category']);
    	}
		if(!empty($search['item'])){
    		$sql.= " AND gds.`grade` = ".$_db->quote($search['item']);
    	}
		if(!empty($search['degree'])){
    		$sql.= " AND vs.`degree` = ".$_db->quote($search['degree']);
    	}
		if(!empty($search['gradeId'])){
    		$sql.= " AND vs.`grade` = ".$_db->quote($search['gradeId']);
    	}
		$sql.= $dbGb->getAccessPermission('vs.`branchId`');
		$sql.= $dbGb->getDegreePermission('COALESCE(vs.`degree`,0)');
		$sql.= $sqlDistinctCondictionQue;
		$orderby = " ORDER BY gds.`stop_type` ASC,vs.`degreeOrdering` ASC, vs.`gradeOrdering` ASC, vs.`groupCode` ASC ";
		
		return $_db->fetchAll($sql.$orderby);
	}

	function getRescheduleByGroupId($id)
	{
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}

		$sql = " SELECT 
					gr.id,
					(SELECT branch_nameen FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_name,	
					(select group_code from rms_group as g where g.id = gr.group_id limit 1) AS group_code,
					gr.branch_id,
					gr.year_id,
					gr.group_id,
					gr.day_id,
					gr.from_hour,
					gr.to_hour,
					gr.subject_id,
					gr.techer_id,
			    	(SELECT room_name AS NAME FROM `rms_room` WHERE is_active=1 AND room_name!='' AND rms_room.room_id=(SELECT g.room_id FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1) )AS room_name,
			    	(SELECT CONCAT(rms_itemsdetail.$colunmname,' ',(SELECT rms_items.$colunmname FROM rms_items WHERE rms_items.id=rms_itemsdetail.items_id AND rms_items.type=1 LIMIT 1)) FROM rms_itemsdetail WHERE rms_itemsdetail.id=(SELECT g.grade FROM rms_group AS g WHERE g.id=gr.group_id LIMIT 1) AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_name,
			    	REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times ,
			    	gd.stu_id
    			FROM 
    				rms_group_reschedule AS gr,
    				rms_group_detail_student AS gd
    			WHERE 
					gd.itemType=1 
    				AND gr.group_id=gd.group_id
    				and gd.is_pass = 0
    	 			AND gd.stu_id=$id
		    	GROUP BY 
		    		gr.year_id,
		    		gr.group_id
		    	ORDER BY 
					gr.year_id,
					gr.group_id,
					times DESC
			";
		return $db->fetchAll($sql);
	}

	function getStudentDocumentById($id)
	{
		$db = $this->getAdapter();
		$sql = " SELECT * from rms_student_document where stu_id = $id ";
		return $db->fetchAll($sql);
	}

	function getStudentMistake($stu_id)
	{
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}

		$sql = "SELECT
					g.id as group_id,
					g.`group_code`,
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
					(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
				
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
					`g`.`semester` AS `semester`,
					(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
					sdd.`stu_id`, st.`stu_code`, st.`stu_enname`, st.`stu_khname`, st.`sex`
				FROM
					rms_student_attendence AS sd 
					 JOIN `rms_student_attendence_detail` AS sdd ON sd.`id` = sdd.`attendence_id` 
						LEFT JOIN `rms_group` AS g ON sd.group_id = g.id 
						LEFT JOIN `rms_student` AS st ON st.`stu_id` = sdd.`stu_id`
						
				WHERE
					(sd.type=2 OR sdd.`attendence_status` IN (4,5))
					AND sd.`id` = sdd.`attendence_id`
					AND sd.group_id = g.id 
					AND sd.status=1
					AND st.`stu_id` = sdd.`stu_id` 
					and sdd.stu_id = $stu_id
			";

		$order = " GROUP BY sd.group_id,sdd.`stu_id` ORDER BY `g`.`degree`,`g`.`grade` DESC,g.group_code ASC ,g.id DESC";
		return $db->fetchAll($sql . $order);
	}

	function getStatusMistakeByStudent($stu_id, $group)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
					sd.`group_id`,
					sd.`type`,
					sdd.`attendence_status` as mistake_type,
					sdd.description,
					sd.`date_attendence` as mistake_date,
					sd.for_session
				FROM
					`rms_student_attendence` AS sd,
					`rms_student_attendence_detail` AS sdd
				WHERE
					(sd.type=2 OR sdd.`attendence_status` IN (4,5))
					AND sd.`id` = sdd.`attendence_id`
					AND sdd.`stu_id` = $stu_id
					AND sd.`group_id` = $group 
			";
		$sql .= " GROUP BY 
				CASE  
					WHEN sd.type = 1 THEN sd.id AND sdd.attendence_status
					ELSE sdd.id
				END
		ORDER BY sd.`date_attendence` ASC,sdd.attendence_status DESC ";
		return $db->fetchAll($sql);
	}


	function getStudentAttendence($stu_id)
	{
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}

		$sql = "SELECT
					g.id AS group_id,
					g.`group_code`,
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic_year,
					(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
				
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
					`g`.`semester` AS `semester`,
					(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
					sdd.`stu_id`,
					st.`stu_code`,st.`stu_enname`,st.`stu_khname`,st.`sex`
				FROM
					`rms_group` AS g,
					`rms_student` AS st,
					rms_student_attendence AS sta,
					`rms_student_attendence_detail` AS sdd
				WHERE
					sta.type=1
					AND sta.`id` = sdd.`attendence_id`
					AND sta.type=1
					AND sta.group_id = g.id
					AND st.`stu_id` = sdd.`stu_id`
					AND sta.status=1
					AND g.is_pass!=1
					AND st.`stu_id` = $stu_id
			";
		$order = " GROUP BY sta.group_id,sdd.stu_id
		ORDER BY `g`.`degree`,`g`.`grade` DESC,g.group_code ASC ,g.id DESC,st.stu_khname ASC ";
		return $db->fetchAll($sql . $order);
	}

	function getStatusAttendence($stu_id, $group)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
					sat.`group_id`,
					satd.`attendence_status`,
					sat.`date_attendence`,
					satd.description
				FROM 
					`rms_student_attendence` AS sat,
					`rms_student_attendence_detail` AS satd
				WHERE 
					sat.`id`= satd.`attendence_id`
					AND sat.type=1
					AND satd.`stu_id`=$stu_id
					AND sat.`group_id`=$group
					GROUP BY sat.`date_attendence` 
					ORDER BY satd.`attendence_status` DESC
			";
		return $db->fetchAll($sql);
	}
	
	function getSumStatusAttendence($stu_id, $group)
	{
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				v.`group_id`
				,v.`maxAttendenceStatus` AS attendence_status
				,COUNT(IF(v.`maxAttendenceStatus` = '1' AND v.`forSemester`=1, v.`maxAttendenceStatus`, NULL)) AS totalComeSemester1
				,COUNT(IF(v.`maxAttendenceStatus` = '2' AND v.`forSemester`=1, v.`maxAttendenceStatus`, NULL)) AS totalASemester1
				,COUNT(IF(v.`maxAttendenceStatus` = '3' AND v.`forSemester`=1, v.`maxAttendenceStatus`, NULL)) AS totalPSemester1
				,COUNT(IF(v.`maxAttendenceStatus` = '4' AND v.`forSemester`=1, v.`maxAttendenceStatus`, NULL)) AS totalLSemester1
				,COUNT(IF(v.`maxAttendenceStatus` = '5' AND v.`forSemester`=1, v.`maxAttendenceStatus`, NULL)) AS totalELSemester1

				,COUNT(IF(v.`maxAttendenceStatus` = '1' AND v.`forSemester`=2, v.`maxAttendenceStatus`, NULL)) AS totalComeSemester2
				,COUNT(IF(v.`maxAttendenceStatus` = '2' AND v.`forSemester`=2, v.`maxAttendenceStatus`, NULL)) AS totalASemester2
				,COUNT(IF(v.`maxAttendenceStatus` = '3' AND v.`forSemester`=2, v.`maxAttendenceStatus`, NULL)) AS totalPSemester2
				,COUNT(IF(v.`maxAttendenceStatus` = '4' AND v.`forSemester`=2, v.`maxAttendenceStatus`, NULL)) AS totalLSemester2
				,COUNT(IF(v.`maxAttendenceStatus` = '5' AND v.`forSemester`=2, v.`maxAttendenceStatus`, NULL)) AS totalELSemester2

			FROM `v_studentattendancestatusperdate` AS v
			WHERE 
				v.`studentId` = $stu_id 
				AND v.`group_id`= $group 
		";
		return $db->fetchRow($sql);

		/*
			COUNT(if(satd.attendence_status = '1' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalComeSemester1,
			COUNT(IF(satd.attendence_status = '2' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalASemester1,
			COUNT(IF(satd.attendence_status = '3' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalPSemester1,
			COUNT(IF(satd.attendence_status = '4' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalLSemester1,
			COUNT(IF(satd.attendence_status = '5' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalELSemester1,
			
			COUNT(IF(satd.attendence_status = '1' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalComeSemester2,
			COUNT(IF(satd.attendence_status = '2' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalASemester2,
			COUNT(IF(satd.attendence_status = '3' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalPSemester2,
			COUNT(IF(satd.attendence_status = '4' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalLSemester2,
			COUNT(IF(satd.attendence_status = '5' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalELSemester2,
		*/
	}
	

	function addReadNews($id)
	{
		try {
			$db = $this->getAdapter();
			$arr = array(
				'new_feed_id' => $id,
				'cus_id' => $this->getUserId(),
				'is_read' => 1,
				'is_click' => 1,
				'date' => date("Y-m-d H:i:s"),
			);
			$this->_name = "ln_news__read";
			$this->insert($arr);
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}

	function getLastExamByStudent($stu_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT 
			s.*,sd.student_id FROM 
			`rms_score_detail` AS sd,
			`rms_score` AS s
			WHERE s.id = score_id
			AND sd.student_id = $stu_id
			GROUP BY s.id
			ORDER BY s.id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getLastStudentEnvaluation($stu_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT e.* FROM `rms_student_evaluation` AS e
		WHERE e.student_id = $stu_id ORDER BY e.id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getStudyHistoryByStudent($stu_id,$oldStudentId=null)
	{
		$db = $this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$title = 'title_en';
		$view = "name_en";
		$teacher = "teacher_name_en";
		$sessionTitle = 'title';
		if ($currentLang == 1) { // khmer
			$title = 'title';
			$view = "name_kh";
			$teacher = "teacher_name_kh";
			$sessionTitle = 'titleKh';
		}
		//,(SELECT $view FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS session_id
		$sql = "SELECT
					g.branch_id
					,g.group_code
					,CONCAT(acy.fromYear, '-', acy.toYear) AS academic_id
					,i.$title AS degree
					,itd.$title AS grade
					,p.$sessionTitle `shift`
					,r.room_name
					,gds.is_pass
					,gds.stop_type
					,gds.is_newstudent
					,gds.is_current
					,dst.discountCode
					,dst.discountValue
					,v.name_en AS entryType
					,DATE_FORMAT(gds.create_date, '%d-%m-%y') AS entryDate
					,rv_group_status.$view AS groupStatus
   					,rv_movement.$view AS movementStatus
				FROM
					rms_group_detail_student AS gds JOIN rms_student as s ON gds.stu_id = s.stu_id
					LEFT JOIN `rms_discount_student` dis ON dis.studentId=gds.stu_id AND dis.degreeId=gds.degree AND dis.grade=gds.grade
					LEFT JOIN `rms_dis_setting` dst ON dst.id=dis.discountGroupId
					LEFT JOIN `rms_group_student_change_group` gsc ON gsc.id=gds.`changeGroupId`
					LEFT JOIN `rms_view` v ON v.`type`=17 AND gsc.change_type=v.`key_code`
					LEFT JOIN rms_group AS g ON gds.group_id = g.id
					LEFT JOIN rms_academicyear AS acy ON acy.id = CASE WHEN gds.is_setgroup = 1 THEN g.academic_year ELSE gds.academic_year END
					LEFT JOIN rms_items AS i ON i.`id`=gds.`degree` AND i.type=1
					LEFT JOIN `rms_itemsdetail` AS itd ON itd.`id`=gds.`grade` AND itd.items_type=1 
					LEFT JOIN rms_parttime_list p ON p.id = g.session
					LEFT JOIN rms_room r ON r.room_id=g.room_id
					LEFT JOIN rms_view AS rv_group_status ON rv_group_status.type = 9 AND rv_group_status.key_code = g.is_pass
					LEFT JOIN rms_view AS rv_movement ON rv_movement.type = 17 AND rv_movement.key_code = gds.movedType
					
				WHERE 
					gds.itemType=1 
					AND gds.stu_id IN($stu_id,$oldStudentId)
					AND gds.status=1
				ORDER BY 
					gds.academic_year DESC,
					i.ordering ASC,
					itd.ordering DESC,
					gds.gd_id DESC,
					gds.create_date DESC
						
			";
			// echo $sql;exit();
			$result1=$db->fetchAll($sql);

			$sql="SELECT 
					g.group_code group_code
					,CONCAT(fromYear,'-',toYear) academic_id
					,i.$title degree
					,idd.$title grade
					,p.$sessionTitle `shift`
					,r.room_name
					,'' is_pass 
					,'' stop_type 
					,'' is_newstudent 
					,v.name_en entryType
					,DATE_FORMAT(d.date_stop, '%d-%m-%y') AS entryDate
					,1 AS is_current
					,'' discountCode
					,'' discountValue
					,'' groupStatus
					,'' movementStatus

					FROM `rms_student_drop` d 
						LEFT JOIN rms_group AS g ON g.id = d.group
						LEFT JOIN rms_room r ON r.room_id=g.room_id
						LEFT JOIN rms_academicyear ac ON ac.id=d.academic_year
						LEFT JOIN rms_items i ON i.id = d.degree
						LEFT JOIN `rms_itemsdetail` AS idd ON idd.id = d.grade AND idd.items_type=1
						LEFT JOIN rms_parttime_list p ON p.id = g.session
						LEFT JOIN `rms_view` v ON v.`type`=5 AND v.`key_code`=d.`type`
					WHERE d.stu_id=".$stu_id;
				$result2=$db->fetchAll($sql);

				$result=array_merge($result2,$result1);
				return $result;


	}


	function getStudentAllTestInfo($stu_id)
	{
		try {
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('st.branch_id');
			$lang = $_db->currentlang();
			if ($lang == 1) { // khmer 
				$label = "name_kh";
				$grade = "idd.title";
				$degree = "i.title";
			} else { // English
				$label = "name_en";
				$grade = "idd.title_en";
				$degree = "i.title_en";
			}

			$db = $this->getAdapter();
			$testCondiction = TEST_CONDICTION;

			$sql = " SELECT st.*,
					(SELECT $label FROM rms_view WHERE TYPE=2 AND key_code=st.sex LIMIT 1) AS sex,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=str.academic_year LIMIT 1) AS academic,
					";

			if ($testCondiction == 2){
				$sql .= "(SELECT tm.note FROM `rms_test_term` AS tm WHERE tm.id=str.study_term) AS study_term,";
			} else {
				$sql .= "(SELECT CONCAT(title,' ( ',DATE_FORMAT(start_date, '%d/%m/%Y'),' - ',DATE_FORMAT(end_date, '%d/%m/%Y'),' )') FROM `rms_startdate_enddate` WHERE rms_startdate_enddate.forDepartment=1 AND rms_startdate_enddate.id=str.study_term) AS study_term,";
			}
			$sql .= "		
					(SELECT $degree FROM `rms_items` AS i WHERE i.id = str.degree AND i.type=1 LIMIT 1) AS degree_title,
					(SELECT $grade FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade AND idd.items_type=1 LIMIT 1) AS grade_title,
					(SELECT $degree FROM `rms_items` AS i WHERE i.id = str.degree_result AND i.type=1 LIMIT 1) AS degree_result_title,
					(SELECT $grade FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade_result AND idd.items_type=1 LIMIT 1) AS grade_result_title,
					(SELECT first_name FROM rms_users WHERE rms_users.id = str.user_id LIMIT 1) AS user_id,
					(SELECT $label FROM rms_view WHERE TYPE=15 AND key_code = str.updated_result LIMIT 1) AS result_status,
					(SELECT first_name FROM rms_users WHERE rms_users.id = str.result_by LIMIT 1) AS result_by,
					str.create_date AS create_date_exam,
					str.result_date,
					str.test_date AS test_date_exam,
					str.updated_result AS updated_result_de,
					str.note AS note_result,
					str.is_registered
				FROM 
					`rms_student` AS st,
					`rms_student_test_result` AS str
				WHERE 
					st.is_studenttest = 1
					AND str.stu_test_id = st.stu_id
					AND status=1
					AND st.stu_khname!=''
					AND st.`stu_id` = $stu_id";

			$where = " ";
			$dbp = new Application_Model_DbTable_DbGlobal();
			$where .= $dbp->getAccessPermission("st.branch_id");
			$sql .= $dbp->getSchoolOptionAccess('str.test_type');

			$order = " ORDER By str.updated_result DESC,str.degree_result ASC,str.grade_result ASC ";
			return $db->fetchAll($sql . $where . $order);
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}

	function getCountAttendanceByStudent($_data)
	{
		$db = $this->getAdapter();
		$attendenceStatus = empty($_data["attendenceStatus"]) ? 0 : $_data["attendenceStatus"];
		$studentId = empty($_data["studentId"]) ? 0 : $_data["studentId"];
		$groupId = empty($_data["groupId"]) ? 0 : $_data["groupId"];
		$forSemester = empty($_data["forSemester"]) ? 1 : $_data["forSemester"];
		$sql = "
			SELECT 
				satd.attendence_id,
				satd.attendence_status
			FROM 
				`rms_student_attendence` AS sat ,
				`rms_student_attendence_detail` AS satd
					
			WHERE 
				sat.`id`= satd.`attendence_id`
				AND sat.status = 1
				AND satd.attendence_status = $attendenceStatus
				AND sat.for_semester = $forSemester
				AND satd.stu_id = $studentId
				AND sat.group_id = $groupId
				
			";
		/*
		$sql .= "
				GROUP BY satd.attendence_id,satd.attendence_status
				ORDER BY satd.attendence_status DESC
		";
		*/
		$sql .= "
				GROUP BY sat.date_attendence,satd.attendence_status
				ORDER BY satd.attendence_status DESC
		";
		$rs = $db->fetchAll($sql);
		$restult = "0";
		if (!empty($rs)) {
			return $restult = "" . COUNT($rs) . "";
		}
		return $restult;
	}
	
	function getAllAchievementByStudent($studentId){
    	$db = $this->getAdapter();
		
    	$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		

    	$sql = "
			SELECT 
				ac.id
				,(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = ac.branchId LIMIT 1) AS branchName
				,(SELECT b.photo FROM `rms_branch` AS b  WHERE b.br_id = ac.branchId LIMIT 1) AS branchLogo
				,(SELECT b.school_namekh FROM `rms_branch` AS b  WHERE b.br_id = ac.branchId LIMIT 1) AS schoolNameKh
				,(SELECT b.school_nameen FROM `rms_branch` AS b  WHERE b.br_id = ac.branchId LIMIT 1) AS schoolNameEn
				
				,ac.title AS achievementTitle
				,ac.achievementType AS achievementTypeTitle
				,ac.description AS achievementDescription
				,g.group_code AS groupCode
				,g.degree AS degreeId
				,(SELECT CONCAT(c.fromYear,'-',c.toYear) FROM `rms_academicyear` AS c WHERE c.id = g.academic_year LIMIT 1) As academicYear
				
				,s.sex
				,COALESCE(s.photo,'') AS photo
				,COALESCE(s.stu_code,'') AS stuCode
				,COALESCE(s.stu_khname,'') AS stuNameKh
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameEn
				,(SELECT first_name FROM rms_users WHERE rms_users.id = ac.userId LIMIT 1) AS userName
				,ac.createDate
    	";
    	$sql.=" FROM 
					`rms_student_achievement` AS ac
					JOIN `rms_student` AS s ON s.stu_id = ac.studentId
					LEFT JOIN `rms_group` AS g ON g.id = ac.groupId
				WHERE 1 
		";
    	$where ='';
		$where.=" AND ac.studentId = ".$studentId;	
	    
	    $where.= $dbp->getAccessPermission('ac.branchId');
		$where.= $dbp->getDegreePermission('g.degree');
	    $order =  ' ORDER BY ac.`id` DESC ' ;
		
	    return $db->fetchAll($sql.$where.$order);
    }
	
	public function getCreditTransaction($studentId){
		try{
			
			$dbGb = new Application_Model_DbTable_DbGlobal();
			$currentLang = $dbGb->currentlang();
			$branch_id = $dbGb->getAccessPermission('cr.branch_id');
			$branch = $dbGb->getBranchDisplay();
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$db=$this->getAdapter();
			
			$sql=" SELECT
						(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id=cr.branch_id LIMIT 1) AS branch_name 
						,cr.`id`
						,cr.`transaction_no`
						,cr.`type`
						,cr.`student_id`
						,cr.`cashType`
						,cr.`creditType`
						,cr.`date`
						,cr.`end_date`
						,cr.`total_amount`
						,cr.`total_amountafter`
						,cr.`isExpired`
						,cr.`note`
						,cr.`prob`
						,CASE 
							WHEN cr.`creditType` =2 THEN '".$tr->translate('IS_VALIDATE')."' 
							ELSE '".$tr->translate('LIFE_TIME')."' 
						END as creditTypeTitle
						,COALESCE(cr.`otherincome_id`,0) AS depositIncomeId
						,COALESCE(inc.invoice,'') AS incInvoice

						,s.tel AS tel 
						,s.stu_code AS studentCode 
						,s.stu_khname AS studentNameKh 
						,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng 
						
						,COALESCE(cr.`fromStuId`,'') AS fromStu 
						,ss.stu_code AS fromStudentCode 
						,ss.stu_khname AS fromStudentNameKh 
						,CONCAT(COALESCE(ss.last_name,''),' ',COALESCE(ss.stu_enname,'')) AS fromStudentNameEng
						,(SELECT first_name FROM `rms_users` WHERE id=cr.user_id LIMIT 1) AS user_name
						,ref.transaction_no AS refTransaction
						,ref.date as refDate
				
				FROM `rms_creditmemo` AS cr 
						JOIN `rms_student` AS s ON s.stu_id = cr.student_id 
						LEFT JOIN `rms_student` AS ss ON ss.stu_id = cr.`fromStuId`
						LEFT JOIN `rms_creditmemo` AS ref ON ref.id = cr.`fromTransactionId`
						LEFT JOIN ln_income AS inc ON inc.id = cr.otherincome_id
				  WHERE cr.status=1 
						AND cr.student_id = $studentId
						$branch_id ";
			$order=" ORDER BY cr.id DESC ";
			$order.=" LIMIT 20 ";
			return $db->fetchAll($sql.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	}
	
	public function getAllSiblingsByFamily($data=[])
	{
		$db = $this->getAdapter();

		$stu_id = empty($data["studentId"]) ? 0 : $data["studentId"];
		$familyId = empty($data["familyId"]) ? 0 : $data["familyId"];
		$oldStudentId = empty($data["oldStudentId"]) ? 0 : $data["oldStudentId"];
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$branchLabel = $dbgb->getBranchDisplay();
		
		$colunmname = 'title_en';
		$view = 'name_en';
		$sessionTitle = 'title';
		if ($currentLang == 1) {
			$colunmname = 'title';
			$view = 'name_kh';
			$sessionTitle = 'titleKh';
		}

		$sql = "
			SELECT 
				b.$branchLabel branchName
				,s.stu_id
				,s.walletBalance
				,s.stu_khname
				,s.last_name
				,s.stu_enname
				,COALESCE(NULLIF(s.stu_code,''),s.serial) AS stu_code
				,s.sex
				,s.pob
				,s.tel
				,s.email
				,CASE 
					WHEN s.dob IS NULL THEN '' 
					WHEN s.dob = '1970-01-01' THEN ''
					ELSE  DATE_FORMAT(s.dob,'%d/%M/%Y') 
				END	As dob
				,CASE 
					WHEN s.dob IS NULL THEN '' 
					WHEN s.dob = '1970-01-01' THEN ''
					ELSE  s.dob
				END	As studentDob

				,ds.group_id
				,g.group_code AS group_name
				,pt.$sessionTitle AS sessionTitle
				,r.room_name roomName
				,CONCAT(ac.fromYear,'-',ac.toYear) AS academicYear
				,i.$colunmname AS degreeTitle
			    ,idd.$colunmname AS gradeTitle
				
				,pre.schoolName AS prevSchoolName
				,s.familyId
			FROM 
				rms_student as s JOIN rms_group_detail_student AS ds ON ds.itemType=1  AND s.stu_id = ds.stu_id  AND ds.is_maingrade=1 AND ds.is_current=1 
				LEFT JOIN `rms_branch` b ON b.br_id = s.branch_id
				LEFT JOIN rms_family AS fam ON fam.id = s.familyId
				LEFT JOIN rms_group AS g ON g.id = ds.group_id
				LEFT JOIN rms_parttime_list AS pt ON pt.id=g.session
				LEFT JOIN rms_room r ON r.room_id=g.room_id
				LEFT JOIN rms_academicyear ac ON ac.id=ds.academic_year
				LEFT JOIN `rms_items` AS i ON i.id = ds.degree AND i.type=1
				LEFT JOIN `rms_itemsdetail` AS idd ON idd.id = ds.grade AND idd.items_type=1
				LEFT JOIN `rms_know_by` AS k ON k.id = s.know_by 
				LEFT JOIN `rms_previous_school` AS pre ON pre.id = s.from_school 
			WHERE  s.stu_id != $stu_id  ";

			$sql.=" AND CASE 
				WHEN  $oldStudentId=0 THEN s.oldStudentId != $stu_id
				ELSE  (s.stu_id != $oldStudentId) 
			END ";
			//not yet finish for old student who transfered

		$where = ' AND s.familyId = '.$familyId;

		$dbp = new Application_Model_DbTable_DbGlobal();
		$where .= $dbp->getAccessPermission("s.`branch_id`");
		return $db->fetchAll($sql . $where);
	}
}
