<?php

class Allreport_Model_DbTable_DbRptOtherExpense extends Zend_Db_Table_Abstract
{
    function getAllOtherExpense($search){//using
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql = "SELECT *,
    				description as note,
	    			payment_type AS payment_methodid, 
	    			(SELECT name_en FROM rms_view WHERE rms_view.type=8 and key_code=payment_type LIMIT 1) AS payment_type,
	    			(SELECT name_en FROM rms_view WHERE type=10 AND key_code=isVoid LIMIT 1) AS voidStatus,
	    			(SELECT first_name FROM rms_users as u WHERE u.id = user_id LIMIT 1) AS byUser,
	    			(SELECT first_name FROM rms_users as u WHERE u.id = voidBy LIMIT 1) AS voidByUser
    			 FROM ln_expense  WHERE 1 $branch_id  ";
    	$where= ' ';
    	
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	
    	
    	if(!empty($search['branch_id'])){
    		$where.=" AND branch_id = ".$search['branch_id'] ;
    	}
    	if(!empty($search['user'])){
    		$where.=" AND user_id = ".$search['user'] ;
    	}
		if(!empty($search['userId'])){
    		$where.=" AND user_id = ".$search['userId'] ;
    	}
    	if(!empty($search['receiptStatus'])){
			if($search['receiptStatus']==1){
				//$where.= " AND isVoid = 0 ";
				$where.= " AND status = 1 ";
			}else if($search['receiptStatus']==2){
				//$where.= " AND isVoid = 1 ";
				$where.= " AND status = 0 ";
			}
		}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " title LIKE '%{$s_search}%'";
    		$s_where[] = " invoice LIKE '%{$s_search}%'";
    		$s_where[] = " external_invoice LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['receipt_order']==0){
    		$order=" ORDER By id ASC ";
    	}else{
    		$order=" ORDER By id DESC ";
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    function getAllExpensebycate($search){//
    	$db=$this->getAdapter();
    	 
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $dbg->getAccessPermission("e.branch_id");
    	 
    	$sql = "SELECT 
    				SUM(ed.total) AS total_expense,
					ac.categoryTitle,
					cat.parentId,
					ac.categoryId,
					(SELECT a.categoryTitle FROM rms_expense_category AS a WHERE a.id=cat.parentId AND a.isMainParent=1 ) AS parent_name
					,cat.categoryTitle AS expCategoryTitle
    			FROM 
		    		ln_expense AS e JOIN ln_expense_detail AS ed ON e.id=ed.expense_id
					LEFT JOIN  rms_expense_item as ac ON ed.category_id = ac.id
					LEFT JOIN  rms_expense_category as cat ON cat.id = ac.categoryId
		    	WHERE
					e.status=1 
					AND e.isVoid=0
    				$branch_id  
    			";
    	$where= ' ';
		if(!empty($search['expenseCategory'])){
			$arrCon = array(
				"categoryId" => $search['expenseCategory'],
			);
			$condiction = $dbg->getAllExpenseCategory($arrCon);
			if (!empty($condiction)){
				$where.=" AND ac.id IN ($condiction)";
			}else{
				$where.=" AND ed.category_id = ".$search['expenseCategory'] ;
			}
		}
    	
    	$order=" GROUP BY ac.categoryId ORDER BY ac.categoryId  ";
    	 
    	$from_date =(empty($search['start_date']))? '1': " e.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " e.date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND e.branch_id = ".$search['branch_id'] ;
    	}
    	
		// if (!empty($search['displayCategory'])) {
		// 	$subQuery = " (SELECT parentId FROM rms_expense_item WHERE status = 1)";
		// 	$where.= $search['displayCategory'] == 1 
		// 		? " AND ac.id IN $subQuery"  // Display as parent
		// 		: " AND ac.id NOT IN $subQuery"; // Display as item
		// }
		
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " e.title LIKE '%{$s_search}%'";
    		$s_where[] = " e.invoice LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where.$order);
		
    }

	function getAllExpenseCateByMethod($search){//
    	$db=$this->getAdapter();
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $dbg->getAccessPermission("e.branch_id");
    	$sql = "SELECT 
					SUM(ed.total) AS total_expense,	
					e.payment_type,
					(SELECT name_en FROM rms_view WHERE rms_view.type = 8 AND key_code = e.payment_type LIMIT 1) AS payment_type_name,
					e.bank_id,
					CASE 
						WHEN e.bank_id = 0 THEN 'Cash'
						ELSE b.bank_name 
					END AS paymentTitle
				FROM 
					ln_expense AS e 
				JOIN 
					ln_expense_detail AS ed ON e.id = ed.expense_id
				LEFT JOIN 
					rms_bank AS b ON b.id = e.bank_id
				WHERE 
					e.status = 1 
					AND e.isVoid = 0
    				$branch_id  
    			";
    	$where= ' ';
    	$order=" GROUP BY 
					e.payment_type, e.bank_id, b.bank_name
				ORDER BY  
					e.bank_id  ";
    	 
    	$from_date =(empty($search['start_date']))? '1': " e.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " e.date <= '".$search['end_date']." 23:59:59'";
    	$where .= "  AND ".$from_date." AND ".$to_date;
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND e.branch_id = ".$search['branch_id'] ;
    	}
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " e.title LIKE '%{$s_search}%'";
    		$s_where[] = " e.invoice LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
		//echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
		
    }
}    