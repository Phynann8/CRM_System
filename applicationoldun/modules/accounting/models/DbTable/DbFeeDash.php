<?php

class Accounting_Model_DbTable_DbFeeDash extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllCurrentTuitionFee($search=null){
    	$db=$this->getAdapter();
		
    	$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
    	$lang = $dbp->currentlang();
		
    	$colName = 'title_en';
    	$str = 'title_eng';
    	if ($lang==1){
    		$colName = 'title';
    		$str = 'title_kh';
    	}
    	$type = empty($search['type']) ? 1 : $search['type'];
    	$sql = "SELECT 
					tf.`id`
					,tf.`academic_year`
					,tf.`branch_id` AS branchId
					,(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = tf.branch_id LIMIT 1) AS branchName
					,CONCAT(COALESCE(aca.`fromYear`,''),'-',COALESCE(aca.`toYear`,'')) AS academicYearTitle
					,tf.`generation`
					,(SELECT sdt.$str FROM rms_studytype AS sdt WHERE sdt.id =tf.term_study  LIMIT 1) AS studyTypeTitle
					
					,(SELECT COALESCE(NULLIF(it.`shortcut`,''),it.$colName) FROM `rms_items` AS it WHERE it.id = itd.`items_id` LIMIT 1) AS categoryTitle
					,tfd.`class_id` AS itemId
					,COALESCE(NULLIF(itd.`shortcut`,''),itd.$colName) AS itemTitle
					,GROUP_CONCAT(tfd.`payment_term` ORDER BY tfd.`payment_term` ASC ) AS payTerm
					,GROUP_CONCAT(COALESCE(tfd.`tuition_fee`,0) ORDER BY tfd.`payment_term` ASC) AS fee
		";
    	
    	$sql.=" FROM (`rms_tuitionfee` AS tf JOIN `rms_tuitionfee_detail` AS tfd ON tf.id = tfd.`fee_id`) 
					LEFT JOIN `rms_itemsdetail` AS itd ON itd.`id` = tfd.`class_id` AND itd.`items_type` =$type
					LEFT JOIN `rms_academicyear` AS aca ON aca.`id` = tf.`academic_year`	
			";
    	$sql.=" WHERE tf.`type` =$type ";
    	if(!empty($search['academic_year'])){
    		$sql.=" AND tf.academic_year=".$search['academic_year'];
    	}
    	if(!empty($search['branch_id'])){
    		$sql.=" AND tf.branch_id=".$search['branch_id'];
    	}
		if(!empty($search['feeId'])){
    		$sql.=" AND tf.id=".$search['feeId'];
    	} 
    	
    	$sql.=$dbp->getAccessPermission("tf.branch_id");
    	$sql.=" GROUP BY tf.`id`,tfd.`class_id` ";
    	$sql.=" ORDER BY tf.branch_id,tf.`id` DESC,itd.`items_id` ASC,itd.`ordering` ASC ";
    	
