TinyMCE WYSIWYG plugin
======================
Plugin to use TinyMCE - http://tinymce.moxiecode.com/.


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.

Once installed, TinyMCE should appear in the list of available editors.

To make this the default editor, create the following setting (for example in
zenmagick/local.php):

  ZMSettings::set('defaultEditor', 'ZMTinyMCEFormWidget');
