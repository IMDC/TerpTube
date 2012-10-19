 flowplayer("player", "flowplayer.swf", { // supply the configuration
      clip: { // Clip is an object, hence '{...}'
          autoPlay: false,
          autoBuffering: true,
          baseUrl: 'http://stream.flowplayer.org/'
      },
      plugins: { // load one or more plugins
          controls: { // load the controls plugin
 
              // always: where to find the Flash object
              url: 'http://releases.flowplayer.org/swf/flowplayer.controls-tube-3.2.11.swf',
 
              // now the custom options of the Flash object
              playlist: true,
              backgroundColor: '#aedaff',
              tooltips: { // this plugin object exposes a 'tooltips' object
                  buttons: true,
                  fullscreen: 'Enter Fullscreen mode'
              }
          }
      },
 
      // set an event handler in the configuration
      onFinish: function() {
          alert("Click Player to start video again");
      }
  });