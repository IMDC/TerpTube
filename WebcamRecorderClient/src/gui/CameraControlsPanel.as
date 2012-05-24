package gui
{
	import events.RecordingEvent;
	
	import flash.display.DisplayObject;
	import flash.display.Sprite;
	import flash.events.IEventDispatcher;
	import flash.events.MouseEvent;
	
	import listeners.CameraControlsListener;

	

	public class CameraControlsPanel extends Sprite implements IEventDispatcher
	{
		public static const START_RECORDING_STRING:String = "record";
		public static const STOP_RECORDING_STRING:String = "stop recording";
		
		[Embed(source='images/record.png')]
		private  static var RecordImage:Class;
		[Embed(source='images/stop.png')]
		private  static var StopImage:Class;
		private var recordButton:Sprite;
		
		private var listenersArray:Array;
		
		private var _fileName:String;
		
		public function CameraControlsPanel()
		{
			recordButton = new Sprite();
			recordButton.addChild(new StopImage());
			recordButton.addChild(new RecordImage());
			recordButton.buttonMode = true;
			recordButton.getChildAt(0).visible = false;
			recordButton.addEventListener(MouseEvent.MOUSE_UP, toggleRecordStopIcons);
			addChild(recordButton);
			recordButton.width = 20;
			recordButton.height = 20;
			listenersArray = new Array();
		}
		
		public function addControlsListener(listener:CameraControlsListener):void
		{
			listenersArray.push(listener);
			this.addEventListener(START_RECORDING_STRING,listener.record);
			this.addEventListener(STOP_RECORDING_STRING, listener.stopRecording);
		}
		
		public function removeControlsListener(listener:CameraControlsListener):void
		{
			if (listenersArray.indexOf(listener)==-1)
				return;
			listenersArray.splice(listenersArray.indexOf(listener), 1);
			this.removeEventListener(START_RECORDING_STRING, listener.record);
			this.removeEventListener(STOP_RECORDING_STRING, listener.stopRecording);
		}
		
		private function toggleRecordStopIcons(event:MouseEvent):void
		{
			if (recordButton.getChildAt(0).visible)
			{
				//STOP BUTTON IS VISIBLE
				this.dispatchEvent(new RecordingEvent(STOP_RECORDING_STRING, fileName));
			}
			else
			{
				//RECORD BUTTON IS VISIBLE
				this.dispatchEvent(new RecordingEvent(START_RECORDING_STRING, fileName));
			}
				recordButton.getChildAt(0).visible = !recordButton.getChildAt(0).visible;
				recordButton.getChildAt(1).visible = !recordButton.getChildAt(0).visible;
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