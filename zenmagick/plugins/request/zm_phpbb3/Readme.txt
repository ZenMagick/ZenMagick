This plugin adds phpBB3 integration to ZenMagick.


Installation
============
* Download (obvious ;)
  Download the latest version from http://www.zenmagick.org

* Extract into the ZenMagick plugins directory

* Install the plugin via the ZenMagick plugins admin page

* Done!


Usage
=====
The plugin will add validation rules for the nickname field ('nick') and enable the nickname
field via the 'isAccountNickname' setting.

If nickname is set up as optional, phpBB registration will be skipped if it is not set during registration.
If the nickname is then set at a later stage, the phpBB account will be created with the current credentials.


Settings
========
The phpBB3 installation folder can either be set via the plugin UI, the setting 'plugins.zm_pbpbb3.root', or, alternatively
with the zen-cart define DIR_WS_PHPBB.
