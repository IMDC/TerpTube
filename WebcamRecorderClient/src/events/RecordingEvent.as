package events
{
	import flash.events.Event;

	public class RecordingEvent extends Event
	{
		private var _fileName:String;
		
		public function RecordingEvent(type:String, fName:String)
		{
			super(type);
			fileName = fName;
		}

		public function get fileName():String
		{
			return _fileName;
		}

		public function set fileName(value:String):void
		{
			_fileName = value;
		}

	}
}