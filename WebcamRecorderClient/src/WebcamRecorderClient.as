package
{
	import flash.display.Sprite;
	import flash.display.StageAlign;
	import flash.events.NetStatusEvent;
	import flash.net.NetConnection;
	import flash.net.ObjectEncoding;
	import flash.text.TextField;
	
	import gui.CameraControlsPanel;
	import gui.CameraViewer;
	
	import listeners.CameraControlsListener;

	[SWF(backgroundColor="0xcc99cc")]
	[SWF(width=640)]
	[SWF(height=480)]

	public class WebcamRecorderClient extends Sprite
	{
		private var netConnection:NetConnection;
		
		public function WebcamRecorderClient()
		{
		//	width = 800;
		//	height = 600;
			this.stage.align = StageAlign.TOP_LEFT;
			graphics.beginFill( 0xaaccff, 1.0 );
			graphics.drawRect( 0, 0, 340, 280 );
			graphics.endFill();
			trace("Creating a new instance");
			//new NetConnection
			netConnection = new NetConnection();
			
			//set encoding to old amf;
			netConnection.objectEncoding = ObjectEncoding.AMF0;
			
			//netstatus event listening
			netConnection.addEventListener(NetStatusEvent.NET_STATUS, netStatus);
			
			//connect to red5, passing false as parameter
			netConnection.connect("rtmp://imdc.ca/webcamRecorder", true);
			
			//close the connection
			netConnection.close();
			
			var cameraViewer:CameraViewer = new CameraViewer();
			cameraViewer.x = 10;
			cameraViewer.y = 10;
			addChild(cameraViewer);
			var cameraControlsPanel:CameraControlsPanel = new CameraControlsPanel();
			cameraControlsPanel.x = 10;
			cameraControlsPanel.y = 260;
			addChild(cameraControlsPanel);
		}
		
		private function netStatus(event:NetStatusEvent):void
		{
			trace (event.info.code);
			if (event.info.code=="NetConnection.Connect.Rejected")
			{
				//trace reject message
				trace(event.info.application);
			}
		}
	}
}