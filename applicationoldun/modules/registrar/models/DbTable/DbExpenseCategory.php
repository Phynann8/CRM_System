<?php
class Registrar_Model_DbTable_DbExpenseCategory extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_expense_category';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->branch_id;
	}
	
	function getAllCateIncome($search=null){
		$db = $this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$id = "c.id";
		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
		$urlEdit = $base_url."/registrar/expensecategory/edit/id/";

		$parenttitle=$tr->translate('IS_PARENT');
		$sql="SELECT 
				c.id,
				CASE 
					WHEN c.isMainParent = 1 THEN '$parenttitle'
					ELSE ''
				END AS parenttitle,
				c.accountCode ,
				c.categoryTitle AS name,
				p.categoryTitle AS category,
				c.createDate AS create_date,
				u.first_name AS USER
			";
		$sql.=$dbgb->caseStatusShowImage("c.status");
		$sql.=$dbgb->caseEdit($id,$urlEdit);
		$sql.=" FROM rms_expense_category AS c
			LEFT JOIN rms_expense_category AS p ON c.parentId = p.id
			LEFT JOIN rms_users AS u ON c.user_id = u.id
			WHERE 1 ";
		$where = '';
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
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
	
	function addExpenseCategory($data){
		$db= $this->getAdapter();
		try{
			$sql="SELECT id FROM rms_expense_category where categoryTitle ='".$data['title']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$isMainParent = empty($data['isMainParent'])?0:1;
			$data['parentId'] = empty($data['parentId']) ? 0 : $data['parentId'];
			$array = array(
					'accountCode'	=>$data['accountCode'],
					'categoryTitle'	=>$data['title'],
					'parentId'		=>$data['parentId'],
					'isMainParent'	=>$isMainParent ,
					'status'		=>1 ,
					'user_id'		=>$this->getUserId(),
					'createDate'	=>date('Y-m-d'),
				);
			$this->insert($array);
		}catch (Exception $e){
		}
 	 }
 	 
	 function updateCateExpense($data){
		$status = empty($data['status'])?0:1;
		$isMainParent = empty($data['isMainParent'])?0:1;
		$data['parentId'] = empty($data['parentId']) ? 0 : $data['parentId'];
		$arr = array(
				'accountCode'	=>$data['accountCode'],
				'categoryTitle'	=>$data['title'],
				'parentId'		=>$data['parentId'],
				'isMainParent'	=>$isMainParent ,
				'status'		=>$status,
				'user_id'		=>$this->getUserId(),
				'createDate'	=>date('Y-m-d'),
			);
		$where=" id = ".$data['id'];
		$this->update($arr, $where);
	}
	
	function getCateExpenseById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_expense_category where id=$id ";
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
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " categoryTitle LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
        $order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getParentCategoryExpense($cate_id='',$parent = 0, $spacing = '', $cate_tree_array = '',$isparent=null,$optionSelect=null){
		$db=$this->getAdapter();
		if (!is_array($cate_tree_array)){$cate_tree_array = array();}
		$sql = " SELECT id , categoryTitle as name FROM rms_expense_category where status=1 ";
		if (!empty($parent)){
			$sql.=" AND parentId = $parent ";
		}
		if (!empty($cate_id)){
			$sql.=" AND id != $cate_id";
		}
		if (!empty($isparent)){
			$sql.=" AND isMainParent = 1 ";
		}
		$query = $db->fetchAll($sql);

		if(!empty($cate_tree_array)){

			$rowCount = count($query);
			if ($rowCount > 0) {
				foreach ($query as $row){
					$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
					$cate_tree_array = $this->getParentCategoryExpense($cate_id,$row['id'], $spacing . ' - ', $cate_tree_array);
				}
			}
			return $cate_tree_array;
		}

		if (!empty($optionSelect)) {
			$options = '';
			if (!empty($query))
				foreach ($query as $value) {
					$resultData = $this->getParentCategoryExpense(null,$value["id"],null,'','','');
					if(empty($value["is_parent"])){
						$options .= '<option data-jsondata="'.htmlspecialchars(json_encode($resultData)).'" value="' . $value['id'] . '" >' . htmlspecialchars($value['name'], ENT_QUOTES) . '</option>';
					}
				}
			return $options;
		}
		return 	$query;
	}
}