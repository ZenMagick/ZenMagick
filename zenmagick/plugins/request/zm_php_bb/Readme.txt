This plugin adds phpBB integration to ZenMagick as exists in zen-cart.

Originally, this code was part of the ZenMagick core, but got extracted into a 
separate plugin as it is considered optional.


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

The plugin uses the global $phpBB as set up by zen-cart.
