<?php
class Allreport_Model_DbTable_DbNewAccounting extends Zend_Db_Table_Abstract
{

	protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
	function getAllPaymentStatistic($search=null){
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch= $_db->getBranchDisplay();

		$lang = $_db->currentlang();
	   	if($lang==1){// khmer
	   		$grade = "title";

	   	}else{ // English
	   		$grade = "title_en";
	   	}

		if ($search['reportType'] == 1) {
			$viewName = " v_studenttutionfeepaid";
			$condiontion = " AND dg.itemType=1 AND dg.is_maingrade=1 ";
		}
		elseif($search['reportType']==2){
			$viewName = " v_studentlunchfeepaid ";
			$condiontion = " AND dg.itemType=2 AND dg.grade = 361 ";//dg.itemType=2 
		 }elseif($search['reportType']==3){
			$viewName = " v_studentnapfeepaid ";//
			$condiontion = " AND dg.itemType=2 AND dg.grade = 360   ";//AND 
		 }
		 $where = "";
		 $whereAcademic="";
		 if(!empty($search['academic_year'])){
			$whereAcademic= " AND vpm.academicYearId = ".$search['academic_year'];
			$where.= " AND dg.academic_year = ".$search['academic_year'];
		}

		$sql = "SELECT 
				s.stu_id,
				s.stu_code,
				stu_khname AS student_name,
				 CASE 
					WHEN s.primary_phone = 1 THEN s.tel
					WHEN s.primary_phone = 2 THEN COALESCE(NULLIF(f.fatherPhone,''),s.tel)
					WHEN s.primary_phone = 3 THEN COALESCE(NULLIF(f.motherPhone,''),s.tel)
					WHEN s.primary_phone = 4 THEN COALESCE(NULLIF(f.guardianPhone,''),s.tel)
				END AS tel,
				dg.stop_type AS stopType,
				DATE_FORMAT(COALESCE(s.enrollDate,s.create_date),'%d/%m/%Y') AS registrationDate,
				COALESCE(NULLIF(it.`shortcut`,''),it.$grade) as gradeAcademic,
				COALESCE(NULLIF(it1.`shortcut`,''),it1.$grade) as gradeLabel,
				vpm.paymentList,
				CASE
					WHEN v.name_en!='' THEN vpm.discountCode
					ELSE dst.discountCode
				END AS discountCode,
				CASE
					WHEN v.name_en!='' THEN vpm.discountValue
					ELSE dst.discountValue
				END AS discountValue,
				vpm.payment_term,
				v.name_en AS stpaymentType,
				(tf.tuition_fee-(tf.tuition_fee*dst.discountValue/100)) as tuitionFee,
				(SELECT SUBSTRING_INDEX(MIN(installmentOrdering),',',1) AS installmentOrdering FROM `rms_startdate_enddate` WHERE FIND_IN_SET(id,termPaidList) LIMIT 1) AS startTerm
			  FROM 
				 rms_student AS s
				INNER JOIN `rms_group_detail_student` AS dg ON s.stu_id = dg.stu_id $condiontion
				INNER JOIN rms_itemsdetail it1 ON it1.id=`dg`.`grade` AND it1.isExtraCourse=0
				LEFT JOIN $viewName AS vpm ON s.stu_id=vpm.studentId $whereAcademic
				
				LEFT JOIN rms_itemsdetail it ON it.id=`vpm`.`grade` AND it.isExtraCourse=0
				LEFT JOIN rms_discount_student ds ON ds.id = (SELECT rds.id FROM rms_discount_student rds  WHERE studentId = s.stu_id AND rds.grade = dg.grade ORDER BY rds.isCurrent DESC, rds.id DESC LIMIT 1)
				LEFT JOIN rms_dis_setting AS dst ON dst.id=ds.discountGroupId
				LEFT JOIN rms_view v ON v.type=6 AND `key_code` = `vpm`.`payment_term`
				LEFT JOIN rms_family AS f ON f.id = s.familyId
				LEFT JOIN rms_tuitionfee_detail tf ON tf.fee_id=dg.feeId AND tf.class_id=dg.grade AND tf.payment_term=4
			  WHERE
			   	s.status=1
				AND dg.movedType!=1
				AND s.customer_type=1 ";
				/*
				 AND payment_term=4 should be follow pay as of first paid 
				 COALESCE(NULLIF(it.`shortcut`,''),$grade) as grade,
				 LEFT JOIN rms_itemsdetail it ON it.id=`vpm`.`grade`
				LEFT JOIN `rms_discount_student` ds ON ds.studentId=s.stu_id AND ds.grade=dg.grade 
				LEFT JOIN rms_dis_setting AS dst ON dst.id=ds.discountGroupId
				LEFT JOIN `rms_discount_student` ds ON ds.studentId=s.stu_id AND ds.grade=dg.grade 
				dst.discountCode as discountCode1,
  				dst.discountValue as discountValue1,
				*/

				
		
	
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			$s_where[] = " tel LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.= " AND s.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$where.= " AND dg.academic_year = ".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$where.= " AND dg.degree = ".$search['degree'];
		}
		if(!empty($search['grade']) AND $search['grade']>0){
			$where.= " AND dg.grade = ".$search['grade'];
		}
		
