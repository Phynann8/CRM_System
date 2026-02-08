
<?php
class Global_KnowbyController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status' => -1
						);
			}
			$db = new Global_Model_DbTable_DbKnowBy();
			$rs_rows= $db->getAllKnowBy($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","BY_USER","STATUS");
			$link=array(
					'module'=>'global','controller'=>'knowby','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('title'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$form = new Application_Form_FrmCombineSearchGlobal();
    	$frm = $form->FormSearchGeneral();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
	}
   function addAction(){
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		try {
	   			$sms = "INSERT_SUCCESS";
	   			$_dbmodel = new Global_Model_DbTable_DbKnowBy();
	   			$_major_id = $_dbmodel->addKnowBy($_data);
	   			if($_major_id==-1){
	   				$sms = "RECORD_EXIST";
	   			}
	   			if(isset($_data['save_close'])){
	   				Application_Form_FrmMessage::Sucessfull($sms,"/global/knowby/index");
	   			}else{
	   				Application_Form_FrmMessage::Sucessfull($sms,"/global/knowby/add");
	   			}
	   
	   		}catch (Exception $e) {
	   			Application_Form_FrmMessage::message("INSERT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}   
	   	}
	   	$classname=new Global_Form_FrmKnowBy();
	   	$frm_classname=$classname->FrmKnowBy();
	   	Application_Model_Decorator::removeAllDecorator($frm_classname);
	   	$this->view->frm_classname = $frm_classname;
   }
   public function editAction()
   {
	   	$id=$this->getRequest()->getParam("id");	   
	   	if($this->getRequest()->isPost())
	   	{
	   		try{
		   		$data = $this->getRequest()->getPost();
		   		$data["id"]=$id;
		   		$db = new Global_Model_DbTable_DbKnowBy();
		   		$db->updateKnowBy($data);
		   		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/knowby/index");
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("EDIT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	}
	   	$db = new Global_Model_DbTable_DbKnowBy();
	   	$row = $db->getKnowByById($id);
		$this->view->row = $row;
	   	if (empty($row)){
	   		Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/global/knowby/");
	   		exit();
	   	}
	   	$obj=new Global_Form_FrmKnowBy();
	   	$frm_room=$obj->FrmKnowBy($row);
	   	$this->view->update_room=$frm_room;
	   	Application_Model_Decorator::removeAllDecorator($frm_room);
   }
   
   function checkduplicateAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$_db = new Global_Model_DbTable_DbKnowBy();
    		$result=$_db->checkingDuplicate($data);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
   
}