<?php
class Allreport_Model_DbTable_DbProductList extends Zend_Db_Table_Abstract
{
    function getProductLocation($search=null){
    	$db=$this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$level = $_db->getUserType();
    	$lang = $_db->currentlang();
		
		$label = "name_en";
		$branch = "branch_nameen";
		$grade = "p.title_en";
		$degree = "it.title_en";
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$branch = "branch_namekh";
    		$grade = "p.title";
    		$degree = "it.title";
    	}
    	// if($level==4){
    	// 	$branch_id = $_db->getAccessPermission("branch_id");
    	// }else{
    	// 	$branch_id = "";
    	// }
    	
    	$sql="SELECT 
    				p.code AS pro_code,
    				p.images,
    				$grade AS pro_name ,
    				(SELECT $degree FROM `rms_items` AS it WHERE it.id = p.items_id LIMIT 1) AS category_name,
    	            (SELECT $branch FROM rms_branch WHERE rms_branch.br_id=pl.branch_id LIMIT 1) AS brand_name,
					CASE    
					WHEN p.product_type = 1 THEN '".$tr->translate("PRODUCT_FOR_SELL")."'
					WHEN p.product_type = 2 THEN '".$tr->translate("OFFICE_MATERIAL")."'
					END AS product_type,
    				pl.pro_qty,
    				pl.note,
					pl.costing,
    				pl.price AS pro_price,
					pl.price_set
			  FROM 
			  		`rms_itemsdetail` AS p 
			  		JOIN rms_product_location AS pl ON p.id=pl.pro_id 
			  WHERE 
			  		p.status=1
    				AND pl.isProductSet = 0
    				AND p.items_type=3
    				";
    	$where=" ";
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]= " p.code LIKE '%{$s_search}%'";
    		$s_where[]= " p.title LIKE '%{$s_search}%'";
    		$s_where[]= " p.cost LIKE '%{$s_search}%'";
    		$s_where[]= " p.price LIKE '%{$s_search}%'";
    		$s_where[]= "  pl.pro_qty LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND pl.branch_id=".$search['branch_id'];
    	}
    	if(!empty($search['product'])){
    		$where.=" AND p.id=".$search['product'];
    	}
    	if($search['category_id']>0){
			$arrCon = array(
				"categoryId" => $search['category_id'],
			);
			$condiction = $_db->getChildItems($arrCon);
			if (!empty($condiction)){
				$where.=" AND p.items_id IN ($condiction)";
			}else{
				$where.=" AND p.items_id=".$search['category_id'];
			}
    	}
    	if($search['product_type']>0){
    		$where.=" AND p.product_type=".$search['product_type'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('pl.branch_id');
    	$order = " ORDER BY pl.branch_id ASC ";
    	if($search['sort_by']==1){
    		$order.=" , p.items_id ASC ";
    	}else if($search['sort_by']==2){
    		$order.=" , $grade ASC ";
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    function getProductsByLocId($loc_id){//use
    	$db=$this->getAdapter();
    	$sql="SELECT p.pro_code,p.pro_name,
				       pl.pro_qty,p.pro_price,pl.total_amount,p.date,
				       (SELECT name_kh FROM rms_view WHERE rms_view.key_code=p.status AND rms_view.type=1) AS `status`
				FROM rms_product AS p,rms_product_location AS pl
				WHERE p.id=pl.pro_id AND pl.pro_id=$loc_id";
    	return $db->fetchAll($sql);
    }
    function getLocationNameById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT CONCAT(branch_nameen,'-',branch_namekh) AS NAME FROM rms_branch WHERE br_id=$id";
    	return $db->fetchRow($sql);
    }
    
}



