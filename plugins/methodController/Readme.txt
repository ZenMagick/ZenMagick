Example plugin illustrating url mappings to use custom controller methods for request processing.
=================================================================================================


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.


Example URLs
============
1) index.php?main_page=foo
Calls the foo method on ZMMethodController

2) index.php?main_page=bar
Calls the bar method on ZMMethodController

3) index.php?main_page=xform
Display form (checks for 'GET')

4) index.php?main_page=xform
Process form (checks for 'POST')
