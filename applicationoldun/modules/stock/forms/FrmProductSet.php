<?php 
Class Stock_Form_FrmProductSet extends Zend_Dojo_Form {
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
	public function FrmProductSet($data=null){
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		

		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_BRANCH"),
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside height-text',));
		$_branch_id->setValue($request->getParam("branch_id"));
		if (count($optionBranch)==1){
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($optionBranch))foreach($optionBranch AS $row){
				$_branch_id->setValue($row['id']);
			}
		}
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"),"-1"=>$this->tr->translate("ADD_NEW"));
    	$Option = $_dbgb->getAllItems(3);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_items_id = new Zend_Dojo_Form_Element_FilteringSelect("items_id");
    	$_items_id->setMultiOptions($_arr_opt);
    	$_items_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'onChange'=>'checkaddItems(), deplicateItem();',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside',
			));
			
		$_price = new Zend_Dojo_Form_Element_NumberTextBox('price');
    	$_price->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'required'=>'true',
    			'placeholder'=>$this->tr->translate("SELL_PRICE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Price")
    	));
		
		$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important;'));
		
		
		$id = new Zend_Form_Element_Hidden('id');
		
		$_arr_opt_branch = array(0=>$this->tr->translate("MANUAL"));
		$brList = $_dbgb->getAllLocation();
		if(!empty($brList))foreach($brList AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_baseOnLocation = new Zend_Dojo_Form_Element_FilteringSelect("baseOnLocation");
		$_baseOnLocation->setMultiOptions($_arr_opt_branch);
		$_baseOnLocation->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_BRANCH"),
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onChange'=>'getDteailByBranch();',
				'class'=>'fullside height-text',));
		$_baseOnLocation->setValue($request->getParam("baseOnLocation"));
		if (count($brList)==1){
			$_baseOnLocation->setAttribs(array('readonly'=>'readonly'));
		}
		
		 if(!empty($data)){
			 if(!empty($data["action"])){
				 $_items_id->setAttribs(array('readonly'=>'readonly'));
				$_branch_id->setAttribs(array('readonly'=>'readonly'));
			 }
			
			
		 	$_branch_id->setValue($data["branch_id"]);
		 	$_items_id->setValue($data["items_id"]);
		 	$_price->setValue($data["price"]);
		 	$id->setValue($data["id"]);
		 	$note->setValue($data["note"]);
		 	
		 }
		$this->addElements(array(
				$_branch_id,
				$_items_id,
				$_price,
				$id,
				$note,
				$_baseOnLocation,
				));
		
		return $this;
		
	}
	
	
	
	
}