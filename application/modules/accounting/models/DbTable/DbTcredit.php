<?php
class Accounting_Model_DbTable_DbTcredit extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_transfer_credit';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllTrasferCreditmemo($search=null){
		$db = $this->getAdapter();
		$_db=new Application_Model_DbTable_DbGlobal();
		$branch= $_db->getBranchDisplay();
		//total_amountafter,
		$sql="SELECT 
				c.id
				,(SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id = c.fromBranchId LIMIT 1) AS branch_name
				,CONCAT(COALESCE(s.stu_code,''),' ',COALESCE(s.stu_khname,'')) AS titleRecord
				,c.date
				,CONCAT(
					'<span class=\"fw-bold text-success d-block text-right\">',
					COALESCE(c.total_amount,0),
					'</span>'
				) AS total_amount
				,b.$branch AS receiveBranch
				,CONCAT(
					COALESCE(ss.stu_code,''),' ',COALESCE(ss.stu_khname,''),
					'<small class=\"subtitle-row text-secondary\">',
					COALESCE(ss.last_name,''),
					' ',COALESCE(ss.stu_enname,''),
					'</small>'
				) AS toStudent
				
				,c.createDate
				,(SELECT first_name FROM `rms_users` WHERE id=c.user_id LIMIT 1) AS user_name
				,c.status AS statusRecord
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS subTitleRecord
			  FROM 
					rms_creditmemo c 
					JOIN rms_student AS s ON s.stu_id = c.fromStuId
					LEFT JOIN rms_branch AS b ON b.br_id = c.branch_id
					LEFT JOIN rms_student AS ss ON ss.stu_id = c.student_id
			  WHERE c.type = 1 ";
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
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
			$s_where[] = " s.last_name LIKE '%{$s_search}%'";
			$s_where[] = " CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) LIKE '%{$s_search}%'";
			
			$s_where[] = " ss.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " ss.stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " ss.stu_enname LIKE '%{$s_search}%'";
			$s_where[] = " ss.last_name LIKE '%{$s_search}%'";
			$s_where[] = " CONCAT(COALESCE(ss.last_name,''),' ',COALESCE(ss.stu_enname,'')) LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.= " AND c.fromBranchId = ".$search['branch_id'];
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('c.fromBranchId');
		
		$order=" order by id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function addTransferCredit($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			
			$walletBalance =  0;
			$toStudentId =  empty($data["toStudentId"]) ? 0 : $data["toStudentId"];
			$totalAmount =  empty($data["total_amount"]) ? 0 : $data["total_amount"];
			
			$dbCredit = new Accounting_Model_DbTable_DbCreditmemo();
			$arrTran = [
				"cashType" => 1,
				"studentId" => $toStudentId,
				"totalAmount" => $totalAmount,
			];
			$stuInfo = $dbCredit->cashInAndOutWallet($arrTran);
			$walletBalance =  empty($stuInfo["walletBalance"]) ? 0 : $stuInfo["walletBalance"];
			$walletBalanceAfter =  empty($stuInfo["walletBalanceAfter"]) ? 0 : $stuInfo["walletBalanceAfter"];
			
			$arrTranFrom = [
				"cashType" => 2,
				"studentId" => $data['studentId'],
				"totalAmount" => $totalAmount,
			];
			$stuInfoFrom = $dbCredit->cashInAndOutWallet($arrTranFrom);
			$fromStuWalletBalance =  empty($stuInfoFrom["walletBalance"]) ? 0 : $stuInfoFrom["walletBalance"];
			$fromStuWalletBalanceAfter =  empty($stuInfoFrom["walletBalanceAfter"]) ? 0 : $stuInfoFrom["walletBalanceAfter"];
			
			$transactionNo  = $dbCredit->generateTransactionNo($data);
			$arr = array(
				'transaction_no'	=>$transactionNo,
				'branch_id'		=>$data['to_branchId'],
				'student_id'	=>$data['toStudentId'],
				'date'			=>$data['for_date'],
				'type'			=>1,
				'note'			=>$data['Descriptions'],
				'prob'			=>$data['Descriptions'],
				
				'total_amount'		=>$totalAmount,
				'total_amountafter'	=>$totalAmount,
				'walletBalance'			=>$walletBalance,
				'walletBalanceAfter'	=>$walletBalanceAfter,
				
				'fromBranchId'	=>$data['branch_id'],
				'fromStuId'		=>$data['studentId'],
				'fromStuWalletBalance'		=>$fromStuWalletBalance,
				'fromStuWalletBalanceAfter'	=>$fromStuWalletBalanceAfter,
				
				'status'		=>1,
				'user_id'		=>$this->getUserId(),
				'createDate'	=>date("Y-m-d H:i:s"),
				'modifyDate'	=>date("Y-m-d H:i:s")
			);
			$this->_name="rms_creditmemo";
			$creditId = $this->insert($arr);
			
			$transactionNoOut  = $dbCredit->generateTransactionNo($data);
			$arr = array(
				'transaction_no'	=>$transactionNoOut,
				'branch_id'		=>$data['branch_id'],
				'student_id'	=>$data['studentId'],
				'date'			=>$data['for_date'],
				'type'			=>0,
				'cashType'		=>2,
				'note'			=>$data['Descriptions'],
				'prob'			=>$data['Descriptions'],
				
				'total_amount'		=>$totalAmount,
				'total_amountafter'	=>$totalAmount,
				
				'walletBalance'			=>$fromStuWalletBalance,
				'walletBalanceAfter'	=>$fromStuWalletBalanceAfter,
				
				'status'		=>1,
				'fromTransactionId'		=>$creditId,
				'user_id'		=>$this->getUserId(),
				'createDate'	=>date("Y-m-d H:i:s"),
				'modifyDate'	=>date("Y-m-d H:i:s")
			);
			$this->_name="rms_creditmemo";
			$this->insert($arr);
			
			
			$db->commit();
			return $creditId;
		} catch (Exception $e) {
			echo $e->getMessage();
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			exit();
		}
	}
	
	function reverseTransferCredit($data){
		$creditId = empty($data['id']) ? 0 : $data['id'];
		$row = $this->getTransferById($creditId);
		
		$dbCredit = new Accounting_Model_DbTable_DbCreditmemo();
		$totalAmount = empty($row["total_amount"]) ? 0 : $row["total_amount"];
		$toStudentId = empty($row["student_id"]) ? 0 : $row["student_id"];
		$fromStuId = empty($row["fromStuId"]) ? 0 : $row["fromStuId"];
		  
		 $arrReverTranTo = [
				"cashType" => 2,
				"studentId" => $toStudentId,
				"totalAmount" => $totalAmount,
			];
		
		 $stuInfo = $dbCredit->cashInAndOutWallet($arrReverTranTo);
		 
		
		 $arrReverTranFrom = [
				"cashType" => 1,
				"studentId" => $fromStuId,
				"totalAmount" => $totalAmount,
			];
		 $dbCredit->cashInAndOutWallet($arrReverTranFrom);
	 }
	function updateTransferCredit($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			
			$this->reverseTransferCredit($data);
			
			$status = empty($data['status']) ? 0 : 1;
			
			$walletBalance =  0;
			$toStudentId =  empty($data["toStudentId"]) ? 0 : $data["toStudentId"];
			$totalAmount =  empty($data["total_amount"]) ? 0 : $data["total_amount"];
			
			$walletBalance = 0;
			$walletBalanceAfter = 0;
			$fromStuWalletBalance = 0;
			$fromStuWalletBalanceAfter = 0;
			
			
			$arr = array(
				'branch_id'		=>$data['to_branchId'],
				'student_id'	=>$data['toStudentId'],
				'date'			=>$data['for_date'],
				'type'			=>1,
				'note'			=>$data['Descriptions'],
				
				'total_amount'		=>$totalAmount,
				'total_amountafter'	=>$totalAmount,
				
				
				'fromBranchId'	=>$data['branch_id'],
				'fromStuId'		=>$data['studentId'],
				
				'status'		=>$status,
				'user_id'		=>$this->getUserId(),
				'modifyDate'	=>date("Y-m-d H:i:s")
			);
			
			if($status==1){
				$dbCredit = new Accounting_Model_DbTable_DbCreditmemo();
				$arrTran = [
					"cashType" => 1,
					"studentId" => $toStudentId,
					"totalAmount" => $totalAmount,
				];
				$stuInfo = $dbCredit->cashInAndOutWallet($arrTran);
				$walletBalance =  empty($stuInfo["walletBalance"]) ? 0 : $stuInfo["walletBalance"];
				$walletBalanceAfter =  empty($stuInfo["walletBalanceAfter"]) ? 0 : $stuInfo["walletBalanceAfter"];
				
				$arrTranFrom = [
					"cashType" => 2,
					"studentId" => $data['studentId'],
					"totalAmount" => $totalAmount,
				];
				$stuInfoFrom = $dbCredit->cashInAndOutWallet($arrTranFrom);
				$fromStuWalletBalance =  empty($stuInfoFrom["walletBalance"]) ? 0 : $stuInfoFrom["walletBalance"];
				$fromStuWalletBalanceAfter =  empty($stuInfoFrom["walletBalanceAfter"]) ? 0 : $stuInfoFrom["walletBalanceAfter"];
				
				
				$arr["walletBalance"] = $walletBalance;
				$arr["walletBalanceAfter"] = $walletBalanceAfter;
				
				$arr["fromStuWalletBalance"] = $fromStuWalletBalance;
				$arr["fromStuWalletBalanceAfter"] = $fromStuWalletBalanceAfter;
			}
			
			
			
			
			$creditId = $data['id'];
			$this->_name="rms_creditmemo";
			$where=" id = ".$creditId;
			
			
			$this->update($arr, $where);
			
			$db->commit();
			return $creditId;
		} catch (Exception $e) {
			echo $e->getMessage();
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			exit();
		}
	}
	
	function getTransferById($id){
		$db = $this->getAdapter();
		$sql=" SELECT t.* FROM rms_creditmemo AS t WHERE t.type =1 AND  t.id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('t.fromBranchId');
		return $db->fetchRow($sql);
	}
}