package org.novosga.painel.client.media;

import java.io.File;
import java.util.Queue;
import java.util.concurrent.ConcurrentLinkedQueue;
import java.util.logging.Logger;
import javafx.scene.media.Media;
import javafx.scene.media.MediaPlayer;


/**
 * @author ulysses
 * @author rogeriolino
 */
public class AudioPlayer {

    private static final Logger LOG = Logger.getLogger(AudioPlayer.class.getName());
    private static AudioPlayer _Instance;
    public static final String AUDIO_PATH = "media/audio/";
    public static final String ALERT_PATH = AUDIO_PATH + "alert";
    private final Vocalizador _vocalizador = new Vocalizador();
    private static Queue<MediaPlayer> medias = new ConcurrentLinkedQueue<MediaPlayer>();

    public static AudioPlayer getInstance() {
        if (_Instance == null) {
            _Instance = new AudioPlayer();
        }
        return _Instance;
    }

    private AudioPlayer() {
    }

    public void playAndWait(String baseDir, String filename) {
        this.play(baseDir, filename, true);
    }

    public void play(String filename) {
        this.play(ALERT_PATH, filename);
    }

    public void play(String baseDir, String filename) {
        this.play(baseDir, filename, false);
    }

    public void play(String baseDir, String filename, boolean wait) {
        this.play(new File(baseDir, filename), wait);
    }

    public void play(final File f, boolean wait) {
        if (!f.exists()) {
            LOG.severe("Erro ao tocar (" + f.getAbsolutePath() + ", arquivo não existe.");
        } else {
            String url = f.toURI().toString();
            MediaPlayer mp = new MediaPlayer(new Media(url));
            mp.setAutoPlay(!wait);
            if (wait) {
                mp.setOnEndOfMedia(new Runnable() {
                    @Override
                    public void run() {
                        AudioPlayer.getInstance().playNext();
                    }
                });
                mp.setOnError(new Runnable() {
                    @Override
                    public void run() {
                        LOG.severe("MediaPlayer Error");
                        AudioPlayer.getInstance().playNext();
                    }
                });
                mp.setOnHalted(new Runnable() {
                    @Override
                    public void run() {
                        LOG.severe("MediaPlayer Halted");
                        AudioPlayer.getInstance().playNext();
                    }
                });
                medias.add(mp);
                if (medias.size() == 1) {
                    mp.play();
                }
            }
        }
    }
    
    private void playNext() {
        MediaPlayer mp;
        while ((mp = medias.poll()) != null) {
            if (!mp.getStatus().equals(MediaPlayer.Status.PLAYING)) {
                mp.play();
                break;
            }
        }
    }

    /**
     * @return the vocalizador
     */
    public Vocalizador getVocalizador() {
        return _vocalizador;
    }
    
}
