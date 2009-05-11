A generic scaffolding package for ZenMagick.
Eventually, this is supposed to move into core.


Installation
============
* Download (obvious ;)
  Download the latest version from http://www.zenmagick.org

* Extract into the ZenMagick plugins directory

* Install the plugin via the ZenMagick plugins admin page

* Done!


Configure
=========
Once installed, all that needs to be done is to set up mappings to make the controller found
for all pages required.


TODO
====
- view handling
- request parsing (key, action, main_page?)
- how to allow setup only a list of page names? (setting/plugin option - SACS?)
- support main_page=PAGE&action=create|read|update|delete[&key=KEY]
  and index.php/PAGE/ACTION[/KEY] {HOW DO WE DO THAT???}
  and (via redirect (example incl)): scaffold/PAGE/ACTION[/KEY]
- add toolbox method to create URL for page and also form action
