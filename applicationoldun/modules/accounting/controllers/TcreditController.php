<?php
class Accounting_TcreditController extends Zend_Controller_Action {
	public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}

    public function indexAction()
    {	
    	try{
    		$db = new Accounting_Model_DbTable_DbTcredit();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    		}
    		else{
    			$formdata = array(
    					"adv_search"	=>'',
    					"branch_id"		=>'',
    					"paid_transfer"	=>-1,
    					"status"		=>-1,
    					'start_date'	=> date('Y-m-d'),
						'end_date'		=>date('Y-m-d'),
    			);
    		}
    		
    		$this->view->adv_search = $formdata;
			$rs_rows= $db->getAllTrasferCreditmemo($formdata);
    		
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","FROM_STUDENT","DATE","TOTAL_AMOUNT","TO_BRANCH","TO_STUDENT","CREATE_DATE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'accounting','controller'=>'tcredit','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('branch_name'=>$link,'stu_code'=>$link,));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
		$frm = new Registrar_Form_FrmSearchexpense();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function addAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$data['id'] = $id;
    		try {
    			$sms="INSERT_SUCCESS";
    			$db = new Accounting_Model_DbTable_DbTcredit();
    			$_transfer = $db->addTransferCredit($data);
    			if($_transfer==-1){
    				$sms = "RECORD_EXIST";
    			}
    			Application_Form_FrmMessage::Sucessfull($sms, "/accounting/tcredit");
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    		}
    	}
    
    	$pructis=new Accounting_Form_FrmTransferCredit();
    	$frm = $pructis->FrmTransferCredit();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_credit=$frm;
    }
    public function editAction()
 	{
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$data['id'] = $id;
    		try {
    			$db = new Accounting_Model_DbTable_DbTcredit();
    			$_transfer = $db->updateTransferCredit($data);
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/accounting/tcredit");
    		} catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    		}
    	}
    	
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	
    	$db = new Accounting_Model_DbTable_DbTcredit();
    	$row  = $db->getTransferById($id);
		if(empty($row)){
			Application_Form_FrmMessage::redirectUrl("/accounting/tcredit");
		}
		
		if($row["status"]==0){
			Application_Form_FrmMessage::Sucessfull("Record Already Void, Can't Edit !","/accounting/tcredit");
		}
    	$this->view->row = $row;
    	
    	$pructis=new Accounting_Form_FrmTransferCredit();
    	$frm = $pructis->FrmTransferCredit($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_credit=$frm;
    }
}