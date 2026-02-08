<?php

class Stock_Model_DbTable_DbMovement extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_stock_movements';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
	// get product location info by id
	public function getProductLocationInfo($_id){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$branch = $dbp->getBranchDisplay();
		$sql = "SELECT 
				pl.pro_id,
				pl.branch_id,
				(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = pl.branch_id LIMIT 1) AS branch_name,
				pl.pro_qty,
				pl.costing,
				pl.price,
				pl.price_set,
				ide.code AS product_code,
				ide.title,
				ide.title_en
		FROM `rms_product_location` AS pl 
		LEFT JOIN `rms_itemsdetail` AS ide ON ide.id = pl.pro_id WHERE pl.id = ?";
		return $db->fetchRow($sql, $_id);
	}

	// get all movement stock by product id and branch id
	public function getMovementStockByProductId($_data){
		$db = $this->getAdapter();
		$sql = "SELECT 
				mv.*,
				v.name_kh AS movementTitle,
				v.name_en AS movementTitleEn,
				mv.movement_type,
				CASE 
					WHEN mv.movement_type = 2 THEN p.purchaseNo
					WHEN mv.movement_type = 3 THEN rec.receive_no
					WHEN mv.movement_type IN (4, 6) THEN req.request_no
					WHEN mv.movement_type = 5 THEN sp.receipt_number
					WHEN mv.movement_type = 7 THEN t.transfer_no
					ELSE '' 
				END AS referenceNo

				FROM `rms_stock_movements` AS mv

				LEFT JOIN `rms_view` AS v 
				ON v.type = 44 AND v.key_code = mv.movement_type

				LEFT JOIN `rms_purchase` AS p 
				ON p.id = mv.reference AND mv.movement_type = 2

				LEFT JOIN `rms_received_note` AS rec 
				ON rec.id = mv.reference AND mv.movement_type = 3

				LEFT JOIN `rms_request_order` AS req
				ON req.id = mv.reference AND mv.movement_type IN (4, 6)

				LEFT JOIN `rms_student_payment` AS sp
				ON sp.id = mv.reference AND mv.movement_type = 5

				LEFT JOIN `rms_transferstock` AS t 
				ON t.id = mv.reference AND mv.movement_type = 7
	
			WHERE mv.product_id = ? AND mv.branch_id = ?";
		return $db->fetchAll($sql, array($_data['product_id'], $_data['branch_id']));
	}
    public function addMovementStock($_data){
    	$_arr = array(
				'branch_id'     =>$_data['branch_id'],
				'product_id'	=>$_data['product_id'],
				'movement_type' =>$_data['movement_type'],
				'quantity'		=>$_data['quantity'],
				'reference'		=>$_data['reference'],
				'note'			=>empty($_data['note'])?'':$_data['note'],
				'movement_date'	=>date("Y-m-d"),
				'create_date'	=>date("Y-m-d H:i:s"),
				'modify_date'	=>date("Y-m-d H:i:s"),
				'user_id'		=>$this->getUserId(),
    		);
		$this->_name='rms_stock_movements';
    	$this->insert($_arr);
    }

	// delete function where refernce and movement_type as parameters
	public function deleteMovementStock($_reference, $_movement_type){
		$where = array();
		$where[] = 'reference = "'.$_reference.'"';
		$where[] = 'movement_type = "'.$_movement_type.'"';
		$this->_name='rms_stock_movements';
		$this->delete($where);
	}
   
}