<?php
class Stock_InitizeqtyController extends Zend_Controller_Action {
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    				'adv_search' => '',
    				'branch_id'=>'',
    				'category_id'=>'',
					'product_type'=>0,
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'),
    				'sort_by'=>-1,
    			);
    		}
			$db =  new Stock_Model_DbTable_DbAdjustStock();
			$rows = $db->getAllProductLocattion($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","BAR_CODE","PRODUCT_NAME","CURRENT_QTY","UNIT_COST","SELL_PRICE","PRICE_SET","TYPE","DATE","BY_USER","STATUS","VIEW");

			$this->view->list=$list->getCheckList(11, $collumns, $rows,array());
			}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("Application Error!");
				
			}

			$frm=new Application_Form_FrmCombineSearchGlobal();
			$form=$frm->FormSearchProductLocation();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->frm_items=$form;
	}

	
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
					$db = new Global_Model_DbTable_DbItemsDetail();
					$row = $db->AddInitizeqty($_data);
					if(isset($_data['save_close'])){
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/initizeqty");
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/initizeqty/add");
					}
					//Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("INSERT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				}
			}
			$_pur = new Stock_Model_DbTable_DbAdjustStock();
			
			$this->view->rq_code=$_pur->getAjustCode();
			$this->view->bran_name=$_pur->getAllBranch();
			 
			$model = new Application_Model_DbTable_DbGlobal();
			$branch = $model->getAllBranchName();
			$this->view->branchopt = $branch;
			
			$this->view->rsmaincategory = $model->getAllItems(3,null,null,0,'','',1,1);
	}
	

	public function updateAction(){
		$db = new Global_Model_DbTable_DbItemsDetail();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{

				$db->updateProductPrice($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/initizeqty");
				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$type=3; //Product
		$frm = new Global_Form_FrmItemsDetail();
		$frm->FrmAddItemsDetail(null,$type);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_items = $frm;

		$model = new Application_Model_DbTable_DbGlobal();
		$this->view->rsmaincategory = $model->getAllItems(3,null,null,0,'','',1,1);
	}

	 public function viewAction(){
    	$dbmv = new Stock_Model_DbTable_DbMovement();
    	$id = $this->getRequest()->getParam("id");
    	$row =$dbmv->getProductLocationInfo($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/stock/initizeqty");
    	}
    	$this->view->row = $row;
		$param = array(
			'product_id' => $row['pro_id'],
			'branch_id' => $row['branch_id'],
		);
    	$this->view->rowMovement = $dbmv->getMovementStockByProductId($param);
    }
    
    function getProductqtyAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbRequestProduct();
    		$gty= $db->getProductQty($data['branch_id'],$data['pro_id']);
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    }
    function getProBylocationAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbRequestProduct();
    		$gty= $db->getAllProductBybranch($data['branch_id']);
    		//array_unshift($gty, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		array_unshift($gty, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    }
    
    function getreceiptAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$branch_id = $data['branch_id'];
    		$_dbcht = new Stock_Model_DbTable_DbAdjustStock();
    		$itemsCode = $_dbcht->getAjustCode($branch_id);
    		print_r(Zend_Json::encode($itemsCode));
    		exit();
    	}
    }
}