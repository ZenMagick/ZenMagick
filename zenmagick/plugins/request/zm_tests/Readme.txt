Unit testing framework for ZenMagick
====================================
For details and licensing of SimpleTest please refer to the included .simpletest documents.


SimpleTest
==========
The SimpleTest unit testing code is stripped and bundled in a single file included with this plugin.
The code is based on version 1.0.1 with three patches:
* pass $method into the skip() method of the SimpleTestCase class.
* remove deprecated use of '&' to dereference objects
* use of split() replaced with preg_split()


jquery treeview
===============
The included treeview plugin has some custom CSS rules to allow for the checkobxes in the tree to work as expected.


Test data
=========
The included tests require the demo store data (tested with zen-cart 1.3.8).
In addition to that, the following configuration changes need to be done:

* In table reviews, change the customers_id of the single review from 0 to 1


Disclamer
=========
EVEN THOUGH IT IS THE INTENTION TO REMOVE ALL CREATED TEST DATA, THERE IS NO GUARANTEE THAT THIS PLUGIN
WILL NOT CREATE DATA OR MODIFY EXISTING DATA. 
THIS PLUGIN IS FOR TESTING AND DEVELOPMENT ONLY AND SHOULD NOT BE USED ON PRODUCTION SYSTEMS.
