<?php
class Global_Model_DbTable_DbKnowBy extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_know_by';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;   	 
    }
	public function addKnowBy($_data){
		$db = $this->getAdapter();
		try{
			$_arr=array(
					'title'	  => $_data['title'],
					'create_date' => date("Y-m-d H:i:s"),
					'status'   => 1,
					'user_id'	  => $this->getUserId()
			);
			 return $this->insert($_arr);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message('INSERT_FAIL');
		}
	}
	public function getKnowByById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_know_by WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateKnowBy($data){		
		$status = empty($data['status'])?0:1;
		$_arr=array(
				'title'	 	 	=> $data['title'],
				'create_date' 	=> date("Y-m-d H:i:s"),
				'status'   		=> $status,
				'user_id'	  	=> $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("id=?", $data["id"]);
		$this->update($_arr,$where);
	}
	
	function getAllKnowBy($search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql = " SELECT 
					g.id AS id
					,g.title
					,(SELECT  CONCAT(first_name) FROM rms_users WHERE id=g.user_id LIMIT 1 )AS user_name
				";
		
		$sql.=$dbp->caseStatusShowImage("g.status");
		$sql.=" FROM rms_know_by AS g WHERE g.title != '' ";
		
		$order=" ORDER BY g.id DESC ";
		$where = '';
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[].=" g.title LIKE '%".$s_search."%'";
			$where.=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1 AND $search['status']!=''){
			$where.= " AND g.status = ".$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);	
	}	
	
	public function checkingDuplicate($data=[]){
		try{
			
			$title = empty($data["title"]) ? "" : $data["title"];
			$sql="SELECT 
				i.id
			FROM rms_know_by AS i
			WHERE i.title=TRIM('".$title."')  ";
		
			if (!empty($data['id'])){
				$sql.=" AND i.id != ".$data['id'];
			}
			$sql.=" LIMIT 1 ";
			$db = $this->getAdapter();
			$rows = $db->fetchOne($sql);	
			if(!empty( $rows )){
				return $rows;
			}
			return 0;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message('INSERT_FAIL');
		}
	}
}