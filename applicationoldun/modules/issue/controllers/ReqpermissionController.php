<?php
class Issue_ReqpermissionController extends Zend_Controller_Action {
    public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction(){
		try{
			$db = new Issue_Model_DbTable_DbStuReqPermission();
			
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'branch_id' => '',
						'group' => '',
						'study_year'=> '',
						'grade'=> '',
						'session_type'=> '',
						'for_semester'=> 0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d')
					);
			}
			$this->view->search=$search;
			$rs_rows = $db->getAllReqPermission($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","STUDENT_ID","NAME","GROUP","ACADEMIC_YEAR","AMOUNT_DAY","ATTENDANCE_DATE","CREATE_DATE","STATUS");
			$link=array(
					'module'=>'issue','controller'=>'reqpermission','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'stu_code'=>$link,'stu_name'=>$link,'group_name'=>$link,'academy'=>$link,));
	
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$db_global=new Application_Model_DbTable_DbGlobal();
		$result= $db_global->getAllGroupName();
		array_unshift($result, array ( 'id' => '', 'name' =>$this->tr->translate("SELECT_GROUP")) );
		$this->view->group = $result;
	}
	public	function addAction(){
		$db = new Issue_Model_DbTable_DbStuReqPermission();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				if(isset($_data['save_new'])){
					 $rs =  $db->addStuReqPermission($_data);
					 Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/reqpermission/add");
				}else {
					 $rs =  $db->addStuReqPermission($_data);
					 Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/reqpermission");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$form=new Issue_Form_FrmReqpermission();
		$form->FrmReqpermission();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->frm = $form;
	}
	
	public	function editAction(){
		$id=$this->getRequest()->getParam('id');
		$db = new Issue_Model_DbTable_DbStuReqPermission();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$rs = $db->editStuReqPermission($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/reqpermission/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$row= $db->getReqPermission($id);
		$this->view->row = 	$row;
		if (date("Y-m-d") > date("Y-m-d",strtotime($row['toDate'])) ){
    		Application_Form_FrmMessage::Sucessfull("Valide Date is Expired","/issue/reqpermission/index");
    	}
		if ($row['status']==0){
    		Application_Form_FrmMessage::Sucessfull("UNABLE_TO_EDIT_DEACTIVE_RECORD","/issue/reqpermission/index");
    	}

		$detail= $db->getAttendacenDetailById($id);
		if ($detail['isCompleted']!=0){
    		Application_Form_FrmMessage::Sucessfull("Already Completed! ","/issue/reqpermission/index");
    	}
		
		$form=new Issue_Form_FrmReqpermission();
		$form->FrmReqpermission($row);
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->frm = $form;
	}
}

