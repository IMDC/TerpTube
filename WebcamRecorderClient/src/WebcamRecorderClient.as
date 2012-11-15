package 
{
	import flash.display.LoaderInfo;
	import flash.display.Sprite;
	import flash.display.StageAlign;
	import flash.display.StageScaleMode;
	import flash.events.Event;
	import flash.events.NetStatusEvent;
	import flash.net.NetConnection;
	import flash.net.ObjectEncoding;
	import flash.text.TextField;
	import flash.ui.ContextMenu;
	
	import recorder.gui.CameraControlsPanel;
	import recorder.gui.CameraViewer;
	import recorder.listeners.CameraControlsListener;
	import recorder.model.CameraMicSource;
	
	import utils.BrowserUtils;

	//[SWF(backgroundColor="0xcc99cc")]
	[SWF(width=660)]
	[SWF(height=620)]

	/*
	 * NEEDS FLEX SDK 4.6.0 in order to work.
	 */
	
	public class WebcamRecorderClient extends Sprite
	{
		private var _netConnection:NetConnection;
		private var _cameraControlsListener:CameraControlsListener 
		private static var _textField:TextField;
		private static var _configurationVariables:Array;
		
		public function WebcamRecorderClient()
		{
//			width = 486;
//			height = 390;
			configurationVariables = new Array();
			configurationVariables["width"] = 660;
			configurationVariables["height"] = 620;
			configurationVariables["debug"] = false;
			configurationVariables["backgroundColor"] = 0xDDDDDD;
			configurationVariables["contentPadding"] = 10;
			configurationVariables["videoWidth"] = 640;
			configurationVariables["videoHeight"] = 480;
			configurationVariables["sliderBackgroundColor"] = 0xCCCCCC;
			configurationVariables["sliderHighlightedColor"] = 0x666666;
			configurationVariables["buttonsBackgroundColor"] = 0xFFFFFF;
			configurationVariables["postURL"] = null;
			configurationVariables["cancelURL"] = "javascript:history.go(-1)";
			configurationVariables["isAjax"] = false;
			configurationVariables["elementID"] = "playerContent";
			configurationVariables["blurFunction"] = "setBlur";
			configurationVariables["blurFunctionText"] = "setBlurText";
			
			configurationVariables["maxRecordingTime"] = 60000; //1 minute
			configurationVariables["minRecordingTime"] = 1000; //1 second
			
			this.loaderInfo.addEventListener(Event.COMPLETE, stageLoaded);//wait for this swf to be loaded and have flashVars ready
			//close the connection
//			netConnection.close();
		}
		
		public function handleResize(e:Event):void
		{
			    //The resize code goes here
			graphics.beginFill( configurationVariables["backgroundColor"], 1.0 );
			graphics.drawRect( 0, 0, configurationVariables["width"], configurationVariables["height"] );
			graphics.endFill();
		}
		
		public function stageLoaded(event:Event):void
		{
			initFlashVars();
			var my_menu:ContextMenu = new ContextMenu();
			my_menu.hideBuiltInItems();
			contextMenu = my_menu;
			this.stage.addEventListener(Event.RESIZE, handleResize);
			this.stage.align = StageAlign.TOP_LEFT;
//			this.stage.scaleMode = StageScaleMode.NO_SCALE;

			graphics.beginFill( configurationVariables["backgroundColor"], 1.0 );
			graphics.drawRect( 0, 0, configurationVariables["width"], configurationVariables["height"] );
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
			netConnection.connect("rtmp://imdc.ca/webcamRecorder/", true);
			
			var supportedVideoElement:String = BrowserUtils.getHTML5VideoSupport();
			//			trace("Name:"+o.appName+", Version:"+ o.version);
			trace("Video codec: "+supportedVideoElement);
			
			textField = new TextField();
			textField.x = configurationVariables["width"];
			textField.y = 0;
			textField.width = 200;
			textField.height = configurationVariables["height"];
			textField.wordWrap = true;
			textField.border = true;
			textField.borderColor = 0x0011ff;
			if (configurationVariables["debug"])
			{	
				textField.visible = true;
				addChild(textField);
			}
		}
		
		public function initFlashVars():void
		{
			var key:String; // This will contain the name of the parameter
			var val:String; // This will contain the value of the parameter
			var flashVars:Object = LoaderInfo(this.root.loaderInfo).parameters;
			var rExp:RegExp=new RegExp(/#/g);
			for (key in flashVars) 
			{
				if (key.indexOf("Color")!=-1)
				{
					//Convert HTML colors to Flash colors
					configurationVariables[key] = uint(String(flashVars[key]).replace(rExp,"0x"));
				}
				else
				{
					configurationVariables[key] = flashVars[key];
				}
			}
		}
		
		public function setup():void
		{
			var cameraViewer:CameraViewer = CameraViewer.getInstance();
			cameraViewer.x = configurationVariables["contentPadding"];
			cameraViewer.y = configurationVariables["contentPadding"];
			addChild(cameraViewer);
			
			var cameraControlsPanel:CameraControlsPanel = new CameraControlsPanel();
			cameraControlsPanel.x = configurationVariables["contentPadding"];
			cameraControlsPanel.y = configurationVariables["videoHeight"] +configurationVariables["contentPadding"]*2;
			cameraControlsPanel.maxTime = configurationVariables["maxRecordingTime"];
			addChild(cameraControlsPanel);
			cameraControlsListener = new CameraControlsListener(netConnection, cameraControlsPanel);
			CameraMicSource.getInstance().addEventListener(CameraMicSource.CAMERA_READY_STRING,cameraControlsListener.cameraReady);
			CameraMicSource.getInstance().addEventListener(CameraMicSource.MICROPHONE_READY_STRING,cameraControlsListener.microphoneReady);
			
			cameraControlsPanel.addControlsListener(cameraControlsListener);
			
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
				setup();
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

		public static function get configurationVariables():Array
		{
			return _configurationVariables;
		}

		public static function set configurationVariables(value:Array):void
		{
			_configurationVariables = value;
		}


	}
}