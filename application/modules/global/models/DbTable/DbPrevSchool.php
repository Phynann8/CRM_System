<?php

class Global_Model_DbTable_DbPrevSchool extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_previous_school';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addPrevSchool($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr=[
				'schoolName'	=> trim($_data['schoolName']),
				'modifyDate' 	=> date("Y-m-d H:i:s"),
				'userId'	  	=> $this->getUserId()
			];
			if(!empty($_data["id"])){
				$_arr["status"] = empty($_data['status']) ? 0 : 1;
				$id  = $_data["id"];
				$where=$this->getAdapter()->quoteInto("id=?", $id);
				$this->update($_arr, $where);
			}else{
				$_arr["status"] = 1;
				$_arr["createDate"] = date("Y-m-d H:i:s");
				$id = $this->insert($_arr);
			}
			$db->commit();
			return $id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	public function getPrevSchoolById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_previous_school WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
	function getAllSchoolList($search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql = " SELECT 
					pvs.id AS id
					,pvs.schoolName
					,pvs.createDate
					,(SELECT u.first_name FROM rms_users AS u WHERE u.id=pvs.userId LIMIT 1) AS user_name
					";
		$sql.=$dbp->caseStatusShowImage("pvs.status");
		$sql.=" FROM rms_previous_school AS pvs ";
		
		$order = ' ORDER BY pvs.id DESC '; 
		$where = ' WHERE schoolName!="" ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " schoolName LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1 AND $search['status']!=''){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
	function checkuDuplicate($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.id
			FROM rms_previous_school AS i
			WHERE 1
		 ";
		$sql.=" AND ".$this->getAdapter()->quoteInto("schoolName=?", trim($data['schoolName']));
		if (!empty($data['id'])){
			$sql.=" AND i.id != ".$data['id'];
		}
		$sql.=" LIMIT 1 ";
		$row = $db->fetchRow($sql);
		if (!empty($row)){
			return 1;
		}
		return 0;
	}
}