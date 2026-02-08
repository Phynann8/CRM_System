/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	/*config.filebrowserBrowseUrl = '../kcfinder/browse.php?opener=ckeditor&type=files';
   config.filebrowserImageBrowseUrl = '../kcfinder/browse.php?opener=ckeditor&type=images';
   config.filebrowserFlashBrowseUrl = '../kcfinder/browse.php?opener=ckeditor&type=flash';
   config.filebrowserUploadUrl = '../kcfinder/upload.php?opener=ckeditor&type=files';
   config.filebrowserImageUploadUrl = '../kcfinder/upload.php?opener=ckeditor&type=images';
   config.filebrowserFlashUploadUrl = '../kcfinder/upload.php?opener=ckeditor&type=flash';*/
   var base_url = location.hostname+window.location.pathname;
 var pathArray = base_url.split( '/' );
 var BaseURL = pathArray[0]+"/"+pathArray[1]+"/public/ckeditor";
 config.filebrowserBrowseUrl = "//"+BaseURL+'/kcfinder/browse.php?opener=ckeditor&type=files';
   config.filebrowserImageBrowseUrl ="//"+ BaseURL+'/kcfinder/browse.php?opener=ckeditor&type=images';
   config.filebrowserFlashBrowseUrl = ""+BaseURL+'/kcfinder/browse.php?opener=ckeditor&type=flash';
config.filebrowserUploadUrl = "//"+BaseURL+'/kcfinder/upload.php?opener=ckeditor&type=files';
   config.filebrowserImageUploadUrl = "//"+BaseURL+'/kcfinder/upload.php?opener=ckeditor&type=images';
   config.filebrowserFlashUploadUrl ="//"+BaseURL+'/kcfinder/upload.php?opener=ckeditor&type=flash';
 
	config.toolbarGroups = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
	                		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
	                		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
	                		{ name: 'links', groups: [ 'links' ] },
	                		{ name: 'insert', groups: [ 'insert' ] },
	                		{ name: 'forms', groups: [ 'forms' ] },
	                		{ name: 'tools', groups: [ 'tools' ] },
	                		{ name: 'others', groups: [ 'others' ] },
	                		'/',
	                		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
	                		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
	                		{ name: 'styles', groups: [ 'styles' ] },
	                		{ name: 'colors', groups: [ 'colors' ] },
	                		{ name: 'about', groups: [ 'about' ] }
	];

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';

	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';
};
