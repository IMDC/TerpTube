package gui
{
	import events.CameraReadyEvent;
	import events.MicrophoneReadyEvent;
	
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.IEventDispatcher;
	import flash.events.StatusEvent;
	import flash.events.TimerEvent;
	import flash.media.Camera;
	import flash.media.Microphone;
	import flash.media.Video;
	import flash.utils.Timer;
	
	import flashx.textLayout.formats.BackgroundColor;

	public class CameraViewer extends Sprite implements IEventDispatcher
	{
		private var camera:Camera;
		private var video:Video;
		private var hasVideo:Boolean;
		private var microphone:Microphone;
		
		public static const CAMERA_READY_STRING:String = "camera ready";
		public static const MICROPHONE_READY_STRING:String = "microphone ready";
		
		public function CameraViewer()
		{
			graphics.beginFill( 0xffffff, 1.0 );
			graphics.drawRect( 0, 0, 320, 240 );
			graphics.endFill();
			if (!cameraExists())
			{
				return;	
			}
			camera = Camera.getCamera();
			if (camera==null)
			{
				trace("Cannot get Camera");
				return;
			}
			camera.addEventListener(StatusEvent.STATUS, cameraStatusHandler); 
			
			camera.setMode(640, 480, 30, true);
			camera.setQuality(0, 100);
			camera.setKeyFrameInterval(5);
			
			microphone = Microphone.getMicrophone();
			if (microphone==null)
			{
				trace("Cannot get Microphone");
//				return;
			}
//			microphone.addEventListener(StatusEvent.STATUS, microphoneStatusHandler); 
			microphone.setUseEchoSuppression(true);
			microphone.setLoopBack(false);
			microphone.rate = 22;
			video = new Video();
			video.attachCamera(camera);
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
		private function cameraStatusHandler(event:StatusEvent):void 
		{ 
			if (camera.muted) 
			{ 
				trace("Unable to connect to active camera."); 
			} 
			else 
			{ 
				trace("Connected to camera");
				this.dispatchEvent(new CameraReadyEvent(CAMERA_READY_STRING, camera));
				this.dispatchEvent(new MicrophoneReadyEvent(MICROPHONE_READY_STRING,microphone));
				// Resize Video object to match camera settings and  
				// add the video to the display list. 
				//video.width = camera.width; 
				//video.height = camera.height; 
				video.width=320;
				video.height=240;
				video.addEventListener(Event.ADDED_TO_STAGE, cameraAdded);
				addChild(video); 
			} 
			// Remove the status event listener. 
			camera.removeEventListener(StatusEvent.STATUS, cameraStatusHandler); 
		}
		
		private function cameraAdded(event:Event):void
		{
			trace("Camera added to stage");
		}
		
		
		private function cameraExists():Boolean
		{
			if (Camera.names.length > 0) 
			{ 
				trace("User has at least one camera installed."); 
				return true;
			} 
			else 
			{ 
				trace("User has no cameras installed."); 
				return false;
			}
		}
	}
}