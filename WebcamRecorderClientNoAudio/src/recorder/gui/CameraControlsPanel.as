package recorder.gui
{
	import flash.display.DisplayObject;
	import flash.display.SimpleButton;
	import flash.display.Sprite;
	import flash.events.IEventDispatcher;
	import flash.events.MouseEvent;
	import flash.text.TextField;
	
	import mx.controls.Button;
	
	import recorder.events.ButtonEvent;
	import recorder.events.RecordingEvent;
	import recorder.listeners.CameraControlsListener;

	

	public class CameraControlsPanel extends Sprite implements IEventDispatcher
	{
		[Embed(source='recorder/images/play.png')]
		private  static var PlayImage:Class;
		[Embed(source='recorder/images/record.png')]
		private  static var RecordImage:Class;
		[Embed(source='recorder/images/stop.png')]
		private  static var StopImage:Class;
		
		private var _previewButton:SpriteButton;
		private var _doneButton:SpriteButton;
		private var _recordButton:SpriteButton;
		
		private var listenersArray:Array;
		
		private var timeArea:TextField;
		private var _maxTime:Number;
		
//		private var _fileName:String;
		//FIXME add a preview button that can preview the just recorded video
		//FIXME add a record again button that deletes the previous recording and records again.
		public function CameraControlsPanel()
		{
			recordButton = new SpriteButton(20, 20, "", new RecordImage(), new StopImage());
			recordButton.isToggle=true;
			addChild(recordButton);
			
			
//			playImage.border = true;
			previewButton = new SpriteButton(20, 20,"",new PlayImage(), new StopImage());
			previewButton.isToggle = true;
			previewButton.x = 30;
			previewButton.enabled = false;
			addChild(previewButton);
			
			doneButton = new SpriteButton(40, 20, "Done");
			doneButton.x = 80;
			doneButton.enabled = false;
			addChild(doneButton);
			
			timeArea = new TextField();
			timeArea.x = 250;
			timeArea.width = 70;
			timeArea.height = 20;
			timeArea.wordWrap = false;
//			timeArea.border = true;
			setTime(0);
			
			addChild(timeArea);
			listenersArray = new Array();
		}
		
		public function setTime(milliseconds:Number):void
		{
			timeArea.text = getMinutesAsString(milliseconds) + ":" + getSecondsAsString(milliseconds)+" / "+getMinutesAsString(maxTime) + ":" + getSecondsAsString(maxTime);
		}
		
		public static function getSeconds(milliseconds:Number):int
		{
			return (milliseconds / 1000) % 60;
		}
		
		public static function getSecondsAsString(milliseconds:Number):String
		{
			var secondsString:String = "" + getSeconds(milliseconds);
			while (secondsString.length < 2)
				secondsString = "0" + secondsString;
			return secondsString;
		}
		
		public static function getMinutes(milliseconds:Number):int
		{
			return ((milliseconds / 1000) / 60) %60;
		}
		
		public static function getMinutesAsString(milliseconds:Number):String
		{
			var minutesString:String = "" + getMinutes(milliseconds);
			while (minutesString.length < 2)
				minutesString = "0" + minutesString;
			return minutesString;
		}
		
		public function addControlsListener(listener:CameraControlsListener):void
		{
			listenersArray.push(listener);
			recordButton.addEventListener(ButtonEvent.CLICK,listener.toggleRecording);
			previewButton.addEventListener(ButtonEvent.CLICK, listener.previewButtonHandler);
			doneButton.addEventListener(ButtonEvent.CLICK, listener.doneButtonHandler);
		}
		
		public function removeControlsListener(listener:CameraControlsListener):void
		{
			if (listenersArray.indexOf(listener)==-1)
				return;
			listenersArray.splice(listenersArray.indexOf(listener), 1);
			recordButton.removeEventListener(ButtonEvent.CLICK, listener.toggleRecording);
			previewButton.removeEventListener(ButtonEvent.CLICK, listener.previewButtonHandler);
			doneButton.removeEventListener(ButtonEvent.CLICK, listener.doneButtonHandler);
		}
		
		
		public function setPreviewing(previewing:Boolean):void
		{
						
		}
		
		public function setPreviewButtonEnabled(enabled:Boolean):void
		{
			previewButton.enabled = enabled;	
		}

		public function get maxTime():Number
		{
			return _maxTime;
		}

		public function set maxTime(value:Number):void
		{
			_maxTime = value;
		}

		public function get previewButton():SpriteButton
		{
			return _previewButton;
		}

		public function set previewButton(value:SpriteButton):void
		{
			_previewButton = value;
		}

		public function get recordButton():SpriteButton
		{
			return _recordButton;
		}

		public function set recordButton(value:SpriteButton):void
		{
			_recordButton = value;
		}

		public function get doneButton():SpriteButton
		{
			return _doneButton;
		}

		public function set doneButton(value:SpriteButton):void
		{
			_doneButton = value;
		}

		
//		public function get fileName():String
//		{
//			return _fileName;
//		}
//
//		public function set fileName(value:String):void
//		{
//			_fileName = value;
//		}

	}
}