package gui
{
	import flash.display.DisplayObject;
	import flash.display.Sprite;
	import flash.events.MouseEvent;

	

	public class CameraControlsPanel extends Sprite
	{
		[Embed(source='images/record.png')]
		private  static var RecordImage:Class;
		[Embed(source='images/stop.png')]
		private  static var StopImage:Class;
		private var recordButton:Sprite;
		
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
		}
		
		private function toggleRecordStopIcons(event:MouseEvent):void
		{
				recordButton.getChildAt(0).visible = !recordButton.getChildAt(0).visible;
				recordButton.getChildAt(1).visible = !recordButton.getChildAt(0).visible;
		}
	}
}