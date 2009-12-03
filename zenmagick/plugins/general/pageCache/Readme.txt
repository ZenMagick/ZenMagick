This is a ZenMagick plugin adding page caching support.


Installation
============
1) Unzip this plugin into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure as required.
4) Enjoy


Supported settings
==================
* plugins.pageCache.stats [true|false]
  Default: true
  Enables / disables appending hidden stats (as HTML comments) (request/cache) to the response.

* plugins.pageCache.ttl [in seconds]
  Default: 300
  The time-to-live (in seconds) before a cache entry expires.

* plugins.pageCache.strategy.callback [function name]
  Default: zm_page_cache_default_strategy
  The value is taken as function name to determine whether the current request is cacheable or not.

* plugins.pageCache.strategy.allowed [array|comma separated string]
  Default: ZM_PLUGINS_PAGE_CACHE_ALLOWED_DEFAULT
  List of page names to be cached.


Miscellaneous
=============
The appended page stats have been stripped down to the minimum. The plugin creates a new event that is 
supported by the pageStats plugin. If installed, hidden page stats information will be added.

NOTE1: If pageStats is installed and configured to hide stats, two sets of stats will be in the returned
       content for cache hits - the first one is the cached information, the second the stats for the actual request.

NOTE2: As of 0.9.1 the behaviour of the caching strategy has changed from opt-out to opt-in.
