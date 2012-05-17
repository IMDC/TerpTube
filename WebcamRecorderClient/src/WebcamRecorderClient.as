package
{
	import flash.net.NetConnection;
	import flash.net.ObjectEncoding;
	import flash.events.NetStatusEvent;
	import flash.display.Sprite;
	import flash.text.TextField;
	
	public class WebcamRecorderClient extends Sprite
	{
		private var netConnection:NetConnection;
		
		public function WebcamRecorderClient()
		{
			trace("Creating a new instance");
			//new NetConnection
			netConnection = new NetConnection();
			
			//set encoding to old amf;
			netConnection.objectEncoding = ObjectEncoding.AMF0;
			
			//netstatus event listening
			netConnection.addEventListener(NetStatusEvent.NET_STATUS, netStatus);
			
			//connect to red5, passing false as parameter
			netConnection.connect("rtmp://imdc.ca/webcamRecorder", true);
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