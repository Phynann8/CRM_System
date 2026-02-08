<?php
class Scan_Model_DbTable_DbScanSetting extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_score_entry_setting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllScoreEntrySetting($search = ''){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
		$branch = $dbp->getBranchDisplay();
		$come=$tr->translate('COME');
		$leave=$tr->translate('LEAVE');
		$currentLang = $dbp->currentlang();
		$colunmName='titleEn';
		if ($currentLang==1){
			$colunmName='title';
		}

    	$sql = " 
			SELECT 
				s.id
				,(SELECT CONCAT($branch) FROM rms_branch WHERE br_id=s.branchId LIMIT 1) AS branch_name
				,s.$colunmName AS titleRecord
				,CASE
					WHEN s.shift = 1 THEN '".$tr->translate('MORNING')."'
					WHEN s.shift = 2 THEN '".$tr->translate('AFTERNOON')."'
					END AS shift
				,CONCAT(COALESCE(s.fromTime,''),' - ',COALESCE(s.toTime,'')) AS scanTime
				,s.`condictionTime`
				,s.createDate 
		";
    	$sql.=$dbp->caseStatusShowImage("s.status");
    	$sql.="
				,CASE
					WHEN s.type = 1 THEN '$come'
					WHEN s.type = 2 THEN '$leave'
					END AS subTitleRecord
		";
    	$sql.=" FROM `rms_scan_att_setting` AS s WHERE 1 ";
    	$orderby = "  ORDER BY s.id DESC";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': "s.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "s.createDate <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['advance_search'])){
   			 $s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " s.title LIKE '%{$s_search}%'";
			$s_where[] = " s.titleEn LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
   	 	}
   	 	if(!empty($search['branch_search'])){
   	 		$where.= " AND s.branchId = ".$db->quote($search['branch_search']);
   	 	}
    	if($search['status_search']>-1){
    		$where.= " AND s.status = ".$db->quote($search['status_search']);
    	}
		
    	$where.=$dbp->getAccessPermission('s.branchId');
    	return $db->fetchAll($sql.$where.$orderby);
    }
	public function addScanSetting($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		
		try{
			$sessoinList = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($sessoinList)){
	    			$sessoinList = $rs;
	    		}else{ $sessoinList = $sessoinList.",".$rs;
	    		}
	    	}

			$_arr = array(
				'branchId'	 =>$_data['branch_id'],
				'shift'	 =>$_data['shift'],
				'title'		 =>$_data['title'],
				'titleEn'	 =>$_data['titleEn'],
				'type'		 =>$_data['type'],
				'sessionList'=>$sessoinList,
				'fromTime'	 =>date('H:i:s', strtotime(ltrim($_data['fromTime'], 'T'))),
				'toTime'	 =>date('H:i:s', strtotime(ltrim($_data['toTime'], 'T'))),
				'condictionTime'	 =>date('H:i:s', strtotime(ltrim($_data['condictionTime'], 'T'))),
				'createDate' =>date("Y-m-d H:i:s"),
				'modifyDate' =>date("Y-m-d H:i:s"),
				'status'	 =>1,
			);
			$this->_name='rms_scan_att_setting';
			$this->insert($_arr);
		  	$db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   
  
 
   public function editScanSetting($_data){
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   	try{
		$sessoinList = "";
		if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
			if (empty($sessoinList)){
				$sessoinList = $rs;
			}else{ $sessoinList = $sessoinList.",".$rs;
			}
		}

   		$_arr = array(
			'branchId'	 =>$_data['branch_id'],
			'shift'	 	=>$_data['shift'],
			'title'		 =>$_data['title'],
			'titleEn'	 =>$_data['titleEn'],
			'type'		 =>$_data['type'],
			'sessionList'=>$sessoinList,
			'fromTime'	 =>date('H:i:s', strtotime(ltrim($_data['fromTime'], 'T'))),
			'toTime'	 =>date('H:i:s', strtotime(ltrim($_data['toTime'], 'T'))),
			'condictionTime'	 =>date('H:i:s', strtotime(ltrim($_data['condictionTime'], 'T'))),
			'createDate' =>date("Y-m-d H:i:s"),
			'modifyDate' =>date("Y-m-d H:i:s"),
			'status'	 =>$_data['status'],
   		);
   		$this->_name='rms_scan_att_setting';
   		$where=' id='.$_data['id'];
   		$this->update($_arr, $where);
   		$db->commit();
   	}catch (Exception $e){
   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   		$db->rollBack();
   	}
   }
   function getScanSettingById($id){
		$db = $this->getAdapter();
		$sql="SELECT s.* FROM `rms_scan_att_setting` AS s WHERE s.id=$id";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('s.branchId');
		return $db->fetchRow($sql);
	}
 
}