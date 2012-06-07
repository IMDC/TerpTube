package sls.recording.red5;

import java.util.ArrayList;
import java.util.HashSet;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;
import org.red5.server.adapter.ApplicationAdapter;
import org.red5.server.api.IConnection;
import org.red5.server.api.IScope;
import org.red5.server.api.Red5;
import org.red5.server.api.ScopeUtils;
import org.red5.server.stream.ClientBroadcastStream;

public class Application extends ApplicationAdapter
{
	private int							suffix					= 0;

	private static final Log			log						= LogFactory
																		.getLog(Application.class);
	private static ArrayList<String>	generatedLists			= new ArrayList<String>();
	private static ArrayList<String>	generatedListsClient	= new ArrayList<String>();

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * org.red5.server.adapter.ApplicationAdapter#connect(org.red5.server.api
	 * .IConnection, org.red5.server.api.IScope, java.lang.Object[])
	 */
	@Override
	public synchronized boolean connect(IConnection conn, IScope scope,
			Object[] params)
	{
		log.info("WebcamRecording.connect " + conn.getClient().getId());
		boolean accept = (Boolean) params[0];
		if (!accept)
			rejectClient("You passed false...");
		return super.connect(conn, scope, params);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * org.red5.server.adapter.ApplicationAdapter#disconnect(org.red5.server
	 * .api.IConnection, org.red5.server.api.IScope)
	 */
	@Override
	public synchronized void disconnect(IConnection conn, IScope scope)
	{
		log.info("WebcamRecording.disconnect " + conn.getClient().getId());
		super.disconnect(conn, scope);
		String clientId = conn.getClient().getId();

		// Clean up after disconnecting
		for (int i = 0; i < generatedListsClient.size(); i++)
		{
			if (generatedListsClient.get(i).equals(clientId))
			{
				generatedListsClient.remove(i);
				generatedLists.remove(i);
			}
		}
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * org.red5.server.adapter.ApplicationAdapter#start(org.red5.server.api.
	 * IScope)
	 */
	@Override
	public synchronized boolean start(IScope scope)
	{
		boolean flag = super.start(scope);
		log.info("WebcamRecording.start");
		return flag;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * org.red5.server.adapter.ApplicationAdapter#stop(org.red5.server.api.IScope
	 * )
	 */
	@Override
	public synchronized void stop(IScope scope)
	{
		log.info("WebcamRecording.stop");
		super.stop(scope);
	}

	private static String getNewStream()
	{
		String generated = "";
		for (int i = 0; i < 20; i++)
		{
			generated += (char) ((int) Math.random() * 52 + 40);
		}
		return generated;
	}

	public static void releaseStream(String stream)
	{
		if (generatedLists.contains(stream))
		{
			int i = generatedLists.indexOf(stream);
			generatedLists.remove(i);
			generatedListsClient.remove(i);
		}
	}

	public static String generateStream()
	{
		String stream = getNewStream();
		while (generatedLists.contains(stream))
		{
			stream = getNewStream();
		}
		IConnection conn = Red5.getConnectionLocal();
		String clientId = conn.getClient().getId();
		generatedLists.add(stream);
		generatedListsClient.add(clientId);
		return stream;
	}

	/**
	 * 
	 * @param fileName
	 * @return the actual fileName of the recording
	 */
	public String record(String fileName)
	{
		IConnection conn = Red5.getConnectionLocal();
		IScope scope = conn.getScope();
		String clientId = conn.getClient().getId();
		/*
		 * String[] names = fileName.split("[0-9]+"); String newFileName = "";
		 * if (names[names.length - 1].matches("[0-9]+")) { suffix =
		 * Integer.parseInt(names[names.length - 1]); for (int i = 0; i <
		 * names.length - 1; i++) newFileName += names[i]; } else { suffix = 0;
		 * for (int i = 0; i < names.length; i++) newFileName += names[i]; }
		 */

		// String streamName = clientId + "_" +
		// String.valueOf(System.currentTimeMillis());
		try
		{
			ClientBroadcastStream stream = (ClientBroadcastStream) this
					.getBroadcastStream(scope, fileName);
			fileName = clientId + "_" + fileName;
			/*
			 * while (new File(getStreamDirectory(scope)+newFileName +
			 * suffix+".flv").exists()) { suffix++; } fileName = newFileName +
			 * suffix;
			 */
			stream.saveAs(fileName, false);
			// You could, if you wish of course, notify the user that the
			// recording has actually started
			// You just have to add the recordingStarted public function on your
			// flash application
			// ServiceUtils.invokeOnConnection(conn, "recordingStarted",
			// new Object[] { clientId });
		}
		catch (Exception e)
		{
			// You could notify the user that the recording failed for some
			// stupid reason
			// sometimes things does not go well :)
			// You just have to add the recordingStarted public function on your
			// flash application
			// ServiceUtils.invokeOnConnection(conn, "recordingFailed", new
			// Object[]{clientId});
			return null;
		}
		return fileName;
	}

	public String stopRecording(String fileName)
	{
		IConnection conn = Red5.getConnectionLocal();
		IScope scope = getRecordingScope(conn, fileName);
		String clientId = conn.getClient().getId();
		if (scope == null)
		{
			return "Cannot find broadcast stream for " + fileName;
		}
		ClientBroadcastStream stream = (ClientBroadcastStream) this
				.getBroadcastStream(scope, fileName);
		stream.stopRecording();
		releaseStream(fileName);
		// ServiceUtils.invokeOnConnection(conn, "recordingStopped",
		// new Object[] { clientId });
		return "Recording stopped for file:" + fileName;
	}

	private IScope getRecordingScope(IConnection conn, String fileName)
	{
		for (IScope scope : conn.getClient().getScopes())
		{
			if (this.hasBroadcastStream(scope, fileName))
			{
				return scope;
			}
		}
		return null;
	}
}