		$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
	    $to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$from_date." AND ".$to_date;

		if($search['active_type']>-1){
			if ($search['active_type'] == 0) {
				$where.= " AND dg.stop_type=0 AND dg.is_current=1";
			} else {
				$where.= " AND dg.stop_type!=0";
			}
		}
		
		if(!empty($search['studentType'])){
			$where.= " AND s.studentType = ".$search['studentType'];
		}
		if(!empty($search['pay_term'])){
			$where.= " AND vpm.payment_term=".$search['pay_term'];
		}
		if(!empty($search['payment_date'])){
			$where.= " AND FIND_IN_SET ('".$search['payment_date']."',vpm.PaidDateList)";
		}
		
		$where.=$_db->getAccessPermission('s.branch_id');
		$order=" GROUP BY dg.stu_id,`dg`.`grade`,dg.academic_year ";
		$order.=" order by it1.ordering ASC";
		$db = $this->getAdapter();		
		$resultStudent =  $db->fetchAll($sql.$where.$order);
		if (!empty($resultStudent)) {
			foreach ($resultStudent as $key=> $result) {
				$dataPayment = json_decode($result['paymentList'],true);
				$extraColumns =  $this->ExtraColumns($dataPayment,$result['startTerm'],$result);
				$resultStudent[$key]['paymentList'] = $extraColumns;
			}
		}
		// print_r($resultStudent[$key]['paymentList']);exit();
		$paymentStatus = $search['paymentstatus']>-1?$search['paymentstatus']:-1; // or Finance etc.
		$filterTerm = ($search['termList']>0)?$search['termList']:0;

		if($paymentStatus>-1 AND $filterTerm>0){
			$resultStudent = array_filter($resultStudent, function ($var) use ($paymentStatus,$filterTerm) {
				return ($var['paymentList']['isUnpaidTerm'.$filterTerm] == $paymentStatus);
			});
		}
	
