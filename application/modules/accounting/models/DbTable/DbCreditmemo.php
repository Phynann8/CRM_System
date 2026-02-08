<?php
class Accounting_Model_DbTable_DbCreditmemo extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_creditmemo';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllStudentWalletBalance($search=null){
		$db = $this->getAdapter();
		$_db=new Application_Model_DbTable_DbGlobal();
		$branch= $_db->getBranchDisplay();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$currentLang = $_db->currentlang();
		$colunmname = "CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,''))";
		
		if ($currentLang == 1) {
			$colunmname = 's.stu_khname';
		}

		$sql="SELECT 
				s.stu_id
				,(SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id = s.branch_id LIMIT 1) AS branch_name
				,s.stu_code
				,$colunmname AS studentName
				,s.walletBalance as total_amount 
			FROM  rms_student AS s
			WHERE s.walletBalance > 0 AND s.status = 1 ";
		$str_date=' s.create_date ';
		$from_date =(empty($search['start_date']))? '1': " $str_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " $str_date <= '".$search['end_date']." 23:59:59'";
		
		$where = " AND ".$from_date." AND ".$to_date;
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " (SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = s.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.= " AND s.branch_id = ".$search['branch_id'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('s.branch_id');
		
		$order=" order by s.stu_id  DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllCreditmemo($search=null){
		$db = $this->getAdapter();
		$_db=new Application_Model_DbTable_DbGlobal();
		$branch= $_db->getBranchDisplay();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		//total_amountafter,
		//,c.end_date
		//,c.note
		//,(SELECT name_kh FROM rms_view WHERE rms_view.type=23 AND key_code=c.type LIMIT 1) AS paid_transfer
				
		$sql="SELECT 
				c.id
				,(SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id = c.branch_id LIMIT 1) AS branch_name
				,s.stu_code
				,s.stu_khname AS titleRecord
				,c.total_amount
				,c.date
				,CASE 
					WHEN c.creditType = 1 THEN '".$tr->translate("LIFE_TIME")."'
					ELSE c.end_date 
				END creditTypes
				,COALESCE(inc.invoice,'') AS incInvoice
				,c.createDate
				,(SELECT first_name FROM `rms_users` WHERE id=c.user_id LIMIT 1) AS user_name
				,c.status AS statusRecord
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS subTitleRecord
			  FROM 
					rms_creditmemo c 
					JOIN rms_student AS s ON s.stu_id = c.student_id
					LEFT JOIN ln_income AS inc ON inc.id = c.otherincome_id
			  WHERE c.type = 0 AND c.cashType = 1";
		$str_date=' c.date ';
		if(!empty($search['by_date'])==0){
		}else if($search['by_date']==1){//create
			$str_date=' c.date ';
		}else if($search['by_date']==2){//expired
			$str_date=' c.end_date ';
		}
		$from_date =(empty($search['start_date']))? '1': " $str_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " $str_date <= '".$search['end_date']." 23:59:59'";
		
		$where = " AND ".$from_date." AND ".$to_date;
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " (SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = c.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.= " AND c.branch_id = ".$search['branch_id'];
		}
		/*
		
		if($search['paid_transfer']>-1){
			$where.= " AND type = ".$search['paid_transfer'];
		}
		if($search['status']>-1){
			$where.= " AND c.status = ".$search['status'];
		}
		*/
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('c.branch_id');
		
		$order=" order by id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	function getStudentWalletInfo($data){
		$db = $this->getAdapter();
		$studentId = empty($data["studentId"]) ? 0 : $data["studentId"];
		$sql="SELECT 
				s.stu_id AS studentId
				,s.walletBalance
			FROM rms_student AS s
			WHERE s.stu_id=$studentId ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('s.branch_id');
		return $db->fetchRow($sql);
	}
	function cashInAndOutWallet($data){
		
		$cashType = empty($data["cashType"]) ? 1 : $data["cashType"];
		$stuInfo = $this->getStudentWalletInfo($data);
		$walletBalance =  empty($stuInfo["walletBalance"]) ? 0 : $stuInfo["walletBalance"];
		$totalAmount =  empty($data["totalAmount"]) ? 0 : $data["totalAmount"];
		
		if($cashType==1){
			$walletBalanceAfter = $walletBalance + $totalAmount;
		}else{
			$walletBalanceAfter = $walletBalance - $totalAmount;
		}
		
		$arrWallet = array(
			'walletBalance'		=>$walletBalanceAfter,
		);
		$this->_name="rms_student";
		$whereWallet=" stu_id = ".$data['studentId'];
		$this->update($arrWallet, $whereWallet);
		
		$arrReturn = array(
			'walletBalance'			=>$walletBalance,
			'walletBalanceAfter'	=>$walletBalanceAfter,
		);
		return $arrReturn;
	}
	function generateTransactionNo($data=[]){
		$db = $this->getAdapter();
		
		$pre = "";
		$sql="SELECT 
				COUNT(s.id) AS amt
			FROM rms_creditmemo AS s
			WHERE 1 ";
		$acc_no = $db->fetchOne($sql);
		
		$new_acc_no = (int) $acc_no + 1;
		$acc_no = strlen((int) $acc_no + 1);
		$lenght = 6;
		for ($i = $acc_no; $i < $lenght; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}
	function addCreditmemo($data){
		$db = $this->getAdapter();
		try{
// 		$sql="SELECT id FROM rms_creditmemo WHERE branch_id =".$data['branch_id'];
// 		$sql.=" AND student_id='".$data['studentId']."'";
// 		$rs = $db->fetchOne($sql);
// 		if(!empty($rs)){
// 			return -1;
// 		}
			
			$walletBalance =  0;
			$totalAmount =  empty($data["total_amount"]) ? 0 : $data["total_amount"];
			$data["totalAmount"] = $totalAmount;
			$data["cashType"] = 1;
			
			$stuInfo = $this->cashInAndOutWallet($data);
			$walletBalance =  empty($stuInfo["walletBalance"]) ? 0 : $stuInfo["walletBalance"];
			$walletBalanceAfter =  empty($stuInfo["walletBalanceAfter"]) ? 0 : $stuInfo["walletBalanceAfter"];
			
			$validDate = $data['end_date'];
			$creditType =  empty($data["creditType"]) ? 1 : $data["creditType"];
			if($creditType==1){
				$validDate = null;
			}
			$transactionNo  = $this->generateTransactionNo($data);
			$arr = array(
				'transaction_no'	=>$transactionNo,
				'branch_id'			=>$data['branch_id'],
				'student_id'		=>$data['studentId'],
				
				'total_amount'		=>$totalAmount,
				'total_amountafter'	=>$totalAmount,
				'walletBalance'			=>$walletBalance,
				'walletBalanceAfter'	=>$walletBalanceAfter,
				
				'note'			=>$data['Description'],
				'prob'			=>$data['prob'],
				'type'			=>0,
				'date'			=>date('Y-m-d H:i:s',strtotime($data['Date'])),
				'end_date'		=>$validDate,
				'creditType'	=>$creditType,
				'status'		=>1,
				'user_id'		=>$this->getUserId(),
				'createDate'	=>date("Y-m-d H:i:s"),
				'modifyDate'	=>date("Y-m-d H:i:s")
			);
			if(!empty($data['otherincome_id'])){
				$arr['otherincome_id']=$data['otherincome_id'];
				$arr['fromTransactionId']=$data['otherincome_id'];
			}
			$this->_name="rms_creditmemo";
			$this->insert($arr);
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
 	 }
	 function reverseCredit($data){
		 
		 if(!empty($data['otherincome_id'])){
			$otherIncomeId = empty($data['otherincome_id']) ? 0 : $data['otherincome_id'];
			$row = $this->getCreditmemoByIncomeId($otherIncomeId);
		 }else{
			$creditId = empty($data['id']) ? 0 : $data['id'];
			$row = $this->getCreditmemobyid($creditId);
		 }
		 $data["cashType"] = 2;
		 $data["totalAmount"] = empty($row["total_amount"]) ? 0 : $row["total_amount"];
		 $stuInfo = $this->cashInAndOutWallet($data);
	 }
	 function updatcreditMemo($data){
		 
			$this->reverseCredit($data);
			
			$walletBalance =  0;
			$totalAmount =  empty($data["total_amount"]) ? 0 : $data["total_amount"];
			$data["totalAmount"] = $totalAmount;
			$data["cashType"] = 1;
			
			$walletBalance = 0;
			$walletBalanceAfter = 0;
			
			$validDate = $data['end_date'];
			$creditType =  empty($data["creditType"]) ? 1 : $data["creditType"];
			if($creditType==1){
				$validDate = null;
			}
			$status = empty($data['status']) ? 0 : 1;
			$arr = array(
				'branch_id'		=>$data['branch_id'],
				'student_id'	=>$data['studentId'],
				
				'total_amount'		=>$totalAmount,
				'total_amountafter'	=>$totalAmount,
				'date'			=>date('Y-m-d H:i:s',strtotime($data['Date'])),
				
				'note'			=>$data['Description'],
				'prob'			=>$data['prob'],
				'status'		=>$status,
				'user_id'		=>$this->getUserId(),
				'modifyDate'	=>date("Y-m-d H:i:s")
			);
			
			if( $status==1 ){
				$stuInfo = $this->cashInAndOutWallet($data);
				$walletBalance =  empty($stuInfo["walletBalance"]) ? 0 : $stuInfo["walletBalance"];
				$walletBalanceAfter =  empty($stuInfo["walletBalanceAfter"]) ? 0 : $stuInfo["walletBalanceAfter"];
				
				$arr["walletBalance"] = $walletBalance;
				$arr["walletBalanceAfter"] = $walletBalanceAfter;
				$arr["end_date"] = $validDate;
			}
			
			if(!empty($data['otherincome_id'])){
				$where="otherincome_id = ".$data['otherincome_id'];
				$arr['fromTransactionId']=$data['otherincome_id'];
			}else{
				$where=" id = ".$data['id'];
			}
			$this->_name="rms_creditmemo";
			$this->update($arr, $where);
	}
	function getCreditmemobyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_creditmemo where id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		return $db->fetchRow($sql);
	}
	function getCreditmemoByIncomeId($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_creditmemo where otherincome_id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		return $db->fetchRow($sql);
	}
	
	function cashOutCreditmemo($data){
		$db = $this->getAdapter();
		try{
			$walletBalance =  0;
			$totalAmount =  empty($data["total_amount"]) ? 0 : $data["total_amount"];
			$data["totalAmount"] = $totalAmount;
			$data["cashType"] = 2;
			
			$stuInfo = $this->cashInAndOutWallet($data);
			$walletBalance =  empty($stuInfo["walletBalance"]) ? 0 : $stuInfo["walletBalance"];
			$walletBalanceAfter =  empty($stuInfo["walletBalanceAfter"]) ? 0 : $stuInfo["walletBalanceAfter"];
			
			$validDate = $data['end_date'];
			$creditType =  empty($data["creditType"]) ? 1 : $data["creditType"];
			if($creditType==1){
				$validDate = null;
			}
			$transactionNo  = $this->generateTransactionNo($data);
			$arr = array(
				'transaction_no'	=>$transactionNo,
				'branch_id'			=>$data['branch_id'],
				'student_id'		=>$data['studentId'],
				'cashType'			=>$data['cashType'],
				
				'total_amount'		=>$totalAmount,
				'total_amountafter'	=>$totalAmount,
				'walletBalance'			=>$walletBalance,
				'walletBalanceAfter'	=>$walletBalanceAfter,
				
				'note'			=>$data['Description'],
				'prob'			=>$data['prob'],
				'type'			=>0,
				'date'			=>date('Y-m-d H:i:s',strtotime($data['Date'])),
				'end_date'		=>$validDate,
				'creditType'	=>$creditType,
				'status'		=>1,
				'user_id'		=>$this->getUserId(),
				'createDate'	=>date("Y-m-d H:i:s"),
				'modifyDate'	=>date("Y-m-d H:i:s"),
				'fromTransactionId'			=>empty($data['fromTransactionId']) ? 0 : $data['fromTransactionId'],
			);
			$this->_name="rms_creditmemo";
			$this->insert($arr);
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
 	 }
	 
	function issueExpiredCredit($data){
		
		try{
			$currentDate = empty($data['currentDate']) ? date("Y-m-d") : $data['currentDate'];
			$dataSelector = empty($data['selector']) ?[] : $data['selector'];
			if(!empty($dataSelector)){
				foreach($dataSelector AS $creditId){
					echo $creditId;
					$row = $this->getCreditmemobyid($creditId);
					
					$end_dat=date("d-M-Y",strtotime($currentDate));
					$total_day=strtotime($end_dat)-strtotime($row['end_date']);
					$totalLateDay=$total_day/(60*60*24);
					
					if($totalLateDay>0){
						$arr = array(
								'isExpired'		=>1,
								'user_id'		=>$this->getUserId(),
								'modifyDate'	=>date("Y-m-d H:i:s")
							);
							
						$where=" id = ".$creditId;
						$this->_name="rms_creditmemo";
						$this->update($arr, $where);
						
						$totalAmount = $row["total_amount"];
						$arrCreditExpired = array(
							'branch_id'			=>$row['branch_id'],
							'studentId'		=>$row['student_id'],
							
							'total_amount'		=>$totalAmount,
							'Description'	=>"Expired credit",
							'prob'			=>"Expired credit",
							'type'			=>0,
							'Date'			=>date('Y-m-d H:i:s',strtotime($currentDate)),
							'end_date'		=>$currentDate,
							'creditType'	=>1,
							'status'		=>1,
							'fromTransactionId'		=>$creditId,
						);
						$this->cashOutCreditmemo($arrCreditExpired);
					}			
					
				}
			}
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
		
		
	}
}