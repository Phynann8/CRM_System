<?php
class Accounting_CreditdashController extends Zend_Controller_Action {
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
		
		
		$dbDash = new Accounting_Model_DbTable_DbCreditDash();
		$this->view->creditList = $dbDash->getAllCreditmemo($search);
		$search["typeRecord"]=1;
		$this->view->transferCreditList = $dbDash->getAllCreditmemo($search);
		//$this->view->transferCreditList = $dbDash->getAllTransferCredit($search);
		
		$this->view->discoutSetting = $dbDash->getAllDiscountSetting($search);
		
		$this->view->countingDis = $dbDash->getCountingDisSetting($search);
		$this->view->countingDisType = $dbDash->getCountingDisType($search);
		$search["creditType"] =1;
		$this->view->countingCreditAvailable = $dbDash->getCountingCreditMemo($search);
    }
	
	public function getDetailInfoAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$dbFe = new Accounting_Model_DbTable_DbCreditDash();
			$d_row= $dbFe->getDetailContentList($data);
			print_r(Zend_Json::encode($d_row));
			exit();
		}
	}
    
}