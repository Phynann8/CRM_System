<?php
class Global_Model_DbTable_DbProgramDash extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_province';
	
	function getCountingDegree($search){
		$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$type = empty($search['type']) ? 1 : $search['type'];
		$sql="
			SELECT 
				COUNT(it.id) AS `value`
			FROM  `rms_items` AS it 
			WHERE 1 AND it.`type` = $type 
		";
		if(!empty($search['status'])){
			$status = $search['status'];
			if($search['status'] ==2){
				$status =0;
			}
    		$sql.= " AND it.status  = ".$status;
    	}
		$dbgb = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbgb->getDegreePermission('it.`id`');
		return $dbgb->getGlobalDbOne($sql);
	}
	
	function getCountingGrade($search){
		$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$type = empty($search['type']) ? 1 : $search['type'];
		$sql="
			SELECT 
				COUNT(it.id) AS `value`
			FROM  `rms_itemsdetail` AS it 
			WHERE 1 AND it.`items_type` = $type
		";
		if(!empty($search['status'])){
			$status = $search['status'];
			if($search['status'] ==2){
				$status =0;
			}
    		$sql.= " AND it.status  = ".$status;
    	}
		$dbgb = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbgb->getDegreePermission('it.`id`');
		return $dbgb->getGlobalDbOne($sql);
	}
	
	function getCountingRoom($search){
		$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$type = empty($search['type']) ? 1 : $search['type'];
		$sql="
			SELECT 
				COUNT(it.room_id) AS `value`
			FROM  `rms_room` AS it 
			WHERE 1 
		";
		if(!empty($search['status'])){
			$status = $search['status'];
			if($search['status'] ==2){
				$status =0;
			}
    		$sql.= " AND it.is_active  = ".$status;
    	}
		$dbgb = new Application_Model_DbTable_DbGlobal();
		return $dbgb->getGlobalDbOne($sql);
	}
	
	
	 function getAllDegreeAndCoutningGrade($search=null){
    	$db=$this->getAdapter();
		
    	$dbGb = new Application_Model_DbTable_DbGlobal();
		$branch = $dbGb->getBranchDisplay();
    	$lang = $dbGb->currentlang();
		
    	$colName = 'title_en';
    	$str = 'title_eng';
    	if ($lang==1){
    		$colName = 'title';
    		$str = 'title_kh';
    	}
		$sql = "SELECT 
					DISTINCT(it.`id`)
					,it.`title` AS titleKh
					,it.`title_en` AS titleEn
					,it.`shortcut`
					,COUNT(IF(it.id = itd.`items_id`,itd.`id`,NULL)) AS amtGrade
		";
		$sql.=$dbGb->caseStatusShowImage("it.status");
		$sql.=" FROM `rms_items` AS it 
					LEFT JOIN `rms_itemsdetail` AS itd ON it.id = itd.`items_id`
			";
    	$sql.=" WHERE it.`type` = 1 ";
		
		$sql.=" GROUP BY it.`id`  ";
    	$sql.=" ORDER BY it.`is_parent` ASC,it.`schoolOption` ASC,it.`ordering` ASC ";
    	
    	return $db->fetchAll($sql);
	}
	
	function getAllAcademicList($search = null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    
    	$sql = " 
			SELECT 
				aca.*
				,CONCAT(COALESCE(aca.`fromYear`),'-',COALESCE(aca.`toYear`)) AS academicYearTitle
				,(SELECT GROUP_CONCAT(i.shortcut) FROM rms_items AS i WHERE i.type=1 AND FIND_IN_SET(i.id,aca.degreeList) LIMIT 1) AS degreeList
		 ";
    	$sql.=$dbp->caseStatusShowImage("aca.status");
    	$sql.=" FROM `rms_academicyear` AS aca WHERE 1 ";
    	$orderby = "  ORDER BY aca.id DESC";
    	$where = '';
    	$where.="";
    	return $db->fetchAll($sql.$where.$orderby);
    }
	
	function getAllSessionList($search = null){
    	$db = $this->getAdapter();
    	$dbGb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbGb->currentlang();
		$branch = $dbGb->getBranchDisplay();
		
    	$colName = 'title';
    	if ($lang==1){
    		$colName = 'titleKh';
    	}
    	$sql = " 
			SELECT 
				s.*
				,(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id=s.branchId LIMIT 1) AS branchName
				,s.titleKh
				,s.title
				,s.$colName AS sessionTitle
				,(SELECT GROUP_CONCAT(i.shortcut) FROM rms_items AS i WHERE i.type=1 AND FIND_IN_SET(i.id,s.degreeId) LIMIT 1) AS degreeList
				, s.createDate
		 ";
    	$sql.=$dbGb->caseStatusShowImage("s.status");
    	$sql.=" FROM `rms_parttime_list` AS s WHERE 1 ";
    	$orderby = "  ORDER BY s.id DESC";
    	$where = '';
    	$where.=$dbGb->getAccessPermission('s.branchId');
    	return $db->fetchAll($sql.$where.$orderby);
    }

}
