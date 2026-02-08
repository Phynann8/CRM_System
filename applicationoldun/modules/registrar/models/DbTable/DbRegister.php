<?php

class Registrar_Model_DbTable_DbRegister extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->branch_id;
    }
    
    function getStudentPaymentStart($studentid,$service_id,$type=1){
    	$db = $this->getAdapter();
    	$sql="SELECT spd.id 
    			FROM rms_student_payment AS sp,
    			   rms_student_paymentdetail AS spd WHERE
    		sp.id=spd.payment_id AND is_start=1 
    		AND spd.itemdetail_id=$service_id 
    		AND sp.student_id=$studentid 
    		ORDER BY spd.validate DESC LIMIT 1 ";
    	return $db->fetchOne($sql);
    }
    function getStuidExist($stu_code){
    	$db=$this->getAdapter();
    	$sql="SELECT stu_id,stu_code FROM rms_student WHERE stu_code=$stu_code LIMIT 1";
    	return $db->fetchRow($sql);
    }

    function setStudentId($data){
    	$gdb = new  Application_Model_DbTable_DbGlobal();
    	$rs_stu = $gdb->getStudentinfoGlobalById($data['old_stu']);
    	if($rs_stu['is_setstudentid']==0 AND !empty($data['student_code'])){
    		$branch_id = $data['branch_id'];
    		$stu_no = $gdb->getnewStudentId($branch_id,0);
    	
    		$arr = array(
    				'stu_code'=>$stu_no,
    				'is_setstudentid'=>1,
    		);
    		$this->_name='rms_student';
    		$where="stu_id = ".$data['old_stu'];
    		$this->update($arr, $where);
			return $stu_no;//return to summit stu_code to database
    	}
    }
    function addStudentFromToTesting($data){//from tested student
    	$dbg = new  Application_Model_DbTable_DbGlobal();
    	$rs_stu = $dbg->getStudentTestinfoById($data['old_stu']);
    	if(!empty($data['auto_test'])){
    		$arr = array(
    				'is_registered'=>1,
    		);
    		$this->_name='rms_student_test_result';
    		$where="id = ".$rs_stu['id'];
    		$this->update($arr, $where);
    	
    		$degreeStudent = empty($rs_stu['degree'])?0:$rs_stu['degree'];
    		$stu_code = $dbg->getnewStudentId($data['branch_id'],$degreeStudent);
    	
    		$settingNewStuID = NEW_STU_ID_FROM_TEST;
    		if ($settingNewStuID==1){
    			$stu_code=empty($data['student_code'])?$stu_code:$data['student_code'];
    		}
    	
    		// $data['degreeStudent']=$degreeStudent;//For Insert To Tale Count ID
    		//$dbg->updateAmountStudetByDegree($data);//For Insert To Tale Count ID// should be remove function and table
    	
			$academicYearEnroll = 0;
			$lastestAca = $dbg->getLatestAcadmicYear();
			if(!empty($lastestAca["id"])){
				$academicYearEnroll = $lastestAca["id"];
			}
    		$arr = array(
    				'customer_type' =>1,
    				'stu_code'=>$stu_code,
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'enrollDate'=>date("Y-m-d"),
					'academicYearEnroll'=>$academicYearEnroll,
    		);
    		$this->_name='rms_student';
    		$where="stu_id = ".$data['old_stu'];
    		$this->update($arr, $where);
    	
    		if(!empty($data['group_id'])){
    				
    			$group_id = $data['group_id'];
    			$is_setgroup = 1;
    			$dbGroup = new Foundation_Model_DbTable_DbGroup();
    			$group_info = $dbGroup->getGroupById($group_id);
    			if($group_info['degree']==$data['degree'] AND $group_info['grade']==$data['grade']){
					$arr =[
						'is_use'=>1,
						'is_pass'=>2
					];
					$where ="id=".$group_id;
    				$this->_name="rms_group";
    				$this->update($arr, $where);

    				$array = array(
    						'group_id'=>$group_id,
							'is_setgroup'=>1
    				);
    				$where =" group_id=0 AND stu_id=".$data['old_stu'];
    				$this->_name="rms_group_detail_student";
    				$this->update($array, $where);
    			}else{//ករណីរើសបង់ថ្លៃកម្មវិធីផ្សេងៗវានឹងបន្ថែម ជួរក្រោម១ទៀត
    				$_arr = array(
    						'stu_id'			=>$data['old_stu'],
    						'itemType'			=>1,
    						'is_newstudent'		=>1,
    						'status'			=>1,
    						'group_id'			=>$group_id,
    						'degree'			=>$data['degree'],
    						'grade'				=>$data['grade'],
    						'is_current'		=>1,
    						'is_setgroup'		=>$is_setgroup,
    						'is_maingrade'		=>1,
    						'create_date'		=>date("Y-m-d H:i:s"),
    						'modify_date'		=>date("Y-m-d H:i:s"),
    						'user_id'			=>$this->getUserId(),
    						'note'				=>'data from payment then choose group when from tested student'
    				);
    				$this->_name="rms_group_detail_student";
    				$this->insert($_arr);
    			}
    		}
			
			if(!empty($rs_stu['crm_id'])){
				$crmId = empty($rs_stu['crm_id']) ? 0 : $rs_stu['crm_id'];
				$arr = array(
						'crm_status' =>3,
						'modify_date' =>date("Y-m-d H:i:s"),
				);
				$this->_name='rms_crm';
				$where="id = ".$crmId;
				$this->update($arr, $where);
			}
			
			if(!empty($data['study_year'])){ //ធ្វើបច្ចុប្បន្នភាពឆ្នាំសិក្សាទៅតាមតម្លៃសិក្សាដែលបង់ចូលរៀន
				$result = $dbg->getFeeStudyinfoByIdGloable($data['study_year']);
				if(!empty($result['academicYearId'])){
					$arrayGdetail = array(
							'academic_year'=>$result['academicYearId']
					);
					$whereGdetail =" is_current=1 AND itemType=1 AND stu_id=".$data['old_stu'];
					$this->_name="rms_group_detail_student";
					$this->update($arrayGdetail, $whereGdetail);
				}
			}
			return !empty($stu_code)?$stu_code:0;
    	}
    }
    function cutStockDetail($data,$i){
    	$db = $this->getAdapter();
    	$condictionSale = $data['conditionCutStock'];
    	$totalQty=0;
    	$totalCosting = 0;
    	if($data['is_productseat']==1){ // product set
    		/*
			$sql="SELECT
	    		set.pro_id as product_set_id,
	    		set.subpro_id as pro_id,
	    		set.qty as set_qty,
	    		idt.cost,
	    		lo.price,
	    		lo.price_set,
	    		lo.costing
    		FROM
	    		rms_itemsdetail as idt,
	    		rms_product_setdetail as `set`,
	    		rms_product_location as lo
    		WHERE
	    		idt.id = set.subpro_id
	    		and set.subpro_id = lo.pro_id
	    		and set.pro_id = ".$data['item_id'.$i]."
	    		and lo.branch_id = ".$data['branch_id'];
    		$sql.=" GROUP BY set.subpro_id ORDER BY set.id ASC ";
			*/
			$sql="
				SELECT 
					set.pro_id AS product_set_id
					,setlc.`pro_id`
					,set.subpro_id AS pro_id
					,set.qty AS set_qty
					,idt.cost
					,lo.price
					,lo.price_set
					,lo.costing
				FROM  
					(rms_product_location AS setlc  JOIN `rms_product_setdetail` AS `set` ON `set`.`id_pro_location` = setlc.`id`)
					JOIN rms_itemsdetail AS idt ON idt.id = set.subpro_id
					JOIN rms_product_location AS lo ON lo.`pro_id` = `set`.`subpro_id` AND lo.`branch_id` = ".$data['branch_id']."
				WHERE 
					set.pro_id = ".$data['item_id'.$i]."
					AND setlc.`branch_id` = ".$data['branch_id']." 
			";
			$sql.=" ORDER BY set.id ASC ";
    		$result = $db->fetchAll($sql);
    		if(!empty($result)){
    			foreach ($result as $row){
					$dbs = new Application_Model_DbTable_DbGlobalStock();
					$arr = array(
						'branch_id'=>$data['branch_id'],
						'productId'=>$row['pro_id']
					);
					$resultItem = $dbs->getProductInfoByLocation($arr);

					$qtySet = $row['set_qty'] * $data['qty_'.$i]; // (qty of set detail) * (qty buy)

    				$qty_after = $qtySet;
    				$totalCosting = $totalCosting+($qty_after*$row['costing']);//for for product
    					
    				if ($condictionSale!=1){
    				
						$qty_after=0;
						if($resultItem['currentQty'] < $qtySet ){
							$qty_after = $qtySet - $resultItem['currentQty'];
						}
    				}
    				$arr_sale = array(
						'payment_id'		=>$data['paymentId'],
						'is_product_set'	=>1,
						'product_set_id'	=>$row['product_set_id'],
						'pro_id'			=>$row['pro_id'],
						'qty'				=>$qtySet, 
						'qty_after'			=>$qty_after,
						'cost'				=>$row['costing'],
						'price'				=>$row['price_set'],
						'user_id'			=>$this->getUserId(),
    				);
    				$this->_name="rms_saledetail";
    				$sale_detailid = $this->insert($arr_sale);
    					
    				if ($condictionSale!=1){
						$totalQty = $totalQty + $qtySet;
						$qtyRecieve = $qtySet - $qty_after;
						$remainQty = $qtySet - $qtyRecieve;
    					$arrs = array(
							'cutstock_id'=>$data['cutStockId'],
							'student_paymentdetail_id'=>$sale_detailid,
							'product_id'=>$row['pro_id'],
							'due_amount'=>$qtySet,
							'qty_receive'=>$qtyRecieve,
							'remain'=>$remainQty,
							'remide_date'=>'',
							'paymentId'=>$data['paymentId'],
    					);
    					$this->_name ='rms_cutstock_detail';
    					$this->insert($arrs);
    					$dbpu = new Stock_Model_DbTable_DbPurchase();
    					$dbpu->updateStock($row['pro_id'],$data['branch_id'],-$qtySet,0,$data['paymentId'],5);
    				}
    			}
    			$this->_name="rms_student_paymentdetail";
    			$arr = array(
    					'productCost'=>$totalCosting
    			);
    			$where ='id='.$data['paymentDetailId'];
    			$this->update($arr, $where);
    		}
    	}else{ // product normal
    			
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$arr = array(
    				'branch_id'=>$data['branch_id'],
    				'productId'=>$data['item_id'.$i]
    		);
    		$resultItem = $dbs->getProductInfoByLocation($arr);
    		$currentCosting = empty($resultItem['currentPrice'])?0:$resultItem['currentPrice'];
    		
    		$arr = array(
    				'productCost'=>$currentCosting
    		);
    		$where ='id='.$data['paymentDetailId'];
    		$this->_name="rms_student_paymentdetail";
    		$this->update($arr, $where);
    			
    		$qty_after = $data['qty_'.$i];
    		if ($condictionSale!=1){
				$qty_after=0;
				if($resultItem['currentQty'] < $data['qty_'.$i] ){
					$qty_after = $data['qty_'.$i] - $resultItem['currentQty'];
				}
    		}
    		$arr_sale = array(
    				'payment_id'		=>$data['paymentId'],
    				'is_product_set'	=>0,
    				'product_set_id'	=>$data['item_id'.$i],
    				'pro_id'			=>$data['item_id'.$i],
    				'qty'				=>$data['qty_'.$i],
    				'qty_after'			=>$qty_after,
    				'cost'				=>$currentCosting,
    				'price'				=>$data['price_'.$i],
    				'user_id'			=>$this->getUserId(),
    		);
    		$this->_name="rms_saledetail";
    		$sale_detailid= $this->insert($arr_sale);
    			
    		if ($condictionSale!=1){
				$totalQty = $totalQty + $data['qty_'.$i];
				$qtyRecieve = $data['qty_'.$i] - $qty_after;
				$remainQty = $data['qty_'.$i] - $qtyRecieve;
    			$arrs = array(
    					'cutstock_id'=>$data['cutStockId'],
    					'student_paymentdetail_id'=>$sale_detailid,
    					'product_id'=>$data['item_id'.$i],
    					'due_amount'=>$data['qty_'.$i],
    					'qty_receive'=>$qtyRecieve,
    					'remain'=>$remainQty,
    					'remide_date'=>'',
						'paymentId'=>$data['paymentId'],
    			);
    			$this->_name ='rms_cutstock_detail';
    			$this->insert($arrs);
    			$dbpu = new Stock_Model_DbTable_DbPurchase();
    			$dbpu->updateStock($data['item_id'.$i],$data['branch_id'],-$data['qty_'.$i],0,$data['paymentId'],5);
    		}
    	}
		return $totalQty;
    }
	function addRegister($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		
		$paid_date = date("Y-m-d ",strtotime($data['paid_date'])).date("H:i:s");

		$gdb = new  Application_Model_DbTable_DbGlobal();
		$stu_id = $data['old_stu'];//$this->getnewStudentId($data['dept']);
		
		$rs_stu = $gdb->getStudentinfoGlobalById($stu_id);
		$receipt_number = $this->getRecieptNo($data['branch_id']);
			try{
				$studentCode="";
				if($data['student_type']==1){//existing student
					$this->setStudentId($data);
				}elseif($data['student_type']==2){//testing student(from tested student)
					$studentCode = $this->addStudentFromToTesting($data);
				}elseif($data['student_type']==3){//from crm
					
					$_dbgb = new Application_Model_DbTable_DbGlobal();
					if(!empty($data['auto_test'])){
						$newSerial = $_dbgb->getTestStudentId($data['branch_id']);
						$arr = [
							'customer_type' =>4,
							'is_studenttest' =>1,
							'serial' => $newSerial,
							'create_date'=>$paid_date,
							'create_date_stu_test'=>date("Y-m-d H:i:s"),
						];
						$this->_name='rms_student';
						$where="stu_id = ".$stu_id;
						$this->update($arr, $where);
					}
				}
				
				
				$data['credit_memo'] = empty($data['credit_memo'])?0:$data['credit_memo'];
				
				$cut_credit_memo = $data['grand_total']-$data['credit_memo'];
				if($cut_credit_memo<0){
					$credit_after=($data['grand_total']<0) ? 0 :abs($cut_credit_memo);
					$cut_credit_memo = ($data['grand_total']<0?0:$data['grand_total']);
				}else{
					$cut_credit_memo = $data['credit_memo'];
					$credit_after = 0;
				}

				$this->_name='rms_student_payment';
				
				$arr = array(
						'is_current'=>0//update old receipt
				);
				$where="student_id=".$data['old_stu'];
				$this->update($arr,$where);
				$data['study_year'] = empty($data['study_year'])?0:$data['study_year'];
				$result = $gdb->getFeeStudyinfoByIdGloable($data['study_year']);
				$academicYearId = empty($result) ? '' : $result['academicYearId'];
				
				$arr=[
					'branch_id'		=> $data['branch_id'],
					'revenue_type'  => $data['customer_type'],
					'data_from'		=> $data['student_type'],
					'student_id'	=> $data['old_stu'],
					'receipt_number'=> $receipt_number,
					'penalty'		=> $data['penalty'],
					'grand_total'	=> $data['grand_total'],
					'credit_memo'	=> $cut_credit_memo,
					'paid_amount'	=> $data['paid_amount'],   
					'balance_due'	=> $data['balance_due'],
					'amount_in_khmer'=> $data['money_in_khmer'],
					'payment_method'=> $data['payment_method'],
					'bank_id'		=> $data['bank_name'],
					'number'	    => $data['number'],
					'note'			=> $data['note'],
					'create_date'	=> $paid_date,
					'entryDate'		=> date("Y-m-d H:i:s"),
					'user_id'		=> $this->getUserId(),
					'academic_year'	=> $data['study_year'],
					'academicYearId'=> $academicYearId,
					'paystudent_type'=>$rs_stu['is_stu_new'],//
					'group_id'		=> empty($data['groupId']) ? 0 : $data['groupId'],
					'degree'		=> $rs_stu['degree'],
					'grade'			=> $rs_stu['grade'],
					'degree_culture'=> $rs_stu['calture'],
					'is_current'=> $data['balance_due']>0?1:0,//1 ជំពាក់
				];
				$paymentid = $this->insert($arr);//inser to payment

				// $paramStu=[
				// 		'stuId'=>$stu_id,
				// 		'stuCode'=>$studentCode,
				// 		'schoolOption'=>!empty($rs_stu['school_option'])?$rs_stu['school_option']:0,
				// 		'acadedmicYear'=>!empty($rs_stu['academic_year'])?$rs_stu['academic_year']:0,
				// 		'degree'=>!empty($rs_stu['degree'])?$rs_stu['degree']:0,
				// 		'isUse'=>1,
				// 		'isCurrent'=>1,
				// 		'createDate'=>date("Y-m-d H:i:s"),
				// 		'modifyDate'=>date("Y-m-d H:i:s"),
				// 		'userId'=>$this->getUserId(),
				// 		'referenceType'=>1,//1=from pmg,2= ឆ្លងភូមិសិក្សា, 3=Change Branch',
				// 		'referenceId'=>$paymentid
				// 	];
				// $dbstu = new Application_Model_DbTable_DbStudentCode();
				// $dbstu->insertStudentCode($paramStu);//for insert stucode to table

				if($data['credit_memo']>0){
					$this->_name='rms_student';
					$creditData= [
						'walletBalance'=>$credit_after,
					];
					$where = " stu_id = ".$stu_id;
					$this->update($creditData, $where);

					$dbcr = new Accounting_Model_DbTable_DbCreditmemo;
					$transactionNo  = $dbcr->generateTransactionNo($data);
					
					$arr = array(
							'transaction_no'	=>$transactionNo,
							'fromTransactionId'		=>$paymentid,
							'branch_id'		=>$data['branch_id'],
							'student_id'	=>$stu_id,
							'date'			=>date("Y-m-d H:i:s"),
							'type'			=>2,//from payment
							'cashType'		=>2,//cash out
							'note'			=>'from payment',
							'walletBalance'		=>$data['credit_memo'],
							'walletBalanceAfter'=>$credit_after,
							'total_amount'		=>$cut_credit_memo,
							'total_amountafter'	=>$cut_credit_memo,
							'status'			=>1,
							'user_id'			=>$this->getUserId(),
							'createDate'		=>date("Y-m-d H:i:s"),
							'modifyDate'		=>date("Y-m-d H:i:s")
						);
						$this->_name="rms_creditmemo";
						$this->insert($arr);
				}
		
				/*alert ទៅទូរសព្ទដៃអាណាព្យាបាលសិស្ស*/
// 					$dbpush = new  Application_Model_DbTable_DbGlobal();
// 					$dbpush->getTokenUser(null,$id, 1);
				
				// $key = new Application_Model_DbTable_DbKeycode();
				// $keydata=$key->getKeyCodeMiniInv(TRUE);

				$condictionSale = Setting_Model_DbTable_DbGeneral::geValueByKeyName('sale_cut_stock');
// 				$condictionSale = empty($stockSetting)?0:$stockSetting;//0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive
				
				$cut_id=0;
				$totalQty=0;
				
				$hasProduct=0;
				$ids = explode(',', $data['identity']);
				$dbitem = new Global_Model_DbTable_DbItemsDetail();
				if(!empty($ids))foreach ($ids as $i){
					
					$spd_id = $this->getStudentPaymentStart($data['old_stu'], $data['item_id'.$i],1);
					$this->_name="rms_student_paymentdetail";
					if(!empty($spd_id)){
						$arr = array(
								'is_start'=>0
						);
						$where=" id = ".$spd_id;
						$this->update($arr,$where);
					}
					
					$extraCondiction = [
						"forCheckIssuePayment"=>1
					];
					$rs_item = $dbitem->getItemsDetailById($data['item_id'.$i],null,1,$extraCondiction);
					if(!empty($rs_item)){

						$data['date_start_'.$i]=($data['date_start_'.$i])=='01/01/1970'?'':$data['date_start_'.$i];
						$data['validate_'.$i]=($data['validate_'.$i])=='01/01/1970'?'':$data['validate_'.$i];
						
						$_arr = [
								'payment_id'	=>$paymentid,
								'feeId'			=>$data['academic_year_'.$i],
								'service_type'	=>$rs_item['items_type'],
								'itemdetail_id'	=>$data['item_id'.$i],
								'payment_term'	=>empty($data['term_'.$i]) ? 0 : $data['term_'.$i],// underfined when submit product
								'fee'			=>$data['price_'.$i],
								'qty'			=>$data['qty_'.$i],
								'qty_balance'	=>$data['qty_'.$i],
								'subtotal'		=>$data['subtotal_'.$i],
								'extra_fee'		=>$data['extra_fee'.$i],
								'discount_type'	=>empty($data['discount_type'.$i]) ? 0 : $data['discount_type'.$i],// underfined when submit product
								'discount_percent'=>$data['discount_'.$i],
								'discount_amount'=>$data['discount_amount'.$i],
								'totalpayment'	=>$data['total_amount'.$i],
								'paidamount'	=>$data['paid_amount'.$i],
								'is_onepayment'	=>($data['term_'.$i]==5)?1:0,//5 = one payment
								'start_date'	=>($data['term_'.$i]==5)?'':$data['date_start_'.$i],
								'validate'		=>($data['term_'.$i]==5)?'':$data['validate_'.$i],
								'is_start'		=>1,
								'note'			=>trim($data['remark'.$i]),
								'is_parent'     =>$spd_id,
								'academicFeeTermId'=>$data["term_study".$i],
							];
						if($rs_item['items_type']==1){
							$arrFilter =[
								'studentId'=>$data['old_stu'],
								'itemDetailId' => $data['item_id'.$i],
							]; 
							$lastPaidTuition = $gdb->getStuLastPaidTutionFee($arrFilter);
							if(!empty($lastPaidTuition)){
								$_arr["previous_startdate"] = $lastPaidTuition["start_date"];
								$_arr["previous_enddate"] = $lastPaidTuition["validate"];
							}
						}
						$this->_name="rms_student_paymentdetail";
						$paymentDetailId = $this->insert($_arr);
						
						if ($condictionSale!=1){//cut ready 
							if($hasProduct!=1){
								if($rs_item['items_type']==3){
									$dbstock = new Stock_Model_DbTable_DbCutStock();
									$itemsCode = $dbstock->getCutStockode($data['branch_id']);
									$_arr=[
											'branch_id'	   => $data['branch_id'],
											'paymentId'	   => $paymentid,
											'serailno'	   => $itemsCode,
											'student_id'   => $data['old_stu'],
											'balance'      => 0,
											'total_qty_due' => 0,
											'received_date' => $paid_date,
											'create_date'   => $paid_date,//date("Y-m-d H:i:s"),
											'modify_date'	=> date("Y-m-d H:i:s"),
											'status'        => 1,
											'note'			=>'Cut From Payment',
											'user_id'       => $this->getUserId(),
									];
									$this->_name ='rms_cutstock';
									$cut_id =  $this->insert($_arr);
									$hasProduct=1;
								}
							}
						}
					}
					
					$arr = [
						'feeId'=>$data['academic_year_'.$i],
						'startDate'=>$data['date_start_'.$i],
						'endDate'=>$data['validate_'.$i],
					];
					
					$balance = $data['total_amount'.$i]-$data['paid_amount'.$i];
					if($balance>0){
						$arr['balance']=$balance;
						$arr['isoldBalance']=1;
					}
					elseif($balance==0 AND $data['isoldBalance'.$i]==1){
						$balance = 0;
						$arr['balance']=$balance;
						$arr['isoldBalance']=0;
					}
					$this->_name='rms_group_detail_student';
					$where = "stu_id=".$data['old_stu']." AND grade=".$data['item_id'.$i];
					if(!empty($rs_item)){
						if($rs_item['items_type']!=1){
							$where.= " AND is_current = 1 ";
						}
					}
					$this->update($arr, $where);
					
					if((!empty($rs_item) AND !empty($data['autoNextPay'.$i])) OR $rs_item['isExtraCourse']==1){
							if($rs_item['items_type']!=1){
								$arrF = [
									'studentId' => $data['old_stu'],
									'degree'	=> $rs_item['items_id'],
									'grade' 	=> $data['item_id'.$i],
									'isCurrent' => 1,
								];
								$stuItemInfo = $this->getStudentGroupDetailInfoByItems($arrF);
								if(!empty($stuItemInfo)){
									if($stuItemInfo["stop_type"]!=0){ // update is_current of stoped service
										$_arrStuItemInfo=array(
											'is_current'=>0,
										);
										$this->_name ='rms_group_detail_student';
										$whereStuItemInfo = "gd_id = ".$stuItemInfo["gd_id"];
										$this->update($_arrStuItemInfo, $whereStuItemInfo);
									}
								}
							}
						
							$_arr= [
								'branch_id'		=> $data['branch_id'],
								'studentId'		=> $data['old_stu'],
								'itemType'		=> $rs_item['items_type'],
								'grade'			=> $data['item_id'.$i],
								'degree'		=> $rs_item['items_id'],
								'feeId'			=> $data['academic_year_'.$i],
								'academic_year'	=> '',
								'startDate'		=> $data['date_start_'.$i],
								'endDate'		=> $data['validate_'.$i],
								'discountType'	=>'',
								'discountAmount'=>'',
								'balance'		=> $balance,
								'schoolOption'	=> $rs_item['schoolOption'],
								'isMaingrade'	=> 1,
								'isCurrent'		=> 1,
								'stopType'		=> 0,
								'status'		=> 1,
								'isNewStudent'	=> 1,
								'remark'		=> trim($data['remark'.$i]),
								'create_date'	=> $paid_date,//date("Y-m-d H:i:s"),
								'user_id'		=> $this->getUserId(),
								'entryFrom'	=>4,//mean from payment
							];

							$_arr['isMaingrade']=!empty($rs_item['isExtraCourse'])?0:1;//for extra course
							$_arr['referenceId']=!empty($rs_item['isExtraCourse'])?$paymentid:"";//for extra course

						$gdb->AddItemToGroupDetailStudent($_arr);//to insert rms_group_detail_student Item
					}
					if ($condictionSale!=1){
						if(!empty($cut_id)){
							if($rs_item['items_type']==3){ // product
								$data['is_productseat'] = $rs_item['is_productseat'];
								$data['paymentId'] = $paymentid;
								$data['paymentDetailId'] = $paymentDetailId;
								$data['cutStockId'] = $cut_id;
								$data['costing'] = $rs_item['cost'];
								$data['conditionCutStock'] = $condictionSale;
								$totalQty = $this->cutStockDetail($data,$i);
							}
						}
					}
				}
				if ($condictionSale!=1){
					if(!empty($cut_id)){
						$dbstock = new Stock_Model_DbTable_DbCutStock();
						$itemsCode = $dbstock->getCutStockode($data['branch_id']);
						$_arr=[
							'balance'=>$totalQty,
							'total_received'=>$totalQty,
						];
						$this->_name ='rms_cutstock';
						$where = "id = ".$cut_id;
						$this->update($_arr, $where);
					}
				}
				//record data student code
				if($data['student_type']==2){
					$_dbStCode = new Application_Model_DbTable_DbStudentCode();
					$arrUp =[
						"referenceType" =>1,
						"referenceId" 	=>$paymentid,
						"stuCode" 		=>$studentCode,
						"stuId" 		=>$data['old_stu'],
						"branchId" 		=>$data['branch_id'],
						"acadedmicYear"	=>$academicYearId,
						"degree" 		=>$rs_stu['degree'],
					];
					$_dbStCode->insertStudentCode($arrUp);
				}
				
				$db->commit();

				$stuResult = $gdb->getStudentinfoGlobalById($stu_id);
				$stuResult['receipt_number'] = $receipt_number;
				$cut_id = empty($cut_id) ? 0 : $cut_id;
				$stuResult['cutStockId'] = $cut_id;
				
				
				
				return $stuResult;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	function updatePaymentInfoBack($payment_id,$studentId){
		$db = $this->getAdapter();
		$sql="SELECT 
				pd.is_parent,
				pd.service_type,
				pd.previous_startdate,
				pd.previous_enddate,
				pd.itemdetail_id 
			FROM 
				rms_student_payment p
				JOIN rms_student_paymentdetail pd
				ON p.`id` = pd.`payment_id` 
		WHERE pd.payment_id = $payment_id ";
		$id_old_record = $db->fetchAll($sql);

		if(!empty($id_old_record)){
			foreach ($id_old_record as $result){
				if($result['is_parent']>0 && $result['service_type'] != 4){
					$this->_name="rms_student_paymentdetail";
					$array = array(
						'is_start'=>1,
					);
					$where = " id = ".$result['is_parent'];
					$this->update($array, $where);
				}

				$arr = array(
					'startDate'=>$result['previous_startdate'],
					'endDate'=>$result['previous_enddate'],
				);
				$this->_name='rms_group_detail_student';
				$where = "stu_id=".$studentId." AND grade=".$result['itemdetail_id'];
				$this->update($arr, $where);
			}
		}			
	}
	
	
	
	
    function getAllStudentRegister($search=null){
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	
    	$branch_id = $_db->getAccessPermission('sp.branch_id');
    	$db=$this->getAdapter();
    	
    	$lang = $_db->currentlang();
		$branch = $_db->getBranchDisplay();
		$label=($lang==1 ? "name_kh" : "name_en");
		
    	$sql=" SELECT 
    				sp.id,
					b.$branch branch_name,
    				sp.receipt_number,
	    			(
						CASE 
							WHEN sp.data_from=3 THEN s.serial 
							ELSE s.stu_code 
						END) AS stu_code,
	    			(CASE 
						WHEN s.stu_khname IS NULL OR s.stu_khname='' THEN s.stu_enname 
						ELSE s.stu_khname 
					END) AS stu_khname,
					v_gender.shortcut AS gender,
					CONCAT(ay.fromYear,'-',ay.toYear,'(',tf.generation,')') AS YEAR,
	    			 sp.grand_total,
					 sp.credit_memo,
					 sp.paid_amount,
					 sp.balance_due,
					v_payment.$label AS payment_method, 
					`number`,
					sp.create_date,
					u1.first_name AS user,
					v_void.$label AS void_label,
					sp.is_void,
					u2.first_name AS void_by
					,COALESCE(bk.`bank_name`,'') AS bankName
 			   FROM 
    				rms_student AS s INNER JOIN
					rms_student_payment AS sp ON s.stu_id=sp.student_id 
					LEFT JOIN rms_branch b ON b.br_id=s.branch_id
					LEFT JOIN rms_tuitionfee AS tf ON tf.id=sp.academic_year
					LEFT JOIN rms_academicyear AS ay ON ay.id=sp.academicYearId
					LEFT JOIN rms_view AS v_payment ON v_payment.type = 8 AND v_payment.key_code = sp.payment_method
					LEFT JOIN rms_view AS v_gender ON v_gender.type = 2 AND v_gender.key_code = s.sex
					LEFT JOIN rms_view AS v_void ON v_void.type = 10 AND v_void.key_code = sp.is_void
					LEFT JOIN rms_users AS u1 ON u1.id = sp.user_id
					LEFT JOIN rms_users AS u2 ON u2.id = sp.void_by
					LEFT JOIN `rms_bank` bk ON bk.id = sp.`bank_id`
				WHERE 
					1
					$branch_id ";
    	
	    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
	    	$where = " AND ".$from_date." AND ".$to_date;
    	
	    	if(!empty($search['adv_search'])){
	    		$s_where=array();
	    		$s_search=addslashes(trim($search['adv_search']));
	    		$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
	    		
	    		$s_where[]= " REPLACE(stu_code,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(serial,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(receipt_number,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(s.stu_khname,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(s.stu_enname,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(s.last_name,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]=	" REPLACE(CONCAT(s.last_name,s.stu_enname),' ','') LIKE '%{$s_search}%'";
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
	    	if(($search['branch_id']>0)){
	    		$where.= " AND sp.branch_id = ".$search['branch_id'];
	    	}
	    	if(($search['pmtStatus']>-1)){
				$where.=" AND sp.is_void=".$search['pmtStatus'];
			}
	    	if(!empty($search['study_year'])){
	    		$where.=" AND sp.academic_year=".$search['study_year'];
	    	}
	    	if(!empty($search['userId'])){
	    		$where.=" AND sp.user_id=".$search['userId'];
	    	}
	    	$order=" ORDER BY sp.id DESC";
    		return $db->fetchAll($sql.$where.$order);
    }
   
    function getStudentInfoBalance($studentid){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
				  s.stu_id,
				  (SELECT id FROM rms_creditmemo WHERE student_id = $studentid AND total_amountafter>0 LIMIT 1) AS id,
				  (SELECT total_amountafter FROM rms_creditmemo WHERE student_id = $studentid AND total_amountafter>0 LIMIT 1) AS total_amountafter,
				  (SELECT SUM(sp.balance_due) FROM rms_student_payment AS sp WHERE sp.student_id=$studentid LIMIT 1 )AS balance
				FROM
				  rms_student AS s
				WHERE s.stu_id=$studentid LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
   
    public function getRecieptNo($branch=0){
    	$db = $this->getAdapter();
    	if($branch==0){
    		$_db = new Application_Model_DbTable_DbGlobal();
    		$branch_id = $_db->getAccessPermission();
    	}else{
    		$branch_id = " and branch_id = $branch ";
    	}
    	
    	$sql="SELECT count(id)  FROM rms_student_payment where 1 $branch_id LIMIT 1 ";
    	$payment_no = $db->fetchOne($sql);
    	
    	$sql1="SELECT count(id)  FROM ln_income where 1 $branch_id LIMIT 1 ";
    	$income_no = $db->fetchOne($sql1);
    	
    	$new_acc_no= (int)$payment_no + (int)$income_no +  1;
		$recieptStart = 0;//psis=-506;
    	$new_acc_no = $new_acc_no+$recieptStart;//for psis
    	
		$pre="";
		$lenghtFormat = 5;
		
		$settingFor = 100; //PSIS CHV
		if($settingFor==100){
			$lenghtFormat = 6;
			$branch = empty($branch) ? 0 : $branch;
			$_dbGb = new Application_Model_DbTable_DbGlobal();
			$rs= $_dbGb->getPrefixCode($branch); 
			if(!empty($rs)){
				$pre.=$rs."-";
			}
		}
		
		
    	$acc_length = strlen((int)$new_acc_no+1);
    	for($i = $acc_length;$i<$lenghtFormat;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	
	function getStudentPaymentHistory($data){
		$db = $this->getAdapter();
		$_db  = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$label = "name_kh";
			$grade = "title";
		}else{ // English
			$label = "name_en";
			$grade = "title_en";
		}
		$sql="SELECT 
				  sp.receipt_number,
				  sp.balance_due as balance,	
				  sp.is_void,
				  DATE_FORMAT(sp.create_date, '%d-%m-%Y') AS create_date ,
    			  spd.payment_id,
				  spd.fee,
				  spd.qty,
				  spd.subtotal,
				  spd.extra_fee,
				  spd.discount_percent,
				  spd.discount_amount,
				  ds.discountCode,
				  spd.paidamount,	
				  spd.note,
				  spd.is_onepayment,
				  DATE_FORMAT(spd.start_date, '%d-%m-%Y') AS start_date,
				  DATE_FORMAT(spd.validate, '%d-%m-%Y') AS validate,
				  item.$grade AS item_name,
				  v1.$label AS payment_term,
				  v2.$label AS void_status,
				  st.title AS term_installment,
				  u.first_name AS user_name
			FROM 
    				rms_student_payment AS sp JOIN rms_student_paymentdetail AS spd ON sp.id=spd.payment_id
					LEFT JOIN rms_itemsdetail AS item ON item.id=spd.itemdetail_id
					LEFT JOIN rms_dis_setting AS ds ON ds.id=spd.discount_type
					LEFT JOIN rms_view AS v1 ON v1.type=6 AND v1.key_code=spd.payment_term
					LEFT JOIN rms_view AS v2 ON v2.type=10 AND v2.key_code=sp.is_void
					LEFT JOIN rms_startdate_enddate AS st ON st.id=spd.academicFeeTermId
					LEFT JOIN rms_users AS u ON u.id=sp.user_id
    			WHERE 
					1 ";
		if(!empty($data['studentId'])){
			$sql.=" AND sp.student_id =".$data['studentId'];
		}
		$sql.=" ORDER BY sp.create_date DESC ";
		$results =  $db->fetchAll($sql);
		
		if(!empty($data['returnHtml'])){
			$str = '';
			if(!empty($results)){
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$str.='<table class="collape tablesorter" style="white-space: nowrap;">'
						.'<thead><tr class="head-td" style="background:#d3e3fd;font-size:10px;color:#000"><th class="tdheader">'.$tr->translate("N_O").'</th>'
						.'<th class="tdheader">'.$tr->translate('SERVICES').'</th>'
						.'<th class="tdheader">'.$tr->translate('PAID_DATE').'</th>'
						.'<th class="tdheader">'.$tr->translate('RECEIPT_NO').'</th>'
						.'<th class="tdheader">'.$tr->translate('PAYMENT_TERM').'</th>'
						.'<th class="tdheader">'.$tr->translate('QTY').'</th>'
						.'<th class="tdheader">'.$tr->translate('PRICE').'</th>'
						.'<th class="tdheader">'.$tr->translate('SUBTOTAL').'</th>'
						.'<th class="tdheader">'.$tr->translate("OTHER").'</th>'
						.'<th class="tdheader">'.$tr->translate('DISCOUNT_TYPE').'</th>'
						.'<th class="tdheader">'.$tr->translate('DISCOUNT').'</th>'
						.'<th class="tdheader">'.$tr->translate('PAID').'</th>'
						.'<th class="tdheader">'.$tr->translate('BALANCE').'</th>'
						.'<th class="tdheader">'.$tr->translate('VALIDATE').'</th>'
						.'<th class="tdheader">'.$tr->translate('NOTE').'</th>'
						.'<th class="tdheader">'.$tr->translate('STATUS').'</th>'
						.'<th class="tdheader">'.$tr->translate('USER').'</th>'
				.'</thead>';
				$url = Zend_Controller_Front::getInstance()->getBaseUrl();
				foreach($results as $key=> $result){
					$str.= '<tr style="background-color:none;font-size:11px;text-align:center;" class="hover">';
						
						$payment_term = !empty($result['term_installment'])?$result['term_installment']:$result['payment_term'];
						$validate ='';
						$validate = ($result['is_onepayment']!=1 AND !empty($result['start_date']) AND !empty($result['validate'])) ? $result['start_date'].'/'.$result['validate']:"";
						$discount_percent=($result['discount_percent']>0)?str_replace(".00","",$result['discount_percent'])."%":"";
						$discount_amount=($result['discount_amount']>0)?$result['discount_amount']:"";
						$sign = ($discount_percent!="" AND $discount_amount!="")?"+":"";

						$str.= '<td>'.($key+1).'</td>';
						$str.= '<td style="text-align:left;"><label class="notedDescription">'.$result['item_name'].'</label></td>';
						$str.= '<td>'.$result['create_date'].'</td>';
						$str.= '<td style="text-align:left;"><a target="_blank" href="'.$url.'/allreport/accounting/rptreceiptdetail/id/'.$result['payment_id'].'">'.$result['receipt_number'].'</a></td>';
						$str.= '<td style="text-align:left;">'.$payment_term.'</td>';
						$str.= '<td>'.str_replace(".00","",$result['qty']).'</td>';
						$str.= '<td>'.str_replace(".00","",$result['fee']).'</td>';
						$str.= '<td>'.str_replace(".00","",$result['subtotal']).'</td>';
						$str.= '<td>'.str_replace(".00","",$result['extra_fee']).'</td>';
						$str.= '<td>'.$result['discountCode'].'</td>';
						$str.= '<td>'.$discount_percent.$sign.$discount_amount.'</td>';
						$str.= '<td><strong>'.number_format($result['paidamount'],0).' </strong><small class="usdright">$ </small></td>';
						$str.= '<td>'.number_format($result['balance'],0).'</td>';
						$str.= '<td>'.$validate.'</td>';
						$str.= '<td>'.$result['note'].'</td>';
						$str .= '<td class="' . ($result['is_void'] == 1 ? 'red' : 'normal') . '">' . $result['void_status'] . '</td>';
						$str.= '<td style="text-align:left;">'.$result['user_name'].'</td>';
					$str.="</tr>";
				}
			}
			return $str;
		}
		return $results;
	}

// 	function updateRegister($data,$payment_id){
// 		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
// 		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
	
// 		if($data['void']==1){  // void
// 			try{
// 				$rsold = $this->getStudentPaymentByID($payment_id);
// 				if($rsold['data_from']!=1){ // not student study payments
// 					if($rsold['data_from']==2){
// 						$arr = array(
// 								'is_registered'=>0,
// 						);
// 						$this->_name='rms_student_test_result';
// 						$where="stu_test_id = ".$data['old_stu']." AND degree_result=".$rsold['degree_id']." AND grade_result=".$rsold['grade'];
// 						$this->update($arr, $where);//reverse to tested student
	
// 						$arr = array(
// 								'customer_type'=>4 //reverse to tested student
// 						);
// 					}elseif($rsold['data_from']==3){
// 						$arr = array(
// 								'customer_type' =>3, //reverse to crm
// 								'is_studenttest' =>0,
// 						);
// 					}
// 					$this->_name='rms_student';
// 					$where='stu_id = '.$data['old_stu'];
// 					$this->update($arr, $where);
// 				}
// 				// update payment and validate of service and tuition fee info back ,  and update stock back to origin
// 				if($rsold['is_void']==0){
// 					$this->updatePaymentInfoBack($payment_id,1);   // 1 is pay for both service and tuition fee
// 				}
	
// 				$this->_name='rms_student_payment';
// 				$arra=array(
// 						'is_void'=>$data['void'],
// 						'void_by'=>$this->getUserId(),
// 						'void_note'=>$data['void_note'],
// 				);
// 				$where = " id = ".$payment_id;
// 				$this->update($arra, $where);
					
// 				if(!empty($data['credit_memo_id']) && $rsold['is_void']==0){//check again because it old code
// 					$this->updateCreditMemoBack($data);
// 				}
	
// 				$key = new Application_Model_DbTable_DbKeycode();
// 				$keydata=$key->getKeyCodeMiniInv(TRUE);
// 				$condictionSale = empty($keydata['sale_cut_stock'])?0:$keydata['sale_cut_stock'];//0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive
// 				if ($condictionSale!=1){
// 					$sql="SELECT sd.* FROM `rms_saledetail` AS sd WHERE sd.payment_id =$payment_id";
// 					$saleDetail = $db->fetchAll($sql);
// 					if (!empty($saleDetail)) foreach ($saleDetail as $rs){
// 						//Qurey Cut Stock Detail
// 						$sql = "SELECT cd.* FROM `rms_cutstock_detail` AS cd WHERE cd.`student_paymentdetail_id` =".$rs['id'];
// 						$cutDetail = $db->fetchAll($sql);
// 						$qtyReceive = 0;
// 						if (!empty($cutDetail)) foreach ($cutDetail as $cut){
// 							$qtyReceive = $qtyReceive+$cut['qty_receive'];
// 							//Void All This Payment Cut Stock
// 							$_arr=array(
// 									'status'	      => 0,
// 									'user_id'  =>$this->getUserId(),
// 									'modify_date'	  => date("Y-m-d H:i:s"),
// 							);
// 							$this->_name ='rms_cutstock';
// 							$where = ' id = '.$cut['cutstock_id'];
// 							$this->update($_arr, $where);
// 						}
// 						//Update Sale Detial back
// 						$_arr=array(
// 								'qty_after'	      => ($rs['qty_after']+$qtyReceive),
// 						);
// 						$this->_name ='rms_saledetail';
// 						$where = ' id = '.$rs['id'];
// 						$this->update($_arr, $where);
	
// 						$dbpu = new Stock_Model_DbTable_DbPurchase();
// 						$dbpu->updateStock($rs['pro_id'],$data['branch_id'],+$qtyReceive);
// 					}
// 				}
	
// 				// 				$where = " payment_id = $payment_id";
// 				// 				$this->_name='rms_saledetail';
// 				// 				$this->delete($where);
	
// 				$db->commit();
// 				return 0;
// 			}catch (Exception $e){
// 				Application_Form_FrmMessage::message("UPDATE_FAIL");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 				$db->rollBack();
// 				exit();
// 			}
// 		}
// 	}

	function getPrevioustPaymentDetail($data){
		$db = $this->getAdapter();
		
		$_dbGb  = new Application_Model_DbTable_DbGlobal();
		$paymentId = empty($data["paymentId"]) ?0 : $data["paymentId"];
		$studentId = empty($data["studentId"]) ? 0 : $data["studentId"];
		$sql="
			SELECT
				sp.id
			FROM rms_student_payment as sp
			WHERE sp.is_void = 0 
				AND sp.id < $paymentId 
				AND sp.student_id = $studentId 
			";
		$sql.= $_dbGb->getUserAccessPermission('sp.branch_id');
		$sql.= " ORDER BY sp.id DESC LIMIT 1 ";
		$previousRecord = $db->fetchOne($sql);
		
		if(!empty($previousRecord)){
			$sqlPrev="
				SELECT 
					pd.*
					,p.academic_year AS feeId
				FROM `rms_student_paymentdetail` AS pd
						JOIN `rms_student_payment` AS p  ON p.`id` = pd.`payment_id` 
				WHERE 
						p.`status` = 1
						AND p.`is_void` = 0
						AND p.`student_id` = $studentId  					
			";
			$sqlPrev.=" AND p.`id` = ".$previousRecord;
			$sqlPrev.=" ORDER BY pd.id DESC";
			return $db->fetchAll($sqlPrev);
		}
		return null;
	}
	
	function updateRegister($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
			try{
				
				$payment_id = $data['id'];
				$rsold = $this->getStudentPaymentByID($payment_id);
				if(empty($rsold)){
					return 2;//not permission
				}
				if($rsold['is_void']==1){
					return 3;//ready
				}
				$data['void_note']=$data['reason'];
				$data['branch_id']=$rsold['branch_id'];
				$data['void']=1;
				
				$studentId=$rsold['stu_id'];
				$data['credit_memo_id']= $rsold['memo_id'];

				$where="entryFrom=4 AND stu_id=".$studentId." AND referenceId=".$payment_id;
				$this->_name="rms_group_detail_student";
				$this->delete($where);
				
				if(!empty($rsold)){
					
					$arr = [
						'walletBalance' => $rsold['walletBalance']+$rsold['credit_memo'],
					];
					$where = " stu_id = ".$studentId;
					$this->_name='rms_student';
					$this->update($arr, $where);
					
					if(!empty($rsold['credit_memo'])){
						$dbcr = new Accounting_Model_DbTable_DbCreditmemo;
						$transactionNo  = $dbcr->generateTransactionNo();
						
						$arr = array(
							'transaction_no'	=>$transactionNo,
							'fromTransactionId'	=>$payment_id,
							'branch_id'		=>$data['branch_id'],
							'student_id'	=>$studentId,
							'date'			=>date("Y-m-d H:i:s"),
							'type'			=>2,//from payment
							'cashType'		=>1,//cash in
							'note'			=>'void receipt',
							'walletBalance'	=>$rsold['walletBalance']+$rsold['credit_memo'],
							'walletBalanceAfter'=>$$rsold['walletBalance']+$rsold['credit_memo'],
							'total_amount'	=>$rsold['credit_memo'],
							'total_amountafter'=>$rsold['credit_memo'],
							'status'		=>1,
							'user_id'		=>$this->getUserId(),
							'createDate'	=>date("Y-m-d H:i:s"),
							'modifyDate'	=>date("Y-m-d H:i:s")
						);
						$this->_name="rms_creditmemo";
						$this->insert($arr);
					}

					$lastPaymentRecord = $this->getLastStudentPaymentRecord($studentId);
					$lastPayId = empty($lastPaymentRecord['id'])?0:$lastPaymentRecord['id'];
					$voidOldreceipt = 0;
					if($lastPayId!=$payment_id){//void old receipt
						$voidOldreceipt=1;
					}
					
					$arrFilter =array(
						'paymentId'=>$payment_id,
						'studentId'=>$studentId,
					); 
					$prevPmtDetail = $this->getPrevioustPaymentDetail($arrFilter);
				
					if(!empty($prevPmtDetail)) foreach($prevPmtDetail AS $rowDe){
						$previousBalance = $rowDe["totalpayment"] - $rowDe["paidamount"];
						if($previousBalance>0){
							$arr = array(
								'feeId'=>$rowDe['feeId'],
								'startDate'=>$rowDe['start_date'],
								'endDate'=>$rowDe['validate'],
								'balance'=>$previousBalance,
								'isoldBalance'=>($previousBalance>0)?1:0,
							);
							$this->_name='rms_group_detail_student';
							$where = "stu_id=".$studentId." AND grade=".$rowDe['itemdetail_id'];
							$this->update($arr, $where);
						}
					}
		
					if($rsold['data_from']!=1 AND $voidOldreceipt==0){ // not student study payments
							
						if($rsold['data_from']==2){
							$arr = array(
								'is_registered'=>0,
							);
							$this->_name='rms_student_test_result';
							$where="stu_test_id = ".$studentId." AND degree_result=".$rsold['degree_id']." AND grade_result=".$rsold['grade'];
							$this->update($arr, $where);//reverse to tested student
							
							$_dbStCode = new Application_Model_DbTable_DbStudentCode();
							$arrUp =[
								"referenceType" =>1,
								"referenceId" 	=>$payment_id,
								"stuId" 		=>$studentId,
								"branchId" 		=>$data['branch_id'],
							];
							$_dbStCode->reverseStudCodeOfStudent($arrUp);
							
							$arr = [
								'customer_type'=>4,
								'stu_code'=>null,
							];//reverse to tested student];
		
						}elseif($rsold['data_from']==3){
		
							$arr = array(
								'customer_type' =>3, //reverse to crm
								'is_studenttest' =>0,
							);
						}
							
						$this->_name='rms_student';
						$where='stu_id = '.$studentId;
						$this->update($arr, $where);
					}
				
					// update payment and validate of service and tuition fee info back ,  and update stock back to origin
					if($rsold['is_void']==0 AND $voidOldreceipt==0){
						$this->updatePaymentInfoBack($payment_id,$studentId);   // 1 is pay for both service and tuition fee
					}
		
					$this->_name='rms_student_payment';
					$arra=array(
							'is_void'=>$data['void'],
							'void_by'=>$this->getUserId(),
							'void_note'=>$data['void_note'],
							'voidDate'	=> date("Y-m-d H:i:s"),
					);
					$where = " id = ".$payment_id;
					$this->update($arra, $where);
		
					$key = new Application_Model_DbTable_DbKeycode();
					$keydata=$key->getKeyCodeMiniInv(TRUE);
					$condictionSale = empty($keydata['sale_cut_stock'])?0:$keydata['sale_cut_stock'];//0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive
					if ($condictionSale!=1){
						$sql="SELECT sd.* FROM `rms_saledetail` AS sd WHERE sd.payment_id =$payment_id";
						$saleDetail = $db->fetchAll($sql);
						if (!empty($saleDetail)) foreach ($saleDetail as $rs){
							//Qurey Cut Stock Detail
							$sql = "SELECT cd.* FROM `rms_cutstock_detail` AS cd WHERE cd.`student_paymentdetail_id` =".$rs['id'];
							$cutDetail = $db->fetchAll($sql);
							$qtyReceive = 0;
							if (!empty($cutDetail)) foreach ($cutDetail as $cut){
								$qtyReceive = $qtyReceive+$cut['qty_receive'];
								//Void All This Payment Cut Stock
								$_arr=array(
										'status'	      => 0,
										'user_id'  =>$this->getUserId(),
										'modify_date'	  => date("Y-m-d H:i:s"),
								);
								$this->_name ='rms_cutstock';
								$where = ' id = '.$cut['cutstock_id'];
								$this->update($_arr, $where);
							}
							//Update Sale Detial back
							$_arr=array(
									'qty_after'	  => ($rs['qty_after']+$qtyReceive),
							);
							$this->_name ='rms_saledetail';
							$where = ' id = '.$rs['id'];
							$this->update($_arr, $where);
		
							$dbpu = new Stock_Model_DbTable_DbPurchase();
							$dbpu->updateStock($rs['pro_id'],$data['branch_id'],+$qtyReceive,0,$payment_id,5);
						}
					}
		
					$db->commit();
					return 1;
				}
			}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				$db->rollBack();
			}
	}
	
	function getStudentPaymentByID($id){
		$db=$this->getAdapter();
		$sql="SELECT
		sp.*,
		s.stu_enname,
		s.stu_khname,
		s.sex,
		s.stu_code,
		s.stu_id,
		s.walletBalance,
		sp.degree as degree_id,
		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=sp.degree AND rms_items.type=1 LIMIT 1) AS degree,
		(SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.itemType=1 AND sgh.stu_id = sp.`student_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id,
		(SELECT first_name from rms_users as u where u.id=sp.user_id  LIMIT 1) as first_name,
		(SELECT last_name from rms_users as u where u.id=sp.user_id  LIMIT 1) as last_name
		FROM
		rms_student_payment as sp,
		rms_student as s
		WHERE
		s.stu_id = sp.student_id
		AND sp.id=$id AND is_closed=0 ";
		return $db->fetchRow($sql);
	}
	function getLastStudentPaymentRecord($stuId){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    			sp.*,
	    		s.stu_enname,
	    		s.stu_khname,
	    		s.sex,
	    		s.stu_code,
	    		s.stu_id,
				sp.degree as degree_id,
	    		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=sp.degree AND rms_items.type=1 LIMIT 1) AS degree,
	    		(SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.itemType=1 AND sgh.stu_id = sp.`student_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id,
	    		(SELECT first_name from rms_users as u where u.id=sp.user_id  LIMIT 1) as first_name,
	    		(SELECT last_name from rms_users as u where u.id=sp.user_id  LIMIT 1) as last_name
    		FROM
    		  	rms_student_payment as sp,
    		  	rms_student as s
    		WHERE 
    			s.stu_id = sp.student_id
    			AND sp.student_id=$stuId AND sp.is_closed=0 AND sp.is_void=0 ";
		$sql.=" ORDER BY sp.id DESC ";
		$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
	
	function getStudentGroupDetailInfoByItems($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
				gs.*
			FROM
				`rms_group_detail_student` AS gs,
				rms_student as st
			WHERE 	st.stu_id=gs.stu_id ";
		if (!empty($data['item_type'])) {
			$sql .= " AND gs.`itemType` = " . $data['item_type'];
		}
		if (!empty($data['group_id'])) {
			$sql .= " AND gs.`group_id` = " . $data['group_id'];
		}
		if (!empty($data['studentId'])) {
			$sql .= " AND gs.`stu_id` = " . $data['studentId'];
		}
		if (isset($data['isMaingrade'])) {
			$sql .= " AND gs.`is_maingrade` = " . $data['isMaingrade'];
		}
		if (isset($data['isCurrent'])) {
			$sql .= " AND gs.`is_current` = " . $data['isCurrent'];
		}
		if (!empty($data['degree'])) {
			$sql .= " AND gs.`degree` = " . $data['degree'];
		}
		if (!empty($data['grade'])) {
			$sql .= " AND gs.`grade` = " . $data['grade'];
		}
		if (isset($data['stopType'])) {	//0 = normal,1 stop ,2 suspend,3 = passed,4 graduate
			$sql .= " AND gs.`stop_type` = " . $data['stopType'];
		}
		if (!empty($data['groupId'])) {
			$sql .= " AND gs.`group_id` = " . $data['groupId'];
		}
		if (!empty($data['branchId'])) {
			$sql .= " AND s.`branch_id` = " . $data['branchId'];
		}
		$sql .= " LIMIT 1 ";
		return $db->fetchRow($sql);
	}

}