    	return $db->fetchAll($sql);
    }
	
	function getAllPaymentPeriod($search=null){
    	$db=$this->getAdapter();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
    	$lang = $dbp->currentlang();
		
    	$colName = 'title_en';
    	$str = 'title_eng';
    	if ($lang==1){
    		$colName = 'title';
    		$str = 'title_kh';
    	}
    	$type = empty($search['type']) ? 1 : $search['type'];
    	$sql = "SELECT 	
					te.id
					,te.`branch_id` AS branchId
					,(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = te.`branch_id` LIMIT 1) AS branchName
					,CONCAT(COALESCE(aca.`fromYear`,''),'-',COALESCE(aca.`toYear`,'')) AS academicYearTitle
					
					,te.`periodId`
					,te.`title`
					,te.`start_date` AS startDate
					,te.`end_date` AS endDate
					,(SELECT GROUP_CONCAT(COALESCE(NULLIF(it.shortcut,''),it.$colName)) FROM `rms_items` AS it WHERE it.type=1 AND FIND_IN_SET(it.id,te.`degreeId`) LIMIT 1) AS degreeListTitle
					,te.`degreeId`
					,CASE 
						WHEN te.`periodId` =1 THEN '".$tr->translate('MONTHLY')."'
						WHEN te.`periodId` =2 THEN '".$tr->translate('TERM')."'
						WHEN te.`periodId` =3 THEN '".$tr->translate('SEMESTER')."'
						WHEN te.`periodId` =4 THEN '".$tr->translate('YEAR')."'
						WHEN te.`periodId` =5 THEN '".$tr->translate('ONE_PAYMENT_ONLY')."'
						ELSE ''
					END AS periodTitle

		";
    	
    	$sql.=" FROM `rms_startdate_enddate` AS te 
					LEFT JOIN `rms_academicyear` AS aca ON aca.`id` = te.`academic_year`	
			";
    	$sql.=" WHERE te.`forDepartment` =$type ";
    	if(!empty($search['academic_year'])){
    		$sql.=" AND te.academic_year=".$search['academic_year'];
    	}
    	if(!empty($search['branch_id'])){
    		$sql.=" AND te.branch_id=".$search['branch_id'];
    	}
    	$sql.=$dbp->getAccessPermission("te.branch_id");
    	$sql.=" ORDER BY te.`degreeId` ASC,te.`periodId` ASC,te.`title` ASC ";
    	
    	return $db->fetchAll($sql);
    }
	
	function getCountingItemsSettedFee($search){
		$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$type = empty($search['type']) ? 1 : $search['type'];
		$sql="
			SELECT 
				COUNT(DISTINCT(tfd.`class_id`)) AS `countingValue`
			FROM  `rms_tuitionfee` AS tf JOIN `rms_tuitionfee_detail` AS tfd ON tf.id = tfd.`fee_id`
			WHERE tf.`type` = $type
		";
		if(!empty($search['academic_year'])){
    		$sql.=" AND tf.academic_year=".$search['academic_year'];
    	}
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbgb->getAccessPermission("tf.branch_id");
		return $dbgb->getGlobalDbOne($sql);
	}
	
	function getFeeDetailContentList($search){
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$termStudy = $dbGb->getAllPaymentTerm();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$arrBgColors = array(
			1=>"bg-label-dark",
			2=>"bg-label-warning",
			3=>"bg-label-success",
			4=>"bg-label-primary",
			5=>"bg-label-secondary",
			
		);;
		$tuitionFeeList= $this->getAllCurrentTuitionFee($search);
		
		$htmlContent='';
		$termCol = count($termStudy);
		$branchId="";
		$feeId="";
		$i=0;
		if(!empty($tuitionFeeList)) {
			$htmlContent.='
				<div class="card-datatable " style="position: relative;  width: 100%; ">
					<table id="exportExcelList" class="table " border="1">
						<thead>
							<tr>
								<th class="text-center" >'.$tr->translate('NUM').'</th>
								<th class="text-center" >'.$tr->translate('GRADE').'</th>';
							if(!empty($termStudy)) foreach($termStudy as $index => $term){
									$bgLable = "bg-label-danger";
									$bgLable = empty($arrBgColors[$index]) ? $bgLable : $arrBgColors[$index];
									$htmlContent.='<th class="text-center" >
												<div class="d-flex align-items-center justify-content-center">
													<span class="rounded '.$bgLable.'  p-1">
													  <i class="fa fa-usd fa-md me-1"></i>
													  '.$term.'
													</span>
												</div>
											</th>';
								}
			$htmlContent.='</tr>
						</thead>
						<tbody>
			
			';
			foreach($tuitionFeeList as $key => $tfRow){ 
				$i++;
				$payTermStr = $tfRow["payTerm"];
				$payTermList = explode(',', $payTermStr);
				
				$feeStr = $tfRow["fee"];
				$feeList = explode(',', $feeStr);
				
				$htmlContent.='
						<tr>
							<td class="items-no text-center">'.$i.'</td>
							<td class="">
								<div>
									<a class=" text-primary text-truncate fw-medium mb-2 text-wrap" href="javascript:void(0);">'.$tfRow["itemTitle"].'</a>
									<div class="d-flex align-items-center mt-1">
										<small class="text-nowrap text-heading">'.$tfRow["categoryTitle"].'</small>
									</div>
								</div>
							</td>
						';
						
						if(!empty($termStudy)) foreach($termStudy as $index => $term){
							$indexFeeOfTerm = array_search($index, array_values($payTermList));
							$feeValue=number_format(0,2);
							$bgLable = "bg-label-danger";
							$bgLable = empty($arrBgColors[$index]) ? $bgLable : $arrBgColors[$index];
							if(!empty($indexFeeOfTerm)){
								if(!empty($feeList[$indexFeeOfTerm])){
									$feeValue=number_format($feeList[$indexFeeOfTerm],2);
								}
								
							}					
							$htmlContent.='
								<th class="text-center" >
									<div class="d-flex align-items-center justify-content-center">
										<span class="rounded '.$bgLable.'  p-1">
										  <i class="fa fa-usd fa-md me-1"></i>
										  '.$feeValue.'
										</span>
									</div>
								</th>
							';
						}
				$htmlContent.='';
			}
			$htmlContent.='
						</tbody>
					</table>
				</div>
			
			';
		}
		
		$arrReturn = array(
			'content'=>$htmlContent
		);
		
		return $arrReturn;
											
	}
    
}