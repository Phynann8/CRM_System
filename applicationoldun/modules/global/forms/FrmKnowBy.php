<?php
class Global_Form_FrmKnowBy extends Zend_Dojo_Form
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
	public function FrmKnowBy($data = null)
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType' => $this->tvalidate, 'required' => 'true', 'class' => 'fullside',));

		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array('dojoType'=>'dijit.form.Textarea','class'=>'fullside',
				'style'=>'width:100%;min-height:60px;'));
		$note->setValue($request->getParam('note'));
		
		$id = new Zend_Form_Element_Hidden("id");
		
		if (!empty($data)) {
			$_title->setValue($data['title']);
			$id->setValue($data['id']);
		}
		$this->addElements(array(
			$_title
			, $id
			, $note
		));
		return $this;
	}
	
}
