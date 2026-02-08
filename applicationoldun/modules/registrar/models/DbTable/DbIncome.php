<?php
class registrar_Model_DbTable_DbIncome extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_income';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->branch_id;
	}	
	function addIncome($data){
		
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dbg = new Registrar_Model_DbTable_DbRegister();
		    $receipt_no = $dbg->getRecieptNo($data['branch_id']);
			$bankId = $data['bank_name'];
			if($data['payment_method']==1){
				$bankId = 0;
			}
			$array = array(
					'branch_id'		=>$data['branch_id'],
					'invoice'		=>$receipt_no,
					'student_id'	=>$data['studentId'],
					'student_status'=>$data['student_status'],
					'groupId'		=>$data['groupId'],
					'optionType'	=>$data['option_type'],
					'title'			=>$data['title'],
					'cate_income'	=>$data['cate_income'],
					'total_amount'	=>$data['total_income'],
					'payment_method'=>$data['payment_method'],
					'bank_id'		=>$bankId ,
					'cheqe_no'		=>$data['cheqe_no'],
					'description'	=>$data['note'],
					'date'			=>$data['date'],
					'expire_date'	=>$data['expire_date'],
					'user_id'		=>$this->getUserId(),
					'create_date'	=>date('Y-m-d H:i:s'), 
				);
			$incomeId = $this->insert($array);
			
			if($data['option_type']==2){
				$dbm = new Accounting_Model_DbTable_DbCreditmemo();
				$arr = array(
						'branch_id'		=>$data['branch_id'],
						'studentId'		=>$data['studentId'],
						'total_amount'	=>$data['total_income'],
						'Description'	=>$data['note'],
						'prob'			=>$data['note'],
						'type'			=>0,
						'Date'			=>$data['date'],
						'end_date'		=>$data['expire_date'],
						'creditType'	=>$data['creditType'],
						'status'		=>1,
						'otherincome_id'=>$incomeId
						);
				$dbm->addCreditmemo($arr);
			}
			$db->commit();
			
			$incomeRs = [
				'receipt_no' => $receipt_no,
				'id' => $incomeId ,
			];
			return $incomeRs;
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			Application_Form_FrmMessage::message("INSERT_FAIL");
			
		}
 	} 	 
	function updateIncome($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$bankId = $data['bank_name'];
			if($data['payment_method']==1){
				$bankId = 0;
			}
			$arr = array(
					'branch_id'		=>$data['branch_id'],
					'student_id'	=>$data['studentId'],
					'student_status'=>$data['student_status'],
					'groupId'		=>$data['groupId'],
					'optionType'	=>$data['option_type'],
					'bank_id'		=>$bankId,
					'title'			=>$data['title'],
					'cate_income'	=>$data['cate_income'],
					'total_amount'	=>$data['total_income'],
					'invoice'		=>$data['invoice'],
					'payment_method'=>$data['payment_method'],
					'cheqe_no'		=>$data['cheqe_no'],
					'description'	=>$data['note'],
					'date'			=>$data['date'],
					'status'		=>$data['status'],
					'expire_date'	=>$data['expire_date'],
					'user_id'		=>$this->getUserId(),
				);
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
			
			if($data['option_type']==2){
				$dbm = new Accounting_Model_DbTable_DbCreditmemo();
				$arr = array(
					'branch_id'		=>$data['branch_id'],
					'studentId'		=>$data['studentId'],
					'total_amount'	=>$data['total_income'],
					'Description'	=>$data['note'],
					'prob'			=>$data['note'],
					'type'			=>0,
					'Date'			=>$data['date'],
					'end_date'		=>$data['expire_date'],
					'status'		=>$data['status'],
					'creditType'	=>$data['creditType'],
					'otherincome_id'=>$data['id']
				);
				$data['id']='';
				$dbm->updatcreditMemo($arr);
			}
				
			$db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			Application_Form_FrmMessage::message("INSERT_FAIL");
				
		}
	}
	
	function getIncomeById($id){
		$db = $this->getAdapter();
		$sql=" SELECT inc.* FROM ln_income AS inc WHERE inc.id=$id ";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("inc.branch_id");
		
		return $db->fetchRow($sql);
	}
	function getAllIncome($search=null){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		$label = "name_en";
		if($lang==1){// khmer
			$label = "name_kh";
		}
		$branch = $dbp->getBranchDisplay();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$Option1 = $tr->translate('OTHER_INCOME');
		$Option2 = $tr->translate('CREDIT_MEMO');
		
		//CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentName,
		$sql=" SELECT inc.id,
				(SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id =inc.branch_id LIMIT 1) AS branch_name,
				COALESCE(s.stu_code,'') AS studentCode,
				COALESCE(s.stu_khname,'') AS studentName,
				(SELECT group_code FROM rms_group g WHERE g.id=inc.groupId LIMIT 1) AS group_name,
				CASE
					WHEN optionType=1 THEN '$Option1'
					WHEN optionType=2 THEN '$Option2'
				END
				AS optionType,	   		
				inc.invoice,
				inc.total_amount,
				inc.date,
				cate.category_name cate_name,
				inc.title,
				(SELECT $label FROM `rms_view` WHERE rms_view.type=8 and rms_view.key_code = inc.payment_method) AS payment_method,
				(SELECT bank_name FROM `rms_bank` b WHERE b.id=inc.bank_id LIMIT 1) AS bank_name,
				cheqe_no,
				description
				
		";
		
		$sql.=$dbp->caseStatusShowImage("inc.status");
		$sql.=" 
			FROM 
				ln_income inc
				LEFT JOIN rms_cate_income_expense AS cate ON cate.id = inc.cate_income 
				LEFT JOIN rms_student AS s ON s.stu_id=inc.student_id

		";
		
		$from_date =(empty($search['start_date']))? '1': " inc.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " inc.date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " inc.title LIKE '%{$s_search}%'";
			$s_where[] = " inc.total_amount LIKE '%{$s_search}%'";
			$s_where[] = " inc.invoice LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " s.last_name LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
			$s_where[] = " CONCAT(s.last_name,' ',s.stu_enname) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['cate_income'])){
			$arrCon = array(
				"categoryId" => $search['cate_income'],
			);
			$condiction = $dbp->getChildCategoryIncome($arrCon);
			if (!empty($condiction)){
				$where.=" AND cate.id IN ($condiction)";
			}else{
				$where.=" AND inc.cate_income = ".$search['cate_income'] ;
			}
		}
		if(!empty($search['branch_id'])){
			$where.= " AND inc.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['option_type']) AND $search['option_type']>0){
			$where.= " AND inc.optionType = ".$search['option_type'];
		}
		if(!empty($search['studentId'])){
			$where.= " AND inc.student_id = ".$search['studentId'];
		}
		if($search['status']>-1){
			$where.= " AND inc.status = ".$search['status'];
		}
		$where.=$dbp->getAccessPermission("inc.branch_id");
        $order=" order by inc.id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllExpenseReport($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
	
		$sql=" SELECT 
			id,
			(SELECT branch_namekh FROM `rms_branch` WHERE rms_branch.br_id =branch_id LIMIT 1) AS branch_name,
			account_id,
			invoice,
		total_amount,disc,date,status FROM $this->_name ";
	
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " account_id LIKE '%{$s_search}%'";
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("branch_id");
		
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}

	function getReceiptNumber($branch_id,$type){  // $type==1 => select from rms_income , $type==2 => select from rms_expense
		$db = $this->getAdapter();
		if($type==1){
			$table = 'ln_income';
		}else{
			$table = 'ln_expense';
		}
		$sql="select count(id) from $table where branch_id = $branch_id limit 1 ";
		$id = $db->fetchOne($sql);
		$id = $id + 1;
		$length = strlen($id) + 1;
		$pre = 'PM';
		for($i=$length;$i<=6;$i++){
			$pre.='0';
		}
		return $pre.$id;
	}
	function getInvoiceNo(){
		$db = $this->getAdapter();
		$sql = " select count(id) from ln_income ";
		$amount = $db->fetchOne($sql);
	}

	
	function getPaymentMethod($type){ // $type = rms_view type
		$_db  = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$label = "name_kh";
		}else{ // English
			$label = "name_en";
		}
		
		$db=$this->getAdapter();
		$sql="SELECT key_code as id,$label as name FROM rms_view WHERE `type`=$type AND `status`=1 ";
		return $db->fetchAll($sql);
	}
	
	function getCateIncome(){ // $type = rms_view type
		$db=$this->getAdapter();
		$sql="SELECT id,category_name as name FROM rms_cate_income_expense WHERE status=1 AND parent=1 and category_name!='' ";
		return $db->fetchAll($sql);
	}
	
	function addNewCateIncome($data){
		$this->_name="rms_cate_income_expense";
		$array = array(
				'category_name'	=>$data['cate_title'],
				'parent'		=>$data['parent'],
				'account_code'	=>$data['acc_code'],
				'create_date'	=>date('Y-m-d'),
				'user_id'		=>$this->getUserId(),
				);
		return $this->insert($array);
	}
}
