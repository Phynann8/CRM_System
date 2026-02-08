<?php

class Application_Model_DbTable_DbNotification extends Zend_Db_Table_Abstract
{
    // set name value
	public function setName($name){
		$this->_name=$name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	function getStuDocumentAlert($condiction=array()){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$branchCol = $dbgb->getBranchDisplay();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql ="
			SELECT 
				s.branch_id
				,(SELECT CONCAT(b.$branchCol) FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name
				,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_logo
				,s.stu_code
				,s.stu_khname
				,s.stu_enname
				,s.last_name
				,s.photo
				,s.sex
				,s.tel
				
				,s.stu_code AS studentCode
				,s.stu_khname AS studentNameKh
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng
				,sd.*
			FROM `rms_student_document` AS sd
				,`rms_student` AS s
			WHERE s.stu_id = sd.stu_id
				AND sd.is_receive=0
		";
		$where ='';
		$to_date = (empty($end_date))? '1': " sd.date_end <= '".$end_date." 23:59:59'";
		$where.= " AND ".$to_date;
		
		$where.=$dbgb->getAccessPermission("s.branch_id");
		$order=" ORDER BY sd.date_end DESC, sd.stu_id ASC";
		$limit=" LIMIT 20";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $db->fetchAll($sql.$where.$order.$limit);
	}
	function getTeachDocumentAlert($condiction=array()){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$branchCol = $dbgb->getBranchDisplay();
		$viewCol = $dbgb->getViewLabelDisplay();
		
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql =" 
		SELECT 
			s.branch_id
			,(SELECT CONCAT(b.$branchCol) FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name
			,(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_logo
			
			,(SELECT v.$viewCol FROM rms_view AS v WHERE v.type=2 AND v.key_code=s.sex LIMIT 1) AS sex
			,(SELECT v.$viewCol FROM rms_view AS v WHERE v.type=24 AND v.key_code=s.teacher_type LIMIT 1) AS teacher_type
			
			,s.teacher_code
			,s.teacher_name_kh
			,s.tel
			,s.email
			,s.photo
			,sd.*
		FROM 
			`rms_teacher_document` AS sd, 
			`rms_teacher` AS s
		WHERE s.id = sd.stu_id
			AND sd.is_receive=0
		";
		$where ='';
		$to_date = (empty($end_date))? '1': " sd.date_end <= '".$end_date." 23:59:59'";
		$where.= " AND ".$to_date;
		$where.=$dbgb->getAccessPermission("s.branch_id");
		$order=" ORDER BY sd.date_end DESC, sd.stu_id ASC";
		$limit=" LIMIT 20";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $db->fetchAll($sql.$where.$order.$limit);
	}
	
	function getStuProductAlert($condiction=array()){
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$new = empty($condiction['new']) ? null : $condiction['new'];
		$db = $this->getAdapter();
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql ="
			SELECT 
				(SELECT CONCAT(b.$branchCol) FROM rms_branch AS b WHERE b.br_id=sp.branch_id LIMIT 1) AS branch_name
				,(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=sp.branch_id LIMIT 1) AS branch_logo
				,sp.student_id as stu_id
				
				,(SELECT ie.$colunmname FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS items_name
				,(SELECT ie.images FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS pro_images
				,spd.*
				,sp.branch_id
				,sp.receipt_number
				
				,s.tel AS tel
				,s.stu_code AS studentCode
				,s.stu_khname AS studentNameKh
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng
			
			";
			
			$fromStatment = "
				FROM 
					`rms_student_payment` AS sp  JOIN `rms_student_paymentdetail` AS spd ON spd.payment_id = sp.id 
					LEFT JOIN `rms_student` AS s ON s.stu_id = sp.student_id
				WHERE 1
					AND (SELECT ie.items_type FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) =3
					AND sp.is_void=0
					AND spd.qty_balance >0
			";
		$where ='';
		$order=" ORDER BY sp.id DESC ";
		if (!empty($new)){
			$sql.="
				,'' AS remide_date
			";
// 			$where = " AND spd.id NOT IN (SELECT ctd.student_paymentdetail_id FROM `rms_cutstock_detail` AS ctd ) ";
			$where = " AND spd.qty_balance = spd.qty ";
			$order = " ORDER BY sp.id DESC";
		}else{
			/*$sql.="
				,(SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) AS remide_date
			";
			$to_date = (empty($end_date))? '1': " (SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) <= '".$end_date." 23:59:59'";
			$where.= " AND ".$to_date;
			$order=" ORDER BY (SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) DESC ";
			*/
		}
		
		$sql.=$fromStatment;
		
		$where.=$dbgb->getAccessPermission("sp.branch_id");
		$limit=" LIMIT 10";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $db->fetchAll($sql.$where.$order.$limit);
	}
	
	function getStudentNotYetGroup($condiction=array()){
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$branchCol = $dbgb->getBranchDisplay();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$db = $this->getAdapter();
		$sql="SELECT
				br.$branchCol AS branch_name
				,br.photo AS branch_logo
				,it.$colunmname AS grade_title
			    ,i.$colunmname AS degree_title
				,s.stu_id
				,s.stu_code AS studentCode
				,s.stu_khname AS studentNameKh
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng
			FROM 
				rms_group_detail_student AS gd 
				JOIN `rms_student` AS s ON s.stu_id = gd.stu_id 
				LEFT JOIN rms_branch AS br ON br.br_id = s.branch_id
				LEFT JOIN rms_itemsdetail it ON it.id=gd.grade
				LEFT JOIN rms_items i ON i.id=gd.degree AND i.type=1
			WHERE 
				gd.itemType=1 AND
				s.customer_type =1
				AND s.status=1
				AND gd.is_current=1
				AND gd.stop_type=0
				AND gd.is_setgroup=0
				AND COALESCE(gd.group_id,0)=0 ";
		
		$sql.=$dbgb->getAccessPermission("s.branch_id");
		$sql.="ORDER BY s.branch_id,s.stu_code DESC ";
		$limit="";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $db->fetchAll($sql.$limit);
	}
	
	function getCrmNextContactNoti($condiction=array()){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$branchCol = $dbgb->getBranchDisplay();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		
		if(!empty($condiction['currentDate'])){
			$end_date = date('Y-m-d',strtotime(" +".$condiction['currentDate']." day"));
		}
		$processTitle = $tr->translate("PROGRESSING");
		$processTitleWait = $tr->translate("WAITING_TEST");
		$processTitleComplete = $tr->translate("COMPLETED");	

		$sql ="
			SELECT 
				b.$branchCol AS branch_name
				,crm.kh_name
				,crm.first_name
				,crm.last_name
				,crm.kh_name AS nameKh
				,CONCAT(COALESCE(crm.last_name,''),' ',COALESCE(crm.first_name,'')) AS nameEng
				,crm.tel
				,crm.reason
				,crh.crm_id
				,crh.proccess
				,crh.feedback
				,crh.next_contact
				,CASE    
					WHEN  crh.proccess = 1 THEN '".$processTitle."'
					WHEN  crh.proccess = 2 THEN '".$processTitleWait."'
					WHEN  crh.proccess = 3 THEN '".$processTitleComplete."'
					ELSE ''
				END AS proccessTitle
				
			FROM `rms_crm_history_contact` AS crh
				JOIN `rms_crm` AS crm
				LEFT JOIN `rms_branch` AS b ON b.br_id = crm.branch_id
			WHERE crm.id = crh.crm_id
				AND crh.proccess != 3
				AND crm.followup_status=1
			
		";
		$to_date = (empty($end_date))? '1': " crh.next_contact <= '".$end_date." 23:59:59'";
		$sql.= " AND ".$to_date;
		
		$sql.=$dbgb->getAccessPermission("crm.branch_id");
		
		$sql.=" GROUP BY crh.crm_id
			ORDER BY crh.next_contact DESC ,crh.id DESC";
		$limit=" LIMIT 20";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		
		return $db->fetchAll($sql.$limit);
	}
	
	function getCreditMemoNearExpired($condiction=array()){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$branchCol = $dbgb->getBranchDisplay();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$day = 7;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql ="
		SELECT 
			(SELECT CONCAT(b.$branchCol) FROM rms_branch AS b WHERE b.br_id=cr.branch_id LIMIT 1) AS branch_name
			,(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=cr.branch_id LIMIT 1) AS branch_logo
			,cr.* 
			
			,s.tel AS tel
			,s.stu_code AS stu_code
			,s.stu_code AS studentCode
			,s.stu_khname AS studentNameKh
			,s.last_name AS last_name
			,s.stu_enname AS stu_enname
			,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng
		FROM `rms_creditmemo` AS cr 
			JOIN `rms_student` AS s ON s.stu_id = cr.student_id
		WHERE cr.status=1 
			AND cr.`cashType` = 1
			AND cr.`creditType` = 2
			AND cr.`isExpired` = 0
		";
		$to_date = (empty($end_date))? '1': " cr.end_date <= '".$end_date." 23:59:59'";
		$sql.= " AND ".$to_date;
		
		$sql.=$dbgb->getAccessPermission("cr.branch_id");
		
		$sql.=" ORDER BY cr.end_date DESC ,cr.id DESC";
		$limit=" LIMIT 20";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $db->fetchAll($sql.$limit);
	}
}
?>