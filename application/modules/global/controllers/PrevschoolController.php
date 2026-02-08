<?php
class Global_PrevschoolController extends Zend_Controller_Action {
	const REDIRECT_URL = '/global/prevschool';   
   public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'title' => $_data['title'],
						'status' => $_data['status_search']);
			}
			else{
				$search = array(
						'title' => '',
						'status' => -1);
			}
			$db = new Global_Model_DbTable_DbPrevSchool();
			$rs_rows= $db->getAllSchoolList($search);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("SCHOOL_TITLE","CREATE_DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'global','controller'=>'prevschool','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('schoolName'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Global_Form_FrmSearchMajor();
		$frms =$frm->FrmsearchOccupation();
		Application_Model_Decorator::removeAllDecorator($frms);
		$this->view->frm_search = $frms;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$sms="INSERT_SUCCESS";
				$_dbmodel = new Global_Model_DbTable_DbPrevSchool();
				$_occupa = $_dbmodel->addPrevSchool($_data);
				$inFrame = empty($_data['inFrame']) ? "" : "true";
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/global/prevschool?inFrame=".$inFrame);
				}else{
					Application_Form_FrmMessage::Sucessfull($sms,"/global/prevschool/add?inFrame=".$inFrame);
				}			
					
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
		
		$inFrame=$this->getRequest()->getParam("inFrame");
		$inFrame = empty($inFrame) ? "" : $inFrame;
		$this->view->inFrame = $inFrame;
		
		$frm = new Global_Form_FrmPrevSchool();
    	$frm->FrmAddPrevSchool(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
	}
	public function editAction(){
		if($this->getRequest()->isPost())
		{
			try{
				$data = $this->getRequest()->getPost();
				$db = new Global_Model_DbTable_DbPrevSchool();
				$inFrame = empty($data['inFrame']) ? "" : "true";
				$db->addPrevSchool($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index?inFrame=".$inFrame);
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id) ? 0 : $id;
		
		$db = new Global_Model_DbTable_DbPrevSchool();
		$row = $db->getPrevSchoolById($id);
		$this->view->rs = $row;
		if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    		exit();
    	}
		$inFrame=$this->getRequest()->getParam("inFrame");
		$inFrame = empty($inFrame) ? "" : $inFrame;
		$this->view->inFrame = $inFrame;
		
		$frm = new Global_Form_FrmPrevSchool();
    	$frm->FrmAddPrevSchool($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
	}
	
	function checkduplicateAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$_db = new Global_Model_DbTable_DbPrevSchool();
    		$result= $_db->checkuDuplicate($data);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
	
	function getPrevschoolAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$rsData = $_dbgb->getAllPrevSchool($data);
			
			$dbUser = new Application_Model_DbTable_DbUsers();
			$checkPermission = $dbUser->getAccessUrl("global","prevschool","add");
			if(!empty($checkPermission)){
				array_unshift($rsData, array ( 'id' =>'-1','name' =>$this->tr->translate("ADD_NEW")));
			}
			array_unshift($rsData, array ( 'id' =>'','name' =>$this->tr->translate("School Name")));
			print_r(Zend_Json::encode($rsData));
			exit();
		}
	}
	
}