		return $resultStudent;
	}
	function ExtraColumns($dataPayment,$startTerm,$resultRow)
	{
		
		$arrExtra = array(
			'stpaymentType'=>'',//pay as ex:term,semester...
			'discountCode'=>'',
			'isUnpaidTerm1' => 0,//1 paid,0unpaid
			'isUnpaidTerm2' => empty($startTerm)?1:0,//ករណីសិស្សមិនទាន់បង់ប្រាក់ត្រូវបង់ត្រូវគិតក្នុងTerm1ទាំងអស់ termបន្ទាប់មិនត្រូវការគណនា
			'isUnpaidTerm3' => empty($startTerm)?1:0,//ដូចនេះត្រូវ init term2,3,4 =1ដើម្បីdisable
			'isUnpaidTerm4' => empty($startTerm)?1:0,

			'periodDate1' => '',
			'periodDate2' => '',
			'periodDate3' => '',
			'periodDate4' => '',

			'payment1'=>'',//amount of term1
			'payment2'=>'',
			'payment3'=>'',
			'payment4'=>'',

			'unPaidCountTerm1'=>empty($startTerm)?$resultRow['tuitionFee']:0,//ករណីសិស្សមិនទាន់បង់ចាប់តម្លៃជាឆ្នាំ
			'unPaidCountTerm2'=>0,
			'unPaidCountTerm3'=>0,
			'unPaidCountTerm4'=>0,

			'paymentPeriod1'=>0,
			'paymentPeriod2'=>0,
			'paymentPeriod3'=>0,
			'paymentPeriod4'=>0,
		);
	
		if (!empty($dataPayment)) {
			$startTerm = !empty($startTerm)?$startTerm:0;

			foreach($dataPayment as $key=> $resultPayment){
				if ($startTerm > 4) {
					break;
				}
				
				if($resultPayment['payment_term']==4){//year
					$arrExtra['isUnpaidTerm1'] = 1;
					$arrExtra['isUnpaidTerm2'] = 1;
					$arrExtra['isUnpaidTerm3'] = 1;
					$arrExtra['isUnpaidTerm4'] = 1;
					$arrExtra['paymentPeriod1'] = $resultPayment['payment_term'];
					$arrExtra['periodDate1'] = $resultPayment['paidDate'];
					$arrExtra['payment1'] = $resultPayment['totalpayment'];
					break;
				}
				if($resultPayment['payment_term']==3){//semestere
					
					if ($startTerm == 1) { // semester 1
						$arrExtra['isUnpaidTerm1'] = 1;
						$arrExtra['isUnpaidTerm2'] = 1;
						$arrExtra['isUnpaidTerm4'] = 1;
						$arrExtra['paymentPeriod1'] = $resultPayment['payment_term'];
						$arrExtra['periodDate1'] = $resultPayment['paidDate'];
						$arrExtra['payment1'] = $resultPayment['totalpayment'];
						$arrExtra['unPaidCountTerm3'] = $resultPayment['totalpayment'];
						$startTerm = 2;//next loop will +1 continue to bottom step
						
					}elseif($startTerm == 3){//semester 2
						
						$arrExtra['isUnpaidTerm1'] = 1;//just update
						$arrExtra['isUnpaidTerm2'] = 1;//just update

						$arrExtra['isUnpaidTerm3'] = 1;
						$arrExtra['isUnpaidTerm4'] = 1;
						$arrExtra['paymentPeriod3'] = $resultPayment['payment_term'];
						
						$arrExtra['periodDate3'] = $resultPayment['paidDate'];
						$arrExtra['payment3'] = $resultPayment['totalpayment'];
						$arrExtra['unPaidCountTerm4'] = 0;//
						$arrExtra['unPaidCountTerm3'] = 0;//
					}
				}
				if($resultPayment['payment_term']==2){//term1
					if ($startTerm == 1) {
						$arrExtra['isUnpaidTerm1'] = 1;
						$arrExtra['paymentPeriod1'] = $resultPayment['payment_term'];
						$arrExtra['periodDate1'] = $resultPayment['paidDate'];
						$arrExtra['payment1'] = $resultPayment['totalpayment'];

						$arrExtra['unPaidCountTerm2'] = $resultPayment['totalpayment'];
						$arrExtra['unPaidCountTerm3'] = $resultPayment['totalpayment'];
						$arrExtra['unPaidCountTerm4'] = $resultPayment['totalpayment'];
					}elseif($startTerm == 2){//term2
						$arrExtra['isUnpaidTerm1'] = 1;//just update
						$arrExtra['isUnpaidTerm2'] = 1;
						
						$arrExtra['paymentPeriod2'] = $resultPayment['payment_term'];
						$arrExtra['periodDate2'] = $resultPayment['paidDate'];
						$arrExtra['payment2'] = $resultPayment['totalpayment'];

						$arrExtra['unPaidCountTerm2'] = 0;
						$arrExtra['unPaidCountTerm3'] = $resultPayment['totalpayment'];
						$arrExtra['unPaidCountTerm4'] = $resultPayment['totalpayment'];
				}elseif($startTerm == 3){//term3
						$arrExtra['isUnpaidTerm1'] = 1;//just update
						$arrExtra['isUnpaidTerm2'] = 1;//just update
						$arrExtra['isUnpaidTerm3'] = 1;
						$arrExtra['isUnpaidTerm4'] = 0;
						$arrExtra['paymentPeriod3'] = $resultPayment['payment_term'];
						$arrExtra['periodDate3'] =  $resultPayment['paidDate'];
						$arrExtra['payment3'] = $resultPayment['totalpayment'];

						$arrExtra['unPaidCountTerm3'] = 0;
						$arrExtra['unPaidCountTerm4'] = $resultPayment['totalpayment'];

				}elseif($startTerm == 4){//term4
					    $arrExtra['isUnpaidTerm1'] = 1;//just update
						$arrExtra['isUnpaidTerm2'] = 1;//just update
						$arrExtra['isUnpaidTerm3'] = 1;//just update
						$arrExtra['isUnpaidTerm4'] = 1;
						$arrExtra['paymentPeriod4'] = $resultPayment['payment_term'];
						$arrExtra['periodDate4'] = $resultPayment['paidDate'];
						$arrExtra['payment4'] = $resultPayment['totalpayment'];

						$arrExtra['unPaidCountTerm4'] = 0;
					}
				}
				$startTerm=$startTerm+1;
				
			}
		} 
		return $arrExtra;
	}

	
	function getAllPaymentSummarry($search=null){
		//
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch= $_db->getBranchDisplay();

		
		$sql = "SELECT 
					b.$branch branchName,
					CONCAT(ac.fromYear,'-',ac.toYear) academicYear,
					COUNT(DISTINCT IF(dg.stop_type = 0, dg.stu_id, NULL)) AS activeStudent,
					COUNT(DISTINCT IF(dg.stop_type != 0, dg.stu_id, NULL)) AS inactiveStudent,
					SUM(vpm.Year) AS `Year`,
					SUM(vpm.Semester1) Semester1,
					SUM(vpm.Semester2) Semester2,
					SUM(vpm.Term1) Term1,
					SUM(vpm.Term2) Term2,
					SUM(vpm.Term3) Term3,
					SUM(vpm.Term4) Term4,
					COUNT(IF(paymentTerm = 4,1,NULL)) AS TTYear,
					COUNT(IF(vpm.Semester1 > 0,1,NULL)) AS TTSemester1,
					COUNT(IF(vpm.Semester2 > 0,1,NULL)) AS TTSemester2,
					COUNT(IF(vpm.Term1 > 0,1,NULL)) AS TTTerm1,
					COUNT(IF(vpm.Term2 > 0,1,NULL)) AS TTTerm2,
					COUNT(IF(vpm.Term3 > 0,1,NULL)) AS TTTerm3,
					COUNT(IF(vpm.Term4 > 0,1,NULL)) AS TTTerm4
			  FROM 
			  	rms_student AS s
				INNER JOIN `rms_group_detail_student` AS dg ON s.stu_id = dg.stu_id AND s.status=1 AND s.customer_type=1 AND itemType=1 AND dg.is_maingrade=1 AND dg.movedType!=1
				INNER JOIN rms_itemsdetail it1 ON it1.id=`dg`.`grade` AND it1.isExtraCourse=0
				LEFT JOIN vpm_paymenttermdetail vpm ON dg.stu_id=vpm.studentId AND dg.`academic_year`=vpm.academicYearId
				LEFT JOIN rms_branch AS b ON b.br_id = s.`branch_id`
				LEFT JOIN `rms_academicyear` AS ac ON ac.id = dg.`academic_year`
			  WHERE 1 ";
		$where=$_db->getAccessPermission('s.branch_id');
		if(!empty($search['branch_id'])){
			$where.= " AND s.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$where.= " AND dg.academic_year = ".$search['academic_year'];
		}
		// $where.=" AND dg.stu_id=1577";
		$order=" GROUP BY s.branch_id,dg.academic_year ORDER BY s.branch_id ASC,dg.academic_year DESC ";
		$db = $this->getAdapter();		
		$paidResult =  $db->fetchAll($sql.$where.$order);
		$unpaidResult = $this->getUnpaidSummary($search);
		return [
			'paidResult'=>$paidResult,
			'unpaidResult'=>$unpaidResult		
		];
	}
	function getUnpaidSummary($search){//for 
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch= $_db->getBranchDisplay();

		$sql=" SELECT 	
				b.$branch branchName,
				CONCAT(ac.fromYear,'-',ac.toYear) academicYear,
				s.stu_code,
				s.`branch_id`,
				dg.`grade`,
				dg.`academic_year`,
				pmi.`payment_term`,
				pmi.`paidamount`,
				pmi.`installmentPaid`,
				SUM(CASE WHEN installmentPaid IS NULL OR NOT FIND_IN_SET('1', installmentPaid) THEN 1 ELSE 0 END) AS countTerm1,
				SUM(CASE WHEN installmentPaid IS NULL OR NOT FIND_IN_SET('1', installmentPaid) THEN tf.tuition_fee-(tf.tuition_fee*vds.discountValue/100) ELSE 0 END) AS unpaidTerm1,
				
				SUM(CASE WHEN NOT FIND_IN_SET('2', installmentPaid) THEN 1 ELSE 0 END) AS countTerm2,
				SUM(CASE WHEN NOT FIND_IN_SET('2', installmentPaid) THEN pmi.`paidamount` ELSE 0 END) AS unpaidTerm2,
				
				SUM(CASE WHEN NOT FIND_IN_SET('3', installmentPaid) THEN 1 ELSE 0 END) AS countTerm3,
				SUM(CASE WHEN NOT FIND_IN_SET('3', installmentPaid) THEN pmi.`paidamount` ELSE 0 END) AS unpaidTerm3,
				
				SUM(CASE WHEN NOT FIND_IN_SET('4', installmentPaid) AND installmentPaid!='1,2' THEN 1 ELSE 0 END) AS countTerm4,
				SUM(CASE WHEN NOT FIND_IN_SET('4', installmentPaid) AND installmentPaid!='1,2' THEN pmi.`paidamount` ELSE 0 END) AS unpaidTerm4
				
			  FROM 
			  	rms_student AS s
				INNER JOIN `rms_group_detail_student` AS dg 
				ON s.stu_id = dg.stu_id AND s.status=1 AND s.customer_type=1 AND dg.stop_type=0 AND dg.itemType=1 AND dg.is_maingrade=1 AND dg.movedType!=1
				LEFT JOIN rms_branch AS b ON b.br_id = s.`branch_id`
				LEFT JOIN `rms_academicyear` AS ac ON ac.id = dg.`academic_year`
				INNER JOIN rms_itemsdetail it1 ON it1.id=`dg`.`grade` AND it1.isExtraCourse=0
				LEFT JOIN `vpm_paymentinstallment` pmi ON pmi.`student_id`=s.stu_id  AND dg.academic_year=pmi.academicYearId
				LEFT JOIN rms_tuitionfee_detail tf ON tf.fee_id=dg.feeId AND tf.class_id=dg.grade AND tf.payment_term=4
				LEFT JOIN vdis_studentdiscountsetting vds ON vds.studentId=s.stu_id AND vds.grade=dg.grade AND vds.academicYearId=dg.academic_year
			  WHERE 1
			 ";
		$where=$_db->getAccessPermission('s.branch_id');
		if(!empty($search['branch_id'])){
			$where.= " AND s.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$where.= " AND dg.academic_year = ".$search['academic_year'];
		}
		$where.=" GROUP BY dg.`branch_id`,dg.`academic_year`";

		$db = $this->getAdapter();		
		return $db->fetchAll($sql.$where);	
	}
} 
    
   