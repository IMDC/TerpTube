package recorder.events
{
	import flash.events.Event;
	import flash.media.Camera;

	public class CameraReadyEvent extends Event
	{
		private var _camera:Camera;
		public function CameraReadyEvent(type:String, cam:Camera)
		{
			super(type);
			camera = cam;
		}

		public function get camera():Camera
		{
			return _camera;
		}

		public function set camera(value:Camera):void
		{
			_camera = value;
		}

	}
}