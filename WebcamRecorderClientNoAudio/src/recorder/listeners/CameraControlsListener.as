package recorder.listeners
{
	import flash.events.MouseEvent;
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
	
	import mx.controls.Button;
	
	import recorder.events.ButtonEvent;
	import recorder.events.CameraReadyEvent;
	import recorder.events.MicrophoneReadyEvent;
	import recorder.events.RecordingEvent;
	import recorder.gui.CameraControlsPanel;
	import recorder.gui.CameraViewer;
	import recorder.gui.SpriteButton;
	import recorder.model.CameraMicSource;

	public class CameraControlsListener 
	{
		public static const MAX_RECORDING_TIME:Number = 60000; //Maximum recording time in milliseconds
		
		private var previewing:Boolean;
		private var netConnection:NetConnection;
		private var responder:Responder;
		private var camera:Camera;
		private var microphone:Microphone;
		private var cameraNetStream:NetStream;
		private var audioNetStream:NetStream;
		private var previewNetStream:NetStream;
		private var fileName:String;
		private var flushTimer:Timer;
		private var recording:Boolean;
		private var fileNameResponder:Responder;
		private var recordButton:SpriteButton;
		private var recordingTimer:Timer;
		private var recordingStartTime:Number;
		private var cameraControlsPanel:CameraControlsPanel;
		private var realFileName:String;
		private var doneRecording:Boolean;
		
		public function CameraControlsListener(nc:NetConnection, cPanel:CameraControlsPanel)
		{
			cameraControlsPanel = cPanel;
			netConnection = nc;
			responder = new Responder(result, status);
			fileNameResponder = new Responder(fileNameResult, fileNameStatus);
			recording = false;
			previewing = false;
			doneRecording = true;
		}
		
		//FIXME NEED TO GET STREAMS FROM CAMERAMICSOURCE CLASS
		public function record(event:RecordingEvent=null):void
		{
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
			cameraControlsPanel.maxTime = MAX_RECORDING_TIME;
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
			cameraControlsPanel.previewButton.enabled = false;
			cameraControlsPanel.doneButton.enabled = false;
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
			var currentTime:Number;
			if (recording)
			{
				currentTime= new Date().time - recordingStartTime;
			}
			else
				currentTime = previewNetStream.time*1000;
			cameraControlsPanel.setTime(currentTime);
			if (recording && currentTime >= MAX_RECORDING_TIME)
			{
				recordButton.toggleButton();
			}
		}
		
		public function toggleRecording(event:ButtonEvent):void
		{
			recordButton = (SpriteButton)(event.target);
			if (SpriteButton(event.target).state==SpriteButton.DOWN_STATE)
				record();
			else
				stopRecording();
		}
		public function stopRecording(event:RecordingEvent=null):void
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
				cameraControlsPanel.previewButton.enabled = true;
				cameraControlsPanel.doneButton.enabled = true;
//				(CameraControlsPanel)(event.target).setRecordingButtonEnabled(true);
			
			}
			else
			{
				trace("Remaining buffer:"+ cameraNetStream.bufferLength);
			}
		}
		
		private function fileNameResult(obj:Object):void
		{
			//FIXME gives the same name except for the last part which is generated on the server :(
			if (doneRecording && fileName==null)
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
				realFileName = obj.toString() + ".flv";
				doneRecording = false;
//				obj.toString();
				WebcamRecorderClient.appendMessage("Recording started to file: "+realFileName);
				
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
		
		public function previewButtonHandler(event:ButtonEvent):void
		{
			
			if (cameraControlsPanel.previewButton.state == SpriteButton.UP_STATE) //was previewing
			{
				if (recordingTimer!=null)
				{
					recordingTimer.stop();
					recordingTimer = null;
				}
				recordButton.enabled = true;
				cameraControlsPanel.doneButton.enabled = true;
				cameraControlsPanel.maxTime = MAX_RECORDING_TIME;
				previewNetStream.pause();
				previewNetStream = null;
				CameraViewer.getInstance().showCameraPreview();
				cameraControlsPanel.setTime(0);
			}
			else
			{
				cameraControlsPanel.maxTime = 0;
				recordingStartTime = new Date().time;
				recordingTimer = new Timer(200, 0);
				recordingTimer.addEventListener(TimerEvent.TIMER, updateTime);
				recordingTimer.start();
				recordButton.enabled = false;
				cameraControlsPanel.doneButton.enabled = false;
				
				previewNetStream = new NetStream(netConnection);
				previewNetStream.addEventListener(NetStatusEvent.NET_STATUS, previewStatus);
				var customClient:Object = new Object();
				customClient.onMetaData=metaDataHandler;
				
				previewNetStream.client=customClient;
				CameraViewer.getInstance().showRemoteRecordingPreview(previewNetStream);
				
				trace("Attempting to play " +realFileName);
				previewNetStream.play(realFileName);
			}
			
			previewing = !previewing;
		}
		
		private function previewStatus(event:NetStatusEvent):void
		{
			trace(event.info.code);
			switch (event.info.code)
			{
				case "NetStream.Play.Stop":
					recordingTimer.stop();
					recordingTimer = null;
					
					cameraControlsPanel.setTime(cameraControlsPanel.maxTime);
					cameraControlsPanel.previewButton.toggleButton();
					break;
			}
		}
		
		public function doneButtonHandler(event:ButtonEvent):void
		{
			doneRecording = true;
			cameraControlsPanel.previewButton.enabled = false;
			cameraControlsPanel.doneButton.enabled = false;
			cameraControlsPanel.recordButton.enabled = true;
			
			//FIXME Call a php function and reload the page
			fileName = null;
			realFileName = null;
		}
		
		private function metaDataHandler(infoObject:Object):void
		{
			trace("metadata"+ infoObject.duration);
			cameraControlsPanel.maxTime = infoObject.duration*1000;
		}
	}
}