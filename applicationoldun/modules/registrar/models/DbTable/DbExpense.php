<?php
class Registrar_Model_DbTable_DbExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_expense';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function addExpense($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		
		$db = new Registrar_Model_DbTable_DbIncome();
		$invoice = $db->getReceiptNumber($data['branch_id'],2);
		
		try{
			// Supplier
			$_arrsup = array(
				'sup_name'		=> $data['supplier_name'],
				'sex'			=> $data['sex'],
				'tel'			=> $data['phone'],
				'email'			=> $data['email'],
				'address'		=> $data['address'],
				'status'		=> 1,
				'date'			=> $data['Date'],
				'create_date'	=>date("Y-m-d H:i:s"),
				'modify_date'	=>date("Y-m-d H:i:s"),
				'user_id'		=> $this->getUserId()
			);
			if(!empty($data['purchase_no'])){
				$_arrsup["purchase_no"]=$data['purchase_no'];
			}
			$this->_name='rms_supplier';
			if(!empty($data['sup_id'])){
				$sup_id=$data['sup_id'];
				$where=" id =".$data['sup_id'];
				$this->update($_arrsup, $where);
			}else{
				$sup_id = $this->insert($_arrsup);
			}
			$bankId = $data['bank_name'];
			if($data['payment_method']==1){
				$bankId = 0;
			}

			/// Expense
			$_arr = array(
					'branch_id'		=>$data['branch_id'],
					'title'			=>$data['title'],
					'total_amount'	=>$data['total_amount'],
					'invoice'		=>$invoice,
					'supplierId'	=>$sup_id,
					'payment_type'	=>$data['payment_method'],
					'description'	=>$data['Description'],
					'receiver'		=>$data['receiver'],
					'cheque_no'		=>$data['cheque_num'],
					'bank_id'		=>$bankId,
					'external_invoice'=>$data['external_invoice'],
					'date'			=>$data['Date'],
					'user_id'		=>$this->getUserId(),
					'create_date'	=>date('Y-m-d H:i:s'),
				);
			$this->_name='ln_expense';
			$expend_id = $this->insert($_arr);
			$ids = explode(',', $data['identity']);
		
			foreach ($ids as $j){
				$arr = array(
						'expense_id'	=>$expend_id,
						'category_id'	=>$data['expense_id_'.$j],
						'description'	=>$data['remark_'.$j],
						'price'			=>$data['price_'.$j],
						'qty'			=>$data['qty_'.$j],
						'total'			=>$data['total_'.$j],
					);
				$this->_name='ln_expense_detail';
			   $this->insert($arr);
			}
			$_db->commit();
			
			$expenseRs = [
				'invoice' => $invoice,
				'id' => $expend_id ,
			];
			return $expenseRs;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
 	}
	function updatExpense($data){
	 	$_db= $this->getAdapter();
	 	$_db->beginTransaction();
	 	try{
			// Supplier
			$_arrsup = array(
				'sup_name'		=> $data['supplier_name'],
				'sex'			=> $data['sex'],
				'tel'			=> $data['phone'],
				'email'			=> $data['email'],
				'address'		=> $data['address'],
				'status'		=> 1,
				'date'			=> $data['Date'],
				'create_date'	=>date("Y-m-d H:i:s"),
				'modify_date'	=>date("Y-m-d H:i:s"),
				'user_id'		=> $this->getUserId()
			);
			if(!empty($data['purchase_no'])){
				$_arrsup["purchase_no"]=$data['purchase_no'];
			}
			$this->_name='rms_supplier';
			if(!empty($data['sup_id'])){
				$sup_id=$data['sup_id'];
				$where=" id =".$data['sup_id'];
				$this->update($_arrsup, $where);
			}else{
				$sup_id = $this->insert($_arrsup);
			}

			$bankId = $data['bank_name'];
			if($data['payment_method']==1){
				$bankId = 0;
			}

			$arr = array(	
					'branch_id'		=> $data['branch_id'],
					'title'			=> $data['title'],
					'total_amount'	=> $data['total_amount'],
					'invoice'		=> $data['invoice'],
					'supplierId'	=>$sup_id,
					'payment_type'	=> $data['payment_method'],
					'bank_id'		=> $bankId,
					'description'	=> $data['Description'],
					'receiver'		=> $data['receiver'],
					'cheque_no'		=> $data['cheque_num'],
					'external_invoice'=> $data['external_invoice'],
					'date'			=> $data['Date'],
					'status'		=> $data['Stutas'],
					'user_id'		=> $this->getUserId(),
				);
			$this->_name='ln_expense';
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
			
			$this->_name='ln_expense_detail';
			$where = "expense_id = ".$data['id'];
			$this->delete($where);
			$ids = explode(',', $data['identity']);
			foreach ($ids as $j){
				$arr = array(
						'expense_id'	=>$data['id'],
						'category_id'	=>$data['expense_id_'.$j],
						'description'	=>$data['remark_'.$j],
						'price'			=>$data['price_'.$j],
						'qty'			=>$data['qty_'.$j],
						'total'			=>$data['total_'.$j],);
				$this->_name='ln_expense_detail';
				$this->insert($arr);
			}
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
	}
	function getexpensebyid($id){
		$db = $this->getAdapter();
		$sql="SELECT e.* FROM ln_expense AS e where e.id=$id ";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("e.branch_id");
		return $db->fetchRow($sql);
	}
	function getexpenseDetailbyid($id){
		$db = $this->getAdapter();
		$sql="SELECT ed.*,
			ei.`categoryTitle` as itemName
			FROM ln_expense_detail AS ed
			LEFT JOIN `rms_expense_item` AS ei ON ed.`category_id` = ei.`id` 
			WHERE  ed.expense_id=".$id;
		return $db->fetchAll($sql);
	}

	function getAllExpense($search=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		$branch = $dbp->getBranchDisplay();
		$label = "name_en";
		if($lang==1){// khmer
			$label = "name_kh";
		}
		$sql=" SELECT 
					id,
					(SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id =branch_id LIMIT 1) AS branch_name,
					invoice,
					external_invoice,
					(SELECT $label FROM `rms_view` WHERE rms_view.type=8 and rms_view.key_code = payment_type limit 1) AS payment_type,
					(SELECT bank_name FROM `rms_bank` b WHERE b.id=bank_id LIMIT 1) AS bank_name,
					total_amount,
					date,
					receiver,
					title,
					(SELECT first_name FROM `rms_users` WHERE rms_users.id=ln_expense.user_id LIMIT 1) as user_name
			";
		$sql.=$dbp->caseStatusShowImage("ln_expense.status");
		$sql.=" FROM ln_expense ";
		
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			$s_where[] = " external_invoice LIKE '%{$s_search}%'";
			$s_where[] = " receiver LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		if($search['payment_type']>-1){
			$where.= " AND payment_type = ".$search['payment_type'];
		}
		$where.=$dbp->getAccessPermission("branch_id");
       	$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllExpenseReport($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
	
		$sql=" SELECT id,
		(SELECT branch_namekh FROM `rms_branch` WHERE rms_branch.br_id =branch_id LIMIT 1) AS branch_name,
		account_id,invoice,
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

	public function getAllCateExpense($type){
		$db = $this->getAdapter();
		$sql = "SELECT id ,categoryTitle as name FROM `rms_expense_item` WHERE status=1 AND categoryTitle!=''";
		return $db->fetchAll($sql);
	}

	function addCateExpense($data){
		$this->_name="rms_expense_item";
		$arr = array(
				'categoryTitle'	=>$data['account_name'],
				'parentId'		=>$data['parent'],
				'user_id'		=>$this->getUserId(),
				'createDate'			=>date('Y-m-d'),
		);
		$id = $this->insert($arr);
		$db = new Application_Model_GlobalClass();
		$new_arrar_cate_expense = $db->getAllExpenseIncomeType(5);
		$result = array(
				'id'=>$id,
				'new_array'=>$new_arrar_cate_expense,
			);
		return $result;
	}

	function getExpenseInfo($data){
    	$db=$this->getAdapter();
    	$sql="SELECT 
				p.`supplierId`, 
				p.`title`,
				p.`date`,
				pd.`category_id`,
				pd.`price`,
				pd.`qty`
				FROM `ln_expense` AS p 
				JOIN `ln_expense_detail` AS pd ON p.`id` = pd.`expense_id`

				WHERE p.`status` AND p.`isVoid` =0 ";
			if($data['expense_id']){
				$sql.= " AND pd.`category_id`= ".$data['expense_id'];
			}
			if($data['sup_id']){
				$sql.= " AND p.`supplierId`= ".$data['sup_id'];
			}
			$sql.= " ORDER BY p.`date` DESC limit 1 ";
    	return $db->fetchRow($sql);
    }

}