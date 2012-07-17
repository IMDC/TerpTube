package recorder.gui
{
	import flash.display.Sprite;
	import flash.events.IEventDispatcher;
	import flash.media.Camera;
	import flash.media.Microphone;
	import flash.media.Video;
	
	import recorder.model.CameraMicSource;
	
//	import mx.controls.Label;

	public class CameraViewer extends Sprite implements IEventDispatcher
	{
		private var camera:Camera;
		private var video:Video;
		private var hasVideo:Boolean;
		private var microphone:Microphone;
		
//		public static const CAMERA_READY_STRING:String = "camera ready";
//		public static const MICROPHONE_READY_STRING:String = "microphone ready";
		
		public function CameraViewer()
		{
			graphics.beginFill( 0xffffff, 1.0 );
			graphics.drawRect( 0, 0, 320, 240 );
			graphics.endFill();
			var cmSource:CameraMicSource = CameraMicSource.getInstance();
			video = cmSource.cameraVideo;
			if (video != null)
				this.addChild(video);
//			else
//			{
//				var noCameraLabel:Label = new Label();
//				noCameraLabel.text = "No cameras found";
//				noCameraLabel.styleSheet.setStyle("padding", "padding: 100px 5px 100px 5px");
//				this.addChild(noCameraLabel);
//			}
//			var numberOfCameras:uint;
//			if ((numberOfCameras = cameraExists())==0)
//			{
//				return;	
//			}
//			else if (numberOfCameras > 1)
//			{
//				//Display the camera Dialog to select a camera
//				Security.showSettings(SecurityPanel.CAMERA);
//			}
//			camera = Camera.getCamera();
//		//	WebcamRecorderClient.appendMessage(camera.name);
//			if (camera==null)
//			{
//				trace("Camera in use elsewhere");
//				WebcamRecorderClient.appendMessage("Camera is in use in another application");
//				return;
//			}
//			camera.addEventListener(StatusEvent.STATUS, cameraStatusHandler); 
//			
//			camera.setMode(640, 480, 30, true);
//			camera.setQuality(0, 100);
//			camera.setKeyFrameInterval(5);
			
//			microphone = Microphone.getMicrophone();
//			if (microphone==null)
//			{
//				trace("Cannot get Microphone");
////				return;
//			}
////			microphone.addEventListener(StatusEvent.STATUS, microphoneStatusHandler); 
//			microphone.setUseEchoSuppression(true);
//			microphone.setLoopBack(false);
//			microphone.rate = 22;
//			microphone.setSilenceLevel(0);
//			video = new Video();
//			video.attachCamera(camera);
		}
//		private function microphoneStatusHandler(event:StatusEvent):void
//		{
//			if (microphone.muted)
//			{
//				trace("Unable to connect to microphone");
//			}
//			else
//			{
//				this.dispatchEvent(new MicrophoneReadyEvent(MICROPHONE_READY_STRING, microphone));
//			}
//		}
//		private function cameraStatusHandler(event:StatusEvent):void 
//		{ 
//			if (camera.muted) 
//			{ 
//				WebcamRecorderClient.appendMessage("User prevented access to camera");
//				trace("Unable to connect to active camera."); 
//			} 
//			else 
//			{ 
//				trace("Connected to camera");
//				this.dispatchEvent(new CameraReadyEvent(CAMERA_READY_STRING, camera));
//				this.dispatchEvent(new MicrophoneReadyEvent(MICROPHONE_READY_STRING,microphone));
//				// Resize Video object to match camera settings and  
//				// add the video to the display list. 
//				//video.width = camera.width; 
//				//video.height = camera.height; 
//				video.width=320;
//				video.height=240;
//				video.addEventListener(Event.ADDED_TO_STAGE, cameraAdded);
//				addChild(video); 
//			} 
//			// Remove the status event listener. 
//			camera.removeEventListener(StatusEvent.STATUS, cameraStatusHandler); 
//		}
//		
//		private function cameraAdded(event:Event):void
//		{
//			trace("Camera added to stage");
//		}
//		
//		
//		private function cameraExists():uint
//		{
//			if (Camera.names.length == 1) 
//			{ 
//				trace("User has at least one camera installed."); 
//			} 
//			else if (Camera.names.length == 0)
//			{ 
//				WebcamRecorderClient.appendMessage("No camera Found");
//				trace("User has no cameras installed."); 
//			}
//			else
//			{
//				WebcamRecorderClient.appendMessage("User has several cameras");
//				trace("User has several cameras installed.");
//			}
//			return Camera.names.length;
//		}
	}
}