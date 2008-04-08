<?php

    // display errors in browser
    //@ini_set("display_errors", true);

    // report ALL errors, warnings and info messages (that might be a lot)
    //error_reporting(E_ALL);

    // show backtrace (and potentially die) using ZMObejct::backtrace(..) - not recommended for production
    //ZMSettings::set('isShowBacktrace', true);

    // log missing settiongs
    //ZMSettings::set('isLogMissingSettings', true);

    // disable ZenMagick themes
    //ZMSettings::set('isEnableZenMagick', false);

    // enable ZenMagick's global variables
    //ZMSettings::set('isLegacyAPI', true);

    // enable zen-cart style define pages
    //ZMSettings::set('isZMDefinePages', false);

    // allow tell a friend for anonymous users
    //ZMSettings::set('isTellAFriendAnonymousAllow', false);

    // enable guest checkout
    //ZMSettings::set('isGuestCheckout', true);
    // logoff guests after checkout
    //ZMSettings::set('isLogoffGuestAfterOrder', false);

    // set error logfile for ZenMagick log entries; 
    //ZMSettings::set('zmLogFilename', ZMRuntime::getZMRootPath()."zenmagick.log");
    // set to true to log all PHP errors in custom file using the ZenMagick error handler
    //ZMSettings::set('isZMErrorHandler', true);

    // disable all patching
    //ZMSettings::set('isEnablePatching', false);

?>
