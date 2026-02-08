<?php
class Global_ViewController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'title' => $_data['title'],
						'status' => $_data['status_search']);
			}else{
				$search = array(
						'title' => '',
						'status' => -1);
			}
 			$db = new Global_Model_DbTable_DbView();
 			$rs_rows= $db->getAllViewItemsByType($search);
		
			$list = new Application_Form_Frmtable();
			$collumns = array("TYPE","SHORTCUT","KH_NAME","NAME_EN","STATUS");
			$link=array(
					'module'=>'global','controller'=>'view','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('TypeName'=>$link,'shortcut'=>$link,'name_kh'=>$link,'name_en'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$forms=new Accounting_Form_FrmSearchProduct();
		$form=$forms->FrmSearchProduct($search);
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;		
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_db = new Global_Model_DbTable_DbView();
				$_db->addViewItemsByType($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/global/view");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
		}
		$classname=new Global_Form_FrmView();
	   	$frm_classname=$classname->FrmView();
	   	Application_Model_Decorator::removeAllDecorator($frm_classname);
	   	$this->view->frm_classname = $frm_classname;
   
	}
	public function editAction(){
		$type = 40;
		if($this->getRequest()->isPost())
		{
			try{
				$_data = $this->getRequest()->getPost();
				$db = new Global_Model_DbTable_DbView();
				$db->updateViewItemsByType($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/view/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id) ? 0 : $id;
		$db = new Global_Model_DbTable_DbView();
		$arr = array(
			'id' => $id
		);
		$rs = $db->getViewItemsByTypeInfo($arr);
		$this->view->rs=$rs;

		$classname=new Global_Form_FrmView();
	   	$frm_classname=$classname->FrmView($rs );
	   	Application_Model_Decorator::removeAllDecorator($frm_classname);
	   	$this->view->frm_classname = $frm_classname;
	}
	
	function addViewPopupAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
			$data["viewType"] = empty($data["viewType"]) ? 40 : $data["viewType"];
    		
			$db = new Global_Model_DbTable_DbView();
    		$id = $db->addPopupView($data);
    		print_r(Zend_Json::encode($id));
    		exit();
    	}
    }
	
}