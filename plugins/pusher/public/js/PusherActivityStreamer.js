function PusherActivityStreamer(channel, container, options) {
  options = options || {};

  // defaults
  this.settings = {
    events: [],
    maxItems: 10,
    defaultHandler: PusherActivityStreamer.stringActivityHandler
  };
  for (key in options) {
    this.settings[key] = options[key];
  }
  
  this.channel = channel;
  this.container = container;
  
  for (var ii in this.settings.events) {
    var type = this.settings.events[ii];
    var handler = this.settings.defaultHandler;

    // wrap in closure to make type stick
    (function bindType(channel, type, handler, self) {
      channel.bind(type, function (activity) {
        handler.call(self, activity, type);
      });
    })(channel, type, handler, this);
  }
  this.count = 0;
};

PusherActivityStreamer.stringActivityHandler = function(activity, type) {
  ++this.count;
  var li = document.createElement('li');
  li.innerHTML = activity;
  this.container.insertBefore(li, this.container.firstChild);
  if (this.count > this.settings.maxItems) {
    this.container.removeChild(this.container.lastChild);
  }
}
