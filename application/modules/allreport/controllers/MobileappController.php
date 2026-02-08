<?php
class Allreport_MobileappController extends Zend_Controller_Action
{
	public function init()
	{
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
	}
	public function studentAccountdisableAction()
	{
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'adv_search' 	=> '',
				'branch_id'		=> 0,
				'degree'		=> 0,
				'academic_year' => '',
				'grade' 		=> '',
				'group'			=> '',
				'start_date' 	=> date('Y-m-d'),
				'end_date'  	=> date('Y-m-d'),
			);
		}
		$group = new Allreport_Model_DbTable_DbRptMobileApp();
		$rs_rows = $group->getAllDisableAccountStudent($search);
		$this->view->rs = $rs_rows;

		$this->view->search = $search;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data = $key->getKeyCodeMiniInv(TRUE);

		$branch_id = empty($search['branch_id']) ? null : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount(2);
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

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}
	public function rptPreregisterAction()
	{
		if ($this->getRequest()->isPost()) {
			$search = $this->getRequest()->getPost();
		} else {
			$search = array(
				'adv_search' 	=> '',
				'degree'		=> 0,
				'grade' 		=> '',
				'start_date' 	=> date('Y-m-d'),
				'end_date'  	=> date('Y-m-d'),
			);
		}
		$group = new Allreport_Model_DbTable_DbRptMobileApp();
		$rs_rows = $group->getAllPreRegister($search);
		$this->view->rs = $rs_rows;

		$this->view->search = $search;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data = $key->getKeyCodeMiniInv(TRUE);

		$branch_id = empty($search['branch_id']) ? null : $search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount(2);
		
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

		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search = $form;
	}
	public	function enableAccountAction()
	{
		if ($this->getRequest()->isPost()) {
			$_data = $this->getRequest()->getPost();
			try {
				$dbs = new Allreport_Model_DbTable_DbRptMobileApp(); //by subject
				$rs =  $dbs->enableStudentAccount($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/allreport/mobileapp/student-accountdisable");
				exit();
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
				exit();
			}
		}
	}
}
