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
	
	import gui.CameraControlsPanel;
	import gui.RecordButton;

	public class CameraControlsListener 
	{
		private var netConnection:NetConnection;
		private var responder:Responder;
		private var camera:Camera;
		private var microphone:Microphone;
		private var netStream:NetStream;
		private var fileName:String;
		private var flushTimer:Timer;
		private var recording:Boolean;
		private var fileNameResponder:Responder;
		private var recordButton:RecordButton;
		
		public function CameraControlsListener(nc:NetConnection)
		{
			netConnection = nc;
			responder = new Responder(result, status);
			fileNameResponder = new Responder(fileNameResult, fileNameStatus);
			recording = false;
		}
		
		public function record(event:RecordingEvent):void
		{
			//FIXME call the server function to get a client stream
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
//			fileName = event.fileName;
			netStream.addEventListener(NetStatusEvent.NET_STATUS, onRecStreamStatus);
			netConnection.call("generateStream", fileNameResponder);
		}
		
		private function onRecStreamStatus(event:NetStatusEvent):void
		{
			trace (event.info.code);
			if ( event.info.code == "NetStream.Publish.Start" )
			{
				recording = true;
				netConnection.call("record", responder, fileName);
			}
			
		}
		
		public function stopRecording(event:RecordingEvent):void
		{
			recordButton = (RecordButton)(event.target);
				recordButton.enabled = false;
			flushTimer = new Timer(100, 0);
			flushTimer.addEventListener(TimerEvent.TIMER, bufferChecker);
			flushTimer.start();
			netStream.pause();
			netStream.attachCamera(null);
			netStream.attachAudio(null);
			WebcamRecorderClient.appendMessage("Recording stopped. Video is uploading...");
		}
		
		private function bufferChecker(event:TimerEvent):void
		{
			if (netStream.bufferLength == 0)
			{
				trace("Buffer cleared");
				flushTimer.stop();
				flushTimer = null;
				recording = false;
				netConnection.call("stopRecording", responder, fileName);
				netStream.close();
				netStream = null;
				trace("Recording stopped");
				WebcamRecorderClient.appendMessage("Uploading finished.");
				recordButton.enabled  = true;
//				(CameraControlsPanel)(event.target).setRecordingButtonEnabled(true);
			
			}
			else
			{
				trace("Remaining buffer:"+ netStream.bufferLength);
			}
		}
		
		private function fileNameResult(obj:Object):void
		{
			fileName = obj.toString();
			netStream.publish(fileName, "live");
			//	WebcamRecorderClient.appendMessage("Recording started to file: "+obj.toString()+".flv");
//			}
			trace("Result is:"+obj);
		}
		
		private function fileNameStatus(obj:Object):void
		{
			for (var i:Object in obj)
			{
				trace("Status: " + i + " : "+obj[i]);
			}
		}
		
		private function result(obj:Object):void
		{
			if (recording)
			{
				obj.toString();
				WebcamRecorderClient.appendMessage("Recording started to file: "+obj.toString()+".flv");
			}
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