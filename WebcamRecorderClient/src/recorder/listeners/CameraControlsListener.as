package recorder.listeners
{
	import flash.events.MouseEvent;
	import flash.events.NetStatusEvent;
	import flash.events.TimerEvent;
	import flash.external.ExternalInterface;
	import flash.media.Camera;
	import flash.media.H264Level;
	import flash.media.H264Profile;
	import flash.media.H264VideoStreamSettings;
	import flash.media.Microphone;
	import flash.net.NetConnection;
	import flash.net.NetStream;
	import flash.net.Responder;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.net.URLVariables;
	import flash.net.navigateToURL;
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
		private var startStopRecordingResponder:Responder;
		private var camera:Camera;
		private var microphone:Microphone;
		private var cameraNetStream:NetStream;
		private var audioNetStream:NetStream;
		private var previewNetStream:NetStream;
		private var streamName:String;
		private var flushTimer:Timer;
		private var recording:Boolean;
		private var streamNameResponder:Responder;
		private var doneButtonResponder:Responder;
		private var recordButton:SpriteButton;
		private var recordingTimer:Timer;
		private var recordingStartTime:Number;
		private var cameraControlsPanel:CameraControlsPanel;
		private var realFileName:String;
		private var doneRecording:Boolean;
		private var deleteTimer:Timer;
		private const DELETE_TIMER_DELAY:Number = 5 * 60 * 1000; //5 minutes
		
		public function CameraControlsListener(nc:NetConnection, cPanel:CameraControlsPanel)
		{
			cameraControlsPanel = cPanel;
			netConnection = nc;
			startStopRecordingResponder = new Responder(startStopRecordingSuccess, startStopRecordingFailure);
			streamNameResponder = new Responder(streamNameResult, streamNameStatus);
			doneButtonResponder = new Responder(doneButtonSuccess, doneButtonFailure);
			recording = false;
			previewing = false;
			doneRecording = true;
		}
		
		public function doneButtonSuccess(obj:Object):void
		{
			CameraMicSource.getInstance().destroyCameraStream();
			CameraMicSource.getInstance().destroyCamera();
			
			
			postData(realFileName.substr(0, realFileName.lastIndexOf("."))+".mp4");
			
			streamName = null;
			realFileName = null;
		}
		
		public function doneButtonFailure(obj:Object):void
		{
			refreshPage();
		}
		
		//FIXME NEED TO GET STREAMS FROM CAMERAMICSOURCE CLASS
		public function record(event:RecordingEvent=null):void
		{
			stopDeleteTimer();
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
			netConnection.call("generateStream", streamNameResponder);
			cameraControlsPanel.previewButton.enabled = false;
			cameraControlsPanel.doneButton.enabled = false;
		}
		
		private function onRecStreamStatus(event:NetStatusEvent):void
		{
			trace (event.info.code);
			if ( event.info.code == "NetStream.Publish.Start" )
			{
				recording = true;
				netConnection.call("record", startStopRecordingResponder, streamName);
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
		
		private function stopDeleteTimer():void
		{
			if (deleteTimer!=null)
			{
				deleteTimer.stop();
				deleteTimer.removeEventListener(TimerEvent.TIMER, refreshPage);
				deleteTimer = null;	
			}
		}
		
		private function startDeleteTimer():void
		{
			stopDeleteTimer();
			netConnection.call("resetDeleteTimer", null, streamName);
			deleteTimer = new Timer(DELETE_TIMER_DELAY, 1);
			deleteTimer.addEventListener(TimerEvent.TIMER, refreshPage);
			deleteTimer.start();
		}
		private function bufferChecker(event:TimerEvent):void
		{
			if (cameraNetStream.bufferLength == 0)
			{
				trace("Buffer cleared");
				flushTimer.stop();
				flushTimer = null;
				recording = false;
				netConnection.call("stopRecording", startStopRecordingResponder, streamName);
				cameraNetStream.close();
				cameraNetStream = null;
				//See startStopRecordingSuccess for enabling buttons etc.
				
//				(CameraControlsPanel)(event.target).setRecordingButtonEnabled(true);
				startDeleteTimer();
			}
			else
			{
				trace("Remaining buffer:"+ cameraNetStream.bufferLength);
			}
		}
		
		private function streamNameResult(obj:Object):void
		{
			//FIXME gives the same name except for the last part which is generated on the server :(
			streamName = obj.toString();
			
			cameraNetStream.publish(streamName, "live");
			//	WebcamRecorderClient.appendMessage("Recording started to file: "+obj.toString()+".flv");
//			}
			trace("Result is:"+obj);
		}
		
		private function streamNameStatus(obj:Object):void
		{
			for (var i:Object in obj)
			{
				trace("Status: " + i + " : "+obj[i]);
			}
		}
		
		private function startStopRecordingSuccess(obj:Object):void
		{
			if (recording)
			{
				
				doneRecording = false;
				WebcamRecorderClient.appendMessage("Recording started");
				
			}
			else
			{
				//recording stopped - returns filename of recording
				realFileName = obj.toString();
				trace("Recording stopped");
				//Enable the buttons
				WebcamRecorderClient.appendMessage("Uploading finished.");
				recordButton.enabled  = true;
				cameraControlsPanel.previewButton.enabled = true;
				cameraControlsPanel.doneButton.enabled = true;
			}
			trace("Start/StopRecording Success:"+obj);
		}
		
		private function startStopRecordingFailure(obj:Object):void
		{
			WebcamRecorderClient.appendMessage("Recording failed!");
			for (var i:Object in obj)
			{
				WebcamRecorderClient.appendMessage("Status: " + i + " : "+obj[i]);
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
			startDeleteTimer();
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
			stopDeleteTimer();
			doneRecording = true;
			cameraControlsPanel.previewButton.enabled = false;
			cameraControlsPanel.doneButton.enabled = false;
			cameraControlsPanel.recordButton.enabled = true;
			
			//FIXME Call a php function and reload the page
			netConnection.call("saveFile", doneButtonResponder, streamName);
			
		}
		
		public function refreshPage(event:TimerEvent=null):void
		{
			var url:String = ExternalInterface.call('window.location.href.toString'); 
			var request:URLRequest = new URLRequest(url);
			navigateToURL(request, "_self");
		}
		
		
		public function postData(fName:String):void 
		{
			var url:String = ExternalInterface.call('window.location.href.toString'); 
			var request:URLRequest = new URLRequest(url);
			
			var variables:URLVariables = new URLVariables();
			variables.vidfile = fName;
			request.data = variables;
			request.method = URLRequestMethod.POST;
			
			navigateToURL(request, "_self");
		}

		
		private function metaDataHandler(infoObject:Object):void
		{
			trace("metadata"+ infoObject.duration);
			cameraControlsPanel.maxTime = infoObject.duration*1000;
		}
	}
}