<?php

class Registrar_Model_DbTable_DbReportProductNearOutStock extends Zend_Db_Table_Abstract
{
    
	function getLowStockQuantity($search=null){
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$level = $_db->getUserType();
		
		if($level==4){
			$branch_id = $_db->getAccessPermission("pl.branch_id");
		}else{
			$branch_id = "";
		}
		
    	$db=$this->getAdapter();
    	$sql="SELECT 
				p.code AS pro_code,
				p.title AS pro_name ,
				(SELECT it.title FROM `rms_items` AS it WHERE it.id = p.items_id AND it.type=3 LIMIT 1) AS category_name,
				(SELECT branch_namekh FROM rms_branch WHERE rms_branch.br_id=pl.branch_id LIMIT 1) AS brand_name,
				pl.branch_id,
				pl.pro_qty,
				pl.price AS pro_price,
				p.price,
				p.create_date AS DATE,
				(SELECT name_kh FROM rms_view WHERE rms_view.key_code=p.status AND rms_view.type=1 LIMIT 1) AS `status` 
			  FROM 
					rms_itemsdetail AS p 
					JOIN rms_product_location AS pl ON p.id=pl.pro_id
			  WHERE 
			  	p.status=1
				AND pl.isProductSet = 0
				AND pl.stock_alert>=pl.pro_qty
    				$branch_id
    		";

    	$where=" ";
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]= " p.code LIKE '%{$s_search}%'";
    		$s_where[]= " p.title LIKE '%{$s_search}%'";
    		$s_where[]= " pl.price LIKE '%{$s_search}%'";
    		$s_where[]= " pl.pro_qty LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND pl.branch_id=".$search['branch_id'];
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
    	if($search['product']>0){
    		$where.=" AND p.id=".$search['product'];
    	}
    	if($search['product_type']>0){
    		$where.=" AND p.product_type=".$search['product_type'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('pl.branch_id');
    	$where.=" ORDER BY pl.branch_id DESC, pl.pro_qty ASC ";
    	return $db->fetchAll($sql.$where);
    }
	    
}