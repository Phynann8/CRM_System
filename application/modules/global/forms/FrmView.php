<?php
class Global_Form_FrmView extends Zend_Dojo_Form
{
	protected $tr;
	protected $tvalidate; //text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	protected $textarea = null;
	//protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_date = 'dijit.form.DateTextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->text = 'dijit.form.TextBox';
		$this->textarea = 'dijit.form.Textarea';
		//$this->check='dijit.form.CheckBox';
	}
	public function FrmView($data = null)
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$_dbgb = new Global_Model_DbTable_DbView();

		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$Option = $_dbgb->getAllViewType();
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$type = new Zend_Dojo_Form_Element_FilteringSelect("type");
    	$type->setMultiOptions($_arr_opt);
    	$type->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'missingMessage'=>'Invalid Module!',
			'class'=>'fullside',
		));

		$name_kh = new Zend_Dojo_Form_Element_TextBox('name_kh');
		$name_kh->setAttribs(array('dojoType' => $this->tvalidate, 'required' => 'true', 'class' => 'fullside',));

		$name_en = new Zend_Dojo_Form_Element_TextBox('name_en');
		$name_en->setAttribs(array('dojoType' => $this->tvalidate, 'required' => 'true', 'class' => 'fullside',));

		$shortcut = new Zend_Dojo_Form_Element_TextBox('shortcut');
		$shortcut->setAttribs(array('dojoType' => $this->tvalidate, 'required' => 'false', 'class' => 'fullside',));

		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array('dojoType'=>'dijit.form.Textarea','class'=>'fullside',
				'style'=>'width:100%;min-height:60px;'));
		$note->setValue($request->getParam('note'));
		
		$id = new Zend_Form_Element_Hidden("id");
		
		if (!empty($data)) {
			$name_kh->setValue($data['name_kh']);
			$name_en->setValue($data['name_en']);
			$shortcut->setValue($data['shortcut']);
			$type->setValue($data['type']);
			$note->setValue($data['note']);
			$id->setValue($data['id']);
		}
		$this->addElements(array(
			$name_kh
			,$name_en
			,$shortcut
			,$type
			, $id
			, $note
		));
		return $this;
	}
	
}
