This plugin adds vBulletin integration to ZenMagick.


Installation
============
* Download (obvious ;)
  Download the latest version from http://www.zenmagick.org/

* Extract into the ZenMagick plugins directory

* Install the plugin via the ZenMagick plugins admin page and configure.

* Done!


Usage
=====
The plugin will add validation rules for the nickname field ('nickName') and enable the nickname
field via the 'isAccountNickname' setting.

If nickname is set up as optional, vBulletin registration will be skipped if it is not set during registration.
If the nickname is then set at a later stage, the vBulletin account will be created with the current credentials.


Settings
========
The vBulletin installation folder can either be set via the plugin UI or the setting 'plugins.vbulletin.root.
