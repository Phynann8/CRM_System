<?php 
Class Accounting_Form_FrmTransferCredit extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	
	public function FrmTransferCredit($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
	
		$for_date = new Zend_Dojo_Form_Element_DateTextBox('for_date');
		$for_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$for_date->setValue(date('Y-m-d'));
	
	
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
		));
	
		$rows = $db->getAllBranch();
		$options=array();
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_branch_id->setMultiOptions($options);
		if(count($rows)==1){
			$_branch_id->setAttribs(array(
				'readOnly'=>'readOnly',
			));
			$_branch_id->setValue($rows[0]['id']);
		}
		
		$_to_branchId = new Zend_Dojo_Form_Element_FilteringSelect('to_branchId');
		$_to_branchId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
		));
		$options=array();
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_to_branchId->setMultiOptions($options);
		if(count($rows)==1){
			$_to_branchId->setAttribs(array(
				'readOnly'=>'readOnly',
			));
			$_to_branchId->setValue($rows[0]['id']);
		}
		
	
		$prob=new Zend_Dojo_Form_Element_TextBox('prob');
		$prob->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'required'=>true,
		));
	
		$problem=new Zend_Dojo_Form_Element_TextBox('problem');
		$problem->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
	
		$Description_s = new Zend_Dojo_Form_Element_Textarea('Descriptions');
		$Description_s ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>"font-size:14px;font-family: 'Khmer OS Battambang';height:50px;"
		));
	
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
	
		
		$id = new Zend_Form_Element_Hidden("id");
		
		$_creditType = new Zend_Dojo_Form_Element_FilteringSelect('creditType');
		$_creditType ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
					
		));
		$optionsCreditType= array(
					1=>$this->tr->translate("LIFE_TIME"),
					2=>$this->tr->translate("IS_VALIDATE")
				);
		$_creditType->setMultiOptions($optionsCreditType);
		
		$_enddate = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_enddate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_enddate->setValue(date('Y-m-d'));
	
		if($data!=null){
			$_branch_id->setValue($data['fromBranchId']);
			$_to_branchId->setValue($data['branch_id']);
			
			$_branch_id->setAttribs(array(
				'readOnly'=>'readOnly',
			));
			$_to_branchId->setAttribs(array(
				'readOnly'=>'readOnly',
			));
			
			$total_amount->setValue($data['total_amountafter']);
			$prob->setValue($data['prob']);
			$for_date->setValue(date_format(date_create($data['date']), "Y-m-d"));
			
			$_creditType->setValue($data['creditType']);
			$_enddate->setValue($data['end_date']);
			$id->setValue($data['id']);
		
		}
		$this->addElements(array($problem,$Description_s,$prob,
				$total_amount
				,$_branch_id
				,$for_date
				,$id
				,$_to_branchId
				,$_creditType
				,$_enddate
				
				));
		return $this;
	
	}	
}