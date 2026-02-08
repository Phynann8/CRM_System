<?php 
Class Stock_Form_FrmProduct extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
	}
	public function FrmAddPurchase($data=null){
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		 
		$_dbcht = new Stock_Model_DbTable_DbCutStock();
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		

		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_BRANCH"),
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside height-text',));
		$_branch_id->setValue($request->getParam("branch"));
		if (count($optionBranch)==1){
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($optionBranch))foreach($optionBranch AS $row){
				$_branch_id->setValue($row['id']);
			}
		}
		
		$_purchase_no = new Zend_Dojo_Form_Element_TextBox('purchase_no');
		$_purchase_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside height-text',
				'readonly'=>'readonly',
				'style'=>'color: red; font-weight: 600;',
				'placeholder'=>$this->tr->translate("PURCHASE_NO"),
		));
		
		
		$_invoice_no = new Zend_Dojo_Form_Element_TextBox('invoice_no');
		$_invoice_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside height-text',
				'readonly'=>'false',
				'style'=>'color: red; font-weight: 600;',
		));
		
		$total_received = new Zend_Dojo_Form_Element_NumberTextBox('total_received');
		$total_received->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("TOTAL_RECEIVED"),
				'missingMessage'=>$this->tr->translate("Forget Enter Total Paid")
		));
		
		$_total_discount = new Zend_Dojo_Form_Element_NumberTextBox('total_discount');
		$_total_discount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("TOTAL_DISCOUNT"),
				'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		
		$_total_remain = new Zend_Dojo_Form_Element_NumberTextBox('total_remain');
		$_total_remain->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("total_remain"),
				'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		
		
		$_purchase_date= new Zend_Dojo_Form_Element_DateTextBox('purchase_date');
		$_purchase_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'value'=>'now',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',));
		$_purchase_date->setValue(date("Y-m-d"));
		
		$_all_balance = new Zend_Dojo_Form_Element_NumberTextBox('all_balance');
		$_all_balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("BALANCE"),
				'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		$_all_balance->setValue(0);
		
		$_amount = new Zend_Dojo_Form_Element_NumberTextBox('amount');
		$_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'onKeyup'=>'checkAmout()',
				'placeholder'=>$this->tr->translate("AMOUNT"),
				'missingMessage'=>$this->tr->translate("Forget Enter Amount")
		));
		$_amount->setValue(0);
		
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("VOID"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		
		$id = new Zend_Form_Element_Hidden('id');
		
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'value'=>'now',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'placeholder'=>$this->tr->translate("START_DATE"),
				'class'=>'fullside',));
		$_date = $request->getParam("start_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		 
		$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(
			array(
				'dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'required'=>false)
			);
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);
		
	
		
		// if(!empty($data)){
		// 	$_branch_id->setValue($data["branch_id"]);
		// 	$_cut_stock_type->setValue($data["cut_stock_type"]);
		// 	$_serailno->setValue($data["serailno"]);
		// 	$_balance->setValue($data["totalAmountDue"]);
		// 	$total_received->setValue($data["totalQtyReceive"]);
		// 	$_amount->setValue($data["totalQtyReceive"]);
		// 	$_total_remain->setValue($data["totalQtyRemain"]);
		// 	if (!empty($data["received_date"])){
		// 		$_date_payment->setValue(date("Y-m-d",strtotime($data["received_date"])));
		// 	}
		// 	$_status->setValue($data["status"]);
		// 	$id->setValue($data["id"]);
		// 	$note->setValue($data["note"]);
		// 	$_branch_id->setAttribs(array(
		// 			'readonly'=>'readonly',
		// 			));
		// 	$_cut_stock_type->setAttribs(array(
		// 		'readonly'=>'readonly',
		// 	));
		// }
		$this->addElements(array(
				$_branch_id,
				$_purchase_no,
				$_purchase_date,

				
				));
		
		return $this;
		
	}
	
	
	
	
}