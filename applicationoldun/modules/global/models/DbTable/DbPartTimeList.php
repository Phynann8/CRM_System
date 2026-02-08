<?php
class Global_Model_DbTable_DbPartTimeList extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_parttime_list';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllScoreEntrySetting($search = ''){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    
    	$sql = " 
			SELECT 
				s.id
				,s.titleKh
				,s.title
				,(SELECT GROUP_CONCAT(i.shortcut) FROM rms_items AS i WHERE i.type=1 AND FIND_IN_SET(i.id,s.degreeId) LIMIT 1) AS degreeList
				, s.createDate
		 ";
    	$sql.=$dbp->caseStatusShowImage("s.status");
		$sql.=" ,(SELECT sh.title FROM rms_shift AS sh WHERE sh.shiftCode =s.shiftStr LIMIT 1 ) AS titleKhSmallInfo";
    	$sql.=" FROM `rms_parttime_list` AS s WHERE 1 ";
    	$orderby = "  ORDER BY s.id DESC";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': "s.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "s.createDate <= '".$search['end_date']." 23:59:59'";
    	//$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['advance_search'])){
			$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " s.titleKh LIKE '%{$s_search}%'";
			$s_where[] = " s.title LIKE '%{$s_search}%'";
			$s_where[] = " s.description LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
   	 	}
    	if($search['status_search']>-1){
    		$where.= " AND s.status = ".$db->quote($search['status_search']);
    	}
    	return $db->fetchAll($sql.$where.$orderby);
    }
	public function addPartTimeList($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
			$_arr = array(
					'degreeId'			=>$dept,
					'shiftStr'			=>$_data['shiftStr'],
					'titleKh'			=>$_data['titleKh'],
					'title'			 	=>$_data['title'],
					'description'		=>$_data['description'],
					'createDate' 		=>date("Y-m-d H:i:s"),
					'modifyDate' 		=>date("Y-m-d H:i:s"),
					'status'			=>1,
			);
			$this->_name='rms_parttime_list';
			$this->insert($_arr);
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   
   function getPartTimeListById($id){
   		$db = $this->getAdapter();
   		$sql="SELECT s.* FROM `rms_parttime_list` AS s WHERE s.id=$id";
   		return $db->fetchRow($sql);
   }
 
   public function editPartTimeList($_data){
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   	try{
		$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
		$status=empty($_data['status'])?0:1;
   		$_arr = array(
			'degreeId'	 	=>$dept,
			'shiftStr'		=>$_data['shiftStr'],
			'titleKh'		=>$_data['titleKh'],
			'title'		 	=>$_data['title'],
			'description'	=>$_data['description'],
			'createDate' 	=>date("Y-m-d H:i:s"),
			'modifyDate' 	=>date("Y-m-d H:i:s"),
			'status'	 	=>$status,
   		);
   		$this->_name='rms_parttime_list';
   		$where=' id='.$_data['id'];
   		$this->update($_arr, $where);
   		$db->commit();
   	}catch (Exception $e){
   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   		$db->rollBack();
   	}
   }
}