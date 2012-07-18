package 
{
	import flash.display.Sprite;
	import flash.display.StageAlign;
	import flash.events.NetStatusEvent;
	import flash.net.NetConnection;
	import flash.net.ObjectEncoding;
	import flash.text.TextField;
	
	import recorder.gui.CameraControlsPanel;
	import recorder.gui.CameraViewer;
	import recorder.listeners.CameraControlsListener;
	import recorder.model.CameraMicSource;

	[SWF(backgroundColor="0xcc99cc")]
	[SWF(width=640)]
	[SWF(height=480)]

	public class WebcamRecorderClient extends Sprite
	{
		private var _netConnection:NetConnection;
		private var _cameraControlsListener:CameraControlsListener 
		private var debug:Boolean = true;
		private static var _textField:TextField;
		
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
			netConnection.client = this;
			
			//set encoding to old amf;
			netConnection.objectEncoding = ObjectEncoding.AMF0;
			
			//netstatus event listening
			netConnection.addEventListener(NetStatusEvent.NET_STATUS, netStatus);
			
			//connect to red5
			netConnection.connect("rtmp://imdc.ca/webcamRecorderNoAudio/", true);
			
			//close the connection
//			netConnection.close();
			
			
			var cameraViewer:CameraViewer = new CameraViewer();
			//CameraMicSource.getInstance().addEventListener(CameraMicSource.MICROPHONE_READY_STRING, cameraControlsListener.microphoneReady);
			cameraViewer.x = 10;
			cameraViewer.y = 10;
			addChild(cameraViewer);
			
			//FIXME need to add eventListeners for the CAMERA and MICROPHONE events
			var cameraControlsPanel:CameraControlsPanel = new CameraControlsPanel();
			cameraControlsPanel.x = 10;
			cameraControlsPanel.y = 255;
			addChild(cameraControlsPanel);
//			cameraControlsPanel.fileName = "testRecording";
			cameraControlsListener = new CameraControlsListener(netConnection, cameraControlsPanel);
			CameraMicSource.getInstance().addEventListener(CameraMicSource.CAMERA_READY_STRING,cameraControlsListener.cameraReady);
			
			cameraControlsPanel.addControlsListener(cameraControlsListener);
			
			
			textField = new TextField();
			textField.x = 340;
			textField.y = 10;
			textField.width = 200;
			textField.height = 240;
			textField.wordWrap = true;
			textField.border = true;
			textField.borderColor = 0x0011ff;
			if (!debug)
				textField.visible = false;
			addChild(textField);
			
		}
		
		public static function appendMessage(message:String):void
		{
			textField.appendText(message+"\n");
		}
		
		private function netStatus(event:NetStatusEvent):void
		{
			trace (event.info.code);
			if (event.info.code=="NetConnection.Connect.Failed")
			{
				//trace reject message
				trace(event.info.application);
				appendMessage("Connected Failed. Reason:" +event.info.application);
				return;
			}
			if (event.info.code=="NetConnection.Connect.Rejected")
			{
				//trace reject message
				trace(event.info.application);
				appendMessage("Connection Rejected. Reason:" +event.info.application);
				return;
			}
			if (event.info.code=="NetConnection.Connect.Success")
			{
				trace("Sucessfully connected");
				appendMessage("Connected to server");
			}
		}

		public function get netConnection():NetConnection
		{
			return _netConnection;
		}

		public function set netConnection(value:NetConnection):void
		{
			_netConnection = value;
		}
		
//		public function recordingStarted(clientID:String):void
//		{
//			trace("Recording started function in main")
//			trace("Client id:"+clientID);
//		}
//		
//		public function recordingStopped(clientID:String):void
//		{
//			trace("Recording stopped function in main")
//			trace("Client id:"+clientID);
//		}

		public function get cameraControlsListener():CameraControlsListener
		{
			return _cameraControlsListener;
		}

		public function set cameraControlsListener(value:CameraControlsListener):void
		{
			_cameraControlsListener = value;
		}

		public static function get textField():TextField
		{
			return _textField;
		}

		public static function set textField(value:TextField):void
		{
			_textField = value;
		}


	}
}