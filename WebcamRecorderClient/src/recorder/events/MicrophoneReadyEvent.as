package recorder.events
{
	import flash.events.Event;
	import flash.media.Microphone;

	public class MicrophoneReadyEvent extends Event
	{
		private var _microphone:Microphone;
		
		public function MicrophoneReadyEvent(type:String, mic:Microphone)
		{
			super(type);
			microphone = mic;
		}

		public function get microphone():Microphone
		{
			return _microphone;
		}

		public function set microphone(value:Microphone):void
		{
			_microphone = value;
		}

	}
}