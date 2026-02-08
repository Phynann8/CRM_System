<?php

class Registrar_CateexpenseController extends Zend_Controller_Action
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
    		$db = new Registrar_Model_DbTable_DbCateExpense();
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
			$rs_rows= $db->getAllExpenseItem($search);//call frome model
			$this->view->row = $rs_rows;
			$list = new Application_Form_Frmtable();
    		$collumns = array("ACCOUNT_CODE","TITLE","CATEGORY","BY_USER","CREATE_DATE","STATUS","ACTION");
    		$link=array(
    				'module'=>'registrar','controller'=>'cateexpense','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('category'=>$link,'accountCode'=>$link,'name'=>$link));
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

		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			try {
				$db = new Registrar_Model_DbTable_DbCateExpense();
				$sms="INSERT_SUCCESS";
				$cate = $db->addCateExpense($data);
				if($cate==-1){
					$sms = "RECORD_EXIST";
				}
				$inFrame = empty($data['inFrame']) ? "" : "true";
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,'/registrar/cateexpense?inFrame='.$inFrame);
				} 
				Application_Form_FrmMessage::Sucessfull($sms,'/registrar/cateexpense/add?inFrame='.$inFrame);				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$dbexp = new Registrar_Model_DbTable_DbCateExpense();
		$expense_row = $dbexp->getParentCategoryExpense();
		array_unshift($expense_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->parent =$expense_row ;
    }
 
    public function editAction()
    {
		

    	if($this->getRequest()->isPost()){
    		$id = $this->getRequest()->getParam('id');
			$data=$this->getRequest()->getPost();	
			$data['id']=$id;
			$db = new Registrar_Model_DbTable_DbCateExpense();				
			try {
				$db->updateCateExpense($data);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', "/registrar/cateexpense");		
			} catch (Exception $e) {
				$this->view->msg = 'EDIT_FAIL';
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbCateExpense();
		$row  = $db->getCateExpenseById($id);
		$this->view->rs = $row;

		$expense_row = $db->getParentCategoryExpense();
		array_unshift($expense_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->parent =$expense_row ;
    }

	function getExpenseitemAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Registrar_Model_DbTable_DbCateExpense();
			$rs = $db->getExpenseItem($data);
			if(!empty($data['addNew'])){
				array_unshift($rs, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
			}
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	

}







