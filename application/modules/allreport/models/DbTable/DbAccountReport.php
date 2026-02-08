<?php

class Allreport_Model_DbTable_DbAccountReport extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_placement_test';
    public function getUserId(){
    	$_db=new Application_Model_DbTable_DbGlobal();
		$userId = $_db->getUserId();
		$userId = empty($userId) ? 0 : $userId;
    	return $userId;
    }
	
	function getMulitpleSubCategory($parent){
		$db=$this->getAdapter();
		$sql="
		SELECT  
			GROUP_CONCAT(id)
		FROM (SELECT cat.* FROM `rms_items` AS cat
			 ORDER BY cat.parent, cat.id) items_sorted,
			(SELECT @iv := '$parent') initialisation
		WHERE FIND_IN_SET(parent, @iv)
			AND LENGTH(@iv := CONCAT(@iv, ',', id))
		";
		$re = $db->fetchOne($sql);
		if(!empty($re)){
			return $re;
		}
		return null;
	}
	function getMainParentOfItems(){
		$db=$this->getAdapter();
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbGb->currentlang();
		$columnItem = "title_en";
		if($currentLang==1){// khmer
			$columnItem = "title";
		}
			
		$sql="
		SELECT 
			i.`id`
			,COALESCE(NULLIF(i.shortcut,''),i.$columnItem) AS title
			,'' AS listSubId
			,'0' AS totalByMethod
			,'0' AS gTotal
		FROM `rms_items` AS i 
		WHERE 
			i.`is_parent` =1
			AND i.`status` =1
			ORDER BY i.`ordering` ASC,i.$columnItem ASC
		";
		$rs = $db->fetchAll($sql);
		if(!empty($rs)) foreach($rs as $key => $cate){
			$subList = $this->getMulitpleSubCategory($cate["id"]);

			$subListss = empty($subList) ? "" : $subList;
			$subListss = empty($subList) ? $cate["id"] : $subList.",".$cate["id"];
			$rs[$key]["listSubId"] = $subListss;
			
		}
		return $rs;
	}
	
	function getCurrentPayemntDetailByParent($data){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		
		$titleCol = "title_en";
		$labelView = "name_en";
		if ($currentLang == 1) {
			$titleCol = "title";
			$labelView = "name_kh";
		}
		
		$paymentId = empty($data["paymentId"]) ? 0 : $data["paymentId"];
		$subList = empty($data["listSubId"]) ? 0 : $data["listSubId"];
		//--,(SELECT v.$labelView FROM rms_view  AS v WHERE v.type=6 AND v.key_code=spmtd.payment_term LIMIT 1) AS payTerm
		$sql="
			SELECT 
			spmtd.`service_type`
			,spmtd.`itemdetail_id`
			,itd.$titleCol AS itemsName
			,COALESCE(itd.`items_id`,0) AS categoryId
			,SUM(spmtd.`fee`) AS totalFee
			,SUM(spmtd.`subtotal`) AS gSubTotalFee
			,MAX(spmtd.`discount_percent`) AS totalDiscountPercent
			,SUM(spmtd.`discount_amount`) AS totalDiscountAmt
			,SUM(spmtd.`total_discount`) AS gTotalDiscount
			,SUM(spmtd.`extra_fee`) AS totalExtraFee
			,spmtd.`extra_fee`
			,SUM(spmtd.`totalpayment`) AS gTotalPayment
			,(SELECT ss.title FROM `rms_startdate_enddate` ss WHERE ss.id=Max(spmtd.academicFeeTermId) LIMIT 1) AS payTerm


			FROM `rms_student_paymentdetail` AS spmtd 
		LEFT JOIN `rms_itemsdetail` AS itd ON itd.id = spmtd.`itemdetail_id`
		WHERE 1
			AND spmtd.`payment_id` = $paymentId
			AND COALESCE(itd.`items_id`,0) IN ($subList)
		";
		$rs = $db->fetchRow($sql);
		return $rs;
	}
	function getPaymentInfoParent($data){
		$parentCol = empty($data["parentCol"]) ? 0 : $data["parentCol"];
		$paymentId = empty($data["paymentId"]) ? 0 : $data["paymentId"];
		
		if(!empty($parentCol)) foreach($parentCol as $key => $rs){
			$gTotalPayment =0;
			if(!empty($rs["listSubId"])){
				$array = array(
					"paymentId" =>$paymentId
					,"listSubId" =>$rs["listSubId"]
				);
				$result = $this->getCurrentPayemntDetailByParent($array);
				if(!empty($result)){
					$gTotalPayment = empty($result["gTotalPayment"]) ? 0 : $result["gTotalPayment"];
					$parentCol[$key]["paymentInfo"] = $result;
					
				}else{
					$parentCol[$key]["paymentInfo"] = array();
				}
			}else{
				$parentCol[$key]["paymentInfo"] = array();
			}
		}
		return $parentCol;
		
	}
	function getStudentPaymentDaily($search=null){
		try{
			$dbGb = new Application_Model_DbTable_DbGlobal();
			$currentLang = $dbGb->currentlang();
			$branch_id = $dbGb->getAccessPermission('spt.`branchId`');
			$branch = $dbGb->getBranchDisplay();
			
			$labelFull = $dbGb->getViewLabelDisplay();
			$labelShort = $dbGb->getViewLabelDisplay("shortcut");
			
			$columnItem = 'title_en';
			if ($currentLang == 1) {
				$columnItem = 'title';
			}
			
			$db=$this->getAdapter();
			$fromDate =(empty($search['start_date']))? '1': " DATE_FORMAT(spt.`createDate`, '%Y-%m-%d %H:%i:%s') >= '".$search['start_date']." 00:00:00'";
			$toDate = (empty($search['end_date']))? '1': " DATE_FORMAT(spt.`createDate`, '%Y-%m-%d %H:%i:%s') <= '".$search['end_date']." 23:59:59'";
			$sql=" SELECT
						(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = spt.`branchId` LIMIT 1) AS branchName
						
						,spt.`studentCode` AS studentCode
						,spt.`studentNameKh` AS studentNameKh
						,spt.`studentNameEn`
						
						,(SELECT v.$labelFull FROM `rms_view` AS v WHERE v.type =8 AND v.key_code = spt.`pmtMethod` LIMIT 1) AS paymentMethodTitle
						,COALESCE((SELECT b.`bank_name` FROM `rms_bank` AS b WHERE b.id = spt.`bankId` LIMIT 1),'') AS bankName
						,fam.`laonNumber`
						,fam.`street`
						,fam.`houseNo`
						
						,(SELECT v.$labelShort FROM `rms_view` AS v WHERE v.type =41 AND v.key_code = fam.`familyType` LIMIT 1) AS familyTypeTitle
						,(SELECT COALESCE(i.shortcut,i.$columnItem) FROM `rms_items` AS i WHERE i.type=1 AND i.id = spt.degree LIMIT 1) AS degreeTitle
						,(SELECT COALESCE(itd.shortcut,itd.$columnItem) FROM `rms_itemsdetail` AS itd WHERE itd.`items_type`=1 AND itd.id = spt.grade LIMIT 1) AS gradeTitle
						,(SELECT u.first_name FROM rms_users AS u WHERE u.id = spt.userId LIMIT 1) AS byUserName
						
						,spt.paymentId
						,spt.`receptNo`
						,spt.`createDate`
						,spt.`creditMemo`
						,spt.`grandTotal`
						,spt.`penalty`
						,spt.`paidAmt`
						,spt.`balanceDue`
						,spt.`pmtMethod`
						,spt.`bankId`
						,spt.number as transactionNote
						,spt.note
						,spt.`isVoid`
						,spt.`pmtDetailJson`
				  FROM
						`v_stupmt_ft_detail_info` AS spt 
							LEFT JOIN `rms_family` AS fam ON fam.`id` = spt.`familyId`
				  WHERE 1
						
						$branch_id ";
	
			$sql.= " AND ".$fromDate." AND ".$toDate;
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[] = " REPLACE(spt.`studentCode`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(spt.`studentNameKh`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(spt.`studentNameEn`,' ','') LIKE '%{$s_search}%'";
				
				$s_where[] = " REPLACE(fam.`laonNumber`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(fam.`street`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(fam.`houseNo`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(spt.receptNo,' ','') LIKE '%{$s_search}%'";
				$sql.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['branch_id'])){
				$sql.= " AND spt.branchId = ".$search['branch_id'];
			}
			if(!empty($search['userId'])){
					$sql.= " AND spt.userId = ".$search['userId'];
			}
			if(!empty($search['degree'])){
				$sql.= " AND spt.degree = ".$search['degree'];
			}
			if(!empty($search['grade_all'])){
				$sql.= " AND spt.grade = ".$search['grade_all'];
			}
			if(!empty($search['stu_name'])){
				$sql.= " AND spt.studentId = ".$search['stu_name'];
			}
			if(!empty($search['receiptStatus'])){
				if($search['receiptStatus']==1){
					$sql.= " AND spt.isVoid = 0 ";
				}else if($search['receiptStatus']==2){
					$sql.= " AND spt.isVoid = 1 ";
				}
			}
	
			$order=" ORDER BY spt.`pmtMethod` DESC,spt.`bankId` ASC,spt.paymentId DESC ";
			if($search['receipt_order']==0){
				$order=" ORDER BY spt.`pmtMethod` DESC,spt.`bankId` ASC,spt.paymentId ASC ";
			}
			return $db->fetchAll($sql.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	}
	
	function getIncomeDailyTotal($search=null){
		try{
			$dbGb = new Application_Model_DbTable_DbGlobal();
			$currentLang = $dbGb->currentlang();
			$branch_id = $dbGb->getAccessPermission('spt.`branchId`');
			$branch = $dbGb->getBranchDisplay();
			
			$labelFull = $dbGb->getViewLabelDisplay();
			$labelShort = $dbGb->getViewLabelDisplay("shortcut");
			
			$columnItem = 'title_en';
			if ($currentLang == 1) {
				$columnItem = 'title';
			}
			
			$db=$this->getAdapter();
			$fromDate =(empty($search['start_date']))? '1': " DATE_FORMAT(spt.`createDateFmt`, '%Y-%m-%d %H:%i:%s') >= '".$search['start_date']." 00:00:00'";
			$toDate = (empty($search['end_date']))? '1': " DATE_FORMAT(spt.`createDateFmt`, '%Y-%m-%d %H:%i:%s') <= '".$search['end_date']." 23:59:59'";
			$sql=" SELECT
						(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = spt.`branchId` LIMIT 1) AS branchName
						
						,(SELECT v.$labelFull FROM `rms_view` AS v WHERE v.type =8 AND v.key_code = spt.`pmtMethod` LIMIT 1) AS paymentMethodTitle
						,COALESCE((SELECT b.`bank_name` FROM `rms_bank` AS b WHERE b.id = spt.`bankId` LIMIT 1),'N/A') AS bankName
						
						,spt.*
				  FROM
						`v_income_daily_total` AS spt 
				  WHERE 1
						
						$branch_id ";
	
			$sql.= " AND ".$fromDate." AND ".$toDate;
			$where = "";
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[] = " REPLACE(spt.`branchId`,' ','') LIKE '%{$s_search}%'";
				
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['branch_id'])){
				$sql.= " AND spt.branchId = ".$search['branch_id'];
			}
	
			$order=" ORDER BY spt.`pmtMethod` DESC,spt.`bankId` ASC";
			
			
			return $db->fetchAll($sql.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	}
	
	public function getCreditTransaction($search = []){
		try{
			
			$dbGb = new Application_Model_DbTable_DbGlobal();
			$currentLang = $dbGb->currentlang();
			$branch_id = $dbGb->getAccessPermission('cr.branch_id');
			$branch = $dbGb->getBranchDisplay();
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$db=$this->getAdapter();
			
			$whereExtra = "";
			$strDate = "cr.`date`";
			if(!empty($search['by_date'])){
				$by_date = $search['by_date'];
				if($by_date==1){
					$strDate = "cr.`date`";
				}else if($by_date==2){
					$strDate = "cr.`end_date`";
					
					$whereExtra = " AND cr.creditType = 2 ";
				}
				
			}
			$fromDate =(empty($search['start_date']))? '1': " DATE_FORMAT($strDate, '%Y-%m-%d %H:%i:%s') >= '".$search['start_date']." 00:00:00'";
			$toDate = (empty($search['end_date']))? '1': " DATE_FORMAT($strDate, '%Y-%m-%d %H:%i:%s') <= '".$search['end_date']." 23:59:59'";
			
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
						,tF.stu_code AS fromStudentCode 
						,tF.stu_khname AS fromStudentNameKh 
						,CONCAT(COALESCE(tF.last_name,''),' ',COALESCE(tF.stu_enname,'')) AS fromStudentNameEng
						,(SELECT first_name FROM `rms_users` WHERE id=cr.user_id LIMIT 1) AS user_name
						,ref.transaction_no AS refTransaction
						,ref.date as refDate
						,stp.receipt_number as receiptNo
						
						,CASE 
							WHEN  (cr.type =0 AND cr.cashType =1 AND cr.fromTransactionId >0) THEN '".$tr->translate('DEPOSIT_CREDIT')."' 
							WHEN  (cr.type =0 AND cr.cashType =2 AND cr.fromTransactionId >0) THEN '".$tr->translate('TRAN_TO')."'
							WHEN  (cr.type =1 AND cr.cashType =1 AND COALESCE(cr.`fromStuId`,0) >0) THEN '".$tr->translate('RECEIVE_FROM')."'
							WHEN  (cr.type =2 AND cr.cashType =2) THEN '".$tr->translate('RECIEPT_PAYMENT')."'
							WHEN  (cr.type =2 AND cr.cashType =1) THEN '".$tr->translate('VODI_RECIEPT_PAYMENT')."'
							ELSE '".$tr->translate('CREDIT_REMAIN')."' END 
						AS refTitle
						,CASE 
							WHEN  (cr.type =0 AND cr.cashType =1 AND cr.fromTransactionId >0) THEN CONCAT('ref #',COALESCE(inc.`invoice`,''))
							WHEN  (cr.type =0 AND cr.cashType =2 AND cr.fromTransactionId >0) THEN CONCAT('ref #',COALESCE(ref.`transaction_no`,''))
							WHEN  (cr.type =1 AND cr.cashType =1 AND COALESCE(cr.`fromStuId`,0) >0) THEN CONCAT('ref #',COALESCE(tF.stu_code,''))
							WHEN  (cr.type =2 AND cr.cashType =2) THEN CONCAT('ref #',COALESCE(stp.receipt_number,''))
							WHEN  (cr.type =2 AND cr.cashType =1) THEN CONCAT('ref #',COALESCE(stp.receipt_number,''))
							ELSE '' END 
						AS refNo
				
				FROM `rms_creditmemo` AS cr 
						JOIN `rms_student` AS s ON s.stu_id = cr.student_id 
						LEFT JOIN `rms_student` AS tF ON tF.stu_id = cr.`fromStuId`
						LEFT JOIN ln_income AS inc ON cr.`type`=0 AND cr.`cashType` =1 AND inc.id = cr.`fromTransactionId` 
						LEFT JOIN `rms_creditmemo` AS ref ON ref.id = cr.`fromTransactionId` 
						LEFT JOIN rms_student_payment AS stp ON stp.id = cr.fromTransactionId AND cr.type = 2
				  WHERE cr.status=1 
						
						$branch_id ";
	
			$sql.= " AND ".$fromDate." AND ".$toDate;
			$sql.= $whereExtra;
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[] = " REPLACE(cr.`transaction_no`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(cr.`prob`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(cr.`note`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(cr.`total_amount`,' ','') LIKE '%{$s_search}%'";
				
				$s_where[] = " REPLACE(s.stu_code,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(s.stu_khname,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')),' ','') LIKE '%{$s_search}%'";
				
				$s_where[] = " REPLACE(tF.stu_code,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(tF.stu_khname,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(CONCAT(COALESCE(tF.last_name,''),' ',COALESCE(tF.stu_enname,'')),' ','') LIKE '%{$s_search}%'";
				
				
				$sql.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['branch_id'])){
				$sql.= " AND cr.branch_id = ".$search['branch_id'];
			}
			if(!empty($search['cashType'])){
				$sql.= " AND cr.cashType = ".$search['cashType'];
			}
			if(!empty($search['creditType'])){
				$sql.= " AND cr.creditType = ".$search['creditType'];
			}
			if(!empty($search['crOpertationType'])){
				if($search['crOpertationType']==1){
					$sql.= " AND (cr.type =0 AND cr.cashType =1 AND cr.fromTransactionId >0) ";
				}else if($search['crOpertationType']==2){
					$sql.= " AND (cr.type =0 AND cr.cashType =1 AND cr.fromTransactionId =0) ";
				}else if($search['crOpertationType']==3){
					$sql.= " AND (cr.type =0 AND cr.cashType =2 AND cr.fromTransactionId >0) ";
				}else if($search['crOpertationType']==4){
					$sql.= " AND (cr.type =1 AND cr.cashType =1 AND COALESCE(cr.`fromStuId`,0) >0) ";
				}else if($search['crOpertationType']==5){
					$sql.= " AND (cr.type =2 AND cr.cashType =2) ";
				}else if($search['crOpertationType']==6){
					$sql.= " AND (cr.type =2 AND cr.cashType =1) ";
				}
			}
    		$sql.=$dbGb->getAccessPermission('cr.branch_id');

			$order=" ORDER BY cr.id DESC ";
			
			
			return $db->fetchAll($sql.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	}
	
	public function getCreditTransactionExpired($search = []){
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
						AND cr.`cashType` = 1
						AND cr.`creditType` = 2
						AND cr.`isExpired` = 0
						$branch_id ";
			
			$toDate = (empty($search['end_date']))? '1': " DATE_FORMAT(cr.`end_date`, '%Y-%m-%d %H:%i:%s') <= '".$search['end_date']." 23:59:59'";
			$sql.= "  AND ".$toDate;
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[] = " REPLACE(cr.`transaction_no`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(cr.`prob`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(cr.`note`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(cr.`total_amount`,' ','') LIKE '%{$s_search}%'";
				
				$s_where[] = " REPLACE(s.stu_code,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(s.stu_khname,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')),' ','') LIKE '%{$s_search}%'";
				
				$s_where[] = " REPLACE(ss.stu_code,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(ss.stu_khname,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(CONCAT(COALESCE(ss.last_name,''),' ',COALESCE(ss.stu_enname,'')),' ','') LIKE '%{$s_search}%'";
				
				
				$sql.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['branch_id'])){
				$sql.= " AND cr.branchId = ".$search['branch_id'];
			}
			$order=" ORDER BY cr.id DESC ";
			
			
			return $db->fetchAll($sql.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	}
	
	function getReferralReport($search = []){
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbGb->currentlang();
		$branch = $dbGb->getBranchDisplay();
		
		$sql="
			SELECT 
				rfm.`id` AS detailId
				,(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id=rfm.`branchId` LIMIT 1) AS branchName
				,(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id=rfs.`branchId` LIMIT 1) AS refByBrName
				,rfm.`branchId`
				,rfm.`payment_id` AS pmtId
				,rfm.`receiptNo` AS refFromReceiptNo
				,rfm.`create_date` AS refFromCreateDate
				,rfm.`refFromStuCode`
				,rfm.`refFromNameKh`
				,rfm.`refFromNameEn`
				,rfm.`refToStuCode`
				,rfm.`refToNameEn`
				,rfm.`refToNameKh`
				,rfs.`id` AS rfsDetailId
				,rfm.`title`
				,rfs.`receiptNo` AS refToReceiptNo
				,rfs.`create_date` AS refToCreateDate
				
				,COALESCE((SELECT tfd.`tuition_fee` FROM `rms_tuitionfee` AS tf JOIN `rms_tuitionfee_detail` AS tfd ON tf.`type`=2 AND tf.id = tfd.`fee_id` WHERE tfd.`class_id` = rfm.`itemdetail_id` AND tf.`academic_year` =rfm.`academicYear` AND tfd.`payment_term`=5 LIMIT 1),0) AS refToFee
				,COALESCE((SELECT tfd.`tuition_fee` FROM `rms_tuitionfee` AS tf JOIN `rms_tuitionfee_detail` AS tfd ON tf.`type`=2 AND tf.id = tfd.`fee_id` WHERE tfd.`class_id` = rfs.`itemdetail_id` AND tf.`academic_year` =rfs.`academicYear` AND tfd.`payment_term`=5 LIMIT 1),0) AS refByFee

		";
		$bracSub="";
		//$bracSub= $dbGb->getAccessPermission('rfs.`branchId`');
		$sql.="
			FROM `v_referred_main` AS rfm 
				LEFT JOIN `v_referred_sub` AS rfs ON rfm.`refToStuId` = rfs.`refToStuId` AND rfm.`refFroStuId` = rfs.`refFroStuId` $bracSub
			WHERE 1
		";
		
		$strDate = "rfm.`create_date`";
		if(!empty($search['by_date'])){
			$strDate = "rfs.`create_date`";
		}
		$fromDate =(empty($search['start_date']))? '1': " DATE_FORMAT($strDate, '%Y-%m-%d %H:%i:%s') >= '".$search['start_date']." 00:00:00'";
		$toDate = (empty($search['end_date']))? '1': " DATE_FORMAT($strDate, '%Y-%m-%d %H:%i:%s') <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND ".$fromDate." AND ".$toDate;
		
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " REPLACE(rfm.`receiptNo`,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(rfm.`refFromStuCode`,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(rfm.`refFromNameKh`,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(rfm.`refFromNameEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(rfm.`refToStuCode`,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(rfm.`refToNameEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(rfm.`refToNameKh`,' ','') LIKE '%{$s_search}%'";
			
			$s_where[] = " REPLACE(rfs.`receiptNo`,' ','') LIKE '%{$s_search}%'";
			
			
			
			$sql.=' AND ('.implode(' OR ', $s_where).')';
		}
			
		if(!empty($search['branch_id'])){
			$sql.= " AND rfm.branchId = ".$search['branch_id'];
		}
		$sql.= $dbGb->getAccessPermission('rfm.`branchId`');
		$order=" ORDER BY rfm.payment_id DESC ";
		
		$db=$this->getAdapter();
		return $db->fetchAll($sql.$order);
	}
	
	function getSubReferral($search = []){
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbGb->currentlang();
		$branch = $dbGb->getBranchDisplay();
		
		$sql="
			SELECT 
				rfs.`id` AS rfsDetailId
				,(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id=rfs.`branchId` LIMIT 1) AS branchName
				,rfs.`branchId`
				,rfs.`payment_id` AS pmtId
				,rfs.`receiptNo` AS refToReceiptNo
				,rfs.`create_date` AS refToCreateDate
				,rfs.`refFromStuCode`
				,rfs.`refToStuCode`
				,rfs.`refToNameEn`
				,rfs.`refToNameKh`
				,rfm.`receiptNo` AS refFromReceiptNo
				,rfm.`create_date` AS refFromCreateDate
				,COALESCE((SELECT tfd.`tuition_fee` FROM `rms_tuitionfee` AS tf JOIN `rms_tuitionfee_detail` AS tfd ON tf.`type`=2 AND tf.id = tfd.`fee_id` WHERE tfd.`class_id` = rfs.`itemdetail_id` AND tf.`academic_year` =rfs.`academicYear` AND tfd.`payment_term`=5 LIMIT 1),0) AS refByFee

		";
		$sql.="
			FROM `v_referred_sub` AS rfs  
				LEFT JOIN `v_referred_main` AS rfm ON rfm.`refToStuId` = rfs.`refToStuId` AND rfm.`refFroStuId` = rfs.`refFroStuId`
			WHERE 1 
			AND rfm.`refFroStuId` IS NULL
		";
		
		$strDate = "rfs.`create_date`";
		$fromDate =(empty($search['start_date']))? '1': " DATE_FORMAT($strDate, '%Y-%m-%d %H:%i:%s') >= '".$search['start_date']." 00:00:00'";
		$toDate = (empty($search['end_date']))? '1': " DATE_FORMAT($strDate, '%Y-%m-%d %H:%i:%s') <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND ".$fromDate." AND ".$toDate;
		
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " REPLACE(rfs.`receiptNo`,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(rfs.`refFromStuCode`,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(rfs.`refToStuCode`,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(rfs.`refToNameEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(rfs.`refToNameKh`,' ','') LIKE '%{$s_search}%'";
			
			$s_where[] = " REPLACE(rfm.`receiptNo`,' ','') LIKE '%{$s_search}%'";
			
			
			
			$sql.=' AND ('.implode(' OR ', $s_where).')';
		}
			
		if(!empty($search['branch_id'])){
			$sql.= " AND rfs.branchId = ".$search['branch_id'];
		}
		$sql.= $dbGb->getAccessPermission('rfm.`branchId`');
		$order=" ORDER BY rfs.payment_id DESC ";
		
		$db=$this->getAdapter();
		return $db->fetchAll($sql.$order);
	}
	
	function updateReferred($data){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
			try{
				if(!empty($data['detailId']) && !empty($data['referedNote'])){
					$detailId = $data['detailId'];
					$this->_name='rms_student_paymentdetail';
						$arrRefferred=array(
								'note'=>$data['referedNote'],
						);
					$where = " id = ".$detailId;
					$this->update($arrRefferred, $where);
				}
				$db->commit();
				return 1;
			}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				$db->rollBack();
			}
	}
    
}
   
    
   