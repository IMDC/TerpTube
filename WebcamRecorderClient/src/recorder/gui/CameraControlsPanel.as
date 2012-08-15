package recorder.gui
{
	import flash.display.DisplayObject;
	import flash.display.MovieClip;
	import flash.display.SimpleButton;
	import flash.display.Sprite;
	import flash.events.IEventDispatcher;
	import flash.events.MouseEvent;
	import flash.text.TextField;
	import flash.text.TextFormat;
	import flash.text.TextFormatAlign;
	
	import mx.controls.Button;
	
	import recorder.events.ButtonEvent;
	import recorder.events.RecordingEvent;
	import recorder.listeners.CameraControlsListener;

	

	public class CameraControlsPanel extends Sprite implements IEventDispatcher
	{
		[Embed(source='recorder/resources/play.png')]
		private  static var PlayImage:Class;
		[Embed(source='recorder/resources/rec1.gif')]
		private  static var RecordImage:Class;
		[Embed(source='recorder/resources/recordingButtonActive.swf')]
		private  static var StopImage:Class;
		[Embed(source='recorder/resources/cancel.png')]
		private  static var CancelImage:Class;
		[Embed(source='recorder/resources/next.png')]
		private  static var NextImage:Class;
		[Embed(source='recorder/resources/recorderResources.swf', symbol="densityBar")]
		private  static var DensityBar:Class;
		
		private var _recordButton:SpriteButton;
		private var _cancelButton:SpriteButton;
		private var _nextButton:SpriteButton;
		
		private var listenersArray:Array;
		
		private var timeArea:TextField;
		private var _maxTime:Number;
		private var _currentTime:Number;
		private var slider:Sprite;
		private static const BUTTON_WIDTH:int = 48;
		private static const BUTTON_HEIGHT:int = 48;
		private static const DENSITY_BAR_HEIGHT:int = 30;
		private static const OFFSET:int = 5;
		
		
//		private var _fileName:String;
		//FIXME add a preview button that can preview the just recorded video
		//FIXME add a record again button that deletes the previous recording and records again.
		public function CameraControlsPanel()
		{
			timeArea = new TextField();
			timeArea.width = 80;
			timeArea.height = 20;
			timeArea.border = true;
			timeArea.x = WebcamRecorderClient.configurationVariables["videoWidth"] - timeArea.width;// - WebcamRecorderClient.configurationVariables["contentPadding"];
			timeArea.y = DENSITY_BAR_HEIGHT + WebcamRecorderClient.configurationVariables["contentPadding"]/2;
			timeArea.wordWrap = false;
			//			timeArea.border = true;
			var timeAreaFormat:TextFormat = new TextFormat();
			timeAreaFormat.size = 12;
			timeAreaFormat.align = TextFormatAlign.CENTER;
			timeArea.defaultTextFormat = timeAreaFormat;
			setTime(0);
			
			addChild(timeArea);
			
			recordButton = new SpriteButton(BUTTON_WIDTH, BUTTON_HEIGHT, "", new RecordImage(), new StopImage());
			recordButton.isToggle=true;
			recordButton.x = WebcamRecorderClient.configurationVariables["videoWidth"]/2 - BUTTON_WIDTH/2;
			recordButton.y = DENSITY_BAR_HEIGHT + WebcamRecorderClient.configurationVariables["contentPadding"]+ timeArea.height; 
			addChild(recordButton);
			
			
//			playImage.border = true;
			nextButton = new SpriteButton(BUTTON_WIDTH, BUTTON_HEIGHT,"",new NextImage());
			nextButton.isToggle = false;
			nextButton.x = WebcamRecorderClient.configurationVariables["videoWidth"] - BUTTON_WIDTH;// - WebcamRecorderClient.configurationVariables["contentPadding"]
			nextButton.y = DENSITY_BAR_HEIGHT +  WebcamRecorderClient.configurationVariables["contentPadding"] + timeArea.height;
			nextButton.enabled = false;
			addChild(nextButton);
			
			cancelButton = new SpriteButton(BUTTON_WIDTH, BUTTON_WIDTH, "",new CancelImage());
			cancelButton.x = 0;
			cancelButton.y = DENSITY_BAR_HEIGHT +  WebcamRecorderClient.configurationVariables["contentPadding"] + timeArea.height;
			addChild(cancelButton);
			
			slider = new DensityBar();
			slider.width = WebcamRecorderClient.configurationVariables["videoWidth"];
			slider.height = DENSITY_BAR_HEIGHT;
			slider.x = 2;
			slider.y = 0;
			initializeTrack();
			addChild(slider);
			
			listenersArray = new Array();
			
		}
		
		public function initializeTrack():void
		{
			(slider.getChildByName("slider")  as  MovieClip).track.graphics.lineStyle();
			(slider.getChildByName("slider")  as  MovieClip).track.graphics.beginFill(WebcamRecorderClient.configurationVariables["sliderBackgroundColor"]);
			(slider.getChildByName("slider")  as  MovieClip).track.graphics.drawRect(0,0,(slider.getChildByName("slider") as MovieClip).track.width,(slider.getChildByName("slider") as MovieClip).track.height);
			(slider.getChildByName("slider")  as  MovieClip).track.graphics.endFill();
		}
		
		public function drawLinkInterval(currentTime:uint):void
		{
			var x:uint = getXForValue(0);
			var width:uint = getXForValue(currentTime);
			trace("CurrentTime:"+currentTime+"Width:"+ width);
			(slider.getChildByName("slider")  as  MovieClip).track.graphics.lineStyle(1,WebcamRecorderClient.configurationVariables["sliderHighlightedColor"]);
			(slider.getChildByName("slider")  as  MovieClip).track.graphics.beginFill(WebcamRecorderClient.configurationVariables["sliderHighlightedColor"]);
			
			(slider.getChildByName("slider")  as  MovieClip).track.graphics.drawRect(x,1,width-1,(slider.getChildByName("slider")  as  MovieClip).track.height-2);
			(slider.getChildByName("slider")  as  MovieClip).track.graphics.endFill();
		}
		
		private function clearTrack():void
		{
			(slider.getChildByName("slider")  as  MovieClip).track.graphics.clear();
		}
		
		public function setTime(milliseconds:Number):void
		{
			if (currentTime > milliseconds)
			{
				clearTrack();
				//clear the track first
			}
			_currentTime = milliseconds;
			timeArea.text = getMinutesAsString(milliseconds) + ":" + getSecondsAsString(milliseconds)+" / "+getMinutesAsString(maxTime) + ":" + getSecondsAsString(maxTime);
			if (slider!=null)
			{
				drawLinkInterval(milliseconds);
				setThumbPositionFromValue(milliseconds);
			}
		}
		
		private function getXForValue(value:Number):Number
		{
			return 1 + value / WebcamRecorderClient.configurationVariables["maxRecordingTime"] * ((slider.getChildByName("slider")  as  MovieClip).track.width-2);
		}
		
		private function setThumbPositionFromValue(value:Number):void
		{
			(slider.getChildByName("thumb")  as  MovieClip).x = getXForValue(value) - (slider.getChildByName("thumb")  as  MovieClip).width/2 -3 +(slider.getChildByName("slider")  as  MovieClip).track.x;//value / WebcamRecorderClient.configurationVariables["maxRecordingTime"] * (slider.getChildByName("slider")  as  MovieClip).track.width-3;
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
			cancelButton.addEventListener(ButtonEvent.CLICK, listener.cancelButtonHandler);
			nextButton.addEventListener(ButtonEvent.CLICK, listener.nextButtonHandler);
		}
		
		public function removeControlsListener(listener:CameraControlsListener):void
		{
			if (listenersArray.indexOf(listener)==-1)
				return;
			listenersArray.splice(listenersArray.indexOf(listener), 1);
			recordButton.removeEventListener(ButtonEvent.CLICK, listener.toggleRecording);
			cancelButton.removeEventListener(ButtonEvent.CLICK, listener.cancelButtonHandler);
			nextButton.removeEventListener(ButtonEvent.CLICK, listener.nextButtonHandler);
		}
		
		public function setNextButtonEnabled(enabled:Boolean):void
		{
			nextButton.enabled = enabled;	
		}

		public function get maxTime():Number
		{
			return _maxTime;
		}

		public function set maxTime(value:Number):void
		{
			_maxTime = value;
			setTime(currentTime);
		}

		public function get recordButton():SpriteButton
		{
			return _recordButton;
		}

		public function set recordButton(value:SpriteButton):void
		{
			_recordButton = value;
		}


		public function get nextButton():SpriteButton
		{
			return _nextButton;
		}

		public function set nextButton(value:SpriteButton):void
		{
			_nextButton = value;
		}

		public function get cancelButton():SpriteButton
		{
			return _cancelButton;
		}

		public function set cancelButton(value:SpriteButton):void
		{
			_cancelButton = value;
		}

		public function get currentTime():Number
		{
			return _currentTime;
		}

		public function set currentTime(value:Number):void
		{
			_currentTime = value;
			setTime(currentTime);
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