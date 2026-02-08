<?php

class Global_Model_DbTable_DbEnglishLevel extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_english_level';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addEnglishLevel($_data){
		$db = $this->getAdapter();
		try{
			$arr = array(
				'title'       => $_data['name_kh'],
				'titleEn'     => $_data['name_en'],
				'shortcut'    => empty($_data['shortcut']) ? "" : $_data['shortcut'],
				'note'        => empty($_data['note']) ? "" : $_data['note'],
				'status'      => 1,
				'createDate'  => date('Y-m-d H:i:s'),
				'modifyDate'  => date('Y-m-d H:i:s'),
			);

			$this->_name = "rms_english_level";
			$this->insert($arr);

		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}

	public function getEnglishLevelById($id){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_english_level WHERE  id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateEnglishLevel($_data){
		$db = $this->getAdapter();
		try{
			$status = empty($_data['status'])?0:1;
			$_arr=array(
				'title'       => $_data['name_kh'],
				'titleEn'     => $_data['name_en'],
				'shortcut'    => empty($_data['shortcut']) ? "" : $_data['shortcut'],
				'note'        => empty($_data['note']) ? "" : $_data['note'],
				'modifyDate'  => date('Y-m-d H:i:s'),
				'status'	  => $status,
					
			);
			$where=$this->getAdapter()->quoteInto(" id=?", $_data["id"]);
			$this->_name = "rms_english_level";
			$this->update($_arr,$where);
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getAllEnglidhLevel($search){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  id,
				  shortcut,
				  title,
				  titleEn
				";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM rms_english_level  WHERE 1  ";
		
		$where = '';
		$order = ' ORDER BY id DESC ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " titleEn LIKE '%{$s_search}%'";
			$s_where[] = " shortcut LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1 AND $search['status']!=''){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
}