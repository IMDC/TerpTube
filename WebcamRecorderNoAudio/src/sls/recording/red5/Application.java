package sls.recording.red5;

import java.io.File;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Timer;
import java.util.concurrent.Executors;
import java.util.concurrent.Future;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.ScheduledFuture;
import java.util.concurrent.TimeUnit;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;
import org.red5.server.adapter.ApplicationAdapter;
import org.red5.server.api.IConnection;
import org.red5.server.api.IScope;
import org.red5.server.api.Red5;
import org.red5.server.stream.ClientBroadcastStream;

public class Application extends ApplicationAdapter
{
	private static final Log					log						= LogFactory
																				.getLog(Application.class);
	private static ArrayList<String>			generatedLists			= new ArrayList<String>();
	private static ArrayList<String>			generatedListsClient	= new ArrayList<String>();
	private static final String					ALLOWED_CHARACTERS		= "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	private static HashMap<String, String>		streamFileNames			= new HashMap<String, String>();
	private static HashMap<String, Future<?>>	futureEvents			= new HashMap<String, Future<?>>();
	private static final int					DELETE_DELAY			= 5 * 60 * 1000;													// 5
																																			// minutes

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

	/**
	 * Generates a new unique stream ID
	 * 
	 * @return the new stream ID
	 */
	private static String getNewStream()
	{
		String generated = "";
		for (int i = 0; i < 20; i++)
		{
			generated += ALLOWED_CHARACTERS.charAt((int) Math.round(Math
					.random() * (ALLOWED_CHARACTERS.length() - 1)));
		}
		return generated;
	}

	/**
	 * Frees a stream that is no longer used. Happens when the recording is
	 * finished
	 * 
	 * @param stream
	 *            the stream to be released
	 */
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
	 * Records a stream with the specified filename preference to use as a base
	 * 
	 * @param streamName
	 * @return the actual fileName of the recording
	 */
	public String record(String streamName)
	{
		IConnection conn = Red5.getConnectionLocal();
		IScope scope = conn.getScope();
		final String file = streamName + "_" + System.currentTimeMillis();

		try
		{
			ClientBroadcastStream stream = (ClientBroadcastStream) this
					.getBroadcastStream(scope, streamName);
			streamFileNames.put(streamName, file + ".flv");
			stream.saveAs(file, false);
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
		return file;
	}

	/**
	 * Stops the recording of a specified stream
	 * 
	 * @param streamName
	 *            the stream name
	 * @return the fileName of the recording
	 */
	public String stopRecording(final String streamName)
	{
		IConnection conn = Red5.getConnectionLocal();
		IScope scope = getRecordingScope(conn, streamName);
		if (scope == null)
		{
			return "Cannot find broadcast stream for " + streamName;
		}
		ClientBroadcastStream stream = (ClientBroadcastStream) this
				.getBroadcastStream(scope, streamName);
		// stream.
		stream.stopRecording();
		releaseStream(streamName);
		
		startDeleteTimer(streamName);
		String fileName = getFileFromStream(streamName);
		return fileName;
	}

	private void startDeleteTimer(String streamName)
	{
		final String file = getFileFromStream(streamName);

		ScheduledExecutorService executor = Executors
				.newSingleThreadScheduledExecutor();
		ScheduledFuture<?> future = executor.schedule(new Runnable()
		{

			@Override
			public void run()
			{
				// TODO Auto-generated method stub
				deleteFile(file);
			}
		}, DELETE_DELAY, TimeUnit.MILLISECONDS);
		futureEvents.put(streamName, future);
	}
	
	public String getFileFromStream(String stream)
	{
		return streamFileNames.get(stream);
	}

	public void resetDeleteTimer(String streamName)
	{
		ScheduledFuture<?> f = (ScheduledFuture<?>) futureEvents.remove(streamName);
		if (f!=null)
		{
			f.cancel(true);
		}
		startDeleteTimer(streamName);
	}
	
	private void deleteFile(String fileName)
	{
		// FIXME Implement deletion of files if user is not happy with preview
		String streamName = getStreamForFileName(fileName);
		streamFileNames.remove(streamName);
		futureEvents.remove(streamName);
		
		File f = new File(FilenameGenerator.recordPath + fileName);
		if (f.exists())
			f.delete();
	}

	private String getStreamForFileName(String fileName)
	{
		String streamName = fileName.substring(0, fileName.lastIndexOf("_"));
		return streamName;
	}

	public void saveFile(String streamName)
	{
		String fileName = getFileFromStream(streamName);
		
		streamFileNames.remove(streamName);
		futureEvents.remove(streamName).cancel(true);
		
		String FFMPEG = "/var/www/include/ffmpeg/ffmpeg";
		File oldFile = new File(FilenameGenerator.recordPath + fileName);
		File newFile = new File(FilenameGenerator.recordPath
				+ fileName.substring(0, fileName.lastIndexOf(".")) + ".mp4");
		ProcessBuilder p = new ProcessBuilder(FFMPEG, "-i",
				oldFile.getAbsolutePath(), "-vcodec", "copy", "-an",
				newFile.getAbsolutePath());
		try
		{
			Process process = p.start();
			process.waitFor();
		}
		catch (IOException e)
		{
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		catch (InterruptedException e)
		{
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		oldFile.delete();
	}

	/**
	 * Locate the streaming scope for this client and stream ID
	 * 
	 * @param conn
	 *            the connection
	 * @param fileName
	 *            the stream ID
	 * @return the streaming scope
	 */
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
