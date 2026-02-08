<?php
class Global_ProgramdashController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$search = array();
			$last = $dbgb->getLatestAcadmicYear();
			
			if(!empty($last)){
				$search["academic_year"] = empty($last["id"]) ? 0 : $last["id"];
			}
			
			
			
			$_db = new Global_Model_DbTable_DbProgramDash();
			$search["limitRecord"] = 1;
			
			$this->view->sessionList = $_db->getAllSessionList($search);
			$this->view->academicList = $_db->getAllAcademicList($search);
			$this->view->rsDegreeList = $_db->getAllDegreeAndCoutningGrade($search);
			
			$this->view->countAllDegree = $_db->getCountingDegree($search);
			$this->view->countAllGrade = $_db->getCountingGrade($search);
			$this->view->countAllRoom = $_db->getCountingRoom($search);
			
			$search["status"] = 1;
			$this->view->countActiveDegree = $_db->getCountingDegree($search);
			
			$search["status"] = 2;
			$this->view->countDeactiveGrade = $_db->getCountingGrade($search);
			$this->view->countDeactiveRoom = $_db->getCountingRoom($search);
 			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$forms=new Accounting_Form_FrmSearchProduct();
		$form=$forms->FrmSearchProduct($search);
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;		
	}
	
}