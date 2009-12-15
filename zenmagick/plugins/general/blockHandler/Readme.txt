BLock handler plugin to allow to assign arbitrary content to content areas marked by HTML comments.

Comments have to be in the form <!-- block::BLOCKID -->

Contents can be attached to a particular blockId by calling (example for sideboxes):

    ZMBlockManager::instance()->registerBlock('leftColumn', new ZMSideboxBlockContents('login.php'));

Different ZMBlockContents implementation could get the contents from file, the database or just make it up
on the fly.

TODO:
* Admin UI
* Add support for block templates
* Integrate sideboxes in Admin UI and remove sample code from plugin class

OPEN:
* What to do about blocks without contents? Should the block template still be applied (if there other required contents in it)
