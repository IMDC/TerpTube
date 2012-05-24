package listeners
{
	import events.CameraReadyEvent;
	import events.MicrophoneReadyEvent;
	import events.RecordingEvent;
	
	import flash.media.Camera;
	import flash.media.Microphone;
	import flash.net.NetConnection;
	import flash.net.NetStream;
	import flash.net.Responder;

	public class CameraControlsListener 
	{
		private var netConnection:NetConnection;
		private var responder:Responder;
		private var camera:Camera;
		private var microphone:Microphone;
		private var netStream:NetStream;
		
		public function CameraControlsListener(nc:NetConnection)
		{
			netConnection = nc;
			responder = new Responder(result, status);
			netStream = new NetStream(netConnection);
		}
		
		public function record(event:RecordingEvent):void
		{
			netConnection.call("record", responder, event.fileName);
			netStream.publish(event.fileName, "record");
		}
		
		public function stopRecording(event:RecordingEvent):void
		{
			netConnection.call("stopRecording", responder, event.fileName);
		}
		
		private function result(obj:Object):void
		{
			trace("Result is:"+obj);
		}
		
		private function status(obj:Object):void
		{
			for (var i:Object in obj)
			{
				trace("Status: " + i + " : "+obj[i]);
			}
		}
		
		public function cameraReady(event:CameraReadyEvent):void
		{
			camera = event.camera;
			netStream.attachCamera(camera);
		}
		
		public function microphoneReady(event:MicrophoneReadyEvent):void
		{
			microphone = event.microphone;
			netStream.attachAudio(microphone);
		}
		
	}
}