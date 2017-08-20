/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// config.skin = 'icy_orange';
	config.language = 'zh-cn';
	// config.skin = 'bootstrapck';
	config.toolbar = 'Full';
	config.toolbar_Full = [
		//'FontSize','RemoveFormat'
		 ['Cleanup','Bold','Italic','Image','NumberedList','BulletedList', 'Blockquote', 'pbckcode', 'Maximize','WecenterImage','WecenterAttach','WecenterLink','ckeditor_wiris_formulaEditor']
	]
	config.removeDialogTabs = 'image:Link;image:advanced';
};
