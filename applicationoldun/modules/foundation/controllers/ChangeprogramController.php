<?php 
class Foundation_ChangeprogramController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		defined('SHOW_TEACH_DOCUMENT') || define('SHOW_TEACH_DOCUMENT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('teacher_doc'));
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			$db = new Foundation_Model_DbTable_DbChangeProgram();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'adv_search' => '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'status'=> -1,
						);
			}
			$rs_rows= $db->getAllChangeProgram($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH", "ACADEMIC_YEAR", "STUDY_PROGRAM", "TO_STUDY_PROGRAM", "CHANGE_DATE", "AMT_STUDENT", "AMT_CLASS", "USER", "STATUS");
			$link=array('module'=>'foundation','controller'=>'changeprogram','action'=>'edit',);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array());
		
			$this->view->search = $search;
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form = new Application_Form_FrmSearchGlobal();
		$forms = $form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->frm_search = $form;
	}
	
	function addAction(){
		$inFrame=$this->getRequest()->getParam("inFrame");
		$inFrame = empty($inFrame) ? "" : $inFrame;
		$this->view->inFrame = $inFrame;
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			if (empty($_data)){
				Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/foundation/family");
				exit();
			}
			try {
				$sms="INSERT_SUCCESS";
				$dbmodel = new Foundation_Model_DbTable_DbChangeProgram();
				
				$dbmodel->addStudentChangeProgram($_data);
				$inFrame = empty($_data['inFrame']) ? "" : "true";
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,'/foundation/changeprogram?inFrame='.$inFrame);
				} 
				Application_Form_FrmMessage::Sucessfull($sms,'/foundation/changeprogram/add?inFrame='.$inFrame);
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$tsub=new Foundation_Form_FrmChangeProgram();
		$frm=$tsub->FrmChangeProgram();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	
	public function viewAction(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$id=$this->getRequest()->getParam("id");	
		$id = empty($id) ? 0 : $id;
		
		$inFrame=$this->getRequest()->getParam("inFrame");
		$inFrame = empty($inFrame) ? "" : $inFrame;
		$this->view->inFrame = $inFrame;
		
		
		$_db = new Foundation_Model_DbTable_DbChangeProgram();
		$row = $_db->getChangeProgramById($id);
		$arr = array(
			'id' => $id,
			'recordType' => 1,
		);
		$rowDetail = $_db->getChangeProgramDetail($arr);
		$arr["recordType"] = 2;
		$rowNewProgram = $_db->getChangeProgramDetail($arr);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/foundation/changeprogram");
		}
		$this->view->rs = $row;
		$this->view->rowDetail = $rowDetail;
		$this->view->rowNewProgram = $rowNewProgram;
		
		$frm = new Application_Form_FrmGlobal();
		$branch_id = empty($row['branchId']) ? 1 : $row['branchId'];
		$this->view->rsheader = $frm->getLetterHeaderReport($branch_id);
		$this->view->rsfooteracc = $frm->getFooterAccount(2);
	}

	
}