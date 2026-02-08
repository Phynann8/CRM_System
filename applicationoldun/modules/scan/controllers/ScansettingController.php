<?php
class Scan_ScansettingController extends Zend_Controller_Action {
	const REDIRECT_URL = '/scan/scansetting';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		
	}
	public function indexAction()
    {
    	try{
	    	$db = new Scan_Model_DbTable_DbScanSetting();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
	    	}
	    	else{
	    		$search = array(
	    				'advance_search' => "",
	    				'branch_search'=>"",
	    				'start_date'=>date("Y-m-d"),
	    				'end_date'=>date("Y-m-d"),
	    				'status_search' => -1
	    		);
	    	}
	    	$rs_rows= $db->getAllScoreEntrySetting($search);
	    	
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("BRANCH","TITLE","SHIFT_TIME","SCAN_TIME","condictionTime","CREATE_DATE","STATUS",);
	    	$link=array(
	    			'module'=>'scan','controller'=>'scansetting','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link ,'titleRecord'=>$link ,'titleEn'=>$link));
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$frm = new Scan_Form_FrmScanSetting();
    	$frm->FrmAddScanSetting(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }  
    public function addAction(){
    	$db = new Scan_Model_DbTable_DbScanSetting();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
		//	print_r($_data);exit();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->addScanSetting($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/add");
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$frm = new Scan_Form_FrmScanSetting();
    	$frm->FrmAddScanSetting(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->faculty = $_db->getAllPartTimeName();
    }
    
    public function editAction(){
    	$id = $this->getRequest()->getParam("id");
    	$id = empty($id)?0:$id;
    	$db = new Scan_Model_DbTable_DbScanSetting();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="EDIT_SUCCESS";
    			$_major_id = $db->editScanSetting($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$row = $db->getScanSettingById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    		exit();
    	}
		$this->view->rs = $row;
		
    	$frm = new Scan_Form_FrmScanSetting();
    	$frm->FrmAddScanSetting(	$row );
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;

		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->faculty = $_db->getAllPartTimeName();
    }
    
    public function copyAction(){
    	$id = $this->getRequest()->getParam("id");
    	$id = empty($id)?0:$id;
    	$db = new Scan_Model_DbTable_DbScanSetting();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="SUCCESS";
    			$_major_id = $db->addScanSetting($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("EDIT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$row = $db->getScanSettingById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    		exit();
    	}
		$this->view->rs = $row;

    	$frm = new Scan_Form_FrmScanSetting();
    	$frm->FrmAddScanSetting($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->faculty = $_db->getAllPartTimeName();
    }
	
}