<?php
class Accounting_FeedashController extends Zend_Controller_Action {
	public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
    public function indexAction()
    {
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$search = array();
		$last = $dbGb->getLatestAcadmicYear();
		if(!empty($last)){
			$search["academic_year"] = empty($last["id"]) ? 0 : $last["id"];
			$search["year"] = empty($last["id"]) ? 0 : $last["id"];
		}
		$this->view->lastestAcademic =	$last;
		$this->view->termStudy = $dbGb->getAllPaymentTerm();
		
		$_db = new Global_Model_DbTable_DbProgramDash();
		$search["type"] = 2;
		$this->view->countAllServiceType = $_db->getCountingDegree($search);
		$this->view->countAllService = $_db->getCountingGrade($search);
			
		
		$dbFeeDas = new Accounting_Model_DbTable_DbFeeDash();
		$search["type"] = 1;
		$this->view->paymentPeriod = $dbFeeDas->getAllPaymentPeriod($search);
		$search["type"] = 2;
		$this->view->countAllServiceFee = $dbFeeDas->getCountingItemsSettedFee($search);
		
		$dbFee = new Accounting_Model_DbTable_DbFee();
		$search["is_finished_search"] = "";
		$search["type_study"] = 0;
		$search["school_option"] = 0;
		$search["status"] = -1;
		$this->view->mainFeeList = $dbFee->getAllTuitionFee($search);
		
		$db = new Accounting_Model_DbTable_DbServiceCharge();
		$this->view->mainServiceList= $db->getAllServiceFee($search);
		
		$dbBank = new Accounting_Model_DbTable_DbBank();
		$this->view->bankList = $dbBank->getAllBank($search);
    }
	
	public function getFeeDetailAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$dbFe = new Accounting_Model_DbTable_DbFeeDash();
			$d_row= $dbFe->getFeeDetailContentList($data);
			print_r(Zend_Json::encode($d_row));
			exit();
		}
	}
    
}