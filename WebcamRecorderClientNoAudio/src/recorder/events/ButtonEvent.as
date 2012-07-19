package recorder.events
{
	import flash.events.Event;

	public class ButtonEvent extends Event
	{
		public static const CLICK:String = "ButtonClicked";
		
		public function ButtonEvent(type:String)
		{
			super(type);
		}


	}
}