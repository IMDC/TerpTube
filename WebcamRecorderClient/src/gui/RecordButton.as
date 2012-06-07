package gui
{
	import events.RecordingEvent;
	
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.IEventDispatcher;
	import flash.events.MouseEvent;
	
	public class RecordButton extends Sprite implements IEventDispatcher
	{
		[Embed(source='images/record.png')]
		private  static var RecordImage:Class;
		[Embed(source='images/stop.png')]
		private  static var StopImage:Class;
		
		public static const START_RECORDING_STRING:String = "record";
		public static const STOP_RECORDING_STRING:String = "stop recording";
		
		private var _state:int;
		private var _enabled:Boolean = true;
		public static const RECORDING_STATE:int = 1;
		public static const STOPPED_STATE:int = 0;
		private var border:Boolean = false;
		
		public function RecordButton(w:Number, h:Number)
		{
			this.addChild(new RecordImage());
			this.addChild(new StopImage());

			width = w;
			height = h;
			
			this.buttonMode = true;
			this.getChildAt(1).visible = false;
			_state = STOPPED_STATE;
			this.addEventListener(MouseEvent.CLICK, toggleRecording);
			this.setBorder(true);
		}
		
		/**
		 * Toggles the recording state of the button
		 * 
		 */
		private function toggleRecording(event:MouseEvent):void
		{
			this.getChildAt(0).visible = !this.getChildAt(0).visible;
			this.getChildAt(1).visible = !this.getChildAt(0).visible;
			if (this.getChildAt(1).visible)
			{
				//STOP BUTTON IS VISIBLE
				this.dispatchEvent(new RecordingEvent(START_RECORDING_STRING));
				_state = RECORDING_STATE; 
			}
			else if (this.getChildAt(0).visible)
			{
				//RECORD BUTTON IS VISIBLE
				this.dispatchEvent(new RecordingEvent(STOP_RECORDING_STRING));
				_state = STOPPED_STATE;
			}

		}

		public function get state():int
		{
			return _state;
		}

		public function get enabled():Boolean
		{
			return _enabled;
		}

		
		override public function dispatchEvent(event:Event):Boolean
		{
			if (!enabled)
				return false;
			else
				return super.dispatchEvent(event);
		}
		
		/**
		 * Enables or disables the component
		 * A disabled component does not trigger mouseEvents
		 * 
		 */
		public function set enabled(value:Boolean):void
		{
			if (_enabled == value)
				return;
			_enabled = value;
			this.buttonMode=value;
			this.getChildAt(state).visible = value;
			if (value)
			{
				this.addEventListener(MouseEvent.CLICK, toggleRecording);
				this.graphics.clear();	
				drawBorder();
			}
			else
			{
				this.removeEventListener(MouseEvent.CLICK, toggleRecording);
				this.graphics.beginFill( 0x666666, 1 );
				this.graphics.drawRect( 0, 0, this.getChildAt(0).width, this.getChildAt(0).height );
				this.graphics.endFill();
			}
		}
		
		/**
		 * Enables or disables the border for the component
		 */
		public function setBorder(value:Boolean):void
		{
			border = value;
			drawBorder();
		}
		
		/**
		 *Draws border around the component if enabled 
		 * 
		 */		
		private function drawBorder():void
		{
			if (border)
			{
				this.graphics.lineStyle(1, 0x000000);
				this.graphics.drawRect(0, 0, this.getChildAt(0).width-1, this.getChildAt(0).height-1);
			}
			else
			{
				this.graphics.clear();
			}
		}
	}
}