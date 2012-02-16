This is a ZenMagick plugin adding Gravatar support to accounts
==============================================================
See http://en.gravatar.com/ for more details.


Installation
------------
1) Unzip this plugin into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure as required.


Usage
=====
The plugin adds the method getGravatar to account objects. The most basic way to display the accounts gravatar image is:

       <?php if ($gravatar = $currentAccount->getGravatar()) { echo $gravatar; } ?>

The method getGravatar accepts the following optional parameter:
- size: The image size in pixels (max is 512)
- img: flag to indicate whether to return a full IMG element or just the image urll; default is true
- attributes: Optional key/value array for IMG element attributes; this is ignored if img == false
