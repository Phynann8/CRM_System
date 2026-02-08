<?php

class Accounting_Model_DbTable_DbCreditDash extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_creditmemo';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
	function getCountingCreditMemo($search){
		$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="
			SELECT 
				cr.`id`
				,SUM(cr.`total_amountafter`) AS totalRemain
				,COUNT(cr.`id`) AS totalRecord
			FROM  `rms_creditmemo` AS cr
			WHERE 1 AND cr.`status` = 1 
			AND cr.`total_amountafter` >0 
		";
		if(!empty($search['creditType'])){
			$date = new DateTime();
			$today =  $date->format("Y-m-d");
			if($search['creditType'] ==1){
				$sql.= " AND cr.`end_date` >= '$today'";
			}else { //expired
				$sql.= " AND cr.`end_date` < '$today'";
			}
    	}
		$dbgb = new Application_Model_DbTable_DbGlobal();
		return $dbgb->getGlobalDbRow($sql);
	}
	
	function getCountingDisSetting($search){
		$db = $this->getAdapter();
    	$dbGb=new Application_Model_DbTable_DbGlobal();
		$sql="
			SELECT 
				COUNT(p.`id`) AS `value`
			FROM  `rms_dis_setting` AS p 
			WHERE 1 
		";
		if(!empty($search['status'])){
			$status = $search['status'];
			if($search['status'] ==2){
				$status =0;
			}
    		$sql.= " AND p.status  = ".$status;
    	}
		if(!empty($search['academic_year'])){
			$sql.= " AND p.academicYear = ".$search['academic_year'];
		}
		$sql.=$dbGb->getAccessPermission('p.branchId');
		return $dbGb->getGlobalDbOne($sql);
	}
	
	function getCountingDisType($search){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				COUNT(p.`disco_id`) AS `value`
			FROM  `rms_discount` AS p 
			WHERE 1 
		";
		if(!empty($search['status'])){
			$status = $search['status'];
			if($search['status'] ==2){
				$status =0;
			}
    		$sql.= " AND p.status  = ".$status;
    	}
		$dbgb = new Application_Model_DbTable_DbGlobal();
		return $dbgb->getGlobalDbOne($sql);
	}
	
    function getAllCreditmemo($search=null){
		$db = $this->getAdapter();
		$dbGb=new Application_Model_DbTable_DbGlobal();
		$branch= $dbGb->getBranchDisplay();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="
			SELECT 
				c.*
				,c.date AS startDate
				,c.end_date AS endDate
				,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id = c.branch_id LIMIT 1) AS branchName
				,s.stu_code AS stuCode
				,s.stu_khname AS studentNameKh
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng
				,CASE 
					WHEN c.creditType = 1 THEN '".$tr->translate("LIFE_TIME")."'
					ELSE c.end_date 
				END creditTypeTitle
				,COALESCE(c.otherincome_id,0) AS otherIncomeId
				,COALESCE(inc.invoice,'') AS incInvoice
				,(SELECT v.name_kh FROM rms_view AS v WHERE v.type=23 AND v.key_code=c.type LIMIT 1) AS paidTransfer
				,(SELECT u.first_name FROM `rms_users` AS u WHERE u.id=c.user_id LIMIT 1) AS userName
				,c.status AS statusValue
				,ss.stu_code AS fromStuCode
				,ss.stu_khname AS fromStudentNameKh
				,CONCAT(COALESCE(ss.last_name,''),' ',COALESCE(ss.stu_enname,'')) AS fromStudentNameEng
				
		";
		$sql.=$dbGb->caseStatusShowImage("c.status");		
		$sql.=" FROM 
					rms_creditmemo c JOIN rms_student AS s ON s.stu_id = c.student_id 
					LEFT JOIN ln_income AS inc ON inc.id = c.otherincome_id
					LEFT JOIN rms_student AS ss ON ss.stu_id = c.fromStuId
				WHERE 1	
			";
			$sql.= " AND c.cashType = 1 ";
		$type = empty($search['typeRecord']) ? 0 : $search['typeRecord'];
		$sql.= " AND  c.type = ".$type;// 0=issue , 1=transfer
	
		
		if(!empty($search['branch_id'])){
			$sql.= " AND c.branch_id = ".$search['branch_id'];
		}
		$sql.=$dbGb->getAccessPermission('c.branch_id');
		$order=" ORDER BY c.id DESC";
		$limit = 10;
		if(!empty($search['limitRecord'])){
			$limit = $search['limitRecord'];
		}
		$order.= " LIMIT ".$limit;
		return $db->fetchAll($sql.$order);
	}
	
	/*
	function getAllTransferCredit($search=null){
		$db = $this->getAdapter();
		$dbGb=new Application_Model_DbTable_DbGlobal();
		$branch= $dbGb->getBranchDisplay();
		$sql="SELECT 
				tr.*
				,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id = tr.branch_id LIMIT 1) AS branchName	
				,s.stu_code AS stuCode
				,s.stu_khname AS studentNameKh
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng
				
				,frS.stu_code AS fromStuCode
				,frS.stu_khname AS fromStudentNameKh
				,CONCAT(COALESCE(frS.last_name,''),' ',COALESCE(frS.stu_enname,'')) AS fromStudentNameEng
				,(SELECT u.first_name FROM `rms_users`  AS u WHERE u.id=tr.user_id LIMIT 1) AS userName
			";
			
		$sql.=$dbGb->caseStatusShowImage("tr.status");		
		$sql.=" FROM 
					rms_transfer_credit tr JOIN rms_student AS s ON s.stu_id = tr.stu_name
					LEFT JOIN rms_student AS frS ON frS.stu_id = tr.student_id
				WHERE 1
			";
		if(!empty($search['branch_id'])){
			$sql.=' AND tr.branch_id='.$search['branch_id'];
		}
		$sql.=$dbGb->getAccessPermission('tr.branch_id');
		$order=" ORDER BY tr.id DESC ";
		$limit = 10;
		if(!empty($search['limitRecord'])){
			$limit = $search['limitRecord'];
		}
		$order.= " LIMIT ".$limit;
		return $db->fetchAll($sql.$order);
	}
	*/
	
	function getAllDiscountSetting($search)
	{
		$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$branch= $dbGb->getBranchDisplay();
		
		$currentLang = $dbGb->currentlang();
		$colunmname = 'name_en';
		$strDegree = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'name_kh';
			$strDegree = 'title';
		}

		
		$sqlPeriod = "(SELECT v.$colunmname FROM `rms_view` AS v WHERE v.type=39 AND v.key_code=ds.discountPeriod LIMIT 1) ";
		$sqlDiscountFor = "(SELECT v.$colunmname FROM `rms_view` AS v WHERE v.type=37 AND v.key_code=ds.discountFor LIMIT 1)";

		$sql = "SELECT 
					ds.id 
					,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=ds.branchId LIMIT 1) AS branchName
					,CONCAT(COALESCE(aca.`fromYear`,''),'-',COALESCE(aca.`toYear`,'')) AS academicYearTitle
					,ds.discountTitle
					,ds.DisValueType AS disValueType
					,ds.discountValue AS discountValue
					,$sqlDiscountFor AS discountForText
					,(SELECT v.$colunmname FROM `rms_view` AS v WHERE v.type=38 AND v.key_code=ds.discountForType LIMIT 1) AS discountForOption
					,(SELECT GROUP_CONCAT($strDegree) FROM `rms_items` WHERE FIND_IN_SET(id,ds.degree)) as degreeListTitle
					,(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=ds.discountId LIMIT 1) AS discName
					,CASE 
						WHEN NULLIF(ds.`startDate`,'') IS NUll THEN $sqlPeriod 
						ELSE CONCAT(COALESCE($sqlPeriod),'',COALESCE(DATE_FORMAT(ds.startDate,'%d-%m-%Y'),''),'/',COALESCE(DATE_FORMAT(ds.endDate,'%d-%m-%Y'),'')) 
					End AS discountPeriod
						
					,(SELECT u.first_name FROM rms_users AS u WHERE u.id=ds.userId LIMIT 1 ) AS user_name
					,ds.createDate
					,(SELECT COUNT(DISTINCT(sd.`studentId`)) FROM `rms_discount_student` AS sd WHERE sd.`discountGroupId` = ds.`id` LIMIT 1) AS amtAllStudent
					,(SELECT COUNT(DISTINCT(sd.`studentId`)) FROM `rms_discount_student` AS sd WHERE sd.`discountGroupId` = ds.`id` AND sd.`isCurrent`=1 LIMIT 1) AS amtActiveStudent
				";

		$sql .= $dbGb->caseStatusShowImage("ds.status");
		$sql .= " FROM 
						rms_dis_setting AS ds 
						LEFT JOIN `rms_academicyear` AS aca ON aca.`id` = ds.`academicYear`
				";
		$sql .= " WHERE 1";
		if (!empty($search['academic_year'])) {
			$sql .= ' AND ds.academicYear=' . $search['academic_year'];
		}
		if (!empty($search['branch_id'])) {
			$sql .= ' AND ds.branchId=' . $search['branch_id'];
		}
		$sql .= $dbGb->getAccessPermission('ds.branchId');
		
		$order = " ORDER BY ds.id DESC ";
		return $db->fetchAll($sql . $order);
	}
	
	function getStudentDiscountBySettingId($search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$currentLang = $dbp->currentlang();
		$view = $dbp->getViewLabelDisplay("shortcut");
		
		$grade = 'title_en';
		$degree = 'title_en';
		if ($currentLang == 1) {
			$grade = 'title';
			$degree = 'title';
		}
		$sql = "
			SELECT 
			    s.stu_id,
				s.stu_code AS stuCode
			
				,s.stu_khname AS stuNameKh
				,CONCAT(COALESCE(s.last_name,''),' ' ,COALESCE(s.stu_enname,'')) AS stuNameEn
				,tel
				,ds.degreeId
				,ds.grade
				,(SELECT $degree FROM `rms_items` WHERE rms_items.id=ds.degreeId LIMIT 1 ) AS degreeTitle
				,(SELECT $grade FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=ds.grade LIMIT 1) AS gradeTitle
				,CONCAT(COALESCE(aca.`fromYear`,''),'-',COALESCE(aca.`toYear`,'')) AS academicYearEnrollTitle
				,COALESCE((SELECT $view FROM `rms_view` AS v WHERE v.key_code= s.studentType AND v.type=40  LIMIT 1),'') AS studentTypeTitle 
				,COALESCE((SELECT $view FROM `rms_view` AS v WHERE v.key_code= s.sex AND v.type=2  LIMIT 1),'') AS genderTitle 
				,ds.isCurrent ";

		$sql .=" FROM  `rms_discount_student` AS ds 
					JOIN rms_student AS s ON ds.studentId = s.stu_id 
					LEFT JOIN rms_academicyear AS aca ON aca.id = s.academicYearEnroll
				";
		$sql.=" WHERE  1 ";
		if(!empty($search['recordId'])){
			$sql .= " AND ds.discountGroupId = ".$search['recordId'];
		}
		$sql.=" ORDER By ds.degreeId,ds.grade ASC , ds.isCurrent DESC";
		return $db->fetchAll($sql);
	}
	
	function getDetailContentList($search){
		
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$type =empty($search["type"]) ? 1 : $search["type"];
		$htmlContent='';
		$i=0;
		if($type==1){
			$stuDisList= $this->getStudentDiscountBySettingId($search);
			if(!empty($stuDisList)) {
				$htmlContent.='
					<div class="card-datatable " style="position: relative;  width: 100%; ">
						<table id="exportExcelList" class="table " border="1">
							<thead>
								<tr>
									<th class="text-center" >'.$tr->translate('NUM').'</th>
									<th class="text-center" >'.$tr->translate('STUDENT').'</th>
									<th class="text-center" >'.$tr->translate('SEX').'</th>
									<th class="text-center" >'.$tr->translate('TEL').'</th>
									<th class="text-center" >'.$tr->translate('ACADEMIC_YEAR_ENROLL').'</th>
									<th class="text-center" >'.$tr->translate('STUDENT_TYPE').'</th>
									<th class="text-center" >'.$tr->translate('STATUS').'</th>
									
								</tr>
							</thead>
							<tbody>
				';
				$oldGrade='';
				foreach($stuDisList as $key => $stRow){ 
					$i++;
					
					$statusBg = "bg-label-success";
					$statusTitle = '<i class="ti ti-checks me-1"></i>'.$tr->translate("USING");
					if($stRow['isCurrent']!=1){ 
						$status = '<i class="ti ti-hexagon-letter-x me-1"></i>'.$tr->translate("STOP_USED"); 
						$statusTitle = "bg-label-danger";
					}
					if($oldGrade!=$stRow['grade']){ 
						$htmlContent.='
							<tr >
								<td colspan="7" class="text-center text-primary">'.$tr->translate("GRADE").' : <span class="fw-bold " >'.$stRow['gradeTitle'].'</span></td>
							</tr>
						';
					}
					$oldGrade = $stRow['grade'];
					$htmlContent.='
							<tr>
								<td class="items-no text-center">'.$i.'</td>
								<td class="">
									<span class="d-block text-primary text-truncate fw-medium mb-0 text-wrap" >'.$stRow["stuCode"].'</span>
									<span class="d-block text-truncate fw-medium mb-0 text-wrap" >'.$stRow["stuNameKh"].'</span>
									<span class="d-block text-truncate fw-medium mb-0 text-wrap" >'.$stRow["stuNameEn"].'</span>
								</td>
								<td class="text-center" >
									'.$stRow["genderTitle"].'
								</td>
								<td class="text-center" >
									'.$stRow["tel"].'
								</td>
								<td class="text-center" >
									'.$stRow["academicYearEnrollTitle"].'
								</td>
								<td class="text-center" >
									'.$stRow["studentTypeTitle"].'
								</td>
								<td class="text-center" >
									<div class="d-flex align-items-center justify-content-center">
										<small class="rounded '.$statusBg.'  p-1 px-2">
										  '.$statusTitle.'
										</small>
									</div>
								</td>
							';
				}
				
				$htmlContent.='
							</tbody>
						</table>
					</div>
				
				';
			}
		}
		
		$arrReturn = array(
			'content'=>$htmlContent
		);
		
		return $arrReturn;
	}
    
}