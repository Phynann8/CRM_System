<?php

class Stock_Model_DbTable_DbTransferstock extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_transferstock';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllTransfer($search=null){  
		$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=$this->getAdapter();

		$pending=$tr->translate('PENDING');
		$reveived=$tr->translate('RECEIVED');
		$branch = $dbp->getBranchDisplay();
    	$sql = "SELECT 
					s.id,
					s.transfer_no,
					s.transfer_date,
					rb_from.$branch AS from_location,
					rb_to.$branch AS to_location,
					s.note,
					CASE 
						WHEN s.is_received = 0 THEN '$pending' 
						WHEN s.is_received = 1 THEN '$reveived'
					END AS transferStatus,
					ru.first_name AS user_name ";
		$sql.=$dbp->caseStatusShowImage("s.status");
		$sql.="	FROM rms_transferstock AS s
				LEFT JOIN rms_branch AS rb_from ON rb_from.br_id = s.from_location
				LEFT JOIN rms_branch AS rb_to ON rb_to.br_id = s.to_location
				LEFT JOIN rms_users AS ru ON ru.id = s.user_id 
				WHERE s.status =1 
				";

    	$from_date =(empty($search['start_date']))? '1': " s.transfer_date>= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.transfer_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
	    if(!empty($search['adv_search'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['adv_search']));
	    	$s_where[] = " s.transfer_no LIKE '%{$s_search}%'";
	    	$s_where[] = " s.note LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
	    }
		if(!empty($search['branch_id'])){
			$where.=" AND s.from_location =".$search['branch_id'];
		}
		// if($search['transfer_status'] > -1 ){
		// 	$where.=" AND s.is_received =".$search['transfer_status'];
		// }
		$where.=$dbp->getAccessPermission('s.from_location');
    	$order=" ORDER BY s.id DESC ";
		//echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
	 function getAllTransferReport($search=null){  
		$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=$this->getAdapter();

		$pending=$tr->translate('PENDING');
		$reveived=$tr->translate('RECEIVED');
		$branch = $dbp->getBranchDisplay();
    	$sql = "SELECT 
					s.id,
					s.transfer_no,
					s.transfer_date,
					rb_from.$branch AS from_location,
					rb_to.$branch AS to_location,
					s.note,
					CASE 
						WHEN s.is_received = 0 THEN '$pending' 
						WHEN s.is_received = 1 THEN '$reveived'
					END AS transferStatus,
					ru.first_name AS user_name,
					it.code,
					it.title,
					td.qty,
					td.qty_after,
					td.note ";
		$sql.=$dbp->caseStatusShowImage("s.status");
		$sql.="	FROM rms_transferstock AS s
		   		INNER JOIN `rms_transferdetail` AS td ON td.transferid = s.id
				LEFT JOIN `rms_itemsdetail` AS it ON it.id = td.pro_id
				LEFT JOIN rms_branch AS rb_from ON rb_from.br_id = s.from_location
				LEFT JOIN rms_branch AS rb_to ON rb_to.br_id = s.to_location
				LEFT JOIN rms_users AS ru ON ru.id = s.user_id 
				WHERE s.status =1 
				";

    	$from_date =(empty($search['start_date']))? '1': " s.transfer_date>= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.transfer_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
	    if(!empty($search['adv_search'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['adv_search']));
	    	$s_where[] = " s.transfer_no LIKE '%{$s_search}%'";
	    	$s_where[] = " s.note LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
	    }
		if(!empty($search['branch_id'])){
			$where.=" AND s.from_location =".$search['branch_id'];
		}
		if($search['transfer_status'] > -1 ){
			$where.=" AND s.is_received =".$search['transfer_status'];
		}
		$where.=$dbp->getAccessPermission('s.from_location');
    	$order=" ORDER BY s.id DESC ";
		//echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    function getTransferById($id){
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$branch = $dbGb->getBranchDisplay();
    	$db = $this->getAdapter();
    	$sql = "SELECT t.* ,
				(SELECT b.$branch  FROM rms_branch AS b WHERE b.br_id=t.from_location)AS from_location_name ,
				(SELECT b.$branch  FROM rms_branch AS b WHERE b.br_id=t.to_location)AS to_location_name
			FROM 
				rms_transferstock AS t 
			WHERE 
				t.id='".$id."'";
		$sql.=$dbGb->getAccessPermission('t.from_location');
    	return $db->fetchRow($sql);
    }
    function getTransferByIdDetail($id,$frombranch=null){
    	$db = $this->getAdapter();
    	if (!empty($frombranch)){
    		$sql = "
    			SELECT trd.*,
				(SELECT pl.pro_qty FROM rms_product_location AS pl WHERE pl.branch_id = $frombranch AND pl.pro_id = trd.pro_id LIMIT 1 ) AS curr_qty,
				(SELECT ide.title FROM `rms_itemsdetail` AS ide WHERE ide.items_type=3 AND ide.id = trd.pro_id LIMIT 1) AS pro_name
		    	FROM rms_transferdetail  AS trd WHERE trd.transferid=$id";
    	}else{
	    	$sql = "SELECT *,
			(SELECT ide.title FROM `rms_itemsdetail` AS ide WHERE ide.items_type=3 AND ide.id = pro_id LIMIT 1) AS pro_name
	    	FROM rms_transferdetail WHERE transferid=".$id;
    	}
    	return $db->fetchAll($sql);
    }
	function getReceiveNoteNo($branch_id){
    	$db = $this->getAdapter();
    	$sql="SELECT (id+1)FROM `rms_received_note` WHERE branch_id = $branch_id ORDER BY id DESC  LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre="RC";
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    public function addTransferStock($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
				$dbmv = new Stock_Model_DbTable_DbMovement();
	    		$key = new Application_Model_DbTable_DbKeycode();
	    		$keydata=$key->getKeyCodeMiniInv(TRUE);
	    		$condictionTransfer = empty($keydata['trasfer_st_cut'])?0:$keydata['trasfer_st_cut'];//0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive
    			   
    			$tran_no = $this->getTransferNo();
	    		$_arr = array(
	    				'transfer_no'=>$_data['transfer_no'],
	    				'transfer_date'=>$_data['transfer_date'],
	    				'from_location'=>$_data['from_location'],
	    				'to_location'=>$_data['to_location'],
	    				'note'=>$_data['note'],
	    				'create_date'=>date("Y-m-d H:i:s"),
	    				'modify_date'=>date("Y-m-d H:i:s"),
	    				'user_id'=>$this->getUserId(),
	    				'status'=>1,
	    				);
		    		if ($condictionTransfer!=1){
		    			$_arr['is_received']=1;
		    		}else{
		    			$_arr['is_received']=0;
		    		}
	    		$tranid= $this->insert($_arr);

				/// Recived Auto

				if ($condictionTransfer!=1){
					$receive_no = $this->getReceiveNoteNo($_data['to_location']);
					$_arr = array(
						'branch_id' 	=>$_data['to_location'],
						'transfer_id' 	=> $tranid,
						'receive_no'	=>$receive_no,
						'receive_date'	=>$_data['transfer_date'],
						'note'			=>$_data['note'],
						'create_date'	=>date("Y-m-d H:i:s"),
						'modify_date'	=>date("Y-m-d H:i:s"),
						'user_id'		=>$this->getUserId(),
						'status'		=>1,
					);
					$this->_name ="rms_received_note";
					$receive_id = $this->insert($_arr);
				}

	    	
	    		$ids = explode(',', $_data['identity']);
	    		foreach ($ids as $i){
    				$_arr = array(
    					'transferid'=> $tranid,
    					'pro_id'	=> $_data['pro_id_'.$i],
    					'qty'		=> $_data['qty_'.$i],
    					'qty_after' =>$_data['qty_'.$i],
    					'note'		=> $_data['remark_'.$i],
    				);
    				$this->_name='rms_transferdetail';
    				$this->insert($_arr);
    				
    				if ($condictionTransfer!=1){
						
						/// Received Detail

						$_arr = array(
    						'receive_id'=> $receive_id,
    						'pro_id'	=> $_data['pro_id_'.$i],
    						'qty'		=> $_data['qty_'.$i],
    						'note'		=> 'Auto Recieved ',
						);
						$this->_name='rms_received_note_detail';
						$this->insert($_arr);

						// add movement stock
						$param = array(
							'branch_id'     =>$_data['to_location'],
							'product_id'	=>$_data['pro_id_'.$i],
							'movement_type' =>3, // 3=Transfer Stock in,
							'quantity'		=>$_data['qty_'.$i],
							'reference'		=>$receive_id ,
						);
						$dbmv->addMovementStock($param );

						/// From Loation
    					$qty_main_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['from_location']);
						$this->_name="rms_product_location";
						if(!empty($qty_main_stock)){
							$qty = $qty_main_stock['pro_qty'] - $_data['qty_'.$i];
							$array = array(
									'pro_qty'=>$qty,
							);
							$where = " id = ".$qty_main_stock['id'];
							$this->update($array, $where);
						}
						//to location
						$qty_stock_recieve = $this->getProductLocation($_data['pro_id_'.$i],$_data['to_location']);
						if(!empty($qty_stock_recieve)){
							// update cost average
							$total_amount_transfer= $qty_main_stock['costing']*$_data['qty_'.$i];
							$this->updateProductCost( $_data['pro_id_'.$i],$_data['to_location'], $_data['qty_'.$i], $total_amount_transfer);

							// Update Qty Stock
							$qty = $qty_stock_recieve['pro_qty'] + $_data['qty_'.$i];
							$array = array(
									'pro_qty'=>$qty,
							);
							$where = " id = ".$qty_stock_recieve['id'];
							$this->update($array, $where);
						}else{
							$data = array(
									'pro_id'=>$_data['pro_id_'.$i],
									'branch_id'=>$_data['to_location'],
									'pro_qty'=>$_data['qty_'.$i],
									'costing'=>$qty_main_stock['costing'],
									'price'	=>$qty_main_stock['price'],
									'price_set'	=>$qty_main_stock['price_set'],
									'note'=>'ពីផ្ទេរទំនិញចូល',
									'date' =>date("Y-m-d"),
									'user_id'=>$this->getUserId(),
									'status'=>1);
							$this->insert($data);
						}

						// add movement stock out
						$param1 = array(
							'branch_id'     =>$_data['from_location'],
							'product_id'	=>$_data['pro_id_'.$i],
							'movement_type' =>7, // 7=Transfer Stock out,
							'quantity'		=>-$_data['qty_'.$i],
							'reference'		=>$tranid,
						
						);
						$dbmv->addMovementStock($param1 );

    				}else{

						$qty_main_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['from_location']);
						$this->_name="rms_product_location";
						if(!empty($qty_main_stock)){
							$qty = $qty_main_stock['pro_qty'] - $_data['qty_'.$i];
							$array = array(
									'pro_qty'=>$qty,
							);
							$where = " id = ".$qty_main_stock['id'];
							$this->update($array, $where);
						}

						// add movement stock

						$param = array(
							'branch_id'     =>$_data['from_location'],
							'product_id'	=>$_data['pro_id_'.$i],
							'movement_type' =>7, // 7=Transfer Stock out,
							'quantity'		=>-$_data['qty_'.$i],
							'reference'		=>$tranid,
							
						);
						$dbmv->addMovementStock($param );

					}
    				
	    		}
	    	    $db->commit();
	    	    return true;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }

	function updateProductCost($pro_id,$branch,$qty,$total_amount_transfer){
    	$db = $this->getAdapter();
    	$sql="SELECT 
    				p.id,
    				pl.costing,
    				SUM(pl.pro_qty) pro_qty
    			from
    				rms_itemsdetail as p,
    				rms_product_location as pl
    			where 
    				p.id = pl.pro_id
    				and p.status = 1
    				and p.id = $pro_id AND pl.branch_id= $branch ";
    	$result = $db->fetchRow($sql);
    	
    	if(!empty($result)){
			$stock_qty = ($result['pro_qty']<0)? '0': $result['pro_qty'];
    		$total_amount_in_stock = $stock_qty * $result['costing'];
    		$total_qty_sum = $stock_qty + $qty;
    		
    		$last_cost = ($total_amount_in_stock + $total_amount_transfer)/$total_qty_sum;

			$array = array(
				"costing"=>$last_cost,
				);
		
			$this->_name = "rms_product_location";
			$where = " branch_id=".$branch ." AND pro_id = ".$result['id'];
			$this->update($array, $where);
    	}
    }
    function getProductSet($product_id){// select item for product set
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `rms_product_setdetail` WHERE pro_id  = ".$product_id;
    	return $db->fetchAll($sql);
    }
    function checkisProductSet($product_id){//if product is set
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM  rms_itemsdetail WHERE is_productseat=1 AND id = ".$product_id;
    	return $db->fetchRow($sql);
    }
    function getTransferNo(){
    	$db = $this->getAdapter();
    	$sql="SELECT (id+1)FROM `rms_transferstock` ORDER BY id DESC  LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
	    $new_acc_no= (int)$acc_no+1;
	  	$acc_no= strlen((int)$acc_no+1);
	  	$pre="";
	  	for($i = $acc_no;$i<5;$i++){
	  		$pre.='0';
	  	}
    	return $pre.$new_acc_no;
    }
    function getProductLocation($pro_id,$location_id){
    	$db = $this->getAdapter();
    	$sql="select * from rms_product_location where pro_id=".$pro_id." AND branch_id = ".$location_id;
    	$row = $db->fetchRow($sql);
    	return $row;
    }
	
	public function updateTransferStock($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$dbmv = new Stock_Model_DbTable_DbMovement();
			$key = new Application_Model_DbTable_DbKeycode();
			$keydata = $key->getKeyCodeMiniInv(TRUE);
			$condictionTransfer = empty($keydata['trasfer_st_cut']) ? 0 : $keydata['trasfer_st_cut'];

			$rsold = $this->getTransferById($_data['id']);
			$_arr = [
				'transfer_no'   => $_data['transfer_no'],
				'transfer_date' => $_data['transfer_date'],
				'from_location' => $_data['from_location'],
				'to_location'   => $_data['to_location'],
				'note'          => $_data['note'],
				'user_id'       => $this->getUserId(),
				'status'        => $_data['status'],
				'is_received'   => ($condictionTransfer != 1 ? 1 : 0),
			];

			$this->update($_arr, "id = ".$_data['id']);

			$rsdetail = $this->getTransferByIdDetail($_data['id']);

			// Rollback previous transfer if already processed and active
			if ($condictionTransfer != 1 && !empty($rsdetail) && $rsold['status'] == 1) {
				foreach ($rsdetail as $rsd) {
					// Roll back from old from_location
					$qty_stock = $this->getProductLocation($rsd['pro_id'], $rsold['from_location']);
					if (!empty($qty_stock)) {
						$this->_name = "rms_product_location";
						$qty = $qty_stock['pro_qty'] + $rsd['qty'];
						$this->update(['pro_qty' => $qty], "id = " . $qty_stock['id']);
					}

					// Roll back to old to_location
					$qty_stock = $this->getProductLocation($rsd['pro_id'], $rsold['to_location']);
					if (!empty($qty_stock)) {
						$this->_name = "rms_product_location";
						$qty = $qty_stock['pro_qty'] - $rsd['qty'];
						$this->update(['pro_qty' => $qty], "id = " . $qty_stock['id']);
					}
				}
			}

			// delete old movement stock

			$dbmv->deleteMovementStock($_data['id'], 7); // 6

			// Delete old transfer details
			$this->_name = 'rms_transferdetail';
			$this->delete("transferid = " . $_data['id']);

			// Re-insert updated detail
			$ids = explode(',', $_data['identity']);
			foreach ($ids as $i) {
				$proId = $_data['pro_id_'.$i];
				$qty   = $_data['qty_'.$i];

				$detailArr = [
					'transferid' => $_data['id'],
					'pro_id'     => $proId,
					'qty'        => $qty,
					'qty_after'  => $qty,
					'note'       => $_data['remark_'.$i],
				];

				$this->_name = 'rms_transferdetail';
				$this->insert($detailArr);

				if ($condictionTransfer != 1 && $_data['status'] == 1) {
					// From location
					$qty_main_stock = $this->getProductLocation($proId, $_data['from_location']);
					if (!empty($qty_main_stock)) {
						$this->_name = "rms_product_location";
						$newQty = $qty_main_stock['pro_qty'] - $qty;
						$this->update(['pro_qty' => $newQty], "id = " . $qty_main_stock['id']);
					}

					// To location
					$qty_stock_receive = $this->getProductLocation($proId, $_data['to_location']);
					$costing = isset($qty_main_stock['costing']) ? $qty_main_stock['costing'] : 0;
					$total_amount_transfer = $costing * $qty;

					if (!empty($qty_stock_receive)) {
						$this->updateProductCost($proId, $_data['to_location'], $qty, $total_amount_transfer);
						$newQty = $qty_stock_receive['pro_qty'] + $qty;
						$this->update(['pro_qty' => $newQty], "id = " . $qty_stock_receive['id']);
					} else {
						$this->_name = "rms_product_location";
						$this->insert([
							'pro_id'    => $proId,
							'branch_id' => $_data['to_location'],
							'pro_qty'   => $qty,
							'costing'   => $costing,
							'note'      => 'ពីផ្ទេរទំនិញចូល',
							'date'      => date("Y-m-d"),
							'user_id'   => $this->getUserId(),
							'status'    => 1,
						]);
					}
					// Add movement stock out

					$param = [
						'branch_id'     => $_data['from_location'],
						'product_id'    => $proId,
						'movement_type' => 7, // 7=Transfer Stock out,
						'quantity'      => -$qty,
						'reference'     => $_data['id'],
						
					];
					$dbmv->addMovementStock($param);
			
				} elseif ($condictionTransfer == 1) {
					// If transfer without receiving, only cut from main
					$qty_main_stock = $this->getProductLocation($proId, $_data['from_location']);
					if (!empty($qty_main_stock)) {
						$this->_name = "rms_product_location";
						$newQty = $qty_main_stock['pro_qty'] - $qty;
						$this->update(['pro_qty' => $newQty], "id = " . $qty_main_stock['id']);
					}

					// Add movement stock out
					$param = [
						'branch_id'     => $_data['from_location'],
						'product_id'    => $proId,
						'movement_type' => 7, // 7=Transfer Stock out,
						'quantity'      => -$qty,
						'reference'     => $_data['id'],
						
					];
					$dbmv->addMovementStock($param);
				}
			}

			$db->commit();
			return true;
		} catch (Exception $e) {
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}  
    
}