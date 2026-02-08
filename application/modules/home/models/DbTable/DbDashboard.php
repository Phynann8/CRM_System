<?php class Home_Model_DbTable_DbDashboard extends Zend_Db_Table_Abstract{

    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    public function getSpecailDiscount($search){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$date=date("Y-m-d");
    	$sql="SELECT d.*,
			(SELECT so.dis_name FROM rms_discount AS so WHERE so.disco_id = d.dis_type LIMIT 1) AS discount_type_title,
			(SELECT name_kh FROM rms_view WHERE TYPE=11 AND key_code =d.status) AS STATUS,
			(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name,
			CASE    
				WHEN  d.duration_type = 1 THEN '".$tr->translate("MONTHLY")."'
				WHEN  d.duration_type = 2 THEN '".$tr->translate("QUARTER")."'
				WHEN  d.duration_type = 3 THEN '".$tr->translate("SEMESTER")."'
				WHEN  d.duration_type = 4 THEN '".$tr->translate("YEAR")."'
				END AS duration_type_title,
			CASE    
				WHEN  d.status = 1 THEN '".$tr->translate("RELATIVE")."'
				WHEN  d.status = 2 THEN '".$tr->translate("FRIEND")."'
				WHEN  d.status = 3 THEN '".$tr->translate("BUSINESS_PARTNER")."'
				WHEN  d.status = 4 THEN '".$tr->translate("OTHER")."'
				END AS status_type
			FROM `rms_specail_discount` AS d WHERE 1
			AND d.expired_date >='$date'";
    	$orderby = " ORDER BY d.id DESC ";
    	$where="";
    	if(!empty($search['advance_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    		$s_search = str_replace(' ', '', addslashes(trim($search['advance_search'])));
    		$s_where[] = " REPLACE(d.request_name,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(d.phone,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(d.stu_name,' ','') LIKE '%{$s_search}%'";
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['dis_type'])){
    		$where.= " AND d.dis_type  = ".$db->quote($search['dis_type']);
    	}
    	if(!empty($search['status_type'])){
    		$where.= " AND d.status = ".$db->quote($search['status_type']);
    	}
    	return $db->fetchAll($sql.$where.$orderby);
    }
    function countStudentDrop($droptype=null){

    	$db = $this->getAdapter();
    	$sql="SELECT COUNT(sd.stu_id) 
    				FROM `rms_student_drop` AS sd,
    				`rms_group` AS `g`
				WHERE sd.status =1
    				AND g.id =sd.group
    				AND g.is_pass=2
    				AND g.group_code != ''
    				 ";
    	if (!empty($droptype)){
    		$sql.=" AND sd.type=$droptype";
    	}
		$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('sd.branch_id');
		$sql.=$dbp->getDegreePermission('sd.degree');
    	return $db->fetchOne($sql);
    }
    function getStudentDropNew(){
    	$db = $this->getAdapter();
    	
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbgb->currentlang();
    	$title="v.name_en";
    	$colunmname='title_en';
    	if ($currentlang==1){
    		$title="v.name_kh";
    		$colunmname='title';
    	}
    	$sql="SELECT d.*,
				(SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = d.branch_id LIMIT 1) AS branch_name,
				(SELECT photo FROM `rms_branch` WHERE rms_branch.br_id = d.branch_id LIMIT 1) AS branch_photo,			
				s.stu_code,
				s.stu_khname,
				s.stu_enname,
				s.last_name,
				s.tel,
				s.sex,
				s.photo,
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = d.academic_year LIMIT 1) AS academic,
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=d.degree AND TYPE=1 LIMIT 1) AS degree,
				(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=d.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=d.group LIMIT 1 ) AS group_name,
				(SELECT $title AS `name` FROM rms_view AS v WHERE v.type=5 AND v.key_code=d.type LIMIT 1) AS type_drop
			FROM 
				`rms_student_drop` AS d,
				`rms_student` AS s
			WHERE s.stu_id = d.stu_id 
				AND d.status=1 
				AND d.isReturn=0
			";
			//AND d.id NOT IN ((SELECT rr.notification_id FROM `rms_read_unread_notif` AS rr WHERE rr.notif_type=1))
    	$sql.=$dbgb->getAccessPermission('d.branch_id');
    	$sql.=$dbgb->getDegreePermission('d.degree');
    	$order =" ORDER BY d.id DESC LIMIT 10 ";
    	return $db->fetchAll($sql.$order);
    }
    function getSettingDiscountNearlyExpire(){
    	$db = $this->getAdapter();
    	$date=date("Y-m-d",strtotime("+1 month"));
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbgb->currentlang();
    	$title="v.name_en";
    	if ($currentlang==1){
    		$title="v.name_kh";
    	}
    	$sql="SELECT 
				(SELECT branch_nameen FROM `rms_branch` WHERE br_id=ds.branchId LIMIT 1)AS branch,
				(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=ds.discountId LIMIT 1)AS disc_name,
				ds.*,
				(SELECT  CONCAT(first_name) FROM rms_users WHERE id=ds.userId LIMIT 1)AS user_name,
				(SELECT $title FROM rms_view as v WHERE v.type=1 AND v.key_code =ds.status LIMIT 1) AS `status` 
			FROM 
				rms_dis_setting AS ds
			WHERE 
				ds.status=1
				AND ds.discountFor=2
				AND ds.discountPeriod=2
				AND ds.endDate <='$date'";
    	$sql.=$dbgb->getAccessPermission('ds.branchId');
    	$order =" ORDER BY ds.discountId DESC";
    	return $db->fetchAll($sql.$order);
    }
	
	function getCountingClass($search){
		$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="
			SELECT 
				COUNT(g.id) AS `value`
				,CASE 
					WHEN it.`shortcut` IS NOT NULL THEN it.`shortcut`
					ELSE it.`title`
				END AS label
			FROM `rms_group` AS g
				JOIN `rms_items` AS it ON it.`id` = g.`degree` AND it.`type` = 1 
			WHERE g.`status` = 1 
		";
    	if(!empty($search['academic_year'])){
    		$sql.= " AND g.`academic_year` = ".$db->quote($search['academic_year']);
    	}
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbgb->getAccessPermission('g.`branch_id`');
    	$sql.=$dbgb->getDegreePermission('g.`degree`');
		
		$sql.= " GROUP BY g.`degree` ";
		$order = " ORDER BY it.`schoolOption` ASC ,it.`ordering` ASC ,g.`degree` ASC";
		$sql.= $order;
		return $dbgb->getGlobalDb($sql);
	}
	
	
	public  function getCountingCrmSummary($search){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql = "
			SELECT 
				COUNT(DISTINCT crm.`id`) AS totalRecord

				,(SELECT COUNT(DISTINCT COALESCE(s.`crm_id`,0)) FROM `rms_student` AS s WHERE COALESCE(s.`crm_id`,0) >0 AND s.`customer_type`=1 LIMIT 1 ) totalRegistered
				,(SELECT COUNT(DISTINCT COALESCE(s.`crm_id`,0)) FROM `rms_student` AS s WHERE COALESCE(s.`crm_id`,0) >0 AND s.`customer_type`=3 LIMIT 1 ) totalCrm
				,(SELECT COUNT(DISTINCT COALESCE(s.`crm_id`,0)) FROM `rms_student` AS s WHERE COALESCE(s.`crm_id`,0) >0 AND s.`customer_type`=4 LIMIT 1 ) totalTesting

				,COUNT(IF(crm.`crm_status` = 0 AND crm.`followup_status` = 0, crm.`crm_status`, NULL)) AS totalDropedCrm
				,COUNT(IF(crm.`crm_status` = 1 AND crm.`followup_status` = 1, crm.`crm_status`, NULL)) AS totalProcessingCrm
				,COUNT(IF(crm.`crm_status` = 2 AND crm.`followup_status` = 1, crm.`crm_status`, NULL)) AS totalWaitingTest
				,COUNT(IF(crm.`crm_status` = 3 AND crm.`followup_status` = 1, crm.`crm_status`, NULL)) AS totalCompletedCrm
			FROM `rms_crm` AS crm 
			WHERE 1
		";
		$sql.='';
		if(!empty($search["branch_id"])){
			$sql.=' AND crm.`branch_id` = '.$search["branch_id"];
		}
		$sql .= $dbGb->getAccessPermission('crm.`branch_id`');
		return $_db->fetchRow($sql);
	}
	
	public function getAmtCustomerByMonth(){
		$_db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql = "
			SELECT 
				COUNT(crm.`id`) AS customer
				,DATE_FORMAT(crm.`create_date`,'%Y-%m') AS year
			FROM `rms_crm` AS crm 
			WHERE 1
		";
		$sql.='';
		if(!empty($search["branch_id"])){
			$sql.=' AND crm.`branch_id` = '.$search["branch_id"];
		}
		$sql .= $dbGb->getAccessPermission('crm.`branch_id`');
		$sql.=" 
			GROUP BY DATE_FORMAT(crm.`create_date`,'%m-%Y')
			ORDER BY DATE_FORMAT(crm.`create_date`,'%Y-%m') DESC 
			";
		$limit=" LIMIT 10";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $_db->fetchAll($sql.$limit);
	}
	
	function getStudentPayment($search){
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$sql="
			SELECT 
				SUM(pmt.`paid_amount`) AS totalIncome
				,SUM(IF(pmt.`payment_method`=1,pmt.`paid_amount`,0)) AS totalIncomeCash
				,SUM(IF(pmt.`payment_method`=2,pmt.`paid_amount`,0)) AS totalIncomeBank
				,SUM(IF(pmt.`payment_method`=3,pmt.`paid_amount`,0)) AS totalIncomeCheck
				,DATE_FORMAT(pmt.`create_date`,'%\Y-%\m-%\d') AS incomeDate
			FROM `rms_student_payment` AS pmt
			WHERE pmt.`status` =1 
		";
		$date = new DateTime($search["currentDate"]);
		$fullDate = $date->format('Y-m-d');
		
		$sql.=" AND DATE_FORMAT(pmt.`create_date`,'%\Y-%\m-%\d') = DATE_FORMAT('$fullDate','%\Y-%\m-%\d') ";
		
		$userInfo = $dbgb->getUserInfo();
		$userLevel = empty($userInfo["level"]) ? 0 : $userInfo["level"];
		$isSuperUser = empty($userInfo["isSuperUser"]) ? 0 : $userInfo["isSuperUser"];
		if($userLevel!=1){
			if($isSuperUser !=1 ){
				$userId = empty($userInfo["user_id"]) ? 0 : $userInfo["user_id"];
				$sql.=" AND pmt.user_id =".$userId;
			}
		}
		
		$sql.=$dbgb->getAccessPermission('pmt.`branch_id`');
		$sql.=" GROUP BY DATE_FORMAT(pmt.`create_date`,'%Y-%m-%d') ";
		return $dbgb->getGlobalDbRow($sql);
	}
	function getOtherIncome($search){
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$sql="
			SELECT 
				SUM(pmt.`total_amount`) AS totalIncome
				,SUM(IF(pmt.`payment_method`=1,pmt.`total_amount`,0)) AS totalIncomeCash
				,SUM(IF(pmt.`payment_method`=2,pmt.`total_amount`,0)) AS totalIncomeBank
				,SUM(IF(pmt.`payment_method`=3,pmt.`total_amount`,0)) AS totalIncomeCheck
				,DATE_FORMAT(pmt.`date`,'%\Y-%\m-%\d') AS incomeDate
			FROM `ln_income` AS pmt
			WHERE pmt.`status` =1 
		";
		$date = new DateTime($search["currentDate"]);
		$fullDate = $date->format('Y-m-d');
		
		$sql.=" AND DATE_FORMAT(pmt.`date`,'%\Y-%\m-%\d') = DATE_FORMAT('$fullDate','%\Y-%\m-%\d') ";
		
		$userInfo = $dbgb->getUserInfo();
		$userLevel = empty($userInfo["level"]) ? 0 : $userInfo["level"];
		$isSuperUser = empty($userInfo["isSuperUser"]) ? 0 : $userInfo["isSuperUser"];
		if($userLevel!=1){
			if($isSuperUser !=1 ){
				$userId = empty($userInfo["user_id"]) ? 0 : $userInfo["user_id"];
				$sql.=" AND pmt.user_id =".$userId;
			}
		}
		
		$sql.=$dbgb->getAccessPermission('pmt.`branch_id`');
		$sql.=" GROUP BY DATE_FORMAT(pmt.`date`,'%Y-%m-%d') ";
		return $dbgb->getGlobalDbRow($sql);
	}
	function getOtherExpense($search){
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$sql="
			SELECT 
				SUM(exp.`total_amount`) AS totalExpense
				,SUM(IF(exp.`payment_type`=1,exp.`total_amount`,0)) AS totalExpenseCash
				,SUM(IF(exp.`payment_type`=2,exp.`total_amount`,0)) AS totalExpenseBank
				,SUM(IF(exp.`payment_type`=3,exp.`total_amount`,0)) AS totalExpenseCheck
				,DATE_FORMAT(exp.`date`,'%\Y-%\m-%\d') AS expenseDate
			FROM `ln_expense` AS exp
			WHERE exp.`status` =1 
		";
		$date = new DateTime($search["currentDate"]);
		$fullDate = $date->format('Y-m-d');
		
		$sql.=" AND DATE_FORMAT(exp.`date`,'%\Y-%\m-%\d') = DATE_FORMAT('$fullDate','%\Y-%\m-%\d') ";
		
		$userInfo = $dbgb->getUserInfo();
		$userLevel = empty($userInfo["level"]) ? 0 : $userInfo["level"];
		$isSuperUser = empty($userInfo["isSuperUser"]) ? 0 : $userInfo["isSuperUser"];
		if($userLevel!=1){
			if($isSuperUser !=1 ){
				$userId = empty($userInfo["user_id"]) ? 0 : $userInfo["user_id"];
				$sql.=" AND exp.user_id =".$userId;
			}
		}
		
		$sql.=$dbgb->getAccessPermission('exp.`branch_id`');
		$sql.=" GROUP BY DATE_FORMAT(exp.`date`,'%Y-%m-%d') ";
		return $dbgb->getGlobalDbRow($sql);
	}
	function getDailyIncomeExpense($search){
		$date = new DateTime();
		$search["currentDate"] = empty($search["currentDate"]) ? $date : $search["currentDate"];
		$incSt = $this->getStudentPayment($search);
		$incOther =$this->getOtherIncome($search);
		$expOther =$this->getOtherExpense($search);
		
		$totalStInc = empty($incSt["totalIncome"]) ? 0 : $incSt["totalIncome"];
		$totalStIncCash = empty($incSt["totalIncomeCash"]) ? 0 : $incSt["totalIncomeCash"];
		$totalStIncBank = empty($incSt["totalIncomeBank"]) ? 0 : $incSt["totalIncomeBank"];
		$totalStIncCheck = empty($incSt["totalIncomeCheck"]) ? 0 : $incSt["totalIncomeCheck"];
		
		$totalOtherInc = empty($incOther["totalIncome"]) ? 0 : $incOther["totalIncome"];
		$totalOtherIncCash = empty($incOther["totalIncomeCash"]) ? 0 : $incOther["totalIncomeCash"];
		$totalOtherIncBank = empty($incOther["totalIncomeBank"]) ? 0 : $incOther["totalIncomeBank"];
		$totalOtherIncCheck = empty($incOther["totalIncomeCheck"]) ? 0 : $incOther["totalIncomeCheck"];
		
		$totalIncome = $totalStInc+$totalOtherInc;
		$totalIncomeCash = $totalStIncCash+$totalOtherIncCash;
		$totalIncomeBank = $totalStIncBank+$totalOtherIncBank;
		$totalIncomeCheck = $totalStIncCheck+$totalOtherIncCheck;
		
		$totalOtherExp = empty($expOther["totalExpense"]) ? 0 : $expOther["totalExpense"];
		$totalOtherExpCash = empty($expOther["totalExpenseCash"]) ? 0 : $expOther["totalExpenseCash"];
		$totalOtherExpBank = empty($expOther["totalExpenseBank"]) ? 0 : $expOther["totalExpenseBank"];
		$totalOtherExpCheck = empty($expOther["totalExpenseCheck"]) ? 0 : $expOther["totalExpenseCheck"];
		
		
		$totalExpense = $totalOtherExp;
		$totalExpenseCash = $totalOtherExpCash;
		$totalExpenseBank = $totalOtherExpBank;
		$totalExpenseCheck = $totalOtherExpCheck;
		
		$array = array(
			"recordDate" => $search["currentDate"],
			"totalIncome" => $totalIncome,
			"totalIncomeCash" => $totalIncomeCash,
			"totalIncomeBank" => $totalIncomeBank,
			"totalIncomeCheck" => $totalIncomeCheck,
			
			"totalExpense" => $totalExpense,
			"totalExpenseCash" => $totalExpenseCash,
			"totalExpenseBank" => $totalExpenseBank,
			"totalExpenseCheck" => $totalExpenseCheck,
		);
		
		return $array;
		
	}
}
