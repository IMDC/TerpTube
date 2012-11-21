package recorder.listeners
{
	import flash.events.Event;
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
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.net.URLVariables;
	import flash.net.getClassByAlias;
	import flash.net.navigateToURL;
	import flash.utils.Timer;
	
	import mx.controls.Button;
	
	import recorder.events.ButtonEvent;
	import recorder.events.CameraReadyEvent;
	import recorder.events.MicrophoneReadyEvent;
	import recorder.events.RecordingEvent;
	import recorder.gui.CameraViewer;
	import recorder.model.CameraMicSource;
	
	import utils.BrowserUtils;

	public class CameraControlsListener 
	{
//		public static const MAX_RECORDING_TIME:Number = 60000; //Maximum recording time in milliseconds
		
		private var previewing:Boolean;
		private var netConnection:NetConnection;
		private var startRecordingResponder:Responder;
		private var stopRecordingCameraResponder:Responder;
		private var stopRecordingAudioResponder:Responder;
		private var transcodeVideoResponder:Responder;
		private var camera:Camera;
		private var microphone:Microphone;
		private var cameraNetStream:NetStream;
		private var audioNetStream:NetStream;
		private var previewNetStream:NetStream;
		private var streamName:String;
		private var flushTimerCamera:Timer;
		private var flushTimerAudio:Timer;
		private var cameraRecording:Boolean;
		private var audioRecording:Boolean;
		private var streamNameResponder:Responder;
		private var doneButtonResponder:Responder;
		private var recordingTimer:Timer;
		private var recordingCameraStartTime:Number;
		private var recordingAudioStartTime:Number;
		private var realFileNameVideo:String;
		private var realFileNameAudio:String;
		private var resultingVideoFile:String;
		private var doneRecording:Boolean;
		private var deleteTimerVideo:Timer;
		private var deleteTimerAudio:Timer;
		private var deleteTimerCombined:Timer;
		private var urlLoader:URLLoader;
		private var totalBytesForUploading:Number;
		private const DELETE_TIMER_DELAY:Number = 5 * 60 * 1000; //5 minutes
		private const RECORDING_CAMERA:int = 1;
		private const RECORDING_AUDIO:int = 0;
		private const RECORDING_COMBINED:int = 2;
		private const SUFFIX_AUDIO:String = "_audio";
		private const SUFFIX_CAMERA:String = "_video";
		
		public function CameraControlsListener(nc:NetConnection)
		{
			netConnection = nc;
			startRecordingResponder = new Responder(startRecordingSuccess, startStopRecordingFailure);
			stopRecordingCameraResponder = new Responder(stopRecordingCameraSuccess, startStopRecordingFailure);
			stopRecordingAudioResponder = new Responder(stopRecordingAudioSuccess, startStopRecordingFailure);
			streamNameResponder = new Responder(streamNameResult, streamNameStatus);
			transcodeVideoResponder = new Responder(transcodeVideoSuccess, transcodeVideoFailure);
			doneButtonResponder = new Responder(doneButtonSuccess, doneButtonFailure);
			cameraRecording = false;
			previewing = false;
			doneRecording = true;
			//Add callback functions for javascript
			registerCallbacks();
		}
		
		public function registerCallbacks():void
		{
			ExternalInterface.addCallback("startRecording", record);
			ExternalInterface.addCallback("stopRecording", stopRecording);
			ExternalInterface.addCallback("startTranscoding", transcode);
		}
		
		public function doneButtonSuccess(obj:Object):void
		{
			CameraMicSource.getInstance().destroyCameraStream();
			CameraMicSource.getInstance().destroyCamera();
			
			
			postData(realFileNameVideo.substr(0, realFileNameVideo.lastIndexOf("."))+".mp4");
			
			streamName = null;
			realFileNameVideo = null;
		}
		
		public function doneButtonFailure(obj:Object):void
		{
			refreshPage();
		}
		
		//FIXME NEED TO GET STREAMS FROM CAMERAMICSOURCE CLASS
		public function record(event:RecordingEvent=null):void
		{
			stopDeleteTimer(RECORDING_AUDIO);
			stopDeleteTimer(RECORDING_CAMERA);
			stopDeleteTimer(RECORDING_COMBINED);
			//FIXME call the server function to get a client stream
			if (cameraNetStream==null)
			{
				cameraNetStream = CameraMicSource.getInstance().getCameraStream(netConnection);
			}
			if (audioNetStream==null)
			{
				audioNetStream = CameraMicSource.getInstance().getAudioStream(netConnection);
			}
//			netStream.bufferTime = 60;
			var h264Settings:H264VideoStreamSettings = new H264VideoStreamSettings();
			h264Settings.setProfileLevel(H264Profile.MAIN, H264Level.LEVEL_3);
			h264Settings.setQuality(0, 100);
			
			cameraNetStream.videoStreamSettings = h264Settings;
			
			cameraNetStream.addEventListener(NetStatusEvent.NET_STATUS, onCameraRecStreamStatus);
			
			audioNetStream.addEventListener(NetStatusEvent.NET_STATUS, onAudioRecStreamStatus);
			
			netConnection.call("generateStream", streamNameResponder);
		}
		
		private function onCameraRecStreamStatus(event:NetStatusEvent):void
		{
			trace ("VIDEO: " + event.info.code);
			if ( event.info.code == "NetStream.Publish.Start" )
			{
				cameraRecording = true;
				netConnection.call("record", startRecordingResponder, streamName, RECORDING_CAMERA);
//				recordingCameraStartTime = new Date().time;
//				recordingTimer = new Timer(200, 0);
//				recordingTimer.addEventListener(TimerEvent.TIMER, updateTime);
//				recordingTimer.start();
			}
		}
		
		private function onAudioRecStreamStatus(event:NetStatusEvent):void
		{
			trace ("AUDIO: " + event.info.code);
			if ( event.info.code == "NetStream.Publish.Start" )
			{
				audioRecording = true;
				netConnection.call("record", startRecordingResponder, streamName, RECORDING_AUDIO);
				recordingAudioStartTime = new Date().time;
//				recordingTimer = new Timer(200, 0);
//				recordingTimer.addEventListener(TimerEvent.TIMER, updateTime);
//				recordingTimer.start();
			}
			
		}
		
//		private function updateTime(event:TimerEvent):void
//		{
//			var currentTime:Number;
//			if (cameraRecording)
//			{
//				currentTime= new Date().time - recordingCameraStartTime;
//			}
//			else
//				currentTime = previewNetStream.time*1000;
//		//	cameraControlsPanel.setTime(currentTime);
//			if (cameraRecording && currentTime >= WebcamRecorderClient.configurationVariables["maxRecordingTime"])
//			{
////				recordButton.toggleButton();
//			}
//			if (cameraRecording && currentTime >= WebcamRecorderClient.configurationVariables["minRecordingTime"])
//			{
////				recordButton.enabled = true;
//			}
//		}
		
		public function stopRecording(event:RecordingEvent=null):void
		{
			//recordButton = (RecordButton)(event.target);
			setBlurText("Uploading...");
			setBlur(true);
//			recordingTimer.stop();
//			recordingTimer = null;
			
			totalBytesForUploading = cameraNetStream.bufferLength + audioNetStream.bufferLength;
			
			flushTimerCamera = new Timer(100, 0);
			flushTimerCamera.addEventListener(TimerEvent.TIMER, bufferCheckerCamera);
			flushTimerCamera.start();
			
			flushTimerAudio = new Timer(100, 0);
			flushTimerAudio.addEventListener(TimerEvent.TIMER, bufferCheckerAudio);
			flushTimerAudio.start();
			
			cameraNetStream.pause();
			cameraNetStream.attachCamera(null);
			cameraNetStream.attachAudio(null);
			
			audioNetStream.pause();
			audioNetStream.attachCamera(null);
			audioNetStream.attachAudio(null);
			
			WebcamRecorderClient.appendMessage("Recording stopped. Video is uploading...");
		}
		
		private function stopDeleteTimer(type:int):void
		{
			if (type == RECORDING_CAMERA && deleteTimerVideo !=null)
			{
				deleteTimerVideo.stop();
				deleteTimerVideo.removeEventListener(TimerEvent.TIMER, refreshPage);
				deleteTimerVideo = null;	
			}
			else if (type == RECORDING_AUDIO && deleteTimerAudio !=null)
			{
				deleteTimerAudio.stop();
				deleteTimerAudio.removeEventListener(TimerEvent.TIMER, refreshPage);
				deleteTimerAudio = null;	
			}
			else if (type == RECORDING_COMBINED && deleteTimerCombined !=null)
			{
				deleteTimerCombined.stop();
				deleteTimerCombined.removeEventListener(TimerEvent.TIMER, refreshPage);
				deleteTimerCombined = null;
			}
		}
		
		private function startDeleteTimer(type:int):void
		{
			stopDeleteTimer(type);
			var suffix:String;
			if (type == RECORDING_CAMERA)
			{
				suffix = SUFFIX_CAMERA;	
				deleteTimerVideo = new Timer(DELETE_TIMER_DELAY, 1);
				deleteTimerVideo.addEventListener(TimerEvent.TIMER, refreshPage);
				deleteTimerVideo.start();
			}
			else if (type == RECORDING_AUDIO)
			{
				suffix = SUFFIX_AUDIO;	
				deleteTimerAudio = new Timer(DELETE_TIMER_DELAY, 1);
				deleteTimerAudio.addEventListener(TimerEvent.TIMER, refreshPage);
				deleteTimerAudio.start();
			}
			else 
			{
				suffix = "";
				deleteTimerCombined = new Timer(DELETE_TIMER_DELAY, 1)
				deleteTimerCombined.addEventListener(TimerEvent.TIMER, refreshPage);
				deleteTimerCombined.start();
			}
			netConnection.call("resetDeleteTimer", null, streamName+suffix);
			
		}
		
		private function bufferCheckerCamera(event:TimerEvent):void
		{
			if (cameraNetStream.bufferLength == 0)
			{
				trace("Buffer cleared");
				flushTimerCamera.stop();
				flushTimerCamera = null;
				
				netConnection.call("stopRecording", stopRecordingCameraResponder, streamName, RECORDING_CAMERA);
				cameraNetStream.close();
				cameraNetStream = null;
				//See startStopRecordingSuccess for enabling buttons etc.
				
//				(CameraControlsPanel)(event.target).setRecordingButtonEnabled(true);
				startDeleteTimer(RECORDING_CAMERA);
				
			}
			else
			{
				var remainingBytesForUploading:Number;
				if (audioNetStream!=null)
					remainingBytesForUploading = (cameraNetStream.bufferLength + audioNetStream.bufferLength);
				else
					remainingBytesForUploading = cameraNetStream.bufferLength;
				setBlurText("Uploading: "+Math.round((totalBytesForUploading - remainingBytesForUploading)/totalBytesForUploading * 100)+"%");

				var percentDone:Number = Math.round((totalBytesForUploading - remainingBytesForUploading)/totalBytesForUploading * 100);
				trace("Remaining buffer:"+ audioNetStream.bufferLength);
				ExternalInterface.call(WebcamRecorderClient.configurationVariables["recordingUploadProgressCallback"], percentDone);
			}
		}
		
		private function bufferCheckerAudio(event:TimerEvent):void
		{
			if (audioNetStream.bufferLength == 0)
			{
				trace("Buffer cleared");
				flushTimerAudio.stop();
				flushTimerAudio = null;
				
				netConnection.call("stopRecording", stopRecordingAudioResponder, streamName, RECORDING_AUDIO);
				audioNetStream.close();
				audioNetStream = null;
				//See startStopRecordingSuccess for enabling buttons etc.
				
				//				(CameraControlsPanel)(event.target).setRecordingButtonEnabled(true);
				startDeleteTimer(RECORDING_AUDIO);
				
				
			}
			else
			{
				var remainingBytesForUploading:Number;
				if (cameraNetStream!=null)
					remainingBytesForUploading = (cameraNetStream.bufferLength + audioNetStream.bufferLength);
				else
					remainingBytesForUploading = audioNetStream.bufferLength;
				setBlurText("Uploading: "+Math.round((totalBytesForUploading - remainingBytesForUploading)/totalBytesForUploading * 100)+"%");
				var percentDone:Number = Math.round((totalBytesForUploading - remainingBytesForUploading)/totalBytesForUploading * 100);
				trace("Remaining buffer:"+ audioNetStream.bufferLength);
				ExternalInterface.call(WebcamRecorderClient.configurationVariables["recordingUploadProgressCallback"], percentDone);
			}
		}
		
		/**
		 * This function gets called after we have received a stream name to use and can now start recording
		 */
		private function streamNameResult(obj:Object):void
		{
			//FIXME gives the same name except for the last part which is generated on the server :(
			streamName = obj.toString();
			
			cameraNetStream.publish(streamName+SUFFIX_CAMERA, "live");
			audioNetStream.publish(streamName+SUFFIX_AUDIO, "live");
			
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
		
		private function startRecordingSuccess(obj:Object):void
		{
			if (cameraRecording && audioRecording)
			{
				
				doneRecording = false;
				WebcamRecorderClient.appendMessage("Recording started");
				trace("StartRecording Success:"+obj);
				//call the javascript function that recording has started
				ExternalInterface.call(WebcamRecorderClient.configurationVariables["recordingStartedCallback"]);
			}
			
		}
		
		private function stopRecordingCameraSuccess(obj:Object):void
		{
				cameraRecording = false;
				//recording stopped - returns filename of recording
				realFileNameVideo = obj.toString();
				trace("Recording stopped");
				//Enable the buttons
				WebcamRecorderClient.appendMessage("Video Uploading finished.");
				
				trace("StopRecordingCamera Success:"+obj);
				if (!audioRecording)
				{
					setBlur(false);
					setBlurText("");
//					transcode();
					ExternalInterface.call(WebcamRecorderClient.configurationVariables["recordingStoppedCallback"]);
				}

		}
		
		
		
		private function stopRecordingAudioSuccess(obj:Object):void
		{
				audioRecording = false;
				//recording stopped - returns filename of recording
				realFileNameAudio = obj.toString();
				trace("Recording stopped");
				//Enable the buttons
				WebcamRecorderClient.appendMessage("Audio Uploading finished.");
				trace("StopRecordingAudio Success:"+obj);
				if (!cameraRecording)
				{
//					transcode();
//					setBlur(false);
//					setBlurText("");
					ExternalInterface.call(WebcamRecorderClient.configurationVariables["recordingStoppedCallback"]);
				}

		}
		//FIXME Make this WEBM Only due to GPL/non-redistributable restrictions
		private function transcode():void
		{
			startDeleteTimer(RECORDING_CAMERA);
			startDeleteTimer(RECORDING_AUDIO);
			setBlurText("Converting video...");
			setBlur(true);
//			var o:Object = BrowserUtils.getVersion();
			//Only use webm as video codec
			var supportedVideoType:String = "webm" //BrowserUtils.getHTML5VideoSupport();
//			trace("Name:"+o.appName+", Version:"+ o.version);
			trace(supportedVideoType);
			var audioDelay:Number = recordingCameraStartTime - recordingAudioStartTime;
			netConnection.call("transcodeVideo",transcodeVideoResponder, streamName, audioDelay, supportedVideoType);	
		}
		
		private function startStopRecordingFailure(obj:Object):void
		{
			WebcamRecorderClient.appendMessage("Recording failed!");
			for (var i:Object in obj)
			{
				WebcamRecorderClient.appendMessage("Status: " + i + " : "+obj[i]);
				trace("Status: " + i + " : "+obj[i]);
			}
			setBlur(false);
		}
		
		private function transcodeVideoSuccess(obj:Object):void
		{
			var fileName:String = obj.toString();
			WebcamRecorderClient.appendMessage("Transcoding successfull. File: " +fileName);
			trace("Transcoding successfull. File: " +fileName);
			stopDeleteTimer(RECORDING_AUDIO);
			stopDeleteTimer(RECORDING_CAMERA);
			startDeleteTimer(RECORDING_COMBINED);
			resultingVideoFile = fileName;
			setBlur(false);
			setBlurText("");
			netConnection.close();
			postData(resultingVideoFile, WebcamRecorderClient.configurationVariables["postURL"], WebcamRecorderClient.configurationVariables["isAjax"]);
			ExternalInterface.call(WebcamRecorderClient.configurationVariables["recordingTranscodingFinishedCallback"], resultingVideoFile, true);
			//postData(fileName);
		}
		
		private function transcodeVideoFailure(obj:Object):void
		{
			WebcamRecorderClient.appendMessage("Transcoding video failed.");
			trace("Transcoding video failed:");
			for (var i:Object in obj)
			{
				WebcamRecorderClient.appendMessage("Status: " + i + " : "+obj[i]);
				trace("Status: " + i + " : "+obj[i]);
			}
			setBlurText("");
			setBlur(false);
			ExternalInterface.call(WebcamRecorderClient.configurationVariables["recordingTranscodingFinishedCallback"], null, false);
		}
		
		public function cameraReady(event:CameraReadyEvent):void
		{
//			if (cameraNetStream!=null)
//			{
//				cameraNetStream = CameraMicSource.getInstance().getCameraStream(netConnection);
//			}
			trace("Camera ready");
			camera = event.camera;
//			cameraNetStream.attachCamera(camera);
		}
		
		public function microphoneReady(event:MicrophoneReadyEvent):void
		{
//			if (audioNetStream==null)
//			{
//				audioNetStream = CameraMicSource.getInstance().getAudioStream(netConnection);
//			}
			trace("Microphone ready");
			microphone = event.microphone;
		}
		
		public function nextButtonHandler(event:ButtonEvent):void
		{
			netConnection.call("cancelDeleteTimer",null, streamName);
			transcode();
		}
		
		public function cancelButtonHandler(event:ButtonEvent):void
		{
			//FIXME put code to handle the cancel button;
			netConnection.close();
			goToURL(WebcamRecorderClient.configurationVariables["cancelURL"]);
		}
		
		public function refreshPage(event:TimerEvent=null):void
		{
			var url:String = ExternalInterface.call('window.location.href.toString'); 
			var request:URLRequest = new URLRequest(url);
			navigateToURL(request, "_self");
		}
		
		public function goToURL(url:String=null):void
		{
			if (url==null)
				refreshPage();
			else if (url.indexOf("javascript:")==0)
			{
				url= url.substr(11);
				ExternalInterface.call(url); 
			}
			else
			{
				var request:URLRequest = new URLRequest(url);
				navigateToURL(request, "_self");
			}
		}
		
		public function postData(fName:String, url:String=null, isAjax:Boolean = false):void 
		{
			//Possibly make this ajax based javascript call if isAjax==true
			if (url == null)
				url = ExternalInterface.call('window.location.href.toString'); 
			var request:URLRequest = new URLRequest(url);
			var variables:URLVariables = new URLVariables();
			variables.vidfile = fName;
			variables.type = 'record';
			variables.keepvideofile = "false";
			request.data = variables;
			request.method = URLRequestMethod.POST;
			
			if (isAjax)
			{
				var dataToSend:String = 'vidfile='+fName+'&type=record&keepvideofile=false';
				ExternalInterface.call("refreshPage", url, dataToSend);
			}
			else
			{
				navigateToURL(request, "_self");	
			}
			
		}
		
		private function setBlur(flag:Boolean):void
		{
			ExternalInterface.call(WebcamRecorderClient.configurationVariables["blurFunction"], flag);			
		}
		
		private function setBlurText(text:String):void
		{
			ExternalInterface.call(WebcamRecorderClient.configurationVariables["blurFunctionText"], text);			
		}

		private function metaDataHandler(infoObject:Object):void
		{
			trace("metadata"+ infoObject.duration);
		}
	}
}