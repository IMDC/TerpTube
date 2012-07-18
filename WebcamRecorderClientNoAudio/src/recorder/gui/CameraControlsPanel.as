package recorder.gui
{
	import flash.display.DisplayObject;
	import flash.display.SimpleButton;
	import flash.display.Sprite;
	import flash.events.IEventDispatcher;
	import flash.events.MouseEvent;
	import flash.text.TextField;
	
	import mx.controls.Button;
	
	import recorder.events.RecordingEvent;
	import recorder.listeners.CameraControlsListener;

	

	public class CameraControlsPanel extends Sprite implements IEventDispatcher
	{
		
		private var recordButton:RecordButton;
		
		private var listenersArray:Array;
		
		private var timeArea:TextField;
		
//		private var _fileName:String;
		//FIXME add a preview button that can preview the just recorded video
		//FIXME add a record again button that deletes the previous recording and records again.
		public function CameraControlsPanel()
		{
			recordButton = new RecordButton(20, 20);
			addChild(recordButton);
			
			timeArea = new TextField();
			timeArea.x = 30;
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
			timeArea.text = getMinutesAsString(milliseconds) + ":" + getSecondsAsString(milliseconds)+" / "+getMinutesAsString(CameraControlsListener.MAX_RECORDING_TIME) + ":" + getSecondsAsString(CameraControlsListener.MAX_RECORDING_TIME);
		}
		
		public function getSeconds(milliseconds:Number):int
		{
			return (milliseconds / 1000) % 60;
		}
		
		public function getSecondsAsString(milliseconds:Number):String
		{
			var secondsString:String = "" + getSeconds(milliseconds);
			while (secondsString.length < 2)
				secondsString = "0" + secondsString;
			return secondsString;
		}
		
		public function getMinutes(milliseconds:Number):int
		{
			return ((milliseconds / 1000) / 60) %60;
		}
		
		public function getMinutesAsString(milliseconds:Number):String
		{
			var minutesString:String = "" + getMinutes(milliseconds);
			while (minutesString.length < 2)
				minutesString = "0" + minutesString;
			return minutesString;
		}
		
		public function addControlsListener(listener:CameraControlsListener):void
		{
			listenersArray.push(listener);
			recordButton.addEventListener(RecordButton.START_RECORDING_STRING,listener.record);
			recordButton.addEventListener(RecordButton.STOP_RECORDING_STRING, listener.stopRecording);
		}
		
		public function removeControlsListener(listener:CameraControlsListener):void
		{
			if (listenersArray.indexOf(listener)==-1)
				return;
			listenersArray.splice(listenersArray.indexOf(listener), 1);
			recordButton.removeEventListener(RecordButton.START_RECORDING_STRING, listener.record);
			recordButton.removeEventListener(RecordButton.STOP_RECORDING_STRING, listener.stopRecording);
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