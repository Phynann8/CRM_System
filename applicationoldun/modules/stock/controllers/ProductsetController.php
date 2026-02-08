<?php
class Stock_ProductsetController extends Zend_Controller_Action {
	const REDIRECT_URL = '/stock/productset';
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
					'adv_search' => "",
					'branch_id' => "",
    				'items_search'=>"",
					'is_onepayment'=>-1,
    				'product_type'=>-1,
    				'status' => -1,
					'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'),
    			);
    		}
    		$type=3; //Product
			$db =  new Global_Model_DbTable_DbItemsDetail();
			//$rs_rows = $db->getAllProductSet($search,$type);
			$rs_rows = $db->getAllProductSet($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","PRODUCT_CODE","PRODUCT_NAME","PRODUCT_CATEGORY","SELL_PRICE","ONE_PAYMENT",
					"DATE","BY_USER","STATUS","ACTIVE");
			$link=array(
					'module'=>'stock','controller'=>'productset','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'title'=>$link,'title_en'=>$link,'code'=>$link,'degree'=>$link,));
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error!");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}

			$frm=new Application_Form_FrmCombineSearchGlobal();
			$form=$frm->FormSearchProductSet();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->frm_items=$form;
	}
	public function addAction(){
		$db = new Global_Model_DbTable_DbItemsDetail();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$addCate=$this->getRequest()->getParam('addCate');
			try{
				$db->addProductSet($_data);
				if(!empty($addCate)){
					$alert = $tr->translate("INSERT_SUCCESS");
					echo "<script> alert('".$alert."');</script>";
		    		echo "<script>window.close();</script>";
				}else{
					if(isset($_data['save_close'])){
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
					}
				}
				
				
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$type=3; //Product
		$frm = new Stock_Form_FrmProductSet();
		$frm->FrmProductSet();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_items = $frm;
	    
	    $db = new Application_Model_DbTable_DbGlobal();
	    $d_row = $db->getAllItems(3);
	    array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
	    $this->view->cat_rows = $d_row;
		$this->view->rsmaincategory = $db->getAllItems(3,null,null,0,'','',1,1);

	}
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
		$db = new Global_Model_DbTable_DbItemsDetail();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$rs = $db->updateProductSet($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		    $type=3; //Product
		    $row =$db->getProductSetLocationById($id);
			$this->view->row = $row;
			$arrData=[
				"id"=>$id
			];
		    $this->view->pro_detail=$db->getProductSetDetailById($arrData);
			
			$dbg = new Application_Model_DbTable_DbGlobal();
			$this->view->rsmaincategory = $dbg->getAllItems(3,null,null,0,'','',1,1);
		  
			$row["action"] = "edit";
		    $frm = new Stock_Form_FrmProductSet();
			$frm->FrmProductSet($row);
		    Application_Model_Decorator::removeAllDecorator($frm);
		    $this->view->frm_items = $frm;
	}
	public function copyAction(){
		$id=$this->getRequest()->getParam('id');
		$db = new Global_Model_DbTable_DbItemsDetail();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$rs = $db->addProductSet($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$type=3; //Product
		$row =$db->getProductSetLocationById($id,$type,1);
		$this->view->row = $row;
		$arrData=[
			"id"=>$id
		];
		$this->view->pro_detail=$db->getProductSetDetailById($arrData);
	
		$frm = new Stock_Form_FrmProductSet();
		$frm->FrmProductSet($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_items = $frm;
	
		$product_type=1;
		$d_row= $db->getAllProductsNormal($product_type);
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
		$this->view->productlist=$d_row;

		$dbg = new Application_Model_DbTable_DbGlobal();
		$this->view->rsmaincategory = $dbg->getAllItems(3,null,null,0,'','',1,1);
	}
	function refreshproductAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db = new Global_Model_DbTable_DbItemsDetail();
				$product_type=1;
				$d_row= $db->getAllProductsNormal($product_type);
				array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
				array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
				print_r(Zend_Json::encode($d_row));
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	
	function deplicateproAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Global_Model_DbTable_DbItemsDetail();
    		$pro_cate = $db->checkProductSetHasExitInLocation($data);
    		print_r(Zend_Json::encode($pro_cate));
    		exit();
    	}
    }
	
	function prosetDetailbyBranchAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db = new Global_Model_DbTable_DbItemsDetail();
				$d_row= $db->getProductSetDetailById($data);
				print_r(Zend_Json::encode($d_row));
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
}