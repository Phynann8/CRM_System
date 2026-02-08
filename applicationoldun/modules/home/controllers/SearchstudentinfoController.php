<?php
class Home_SearchstudentinfoController extends Zend_Controller_Action {
	
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
			$param = $this->getRequest()->getParams();
			if(isset($param['search'])){
				$search=$param;
			}
			else{
				$search = array(
						'adv_search' => '',
						'academic_year'=> '',
						'grade_all'=> '',
						'session'=> '',
						'time'=> '',
						'group'=>'',
						'degree'=> '',
						'study_type'=>"",
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->adv_search=$search;
			$db_student= new Home_Model_DbTable_DbStudent();
			$rs_rows = $db_student->getAllStudentFronDesk($search);
			$this->view->rowdata = $rs_rows;
			$this->view->list ="";
			
			$paginator = Zend_Paginator::factory($rs_rows);
			$paginator->setDefaultItemCountPerPage(35);
			$allItems = $paginator->getTotalItemCount();
			$countPages= $paginator->count();
			$p = Zend_Controller_Front::getInstance()->getRequest()->getParam('pages');
			 
			if(isset($p))
			{
				$paginator->setCurrentPageNumber($p);
			} else $paginator->setCurrentPageNumber(1);
			
			$currentPage = $paginator->getCurrentPageNumber();
			 
			$this->view->row  = $paginator;
			$this->view->countItems = $allItems;
			$this->view->countPages = $countPages;
			$this->view->currentPage = $currentPage;
			
			if($currentPage == $countPages)
			{
				$this->view->nextPage = $countPages;
				$this->view->previousPage = $currentPage-1;
			}
			else if($currentPage == 1)
			{
				$this->view->nextPage = $currentPage+1;
				$this->view->previousPage = 1;
			}
			else {
				$this->view->nextPage = $currentPage+1;
				$this->view->previousPage = $currentPage-1;
			}
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Application_Form_FrmCombineSearchGlobal();
		$form->FormSearchFontDesk();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
	public function studentDetailAction(){
		$db= new Home_Model_DbTable_DbStudent();
		$dbgb= new Application_Model_DbTable_DbGlobal();
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'branch_id'=>'',
						'payment_by'=>-1,
						'grade_all' =>-1,
						'user'=>-1,
						'adv_search' => '',
						'study_year'=> '',
						'grade'=> '',
						'session'=> '',
						'time'=> '',
						'degree'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
		    $id = $this->getRequest()->getParam('id');
		    $id = (empty($id))?0:$id;
		    
		   
			$this->view->adv_search=$search;
			$studentResult = $db->getStudentFrontDeskDetailById($id);
			if(empty($studentResult)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD","/home/searchstudentinfo");
			}
			$familyId = empty($studentResult["familyId"]) ? 0 : $studentResult["familyId"];
			$oldStudentId = empty($studentResult["oldStudentId"]) ? 0 : $studentResult["oldStudentId"];
			if($familyId>0){
				$arrFilter=[
					"familyId" => $familyId,
					"studentId" => $id,
					"oldStudentId" => $oldStudentId,
				];
				$this->view->siblingsInfo = $db->getAllSiblingsByFamily($arrFilter);
			}
			
			$this->view->rs =$studentResult;
			$this->view->rsStudentRerecord = $db->getAllStudentStudyRecord($id);

			
			$this->view->document =$db->getStudentDocumentById($id);
			
			$rs=$this->view->row = $db->homeGetStudentPaymentDetail($id,$oldStudentId);
			$search["studentId"] = $id;
			$search["type"] = 1;
			$this->view->service=$db->getStudentServiceUsing($search);
			
			$re=$this->view->re_row=$db->getRescheduleByGroupId($id);
			$this->view->student_mistake = $db->getStudentMistake($id);
			$this->view->student_attendance = $db->getStudentAttendence($id);
			
			
			$this->view->study_history = $db->getStudyHistoryByStudent($id,$oldStudentId);

			$this->view->studentTestInfo = $db->getStudentAllTestInfo($id);
			
			$this->view->studentAchievement = $db->getAllAchievementByStudent($id);
			$this->view->studentCredit = $db->getCreditTransaction($id);
			
			$droplink= $this->getRequest()->getParam('droplink');
			$drid= $this->getRequest()->getParam('drid');
			if (!empty($droplink)){
				if ($droplink=="true" AND !empty($drid)){
					//read notification student drop
					$dbgb->updateReadNotif(1, $drid);
				}
			}
			
			$this->view->day = $dbgb->getAllDay();
			$branch_id = empty($studentResult['branch_id'])?1:$studentResult['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooter = $frm->getFooterAccount(2);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
}