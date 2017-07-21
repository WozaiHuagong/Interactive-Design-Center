/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.fillEmptyBlocks = false;
	config.skin = 'bootstrapck';
	config.toolbar = 'Full';
	config.language = 'zh-cn';
	config.toolbar_Full = [
		//'FontSize','RemoveFormat'
		 ['Cleanup','Bold','Italic','NumberedList','BulletedList', 'Blockquote', 'pbckcode', 'Maximize']
	]

	config.extraPlugins = 'pbckcode';
	config.height = 300;
	config.magicline_color = '#ccc';
	//config.enterMode = CKEDITOR.ENTER_BR;
    config.shiftEnterMode = CKEDITOR.ENTER_P;
	//config.magicline_everywhere = true;
	//config.forcePasteAsPlainText = true;

	config.pasteFromWordIgnoreFontFace = true; //默认为忽略格式
	config.pasteFromWordRemoveFontStyles = false;
	config.pasteFromWordRemoveStyles = false;
	//config.fontSize_sizes = '16px;18px';
	// Dialog windows are also simplified.
	config.removeDialogTabs = 'link:advanced;image:advanced';
	//config.removeButtons = 'Cut,Copy,Paste,Undo,Redo,Anchor,Underline,Strike,Subscript,Superscript';

	//config.removePlugins = 'enterkey,elementspath,tabletools,contextmenu';

};
