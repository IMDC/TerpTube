package listeners
{
	import events.CameraReadyEvent;
	import events.MicrophoneReadyEvent;
	import events.RecordingEvent;
	
	import flash.events.NetStatusEvent;
	import flash.events.TimerEvent;
	import flash.media.Camera;
	import flash.media.H264Level;
	import flash.media.H264Profile;
	import flash.media.H264VideoStreamSettings;
	import flash.media.Microphone;
	import flash.net.NetConnection;
	import flash.net.NetStream;
	import flash.net.Responder;
	import flash.utils.Timer;

	public class CameraControlsListener 
	{
		private var netConnection:NetConnection;
		private var responder:Responder;
		private var camera:Camera;
		private var microphone:Microphone;
		private var netStream:NetStream;
		private var fileName:String;
		private var flushTimer:Timer;
		
		public function CameraControlsListener(nc:NetConnection)
		{
			netConnection = nc;
			responder = new Responder(result, status);
		}
		
		public function record(event:RecordingEvent):void
		{
			if (netStream==null)
			{
				netStream = new NetStream(netConnection);
				if (camera!=null)
				{
					netStream.attachCamera(camera);
				}
				if (microphone!=null)
				{
					trace("Microphone ready");
					netStream.attachAudio(microphone);
				}
			}
			netStream.bufferTime = 60;
			var h264Settings:H264VideoStreamSettings = new H264VideoStreamSettings();
			h264Settings.setProfileLevel(H264Profile.MAIN, H264Level.LEVEL_3);
			netStream.videoStreamSettings = h264Settings;
			fileName = event.fileName;
			netStream.addEventListener(NetStatusEvent.NET_STATUS, onRecStreamStatus);
			netStream.publish(event.fileName, "live");
		}
		
		private function onRecStreamStatus(event:NetStatusEvent):void
		{
			trace (event.info.code);
			if ( event.info.code == "NetStream.Publish.Start" )
			{
				netConnection.call("record", responder, fileName);
			}
			
		}
		public function stopRecording(event:RecordingEvent):void
		{
			netStream.pause();
			netStream.attachCamera(null);
			netStream.attachAudio(null);
			flushTimer = new Timer(100, 0);
			flushTimer.addEventListener(TimerEvent.TIMER, bufferChecker);
			flushTimer.start();
		}
		
		private function bufferChecker(event:TimerEvent):void
		{
			if (netStream.bufferLength == 0)
			{
				trace("Buffer cleared");
				flushTimer.stop();
				flushTimer = null;
				netConnection.call("stopRecording", responder, fileName);
				netStream.close();
				netStream = null;
				trace("Recording stopped");
			}
			else
			{
				trace("Remaining buffer:"+ netStream.bufferLength);
			}
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
			if (netStream==null)
			{
				netStream = new NetStream(netConnection);
			}
			trace("Camera ready");
			camera = event.camera;
			netStream.attachCamera(camera);
		}
		
		public function microphoneReady(event:MicrophoneReadyEvent):void
		{
			if (netStream==null)
			{
				netStream = new NetStream(netConnection);
			}
			trace("Microphone ready");
			microphone = event.microphone;
			netStream.attachAudio(microphone);
		}
		
	}
}