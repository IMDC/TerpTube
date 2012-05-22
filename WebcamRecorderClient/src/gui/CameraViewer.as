package gui
{
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.StatusEvent;
	import flash.events.TimerEvent;
	import flash.media.Camera;
	import flash.media.Video;
	import flash.utils.Timer;
	
	import flashx.textLayout.formats.BackgroundColor;

	public class CameraViewer extends Sprite
	{
		private var camera:Camera;
		private var video:Video;
		private var hasVideo:Boolean;
		
		public function CameraViewer()
		{
			graphics.beginFill( 0xffffff, 1.0 );
			graphics.drawRect( 0, 0, 320, 240 );
			graphics.endFill();
//	Security.showSettings(SecurityPanel.PRIVACY);
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
			camera.addEventListener(StatusEvent.STATUS, statusHandler); 
			
			camera.setMode(640, 480, 30, true);
			video = new Video();
			video.attachCamera(camera);
		}
		
		private function statusHandler(event:StatusEvent):void 
		{ 
			if (camera.muted) 
			{ 
				trace("Unable to connect to active camera."); 
			} 
			else 
			{ 
				trace("Connected to camera");
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
			camera.removeEventListener(StatusEvent.STATUS, statusHandler); 
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