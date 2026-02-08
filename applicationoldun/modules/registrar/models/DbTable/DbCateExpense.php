<?php
class Registrar_Model_DbTable_DbCateExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_expense_item';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->branch_id;
	}

	function getAllExpenseItem($search){
		$db = $this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$id = "c.id";
		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
		$urlEdit = $base_url."/registrar/cateexpense/edit/id/";

		$sql="SELECT 
				c.id,
				c.accountCode ,
				c.categoryTitle AS name,
				p.categoryTitle AS category,
				c.createDate AS create_date,
				u.first_name AS USER
			";
		$sql.=$dbgb->caseStatusShowImage("c.status");
		$sql.=$dbgb->caseEdit($id,$urlEdit);
		$sql.=" FROM rms_expense_item AS c
			LEFT JOIN rms_expense_category AS p ON c.categoryId = p.id
			LEFT JOIN rms_users AS u ON c.user_id = u.id
			WHERE 1 ";
		$where = '';
		if(!empty($search['adv_search'])){
			$s_where = array();
			$advSearch = htmlspecialchars($search['adv_search'], ENT_QUOTES);
			$s_search = trim(addslashes($advSearch));
			$s_where[]=" c.accountCode LIKE '%{$s_search}%'";
			$s_where[]=" c.categoryTitle LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND c.status = ".$search['status'];
		}
		$order = " ORDER BY c.id desc ";
		return $db->fetchAll($sql.$where.$order);
		
	
	}
	
	function getExpenseItem($search=null){
		$db = $this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$sql="SELECT
					t.id,
					t.categoryTitle as name,
					t.categoryId,
					t.createDate as create_date,
					(SELECT first_name FROM rms_users WHERE rms_users.id = t.user_id) as user,
					t.status
			FROM
				rms_expense_item as t
				LEFT JOIN rms_expense_category as c on c.id = t.categoryId
				where t.status =1 ";
		$where = '';
		if(!empty($search['categoryId'])){
			$where.= " AND t.categoryId = ".$search['categoryId'];
		}
		if(!empty($search['parentId'])){
			$where.= " AND c.parentId = ".$search['parentId'];
		}
		$order = " ORDER BY t.id desc ";
		return $db->fetchAll($sql.$where.$order);
		
	}
	
	function addCateExpense($data){
		$db= $this->getAdapter();
		try{
			
			$titleCate = htmlspecialchars($data['title'], ENT_QUOTES);
			
			$sql="SELECT id FROM rms_expense_item where categoryTitle ='".$titleCate."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			
			$array = array(
					'accountCode'	=>$data['accountCode'],
					'categoryTitle'	=>$data['title'],
					'categoryId'	=>$data['categoryId'],
					'user_id'		=>$this->getUserId(),
					'createDate'	=>date('Y-m-d'),
				);
			$this->insert($array);
		}catch (Exception $e){
		}
 	 }
 	 
	 function updateCateExpense($data){
		$status = empty($data['status'])?0:1;
		$arr = array(
				'accountCode'	=>$data['accountCode'],
				'categoryTitle'	=>$data['title'],
				'categoryId'	=>$data['categoryId'],
				'status'		=>$status,
				'user_id'		=>$this->getUserId(),
			);
		$where=" id = ".$data['id'];
		$this->update($arr, $where);
	}
	
	function getCateExpenseById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_expense_item where id=$id ";
		return $db->fetchRow($sql);
	}
	
	function getAllCateExpense($search=null){
		$db = $this->getAdapter();
		$sql=" SELECT 
					ac.id,
					ac.categoryTitle,
					(select first_name from rms_users where rms_users.id = ac.user_id) as user,
					createDate,
					ac.status
				FROM 
					rms_expense_item as ac 
				where 
					categoryTitle!=''";
		$where = " ";
		if (!empty($search['adv_search'])){
			$s_where = array();
			$advSearch = htmlspecialchars($search['adv_search'], ENT_QUOTES);
			$s_search = trim(addslashes($advSearch));
			$s_where[] = " categoryTitle LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
        $order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getParentCateExpense($cate_id='',$parent = 0, $spacing = '', $cate_tree_array = ''){
		$db=$this->getAdapter();
		if (!is_array($cate_tree_array)){$cate_tree_array = array();}
		$sql = " SELECT id , categoryTitle as name FROM rms_expense_item where status=1 AND `parentId` = $parent ";
		if (!empty($cate_id)){
			$sql.=" AND id != $cate_id";
		}
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
	
		$id='';
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getParentCateExpense($cate_id,$row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
// New Category Expense
	public function getParentCategoryExpense($cate_id='',$parent = 0, $spacing = '', $cate_tree_array = ''){
		$db=$this->getAdapter();
		if (!is_array($cate_tree_array)){$cate_tree_array = array();}
		$sql = " SELECT id , categoryTitle as name FROM rms_expense_category where status=1 AND `parentId` = $parent ";
		if (!empty($cate_id)){
			$sql.=" AND id != $cate_id";
		}
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
	
		$id='';
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getParentCategoryExpense($cate_id,$row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
}