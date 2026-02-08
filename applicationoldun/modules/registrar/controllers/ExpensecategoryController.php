<?php

class Registrar_ExpensecategoryController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/registrar/expense';
	
    public function init()
    {
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{ 
    		$db = new Registrar_Model_DbTable_DbExpenseCategory();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					"adv_search"=>'',
    					"status"=>-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		
    		$this->view->adv_search = $search;
    		
			$rs_rows= $db->getAllCateIncome($search);//call frome model
			$this->view->row = $rs_rows;
			$list = new Application_Form_Frmtable();
    		$collumns = array("TYPE","ACCOUNT_CODE","TITLE","PARENT","BY_USER","CREATE_DATE","STATUS","ACTION");
    		$link=array(
    				'module'=>'registrar','controller'=>'expensecategory','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('parenttitle'=>$link,'accountCode'=>$link,'name'=>$link));

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
		$inFrame=$this->getRequest()->getParam("inFrame");
		$inFrame = empty($inFrame) ? "" : $inFrame;
		$this->view->inFrame = $inFrame;

    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			try {
				$db = new Registrar_Model_DbTable_DbExpenseCategory();
				$sms="INSERT_SUCCESS";
				$cate = $db->addExpenseCategory($data);
				if($cate==-1){
					$sms = "RECORD_EXIST";
				}
				$inFrame = empty($data['inFrame']) ? "" : "true";
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,'/registrar/expensecategory?inFrame='.$inFrame);
				} 
				Application_Form_FrmMessage::Sucessfull($sms,'/registrar/expensecategory/add?inFrame='.$inFrame);			
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Registrar_Model_DbTable_DbExpenseCategory();
		$this->view->parent = $db->getParentCategoryExpense(0,null,null,0,1,null);
    }
 
    public function editAction()
    {
    	if($this->getRequest()->isPost()){
    		$id = $this->getRequest()->getParam('id');
			$data=$this->getRequest()->getPost();	
			$data['id']=$id;
			$db = new Registrar_Model_DbTable_DbExpenseCategory();				
			try {
				$db->updateCateExpense($data);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', "/registrar/expensecategory");		
			} catch (Exception $e) {
				$this->view->msg = 'EDIT_FAIL';
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbExpenseCategory();
		$row  = $db->getCateExpenseById($id);
		$this->view->rs = $row;
		$this->view->parent = $db->getParentCategoryExpense($id,null,null,0,1,null);
    }
	function refreshCategoryAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbExpenseCategory();
    		$rs_rows= $db->getParentCategoryExpense();
			array_unshift($rs_rows, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		print_r(Zend_Json::encode($rs_rows));
    		exit();
    	}
    }

}







