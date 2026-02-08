<?php

class Allreport_Model_DbTable_DbRptIncomeExpense extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_income';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }

	public function getAllexpenseDetail($search){//report expense
	   $db=$this->getAdapter();
	   
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$branch = $dbp->getBranchDisplay();
		
    	$label = "name_en";
		if($currentLang==1){ 
    		$label = "name_kh";
    	}
		
	   $sql="SELECT 
				e.*
				,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id =e.branch_id LIMIT 1) AS branch_name
				,u.user_name 
				,u.first_name
				,e.receiver
				,e.cheque_no
				,e.external_invoice
				,(SELECT b.bank_name FROM `rms_bank` b WHERE b.id=e.bank_id LIMIT 1) AS bank_name
				,e.payment_type AS paymentMethodId
				,(SELECT v.$label FROM rms_view AS v WHERE v.type=8 AND v.key_code= e.payment_type LIMIT 1) AS paymentMethodTitle,
				ext.categoryTitle AS itemTitle,
				ext.accountCode AS itemAccountCode,
				cat.categoryTitle AS categoryTitle,
				cat.accountCode AS categoryAccountCode,
				s.sup_name,
				ext.categoryId,
				ed.price,
				ed.qty,
				ed.total
			FROM 
				ln_expense AS e 
				INNER JOIN `ln_expense_detail` AS ed ON ed.expense_id = e.id
				LEFT JOIN `rms_expense_item` AS ext ON ext.id = ed.category_id 
				LEFT JOIN `rms_expense_category` AS cat ON cat.id = ext.categoryId
				LEFT JOIN `rms_supplier` AS s ON s.id = e.supplierId
				LEFT JOIN rms_branch AS b ON b.br_id=e.branch_id 
				LEFT JOIN rms_users AS u  ON e.user_id=u.id
			WHERE 
				1  
		";
	   
	   $where="";
	   $from_date =(empty($search['start_date']))? '1': " e.date  >= '".$search['start_date']." 00:00:00'";
	   $to_date = (empty($search['end_date']))? '1': " e.date <= '".$search['end_date']." 23:59:59'";
	   $where = " AND ".$from_date." AND ".$to_date;
	   
	   if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " REPLACE(e.invoice,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(e.external_invoice,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(e.receiver,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(e.title,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(e.description,' ','') LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
	   }
	   if(!empty($search['branch_id'])){
	   	$where.= " AND e.branch_id = ".$search['branch_id'];
	   }
	   if(!empty($search['supplier_id'])){
	   	$where.= " AND e.supplierId = ".$search['supplier_id'];
	   }
	   if(!empty($search['expenseCategoryId'])){
			$arrCon = array(
				"categoryId" => $search['expenseCategoryId'],
			);
			$condiction = $dbp->getChildExpenseCategory($arrCon);
			if (!empty($condiction)){
				$where.=" AND  cat.id IN ($condiction)";
			}else{
				$where.=" AND ext.categoryId = ".$search['expenseCategoryId'] ;
			}
		}
		if(!empty($search['userId'])){
			$where.= " AND e.user_id = ".$search['userId'];
		}
		if(!empty($search['receiptStatus'])){
			if($search['receiptStatus']==1){
				$where.= " AND e.status = 1 ";
			}else if($search['receiptStatus']==2){
				$where.= " AND e.status = 0 ";
			}
		}
	   $where.=$dbp->getAccessPermission("e.branch_id");
		
		$expRecordOrdering = empty($search['expRecordOrdering']) ? 1 : $search['expRecordOrdering'];
		$receipt_order = empty($search['receipt_order']) ? 1 : $search['receipt_order'];

		if($receipt_order==1){
			$order=" ORDER BY e.id DESC ";
			if($expRecordOrdering==1){
				$order=" ORDER BY ext.categoryId ASC,e.bank_id ASC,e.id DESC ";
			}else if($expRecordOrdering==2){
				$order=" ORDER BY s.sup_name DESC,e.id DESC ";
			}
		}else{
			$order=" ORDER BY e.id ASC ";
			if($expRecordOrdering==1){
				$order=" ORDER BY ext.categoryId ASC,e.bank_id ASC,e.id ASC ";
			}else if($expRecordOrdering==2){
				$order=" ORDER BY s.sup_name ASC,e.id ASC ";
			}
		}
		
	   return $db->fetchAll($sql.$where.$order);
	}
    
	public function getAllexspan($search){//report expense
	   $db=$this->getAdapter();
	   
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$branch = $dbp->getBranchDisplay();
		
    	$label = "name_en";
		if($currentLang==1){ 
    		$label = "name_kh";
    	}
		
	   $sql="SELECT 
				e.*
				 ,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id =e.branch_id LIMIT 1) AS branch_name
				 ,u.user_name 
				 ,u.first_name
				 ,e.receiver
				 ,e.cheque_no
				 ,e.external_invoice
				 ,(SELECT b.bank_name FROM `rms_bank` b WHERE b.id=e.bank_id LIMIT 1) AS bank_name
				 ,e.payment_type AS paymentMethodId
				 ,(select v.$label FROM rms_view AS v WHERE v.type=8 and v.key_code= e.payment_type LIMIT 1) as paymentMethodTitle
				 ,(select v.$label FROM rms_view AS v WHERE v.type=8 and v.key_code= e.payment_type LIMIT 1) as payment_type
				 ,(SELECT v.$label FROM rms_view AS v WHERE v.type=8 AND v.key_code= e.payment_type LIMIT 1) AS pay
			FROM 
				ln_expense AS e JOIN rms_branch AS b ON b.br_id=e.branch_id 
				LEFT JOIN rms_users AS u  ON e.user_id=u.id
			WHERE 
	   			1  
		";
	   
	   $where="";
	   $from_date =(empty($search['start_date']))? '1': " e.date  >= '".$search['start_date']." 00:00:00'";
	   $to_date = (empty($search['end_date']))? '1': " e.date <= '".$search['end_date']." 23:59:59'";
	   $where = " AND ".$from_date." AND ".$to_date;
	   
	   if(!empty($search['txtsearch'])){
	   	$s_where = array();
		$s_search = str_replace(' ', '', addslashes(trim($search['txtsearch'])));
	   	$s_where[] = " REPLACE(e.invoice,' ','') LIKE '%{$s_search}%'";
	   	$s_where[] = " REPLACE(e.external_invoice,' ','') LIKE '%{$s_search}%'";
	   	$s_where[] = " REPLACE(e.receiver,' ','') LIKE '%{$s_search}%'";
	   	$s_where[] = " REPLACE(e.title,' ','') LIKE '%{$s_search}%'";
	   	$s_where[] = " REPLACE(e.description,' ','') LIKE '%{$s_search}%'";
	   	$where .=' AND ( '.implode(' OR ',$s_where).')';
	   }
		if($search['branch_id']){
			$where.= " AND e.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['userId'])){
			$where.= " AND e.user_id = ".$search['userId'];
		}
		if(!empty($search['receiptStatus'])){
			if($search['receiptStatus']==1){
				$where.= " AND e.status = 1 ";
			}else if($search['receiptStatus']==2){
				$where.= " AND e.status = 0 ";
			}
		}
			
	   $search['receipt_order'] = empty($search['receipt_order']) ? 0 : $search['receipt_order'];
		if($search['receipt_order']==1){
			$order=" ORDER BY e.payment_type DESC,e.bank_id ASC,e.id DESC ";
		}else{
			$order=" ORDER BY e.payment_type DESC,e.bank_id ASC,e.id ASC ";
		}
		
	   return $db->fetchAll($sql.$where.$order);
	}
	
	public function getAmountExpest(){//count to dashboard
    	$db = $this->getAdapter();
    	$sql =' SELECT SUM(total_amount) FROM ln_expense ';
    	$where=' WHERE status=1 ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("branch_id");
    	return $db->fetchOne($sql.$where);
    }
    public function getTotalIncome(){//count to dashboard
    	$db = $this->getAdapter();
    	$total=0;
    	$sql =' SELECT SUM(paid_amount) FROM rms_student_payment ';
    	$where=' WHERE status=1 and is_void=0 ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("branch_id");
    	$student_payment = $db->fetchOne($sql.$where);
    	
    	
    	$sql1 =' SELECT SUM(total_amount) FROM ln_income  ';
    	$where1=' WHERE status=1 ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where1.=$dbp->getAccessPermission("branch_id");
    	$other_payment = $db->fetchOne($sql1.$where1);
    	
    	$total = $student_payment + $other_payment;
    	return $total;
    }
    
	public function getAllexspanByid($id){//using
	    $db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
		
	    $sql="SELECT
				e.*,
				u.user_name,
				s.`sup_name`,
				s.`tel`,
				DATE_FORMAT (e.date, '%d-%m-%Y') DATE,
				(SELECT
					br.$branch
				FROM
					`rms_branch` AS br
				WHERE br.br_id = e.branch_id
				LIMIT 1) AS branch_name,
				(SELECT
					name_kh
				FROM
					`rms_view`
				WHERE rms_view.type = 8
					AND rms_view.key_code = payment_type
				LIMIT 1) AS payment_type,
				(SELECT
					bank_name
				FROM
					`rms_bank` b
				WHERE b.id = e.bank_id
				LIMIT 1) AS bank_name,
				(SELECT
					v.name_kh
				FROM
					rms_view AS v
				WHERE v.type = 8
					AND v.key_code = e.payment_type
				LIMIT 1) AS pay
				FROM
				ln_expense AS e
				LEFT JOIN rms_users AS u ON e.user_id = u.id
				LEFT JOIN `rms_supplier`AS s ON s.`id` = e.`supplierId`
				WHERE e.id=$id
	      		";
	    return $db->fetchrow($sql);
    }
	public function getAllexspandetailByid($id){//using
	    $db=$this->getAdapter();
	      $sql="SELECT 
	      			e.* ,
	      			s.categoryTitle as service,
	      			s.accountCode as accountCode
	      		 FROM 
	      		 	ln_expense_detail AS e,
	      			rms_expense_item AS s 
	      		WHERE 
	      			e.category_id=s.id 
	      			and e.expense_id=$id
	      	";
	    return $db->fetchAll($sql);
    }
}
   
    
   