<?php
class Allreport_Model_DbTable_DbRequestStock extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_request_order';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	
	
	function getAllRequestProductDetail($search=null){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
    	$lang = $dbp->currentlang();
		$colTitle = "title_en";
    	if($lang==1){
    		$colTitle = "title";
    	}
		$sql="SELECT 	
				r.id,
				r.`request_no`,
				b.$branch AS branch_name,
				rf.title AS request_for,
				fs.title AS for_section,
				p.$colTitle AS pro_name,
				p.code AS pro_code,
				rd.`qty_request`,
				rd.`qty_receive`,
				rd.`qty_return`,
				r.`isReturn`,
				r.`request_date`,
				r.`returnDate`
			FROM rms_request_order AS r
			JOIN rms_request_orderdetail AS rd ON r.id = rd.request_id
			LEFT JOIN rms_itemsdetail AS p ON p.id = rd.pro_id
			LEFT JOIN rms_branch AS b ON b.br_id = r.branch_id
			LEFT JOIN rms_request_for AS rf ON rf.id = r.request_for
			LEFT JOIN rms_for_section AS fs ON fs.id = r.for_section
			WHERE r.status = 1";
		$where="";
		$from_date =(empty($search['start_date']))? '1': " r.request_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " r.request_date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$from_date." AND ".$to_date;
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]= " REPLACE(r.request_no,' ','') LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(rf.title,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(fs.title,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(p.title,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(p.code,' ','') LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($search['branch_id']>0){
			$where.=" AND r.branch_id=".$search['branch_id'];
		}
		if($search['request_for']>0){
			$where.=" AND r.request_for=".$search['request_for'];
		}
		if($search['for_section']>0){
			$where.=" AND r.for_section=".$search['for_section'];
		}
		if($search['category_id']>0){
			$where.=" AND p.items_id =".$search['category_id'];
			$arrCon = array(
				"categoryId" => $search['category_id'],
			);
			$condiction = $dbp->getChildItems($arrCon);
			if (!empty($condiction)){
				$where.=" AND p.items_id IN ($condiction)";
			}else{
				$where.=" AND p.items_id=".$search['category_id'];
			}
		}
		if($search['product']>0){
			$where.=" AND rd.`pro_id` =".$search['product'];
		}
		if($search['product_type']>0){
			$where.=" AND p.product_type =".$search['product_type'];
		}
		if($search['request_status']>-1){
			$where.=" AND r.isReturn =".$search['request_status'];
		}
		$sql.=$dbp->getAccessPermission('r.branch_id');
		$order = " ORDER BY r.id DESC, rd.`pro_id` ASC";
		return $db->fetchAll($sql.$where.$order);
	}
	function getRequestProductById($id){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
    	$lang = $dbp->currentlang();
		$label = "name_en";
    	if($lang==1){
    		$label = "name_kh";
    	}
		$sql="SELECT
					re.*,
					(SELECT title FROM rms_request_for as rf WHERE rf.id = request_for LIMIT 1) as request_for,
    				(SELECT title FROM rms_for_section as fs WHERE fs.id = for_section LIMIT 1) as for_section,
					(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = branch_id LIMIT 1) as branch_name,
					(SELECT first_name FROM rms_users as u WHERE u.id = re.user_id LIMIT 1) as user,
					(SELECT v.$label FROM rms_view AS v WHERE v.type=1 AND v.key_code = re.status LIMIT 1) as status
				FROM
					rms_request_order AS re
				WHERE
					re.id = $id
		";
		return $db->fetchRow($sql);
	}
	
	function getAllRequestProductDetailById($id){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
		$lang = $dbp->currentlang();
		$colTitle = "title_en";
    	if($lang==1){
    		$colTitle = "title";
    	}
		$sql="SELECT
					*,
					(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = branch_id LIMIT 1) as branch_name,
					(SELECT d.$colTitle FROM `rms_itemsdetail` AS d WHERE d.items_type=3 AND d.id = pro_id LIMIT 1) AS pro_name
				FROM
					rms_request_orderdetail 
				WHERE
					request_id = $id
			";
		return $db->fetchAll($sql);
	}
	
	
	function getAllAdjustStockDetail($search=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
		$sql="SELECT 
					ad.adjust_no,
					ad.request_name,
					ad.note,
					ad.request_date,
					ad.create_date,
			        (SELECT b.$branch FROM rms_branch AS b WHERE b.br_id=adj.branch_id LIMIT 1)AS branch_name,
			        (SELECT it.title FROM `rms_items` AS it WHERE it.id = i.items_id LIMIT 1) AS category,
			       	i.title AS pro_name,
			         adj.qty_befor,
			         adj.qty_after,
			         adj.difference,
		      		 ad.status,
					(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id=ad.user_id LIMIT 1) AS user_name
				FROM 
					rms_adjuststock AS ad,
					rms_adjuststock_detail AS adj,
					rms_itemsdetail as i
				WHERE 
					ad.id=adj.adjuststock_id
					AND i.id = adj.pro_id
			";
		$from_date =(empty($search['start_date']))? '1': " ad.request_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " ad.request_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['title'])){
			$s_where=array();
			$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
			$s_where[]= " REPLACE(ad.adjust_no,' ','') LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(ad.request_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(ad.note,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(i.title,' ','') LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if(!empty($search['product'])){
    		$where.=" AND adj.pro_id=".$search['product'];
    	}
    	if($search['category_id']>0){
    		//$where.=" AND i.items_id =".$search['category_id'];
			$arrCon = array(
				"categoryId" => $search['category_id'],
			);
			$condiction = $dbp->getChildItems($arrCon);
			if (!empty($condiction)){
				$where.=" AND i.items_id IN ($condiction)";
			}else{
				$where.=" AND i.items_id=".$search['category_id'];
			}
    	}
    	if($search['product_type']>0){
    		$where.=" AND i.product_type =".$search['product_type'];
    	}
		if($search['branch_id']){
			$where.=" AND adj.branch_id=".$search['branch_id'];
		}
		if($search['user']){
			$where.=" AND ad.user_id=".$search['user'];
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('ad.branch_id');
		$order=" ORDER BY ad.id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
    
}



