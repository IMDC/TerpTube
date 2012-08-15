package sls.recording.red5;

import java.io.File;
import java.io.IOException;
import java.lang.reflect.Array;
import java.util.ArrayList;
import java.util.HashMap;
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
	// 5 minutes
	private static final int					DELETE_DELAY			= 5 * 60 * 1000;
	private static String						FFMPEG					= "/var/www/include/ffmpeg/ffmpeg";
	private static final int					AUDIO_RECORDING			= 0;
	private static final int					CAMERA_RECORDING		= 1;
	public static final String					SUFFIX_AUDIO			= "_audio";
	public static final String					SUFFIX_VIDEO			= "_video";

	public static final String					OUTPUT_TYPE_H264		= "mp4";
	public static final String					OUTPUT_TYPE_WEBM		= "webm";
	public static final String					OUTPUT_TYPE_OGV			= "ogv";

	/**
	 * @param fFMPEG
	 *            the fFMPEG to set
	 */
	public void setFFMPEG(String fFMPEG)
	{
		FFMPEG = fFMPEG;
	}

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
	public String record(String streamName, int type)
	{
		IConnection conn = Red5.getConnectionLocal();
		IScope scope = conn.getScope();
		String suffix;
		final String file;
		if (type == CAMERA_RECORDING)
			suffix = "_video";
		else
			suffix = "_audio";
		streamName += suffix;
		file = streamName + "_" + System.currentTimeMillis();
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
	public String stopRecording(String streamName, int type)
	{
		IConnection conn = Red5.getConnectionLocal();
		String suffix;
		if (type == CAMERA_RECORDING)
			suffix = "_video";
		else
			suffix = "_audio";
		streamName += suffix;
		IScope scope = getRecordingScope(conn, streamName);
		if (scope == null)
		{
			return "Cannot find broadcast stream for " + streamName;
		}

		ClientBroadcastStream stream = (ClientBroadcastStream) this
				.getBroadcastStream(scope, streamName);
		stream.stopRecording();
		// stream should be released after both audio and video streams are
		// received
		// releaseStream(streamName);

		startDeleteTimer(streamName);
		String fileName = getFileFromStream(streamName);
		return fileName;
	}

	/**
	 * convert from FFMPEG Duration to milliseconds
	 * 
	 * @param time
	 *            in the format HH:MM:SS.mmm
	 * @return time in milliseconds
	 */
	public static long parseFFMPEGTime(String time)
	{
		return Integer.parseInt(time.substring(0, time.indexOf(":")))
				* 60
				* 60
				* 1000
				+ Integer.parseInt(time.substring(time.indexOf(":") + 1,
						time.lastIndexOf(":")))
				* 60
				* 1000
				+ Integer.parseInt(time.substring(time.lastIndexOf(":") + 1,
						time.indexOf("."))) * 1000
				+ Integer.parseInt(time.substring(time.indexOf(".") + 1));
	}

	/**
	 * convert from time in milliseconds to FFMPEG String
	 * 
	 * @param time
	 *            in milliseconds
	 * @return String representation of time in the format HH:MM:SS,mmm
	 */
	public static String parseFFMPEGTime(long time)
	{
		String mil = "" + time % 1000;
		String sec = "" + (time / 1000) % 60;
		String min = "" + ((time / 1000) / 60) % 60;
		String hrs = "" + (((time / 1000) / 60) / 60) % 60;
		while (mil.length() < 3)
			mil = "0" + mil;
		while (sec.length() < 2)
			sec = "0" + sec;
		while (min.length() < 2)
			min = "0" + min;
		while (hrs.length() < 2)
			hrs = "0" + hrs;

		return hrs + ":" + min + ":" + sec + "." + mil;
	}

	public String transcodeVideo(String streamName, long audioDelay, String type)
	{
		String fileName = null;
		// FIXME make it so that it can work if I am recording only audio or
		// only video
		String audioFileString = getFileFromStream(streamName + SUFFIX_AUDIO);
		String videoFileString = getFileFromStream(streamName + SUFFIX_VIDEO);
		String[] outputFFMPEG;
		String[] inputFFMPEG;
		File newFile = null;
		if (type.equals(OUTPUT_TYPE_H264))
		{
			fileName = videoFileString.substring(0,
					videoFileString.indexOf("_"))
					+ ".mp4";
			newFile = new File(FilenameGenerator.recordPath + fileName);

			outputFFMPEG = new String[] { "-vcodec", "copy", "-acodec",
					"libfaac", "-ar", "22050", "-ab", "64000", "-ac", "1",
					newFile.getAbsolutePath() };
		}
		else if (type.equals(OUTPUT_TYPE_WEBM))
		{
			fileName = videoFileString.substring(0,
					videoFileString.indexOf("_"))
					+ ".webm";
			newFile = new File(FilenameGenerator.recordPath + fileName);
			outputFFMPEG = new String[] { "-acodec", "libvorbis", "-ar",
					"22050", "-ab", "64000", "-ac", "1",
					newFile.getAbsolutePath() };

		}
		else if (type.equals(OUTPUT_TYPE_OGV))
		{
			fileName = videoFileString.substring(0,
					videoFileString.indexOf("_"))
					+ ".ogv";
			newFile = new File(FilenameGenerator.recordPath + fileName);
			outputFFMPEG = new String[] { "-acodec", "libvorbis", "-ar",
					"22050", "-ab", "64000", "-ac", "1",
					newFile.getAbsolutePath() };

		}
		else
			return null;
		File audioFile = new File(FilenameGenerator.recordPath
				+ audioFileString);
		File videoFile = new File(FilenameGenerator.recordPath
				+ videoFileString);
		ProcessBuilder p;
		// MUST transcode to the appropriate video/audio codec
		if (audioDelay < 0)
		{ // must delay the audio
			audioDelay *= -1;
			String time = parseFFMPEGTime(audioDelay);
			inputFFMPEG = new String[] { FFMPEG, "-i",
					videoFile.getAbsolutePath(), "-itsoffset", time, "-i",
					audioFile.getAbsolutePath(), "-map", "0:0", "-map", "1:1" };
			// p = new ProcessBuilder(FFMPEG, "-i", videoFile.getAbsolutePath(),
			// "-itsoffset", time, "-i", audioFile.getAbsolutePath(),
			// "-map", "0:0", "-map", "1:1", "-vcodec", "copy", "-acodec",
			// "libfaac", "-ar", "22050", "-ab", "64000", "-ac", "1",
			// newFile.getAbsolutePath());
		}
		else
		{ // must delay the video

			String time = parseFFMPEGTime(audioDelay);
			inputFFMPEG = new String[] { FFMPEG, "-i",
					audioFile.getAbsolutePath(), "-itsoffset", time, "-i",
					videoFile.getAbsolutePath(), "-map", "1:0", "-map", "0:1" };
			// p = new ProcessBuilder(FFMPEG, "-i", audioFile.getAbsolutePath(),
			// "-itsoffset", time, "-i", videoFile.getAbsolutePath(),
			// "-map", "1:0", "-map", "0:1", "-vcodec", "copy", "-acodec",
			// "libfaac", "-ar", "22050", "-ab", "64000", "-ac", "1",
			// newFile.getAbsolutePath());
		}
		p = new ProcessBuilder(concatenateArrays(inputFFMPEG, outputFFMPEG));
		try
		{
			Process process = p.start();
			process.waitFor();
			// Delete the original files
			deleteFile(streamName + SUFFIX_AUDIO);
			deleteFile(streamName + SUFFIX_VIDEO);
			cancelDeleteTimer(streamName + SUFFIX_AUDIO);
			cancelDeleteTimer(streamName + SUFFIX_VIDEO);
			streamFileNames.put(streamName, newFile.getName());
			startDeleteTimer(streamName);
			// audioFile.delete();
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
		return fileName;
	}

	private void startDeleteTimer(final String streamName)
	{
		ScheduledExecutorService executor = Executors
				.newSingleThreadScheduledExecutor();
		ScheduledFuture<?> future = executor.schedule(new Runnable()
		{

			@Override
			public void run()
			{
				// TODO Auto-generated method stub
				deleteFile(streamName);
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
		ScheduledFuture<?> f = (ScheduledFuture<?>) futureEvents
				.remove(streamName);
		if (f != null)
		{
			f.cancel(true);
		}
		startDeleteTimer(streamName);
	}

	public void cancelDeleteTimer(String streamName)
	{
		ScheduledFuture<?> f = (ScheduledFuture<?>) futureEvents
				.remove(streamName);
		if (f != null)
		{
			f.cancel(true);
		}
		// Will not need to reference this stream anymore so remove it.
		streamFileNames.remove(streamName);
	}

	private void deleteFile(String streamName)
	{
		String fileName = getFileFromStream(streamName);
		streamFileNames.remove(streamName);
		futureEvents.remove(streamName);

		File f = new File(FilenameGenerator.recordPath + fileName);
		if (f.exists())
			f.delete();
	}

	/*
	 * private String getStreamForFileName(String fileName) { String streamName
	 * = fileName.substring(0, fileName.lastIndexOf("_")); return streamName; }
	 */

	public void saveFile(String streamName)
	{
		String fileName = getFileFromStream(streamName);

		streamFileNames.remove(streamName);
		futureEvents.remove(streamName).cancel(true);

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
			oldFile.delete();
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

	@SuppressWarnings("unchecked")
	private static <T> T[] concatenateArrays(T[] a, T[] b)
	{
		T[] c = (T[]) Array.newInstance(a.getClass(), a.length + b.length);

		System.arraycopy(a, 0, c, 0, a.length);
		System.arraycopy(b, 0, c, a.length, b.length);

		return c;
	}
}
