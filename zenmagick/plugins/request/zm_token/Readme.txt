Secure token service plugin
===========================

This plugin adds a new service to ZenMagick that allows to manage secure token.
Possible use cases might be:
* auto login
  Store a managed hash in the auto login cookie rather than the encoded password
* newsletter
  - Make unsubscribe subject to a valid hash being passed back in the URL. That way 
    it would no not be possible to unsubscribe random email addresses.
  - Implement a proper opt-in with an email containing a confirmation URL that will
    perform the actual subscribe (for anonymous subscriptions)


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.


This plugin doesn't do anything itself, but provides a service for other plugins or core logic.
If used, the code should be aware of the fact that the service might not be available (ie. not installed) and handle this gracefully.
