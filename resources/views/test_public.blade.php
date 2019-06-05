<!DOCTYPE html>
<head>
  <title>Pusher Test</title>
  <script src="https://js.pusher.com/4.4/pusher.min.js"></script>
  <script>

    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('0c90dea9181e27899209', {
      cluster: 'ap1',
      forceTLS: true
    });

    var channel = pusher.subscribe('parkit-main');
    channel.bind('App\\Events\\UserLogin', function(data) {
      alert(JSON.stringify(data));
      alert('event fired');
    });
  </script>
</head>
<body>
  <h1>Pusher Test</h1>
  <p>
    Try publishing an event to channel <code>my-channel</code>
    with event name <code>my-event</code>.
  </p>
</body>