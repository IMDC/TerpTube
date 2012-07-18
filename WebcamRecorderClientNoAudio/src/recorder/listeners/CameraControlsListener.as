package recorder.listeners
{
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
	
	import recorder.events.CameraReadyEvent;
	import recorder.events.MicrophoneReadyEvent;
	import recorder.events.RecordingEvent;
	import recorder.gui.CameraControlsPanel;
	import recorder.gui.RecordButton;
	import recorder.model.CameraMicSource;

	public class CameraControlsListener 
	{
		public static const MAX_RECORDING_TIME:Number = 60000; //Maximum recording time in milliseconds
		
		
		private var netConnection:NetConnection;
		private var responder:Responder;
		private var camera:Camera;
		private var microphone:Microphone;
		private var cameraNetStream:NetStream;
		private var audioNetStream:NetStream;
		private var fileName:String;
		private var flushTimer:Timer;
		private var recording:Boolean;
		private var fileNameResponder:Responder;
		private var recordButton:RecordButton;
		private var recordingTimer:Timer;
		private var recordingStartTime:Number;
		private var cameraControlsPanel:CameraControlsPanel;
		
		public function CameraControlsListener(nc:NetConnection, cPanel:CameraControlsPanel)
		{
			cameraControlsPanel = cPanel;
			netConnection = nc;
			responder = new Responder(result, status);
			fileNameResponder = new Responder(fileNameResult, fileNameStatus);
			recording = false;
		}
		
		//FIXME NEED TO GET STREAMS FROM CAMERAMICSOURCE CLASS
		public function record(event:RecordingEvent):void
		{
			recordButton = (RecordButton)(event.target);
			//FIXME call the server function to get a client stream
			if (cameraNetStream==null)
			{
				cameraNetStream = CameraMicSource.getInstance().getCameraStream(netConnection);
//				if (camera!=null)
//				{
//					netStream.attachCamera(camera);
//				}
//				if (microphone!=null)
//				{
//					trace("Microphone ready");
//					netStream.attachAudio(microphone);
//				}
			}
//			netStream.bufferTime = 60;
			var h264Settings:H264VideoStreamSettings = new H264VideoStreamSettings();
			h264Settings.setProfileLevel(H264Profile.MAIN, H264Level.LEVEL_3);
			h264Settings.setQuality(0, 100);
			cameraNetStream.videoStreamSettings = h264Settings;
//			netStream.videoReliable = true;
//			fileName = event.fileName;
			cameraNetStream.addEventListener(NetStatusEvent.NET_STATUS, onRecStreamStatus);
//			if (audioNetStream == null)
//			{
//				audioNetStream = CameraMicSource.getInstance().getAudioStream(netConnection);				
//			}
			netConnection.call("generateStream", fileNameResponder);
		}
		
		private function onRecStreamStatus(event:NetStatusEvent):void
		{
			trace (event.info.code);
			if ( event.info.code == "NetStream.Publish.Start" )
			{
				recording = true;
				netConnection.call("record", responder, fileName);
				recordingStartTime = new Date().time;
				recordingTimer = new Timer(200, 0);
				recordingTimer.addEventListener(TimerEvent.TIMER, updateTime);
				recordingTimer.start();
			}
			
		}
		
		private function updateTime(event:TimerEvent):void
		{
			var currentTime:Number = new Date().time - recordingStartTime;
			cameraControlsPanel.setTime(currentTime);
			if (currentTime >= MAX_RECORDING_TIME)
			{
				recordButton.toggleRecording();
			}
		}
		
		public function stopRecording(event:RecordingEvent):void
		{
			//recordButton = (RecordButton)(event.target);
			recordButton.enabled = false;
			recordingTimer.stop();
			recordingTimer = null;
			flushTimer = new Timer(100, 0);
			flushTimer.addEventListener(TimerEvent.TIMER, bufferChecker);
			flushTimer.start();
			cameraNetStream.pause();
			cameraNetStream.attachCamera(null);
			cameraNetStream.attachAudio(null);
			WebcamRecorderClient.appendMessage("Recording stopped. Video is uploading...");
		}
		
		private function bufferChecker(event:TimerEvent):void
		{
			if (cameraNetStream.bufferLength == 0)
			{
				trace("Buffer cleared");
				flushTimer.stop();
				flushTimer = null;
				recording = false;
				netConnection.call("stopRecording", responder, fileName);
				cameraNetStream.close();
				cameraNetStream = null;
				trace("Recording stopped");
				WebcamRecorderClient.appendMessage("Uploading finished.");
				recordButton.enabled  = true;
//				(CameraControlsPanel)(event.target).setRecordingButtonEnabled(true);
			
			}
			else
			{
				trace("Remaining buffer:"+ cameraNetStream.bufferLength);
			}
		}
		
		private function fileNameResult(obj:Object):void
		{
			fileName = obj.toString();
			cameraNetStream.publish(fileName, "live");
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
			if (cameraNetStream==null)
			{
				cameraNetStream = new NetStream(netConnection);
			}
			trace("Camera ready");
			camera = event.camera;
			cameraNetStream.attachCamera(camera);
		}
		
		public function microphoneReady(event:MicrophoneReadyEvent):void
		{
			if (audioNetStream==null)
			{
				audioNetStream = new NetStream(netConnection);
			}
			trace("Microphone ready");
			microphone = event.microphone;
			audioNetStream.attachAudio(microphone);
		}
		
	}
}