package recorder.gui
{
	import recorder.events.RecordingEvent;
	
	import flash.display.DisplayObject;
	import flash.display.SimpleButton;
	import flash.display.Sprite;
	import flash.events.IEventDispatcher;
	import flash.events.MouseEvent;
	
	import recorder.listeners.CameraControlsListener;
	
	import mx.controls.Button;

	

	public class CameraControlsPanel extends Sprite implements IEventDispatcher
	{
		
		private var recordButton:RecordButton;
		
		private var listenersArray:Array;
		
//		private var _fileName:String;
		
		public function CameraControlsPanel()
		{
			recordButton = new RecordButton(20, 20);
			addChild(recordButton);
			listenersArray = new Array();
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