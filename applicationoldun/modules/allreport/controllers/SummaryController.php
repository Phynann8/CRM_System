<?php
class Allreport_SummaryController extends Zend_Controller_Action {
	public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		defined('BRANCH_DISPLAY_SETTING') || define('BRANCH_DISPLAY_SETTING', Setting_Model_DbTable_DbGeneral::geValueByKeyName('branch_display_setting'));
	}
	public function indexAction()
	{
	}
	
	public function rptStudentSummaryAction(){
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}else{			
			$search=array(
				'adv_search' 		=>'',
				'academic_year' 	=>0,
				'hideInactive' 	=>1,
			);
			$last = $dbgb->getLatestAcadmicYear();
			if(!empty($last)){
				$search["academic_year"] = empty($last["id"]) ? 0 : $last["id"];
			}
		}
		
		$form = new Application_Form_FrmCombineSearchGlobal();
    	$frm = $form->FormSearchStuSummaryReport();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->form_search = $frm;
		
		
		$arr = [
			"itemsType"=>1,
			"displayLabel"=>"shortcut",
		];
		$this->view->allGrade = $dbgb->getAllItemDetail($arr);
		
		$group= new Allreport_Model_DbTable_DbRptSummary();
		$this->view->rs = $group->getAllSummaryStudentReport($search);
		$this->view->search=$search;
		
		
	
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooter = $frm->getFooterAccount(2);
		
		$today = new DateTime();
		$today->modify('+1 min');
		$textTran = "initial";
		$printDate =  $today->format("d/m/Y  h:i");
		$printDate.=  " ".strtoupper($today->format("a"));
		$prefixDate= "Printed";
		$arr=array(
			"footerLeftContent" => " $prefixDate : ".$printDate,
			"footerLeftTextTransform" => $textTran,
		);
		$this->view->printFormat = $frm->getPrintPageFormat($arr);
		$this->view->controlReport = $frm->reportControl();
	}
	
	function rptDailyIncomeSummaryAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' =>'',
						'branch_id'     =>0,
						'start_date'=> date('Y-m-d'),
						'end_date'  => date('Y-m-d'),
				);
			}
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$db = new Allreport_Model_DbTable_DbAccountReport();
		$parentCol =  $db->getMainParentOfItems();
		$this->view->parentCol =  $parentCol;
		
		$dbSum = new Allreport_Model_DbTable_DbRptSummary();
		$rowStDailyPmt =  $dbSum->getDailyIncomeSummary($search);
		$this->view->rowStDailyPmt =  $rowStDailyPmt;
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$this->view->bank =  $dbGb->getAllBank();
		
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount();
		
		$today = new DateTime();
		$today->modify('+1 min');
		$textTran = "initial";
		$printDate =  $today->format("d/m/Y  h:i");
		$printDate.=  " ".strtoupper($today->format("a"));
		$prefixDate= "Printed";
		$arr=array(
			"footerLeftContent" => " $prefixDate : ".$printDate,
			"footerLeftTextTransform" => $textTran,
		);
		$this->view->printFormat = $frm->getPrintPageFormat($arr);
		$this->view->controlReport = $frm->reportControl();
		
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$this->view->search = $search;
	}
	
	function rptMonthlyIncomeYearSummaryAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' =>'',
						'branch_id'     =>0,
						'start_date'=> date('Y-m-d'),
						'end_date'  => date('Y-m-d'),
				);
			}
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$dbSum = new Allreport_Model_DbTable_DbRptSummary();
		$rowStDailyPmt =  $dbSum->getMonthlyIncomeYearSummary($search);
		$this->view->rowStDailyPmt =  $rowStDailyPmt;
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$this->view->bank =  $dbGb->getAllBank();
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount();
		
		$today = new DateTime();
		$today->modify('+1 min');
		$textTran = "initial";
		$printDate =  $today->format("d/m/Y  h:i");
		$printDate.=  " ".strtoupper($today->format("a"));
		$prefixDate= "Printed";
		$arr=array(
			"footerLeftContent" => " $prefixDate : ".$printDate,
			"footerLeftTextTransform" => $textTran,
		);
		$this->view->printFormat = $frm->getPrintPageFormat($arr);
		$this->view->controlReport = $frm->reportControl();
		
		
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		$this->view->search = $search;
	}
	function rptScholarshipsdiscountAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'title' => '',
					'academic_year' => '0',
					'branch_id' => '',
					'discountId' => '',
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbRptPayment();
			$this->view->row = $db->getScholarshipsDiscountValue($search);
			$this->view->search = $search;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$this->view->adv_search = $search;
		$frm = new Global_Form_FrmSearchMajor();
		$frms =$frm->FrmsearchDiscount();
		Application_Model_Decorator::removeAllDecorator($frms);
		$this->view->form_search = $frms;
			
		$model = new Application_Model_DbTable_DbGlobal();
		$disc = $model->getAllDiscount();
		$this->view->discount = $disc;
		 
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);

		$paramFormat = array(
			'marginTop'=>'0.3cm',
			'marginRight'=>'0.5cm',
			'marginLeft'=>'0.5cm',
		);
		//'marginBottom'=>'0.9cm',
		$this->view->printFormat = $frm->getPrintPageFormat($paramFormat);
	}
	public function rptPaymentSummaryAction(){
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}else{			
			$search=array(
				'branch_id'		=>'',
				'academic_year'=>0,
			);
			$last = $dbgb->getLatestAcadmicYear();
			if(!empty($last)){
				$search["academic_year"] = empty($last["id"]) ? 0 : $last["id"];
			}
		}
		
		$dbp= new Allreport_Model_DbTable_DbNewAccounting();
		$this->view->paymentSummary =$dbp->getAllPaymentSummarry($search);

		$form = new Application_Form_FrmCombineSearchGlobal();
    	$frm = $form->FormSearchPaymentSummaryReport();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->form_search = $frm;
		$this->view->search=$search;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		
		$today = new DateTime();
		$today->modify('+1 min');
		$textTran = "initial";
		$printDate =  $today->format("d/m/Y  h:i");
		$printDate.=  " ".strtoupper($today->format("a"));
		$prefixDate= "Printed";
		$arr=array(
			'pageSize'=>'A4 landscape',
			"footerLeftContent" => " $prefixDate : ".$printDate,
			"footerLeftTextTransform" => $textTran,
		);
		$this->view->printFormat = $frm->getPrintPageFormat($arr);
		$this->view->controlReport = $frm->reportControl();
	}
}