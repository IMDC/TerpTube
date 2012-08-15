package utils
{
	import flash.external.ExternalInterface;
	
	
	public class BrowserUtils 
	{
	
		public static const VIDEO_TYPE_MP4:String = "mp4";
		public static const VIDEO_TYPE_OGV:String = "ogv";
		public static const VIDEO_TYPE_WEBM:String = "webm";
		
		public static function getMime(type:String):String
		{
			var mime:String;
			switch (type)
			{
				case VIDEO_TYPE_MP4: mime = 'video/mp4; codecs="avc1.42E01E, mp4a.40.2"';
					break;
				case VIDEO_TYPE_OGV: mime = 'video/ogg; codecs="theora, vorbis"';
					break;
				case VIDEO_TYPE_WEBM: mime = 'video/webm; codecs="vp8, vorbis"';
					break;
			}
			return mime;
		}
		
		private static const CHECK_VERSION:XML = <![CDATA[
			 function( ) { 
				return { appName: navigator.appName, version: navigator.appVersion };
				}
			]]>;
		
		
		public static function getVersion():Object 
		{
			if ( !ExternalInterface.available ) return null;            
			
			return ExternalInterface.call( CHECK_VERSION );
		}
		
		public static function getHTML5VideoSupport():String
		{
		 	var types:Array = new Array(VIDEO_TYPE_WEBM, VIDEO_TYPE_OGV, VIDEO_TYPE_MP4);
			var supported:Boolean;
			if ( !ExternalInterface.available ) return null;
			for each (var type:String in types)
			{
				supported = checkHTML5VideoTypeForSupport(getMime(type));
				if (supported)
					return type;
			}
			return "NOT FOUND";
		}
		
		private static function checkHTML5VideoTypeForSupport(type:String):Boolean
		{
			var query:String = '(function()'+
			'{\n'+
				'var elem = document.createElement("video");\n' +
				'if(typeof elem.canPlayType == "function")\n' +
				'{\n' +
					'var playable = elem.canPlayType(\''+type+'\');\n' +
					'if((playable.toLowerCase() == "maybe")||(playable.toLowerCase() == "probably"))\n' +
					'{\n'+
						'return true;\n'+
					'}\n'+
					'return false;\n'+
				'}\n'+
				'return false;\n'+
			'})';
			var x:Boolean = ExternalInterface.call(query, null);
			return x;
		}
	}
	
}
