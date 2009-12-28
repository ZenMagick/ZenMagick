haml for ZenMagick - using phphaml


Installation
============
1) Unzip this plugin into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Enjoy


Version
=======
The included version is 1.0RC1


Patches
=======
The haml parser class has been patched to use $__oHamlParser instead of $this for calls to methods in generated code. Also, $__oHamlParser is set to $this in the parse/render method.
This allows to use the parser in the context of a Savant view without breaking references to $this.

Also, a new generate() method was added that just generated the compiled file without executing anything. Since the used Savant view is file based, this means that effectively only the parser is used and filters will not work right now.
