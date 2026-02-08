<?php

class Allreport_Model_DbTable_DbRptStudentBalance extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_payment';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
   
    
    
    public function getStudentBalance($search){
    	    	$db = $this->getAdapter();
				$_db=new Application_Model_DbTable_DbGlobal();
				$branch= $_db->getBranchDisplay();

    	    	$where=' ';
    	    	$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    	    	$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    	    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    		   	$sql=" SELECT 
				   (SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id = sp.branch_id LIMIT 1) AS branch_name,
				   sp.receipt_number,
				   sp.`penalty`,
				   sp.`grand_total`,
				   sp.`credit_memo`,
				   sp.`paid_amount`,
				   sp.`balance_due`,
				   s.stu_code,
				   s.stu_khname,
				   s.stu_enname,
				   s.last_name,
				   sp.is_current,
				   (SELECT title FROM rms_itemsdetail WHERE rms_itemsdetail.id=sp.grade LIMIT 1) AS grade_name,	
				   sp.note,		  
				   (SELECT first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS USER ,
				   sp.create_date                    
			   FROM  rms_student_payment AS sp, rms_student AS s WHERE s.stu_id = sp.student_id AND
				  is_void=0 AND sp.status=1 AND sp.`balance_due` > 0";
    	    	$order=" ORDER BY id DESC";
    	 
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
    		
    		$s_where[] = " REPLACE(stu_code,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(stu_enname,' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(stu_khname,' ','') LIKE '%{$s_search}%'";
    		$s_where[]=	 " REPLACE(CONCAT(last_name,stu_enname),' ','') LIKE '%{$s_search}%'";
    		$s_where[] = " REPLACE(receipt_number,' ','') LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['branch_id']>0){
    		$where .= " and sp.branch_id = ".$search['branch_id'];
    	}
    	if($search['grade']>0){
    		$where .= " and sp.grade = ".$search['grade'];
    	}
		if($search['is_current']>-1 AND $search['is_current'] !=''){
    		$where .= " and sp.is_current  = ".$search['is_current'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }

	
 
}   