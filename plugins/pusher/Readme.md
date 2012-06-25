This is a ZenMagick plugin adding Pusher support
================================================
See http://pusher.com/ for more details.


Installation
------------
1) Unzip this plugin into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure as required.


Usage
-----
The default setup will listen to the test_channel as used by the Pusher API access page. The test event is my_event.

To listen to multiple events, configure a comma separated list of event names.

The default activity handler will just manage a unordered list and insert new items at the top with the list element content being the activity data.


TODO
----
- allow custom activity handler
