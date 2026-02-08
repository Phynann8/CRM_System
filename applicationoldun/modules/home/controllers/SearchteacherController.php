<?php
class Home_SearchteacherController extends Zend_Controller_Action {
	
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
						'grade'=> '',
						'session'=> '',
						'time'=> '',
						'group'=>'',
						'degree'=> '',
						'student_status'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
				$dbGb = new Application_Model_DbTable_DbGlobal();
				$last = $dbGb->getLatestAcadmicYear();
				if(!empty($last)){
					$search["academic_year"] = empty($last["id"]) ? 0 : $last["id"];
				}
			}
			$this->view->adv_search=$search;
			$dbTec= new Home_Model_DbTable_DbTeacher();
			$rs_rows = $dbTec->getAllTeacherInfo($search);
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
		
		$form = new Application_Form_FrmCombineSearchGlobal();
		$forms = $form->FormSearchTeachingClass();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->formSearch = $form;
	}
	public function detailAction(){
		
		$id = $this->getRequest()->getParam('id');
		$id = (empty($id))?0:$id;
		$academicYear = $this->getRequest()->getParam('academicYear');
		$academicYear = (empty($academicYear))?0:$academicYear;
		$dbTec= new Home_Model_DbTable_DbTeacher();
		$search = array(
			'teacherId' => $id,
			'academic_year' => $academicYear,
		);
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$last = $dbGb->getLatestAcadmicYear();
		if(empty($academicYear)){
			if(!empty($last)){
				$search["academic_year"] = empty($last["id"]) ? 0 : $last["id"];
			}
		}
		$rs_rows = $dbTec->getTeacherInfoById($search);
		$this->view->teacherInfo = $rs_rows;
		
	}
}