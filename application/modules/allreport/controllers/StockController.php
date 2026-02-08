<?php
class Allreport_StockController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
	}
	public function rptProductLocationAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search' =>'',
					'branch_id' =>'',
					'product'=>'',
					'sort_by'=>-1,
					'category_id'=>'',
					'product_type'=>0
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbProductList();
			$this->view->pro_loc = $db->getProductLocation($search);
	
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}

		$frm=new Application_Form_FrmCombineSearchGlobal();
		$form=$frm->FormSearchProductLocation();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$frm = new Application_Form_FrmGlobal();
		$today = new DateTime();
		$today->modify('+1 min');
		$textTran = "initial";
		$printDate =  $today->format("d/m/Y  h:i");
		$printDate.=  " ".strtoupper($today->format("a"));
		$prefixDate= "Printed";
		$arr=array(
			"footerLeftContent" => " $prefixDate : ".$printDate,
			"footerLeftTextTransform" => $textTran,
		);
		$this->view->printFormat = $frm->getPrintPageFormat($arr);
		$this->view->controlReport = $frm->reportControl();
	}
	public function rptListProductAction(){
		$id=$this->getRequest()->getParam('id');
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'txtsearch' =>$_data['txtsearch'],
						'start_date'=> $_data['from_date'],
						'end_date'=> $_data['to_date']
				);
			}
			else{
				$search = array(
						'txtsearch' =>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbProductList();
			$this->view->list_pro = $db->getProductsByLocId($id);
			$this->view->branch_name = $db->getLocationNameById($id);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			
		}
	}
	
	//Start Block Purchase
	public function rptPurchaseAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'title' =>'',
					'branchid' =>'',
					'supplier_id'=>-1,
					'category_id'=>-1,
					'product' =>'',
					'product_type' =>'',
					'status_search'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbPurchase();
			$this->view->pur_all = $db->getAllPurchaseSumarryReport($search);
			
			$branch_id = empty($search['branchid'])?null:$search['branchid'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Accounting_Form_FrmSearchProduct();
		$form=$form->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$frm = new Application_Form_FrmGlobal();
		$today = new DateTime();
		$today->modify('+1 min');
		$textTran = "initial";
		$printDate =  $today->format("d/m/Y  h:i");
		$printDate.=  " ".strtoupper($today->format("a"));
		$prefixDate= "Printed";
		$arr=array(
			"footerLeftContent" => " $prefixDate : ".$printDate,
			"footerLeftTextTransform" => $textTran,
		);
		$this->view->printFormat = $frm->getPrintPageFormat($arr);
		$this->view->controlReport = $frm->reportControl();
	}
	
	//End Block Purchase
	public function rptPurchaseSupplierAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = $search = array(
						'title' =>'',
						'location' =>'',
						'status_search'=>1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbPurchase();
			$id=$this->getRequest()->getParam('id');
			$this->view->purchase = $db->getPruchaseById($id);
			$this->view->pur_detail=$db->getPurchaseProductDetail($id,$search);
	
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			
		}
		$form=new Accounting_Form_FrmSearchProduct();
		$form=$form->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$frm = new Application_Form_FrmGlobal();
		$today = new DateTime();
		$today->modify('+1 min');
		$textTran = "initial";
		$printDate =  $today->format("d/m/Y  h:i");
		$printDate.=  " ".strtoupper($today->format("a"));
		$prefixDate= "Printed";
		$arr=array(
			"footerLeftContent" => " $prefixDate : ".$printDate,
			"footerLeftTextTransform" => $textTran,
		);
		$this->view->printFormat = $frm->getPrintPageFormat($arr);
		$this->view->controlReport = $frm->reportControl();
	}
	
	//Start Block Sale Product
	public function rptProductsoldAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search' 		=> '',
					'branch_id'		=> '',
					'study_year'	=> '',
					'user_id' 		=> '',
					'product'		=> '',
					'category_id'	=> '',
					'start_date'	=> date('Y-m-d'),
					'end_date'		=> date('Y-m-d'),
				);
			}
			
			$db = new Allreport_Model_DbTable_DbRptSummaryStock();
			$this->view->product_sold = $db->getAllProductSold($search);
	
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
			$today = new DateTime();
			$today->modify('+1 min');
			$textTran = "initial";
			$printDate =  $today->format("d/m/Y  h:i");
			$printDate.=  " ".strtoupper($today->format("a"));
			$prefixDate= "Printed";
			$arr=array(
				"footerLeftContent" => " $prefixDate : ".$printDate,
				"footerLeftTextTransform" => $textTran,
			);
			$this->view->printFormat = $frm->getPrintPageFormat($arr);
			$this->view->controlReport = $frm->reportControl();
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$this->view->search = $search;
		
		$frm=new Application_Form_FrmCombineSearchGlobal();
		$form=$frm->FormgetProdcutSold();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
	public function rptproductqtysoldAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search=array(
						'adv_search' 		=>'',
						'branch_id'		=>'',
						'user_id' 			=>'',
						'product'		=>'',
						'category_id'	=>'',
						'start_date'	=> date('Y-m-d'),
						'end_date'		=>date('Y-m-d'),
				);
			}
			$db = new Allreport_Model_DbTable_DbRptSummaryStock();
			$this->view->rspro = $db->getProductSold($search);
			$type=3;//product
				
			$this->view->search = $search;
				
			$db = new Global_Model_DbTable_DbItemsDetail();
			$d_row= $db->getAllProductsNormal();
			array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
			$this->view->product= $d_row;
			
			$branch_id = empty($search['branch_search'])?null:$search['branch_search'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
			
			$today = new DateTime();
			$today->modify('+1 min');
			$textTran = "initial";
			$printDate =  $today->format("d/m/Y  h:i");
			$printDate.=  " ".strtoupper($today->format("a"));
			$prefixDate= "Printed";
			$arr=array(
				"footerLeftContent" => " $prefixDate : ".$printDate,
				"footerLeftTextTransform" => $textTran,
			);
			$this->view->printFormat = $frm->getPrintPageFormat($arr);
			$this->view->controlReport = $frm->reportControl();
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}

		$frm=new Application_Form_FrmCombineSearchGlobal();
		$form=$frm->FormgetProdcutSold();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	//End Block Sale Product
	
    public function rptTransferAction(){
		$db = new Stock_Model_DbTable_DbTransferstock();
    	try{
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}else{
    			$search=array(
    					'adv_search' => '',
    					'branch_id' => '',
    					'transfer_status' => -1,
    					'start_date' =>date("Y-m-d"),
    					'end_date' =>date("Y-m-d"),
    			);
    		}
    		$this->view->search=$search;
    		$this-> view->all_transfer = $db->getAllTransferReport($search);	
    		$branch_id = empty($search['branch_search'])?null:$search['branch_search'];
    		$frm = new Application_Form_FrmGlobal();
    		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
    		$this->view->rsfooteracc = $frm->getFooterAccount();
			
			$today = new DateTime();
			$today->modify('+1 min');
			$textTran = "initial";
			$printDate =  $today->format("d/m/Y  h:i");
			$printDate.=  " ".strtoupper($today->format("a"));
			$prefixDate= "Printed";
			$arr=array(
				"footerLeftContent" => " $prefixDate : ".$printDate,
				"footerLeftTextTransform" => $textTran,
			);
			$this->view->printFormat = $frm->getPrintPageFormat($arr);
			$this->view->controlReport = $frm->reportControl();
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
		$frm=new Application_Form_FrmCombineSearchGlobal();
		$form=$frm->FormSearchTransferStock();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
    public function rptTransferdetailAction(){

		$id = $this->getRequest()->getParam("id");
		$db = new Stock_Model_DbTable_DbTransferstock();
		$row =$db->getTransferById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/stock/transfer");
			exit();
		}
		$this->view->row = 	$row;
		$this->view->detail = $db->getTransferByIdDetail($id);
	
		$branch_id = empty($row['from_location'])?null:$row['from_location'];
		$_db = new Application_Form_FrmGlobal();
		$this->view->header = $_db->getHeaderReceipt($branch_id);
		$today = new DateTime();
		$today->modify('+1 min');
		$textTran = "initial";
		$printDate =  $today->format("d/m/Y  h:i");
		$printDate.=  " ".strtoupper($today->format("a"));
		$prefixDate= "Printed";
		$arr=array(
			"footerLeftContent" => " $prefixDate : ".$printDate,
			"footerLeftTextTransform" => $textTran,
		);
		$this->view->printFormat = $_db->getPrintPageFormat($arr);
		$this->view->controlReport = $_db->reportControl();
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->officailTransferVoucher = $frmpopup->getTransferVoucher();
	}
	
	//Start Block Request Product
	function rptRequestProductAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
					
			}else{
				$search = array(
						'adv_search'	=>  '',
						'branch_id'		=>  '',
						'request_for' 	=> -1,
						'for_section' 	=> -1,
						'category_id'	=>  '',
						'product_type'	=>  '',
						'product'		=>  '',
						'request_status'=>  -1,
						'start_date'	=>	date('Y-m-d'),
						'end_date'		=>	date('Y-m-d'),
				);
			}
				
			$db=new Allreport_Model_DbTable_DbRequestStock();
			$ds=$this->view->rows=$db->getAllRequestProductDetail($search);
	
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
			$today = new DateTime();
			$today->modify('+1 min');
			$textTran = "initial";
			$printDate =  $today->format("d/m/Y  h:i");
			$printDate.=  " ".strtoupper($today->format("a"));
			$prefixDate= "Printed";
			$arr=array(
				"footerLeftContent" => " $prefixDate : ".$printDate,
				"footerLeftTextTransform" => $textTran,
			);
			$this->view->printFormat = $frm->getPrintPageFormat($arr);
			$this->view->controlReport = $frm->reportControl();
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	
		$this->view->search = $search;
		$form=new Application_Form_FrmCombineSearchGlobal();
		$form->FormSearchRequestReport();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}

	function rptRequestProductdetailAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Allreport_Model_DbTable_DbRptSummaryStock();
		$row =$db->getRequestProductById($id);
		$this->view->row = 	$row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/stock/requestproduct");
			exit();
		}
		$this->view->detail = $db->getAllRequestProductDetailById($id);
	
		$branch_id = empty($row['branch_id'])?null:$row['branch_id'];
		$_db = new Application_Form_FrmGlobal();
		$this->view->header = $_db->getHeaderReceipt($branch_id);
		
		$today = new DateTime();
		$today->modify('+1 min');
		$textTran = "initial";
		$printDate =  $today->format("d/m/Y  h:i");
		$printDate.=  " ".strtoupper($today->format("a"));
		$prefixDate= "Printed";
		$arr=array(
			"footerLeftContent" => " $prefixDate : ".$printDate,
			"footerLeftTextTransform" => $textTran,
		);
		$this->view->printFormat = $_db->getPrintPageFormat($arr);
		$this->view->controlReport = $_db->reportControl();
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->officailRequestVoucher = $frmpopup->getRequestVoucher();
	}
	public function reprintRequestProductAction(){
		$db = new Allreport_Model_DbTable_DbRptSummaryStock();
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$row = $db->getRequestProductById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/stock/rpt-request-product");
			exit();
		}
		$this->view->req =$row;
		$this->view->req_detail = $db->getAllRequestProductDetailById($id);
	}
	//End Block Request Product
	
	function rptAdjustStockdetailAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}else{
				$search = array(
						'title'	        =>	'',
						'branch_id'		=>  '',
						'start_date'	=>	date('Y-m-d'),
						'end_date'		=>	date('Y-m-d'),
						'category_id'	=> "",
						'product'		=> "",
						'product_type'	=> "",
						'user'			=> "",
				);
			}
			$this->view->search = $search;
			$db=new Allreport_Model_DbTable_DbRequestStock();
			$this->view->rows=$db->getAllAdjustStockDetail($search);
	
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
			
			$today = new DateTime();
			$today->modify('+1 min');
			$textTran = "initial";
			$printDate =  $today->format("d/m/Y  h:i");
			$printDate.=  " ".strtoupper($today->format("a"));
			$prefixDate= "Printed";
			$arr=array(
				"footerLeftContent" => " $prefixDate : ".$printDate,
				"footerLeftTextTransform" => $textTran,
			);
			$this->view->printFormat = $frm->getPrintPageFormat($arr);
			$this->view->controlReport = $frm->reportControl();
		}catch(Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Accounting_Form_FrmSearchProduct();
		$form=$form->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public function alertstockAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' =>'',
						'branch_id' =>'',
						'category_id'=>0,
						'product'=>'',
						'product_type'=>0,
				);
			}
			$this->view->search = $search;
			$db = new Registrar_Model_DbTable_DbReportProductNearOutStock();
			$this->view->pro_loc = $db->getLowStockQuantity($search);
	
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}

		$frm=new Application_Form_FrmCombineSearchGlobal();
		$form=$frm->FormLowStockReport();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$frm = new Application_Form_FrmGlobal();
		$today = new DateTime();
		$today->modify('+1 min');
		$textTran = "initial";
		$printDate =  $today->format("d/m/Y  h:i");
		$printDate.=  " ".strtoupper($today->format("a"));
		$prefixDate= "Printed";
		$arr=array(
			"footerLeftContent" => " $prefixDate : ".$printDate,
			"footerLeftTextTransform" => $textTran,
		);
		$this->view->printFormat = $frm->getPrintPageFormat($arr);
		$this->view->controlReport = $frm->reportControl();
	}
	function rptSummaryStockAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
					
			}else{
				$search = array(
						'adv_search'	=>	'',
						'branch_id'		=>  '',
						'closeStockId'	=>  '',
						'category_id'	=>  '',
						'product'		=>  '',
						'product_type'	=>  '',
						'status_search'	=> 1,
						'sort_by'		=>  -1,
						'start_date'	=>	date('Y-m-d'),
						'end_date'		=>	date('Y-m-d'),
				);
				$dbGb = new Stock_Model_DbTable_DbClosingStock();
				$last = $dbGb->getLatestClosingStock();
				if(!empty($last)){
					$search["closeStockId"] = empty($last["id"]) ? '' : $last["id"];
				}
			}
			$this->view->search = $search;
			$db=new Allreport_Model_DbTable_DbRptSummaryStock();
			$ds=$this->view->rows=$db->getSummaryStockReport($search);
	
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
			
			$today = new DateTime();
			$today->modify('+1 min');
			$textTran = "initial";
			$printDate =  $today->format("d/m/Y  h:i");
			$printDate.=  " ".strtoupper($today->format("a"));
			$prefixDate= "Printed";
			$arr=array(
				"footerLeftContent" => " $prefixDate : ".$printDate,
				"footerLeftTextTransform" => $textTran,
			);
			$this->view->printFormat = $frm->getPrintPageFormat($arr);
			$this->view->controlReport = $frm->reportControl();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	
		$frm=new Application_Form_FrmCombineSearchGlobal();
		$form=$frm->FormSearchSumarryReport();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
	// Start Blog Action Purchase Payment
	public function rptPurchasePaymentAction(){
		try{
		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    							'branch_search' => '',
    							'adv_search' => '',
    					        'supplier_search'=>'',
    							'paid_by_search'=>'',
    							'start_date'=> date('Y-m-d'),
    							'end_date'=>date('Y-m-d'),
    							'status'=>"",
    							'first'=>1,
    					
    					);
    		}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbPurchase();
			$this->view->row = $db->getAllPurchasePayment($search);
			
			$branch_id = empty($search['branch_search'])?null:$search['branch_search'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Stock_Form_FrmPurchasePayment();
		$frm->FrmAddPurchasePayment(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_payment = $frm;
	}
	
	public function rptPaymentReceiptAction(){
		try{
			$id=$this->getRequest()->getParam('id');
			$id = empty($id)?0:$id;
			$db = new Allreport_Model_DbTable_DbPurchase();
			$row = $db->getPurchasePaymentById($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("No Record","/allreport/stock/rpt-purchase-payment");
				exit();
			}
			$this->view->row = $row;
			$this->view->rowDetail = $db->getPurchasePaymentDetail($id);
			
			$branch_id = empty($row['branch_id'])?null:$row['branch_id'];
			$_db = new Application_Form_FrmGlobal();
			$this->view->header = $_db->getHeaderReceipt($branch_id);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	public function rptClosingPurchasepaymentAction(){
		try{
			if($this->getRequest()->isPost()){
				$search = $this->getRequest()->getPost();
			}
			else{
				$search=array(
						'branch_search' => '',
						'adv_search' => '',
						'supplier_search'=>'',
						'paid_by_search'=>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'status'=>"",
						'user_id'=>"",
						'first'=>1,
							
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbPurchase();
			$this->view->row = $db->getAllPurchasePaymentForClose($search);
	
			$branch_id = empty($search['branch_search'])?null:$search['branch_search'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Stock_Form_FrmPurchasePayment();
		$frm->FrmAddPurchasePayment(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_payment = $frm;
	}
	public function closingpurchasepaymentAction(){
		try{
			$db =  new Allreport_Model_DbTable_DbPurchase();
			if($this->getRequest()->isPost()){
				$_data = $this->getRequest()->getPost();
				if (empty($_data['selector'])){
					Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/stock/rpt-closing-purchasepayment");
					exit();
				}
				$db->closingPurchasePayment($_data);
				Application_Form_FrmMessage::Sucessfull("CLOSING_SUCCESS", "/allreport/stock/rpt-closing-purchasepayment");
				exit();
			}
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/stock/rpt-closing-purchasepayment");
			exit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	// End Blog Action Purchase Payment
	
	
	public function rptSupplierBalanceAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'title' =>'',
						'branch_id' =>'',
						'paymentstatus'=>-1,
						'supplier_id'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$this->view->search = $search;
			$db = new Allreport_Model_DbTable_DbPurchase();
			$this->view->pur_code = $db->getSuplierPuchaseBalance($search);
		
			$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}

		$frm=new Application_Form_FrmCombineSearchGlobal();
		$form=$frm->FormSearchSupplierBalanceReport();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
		
		$frm = new Application_Form_FrmGlobal();
		$today = new DateTime();
		$today->modify('+1 min');
		$textTran = "initial";
		$printDate =  $today->format("d/m/Y  h:i");
		$printDate.=  " ".strtoupper($today->format("a"));
		$prefixDate= "Printed";
		$arr=array(
			"footerLeftContent" => " $prefixDate : ".$printDate,
			"footerLeftTextTransform" => $textTran,
		);
		$this->view->printFormat = $frm->getPrintPageFormat($arr);
		$this->view->controlReport = $frm->reportControl();
	}
	public function rptStudentProductAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search=array(
					'title' 		=>'',
					'study_year' 	=>'',
					'grade_all' 	=>'',
					'branch_id'=>0,
					'group'			=>'',
					'degree'=>0,
					'end_date'		=> date('Y-m-d'),
			);
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$forms=$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
		$stock= new Allreport_Model_DbTable_DbRptSummaryStock();
		$this->view->rs = $stock->getAllStudentProduct($search);
		$this->view->rsnew = $stock->getAllStudentProduct($search,1);
		$this->view->search=$search;
		
		$branch_id = empty($search['branch_id'])?null:$search['branch_id'];
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		
	}
	public function rptCutstockAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    							'branch_id' => '',
    							'adv_search' => '',
    					        'student_id'=>'',
								'cut_stock_type'=>'',
    							'start_date'=> date('Y-m-d'),
    							'end_date'=>date('Y-m-d'),
    							'status'=>'',
    					);
    		}
			$db =  new Allreport_Model_DbTable_DbRptSummaryStock();
			$rows = $db->getAllCutStock($search);
			$this->view->rs=$rows;
			
			$branch_id = empty($search['branch_search'])?null:$search['branch_search'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$this->view->search = $search;
		
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
	}
	public function rptStudentGetProductAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}else{
    			$search=array(
					'branch_id' => '',
					'adv_search' => '',
					'student_id'=>'',
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
					'stock_status'=>'',
				);
    		}
			$db =  new Allreport_Model_DbTable_DbRptSummaryStock();
			$rows = $db->studentGetProduct($search);
			$this->view->rs=$rows;
			
			$branch_id = empty($search['branch_search'])?null:$search['branch_search'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			
			$today = new DateTime();
			$today->modify('+1 min');
			$textTran = "initial";
			$printDate =  $today->format("d/m/Y  h:i");
			$printDate.=  " ".strtoupper($today->format("a"));
			$prefixDate= "Printed";
			$arr=array(
				"footerLeftContent" => " $prefixDate : ".$printDate,
				"footerLeftTextTransform" => $textTran,
			);
			$this->view->printFormat = $frm->getPrintPageFormat($arr);
			$this->view->controlReport = $frm->reportControl();
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$this->view->search = $search;

		$frm=new Application_Form_FrmCombineSearchGlobal();
		$form=$frm->FormSearchStudentGetReport();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public function rptClosingProductstudentAction(){
		try{
			if($this->getRequest()->isPost()){
				$search = $this->getRequest()->getPost();
			}
			else{
				$search=array(
						'branch_search' => '',
						'adv_search' => '',
						'student_id'=>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'status_search'=>'',
						'userId'=>"",
				);
			}
			$db =  new Allreport_Model_DbTable_DbRptSummaryStock();
			$rows = $db->getAllCutStockForClose($search);
			$this->view->rs=$rows;
			
			$branch_id = empty($search['branch_search'])?null:$search['branch_search'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
			$this->view->rsfooteracc = $frm->getFooterAccount();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$this->view->search = $search;
		$frm = new Stock_Form_FrmCutStock();
		$frm->FrmAddCutStock(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_payment = $frm;
	}
	public function closingstuproductAction(){
		try{
			$db =  new Allreport_Model_DbTable_DbRptSummaryStock();
			if($this->getRequest()->isPost()){
				$_data = $this->getRequest()->getPost();
				if (empty($_data['selector'])){
					Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/stock/rpt-closing-productstudent");
					exit();
				}
				$db->closingStuProduct($_data);
				Application_Form_FrmMessage::Sucessfull("CLOSING_SUCCESS", "/allreport/stock/rpt-closing-productstudent");
				exit();
			}
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/allreport/stock/rpt-closing-productstudent");
			exit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	
	function getProductbycateAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$data = array(
				'categoryId'=>$data['cate_id']
			);
			$rows = $db->getProductbyBranch($data,1);
			array_unshift($rows, array ( 'id' => 0,'name' => $this->tr->translate('SELECT_PRODUCT')));
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	
}