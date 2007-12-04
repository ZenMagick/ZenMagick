<?php

    // display errors in browser
    //@ini_set("display_errors", true);

    // report ALL errors, warnings and info messages (and that might be a lot)
    //error_reporting(E_ALL);

    // disable ZenMagick themes
    //zm_set_setting('isEnableZenMagick', false);

    // disable ZenMagick page caching
    //zm_set_setting('isPageCacheEnabled', false);

    // enable SEO for all pages
    //zm_set_setting('seoEnabledPagesList', null);

    // disable ZenMagick POST request handling
    //zm_set_setting('postRequestEnabledList', null);

    // set error logfile for ZenMagick log entries; 
    // null = leave it to PHP's error_log(..) function where to write to (usually the httpd error log)
    //zm_set_setting('zmLogFilename', $zm_runtime->getZMRootPath()."zenmagick.log");

    // set to true to log all PHP errors in custom file using the ZenMagick error handler
    //zm_set_setting('isZMErrorHandler', true);


    /*===============================================================================
     * The following settings are to disable automatic patching of zen-cart files.
     *===============================================================================*/

    // disable all patching
    //zm_set_setting('isEnablePatching', false);

    // disable admin menu patch
    //zm_set_setting('isAdminAutoRebuild', false);

    // disable patching index.php for ZenMagick theme support
    //zm_set_setting('isAdminPatchThemeSupport', false);

    // disable creation of dummy sidebox files
    //zm_set_setting('isAutoCreateZCSideboxes', false);

    // disable creation of dummy zen-cart template files for ZenMagick themes
    //zm_set_setting('isAutoCreateZCThemeDummies', false);

    // disable .htaccess Rewritebase patching
    //zm_set_setting('isPatchRewriteBase', false);

    // disable global zen-cart event handling in ZenMagick
    //zm_set_setting('isEventProxySupport', false);

    // disable dynamic admin menu support
    //zm_set_setting('isDynamicAdminMenuPatchSupport', false);

?>
