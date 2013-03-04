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
    private boolean isPlaying = false;

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
            MediaPlayer mp = new MediaPlayer(new Media(f.toURI().toString()));
            mp.setAutoPlay(false);
            if (wait) {
                medias.add(mp);
                mp.setOnEndOfMedia(new Runnable() {
                    @Override
                    public void run() {
                        AudioPlayer.getInstance().isPlaying = false;
                        AudioPlayer.getInstance().playNext();
                    }
                });
                playNext();
            } else {
                mp.play();
            }
        }
    }
    
    private void playNext() {
        if (!isPlaying) {
            MediaPlayer mp = medias.poll();
            if (mp != null) {
                isPlaying = true;
                mp.play();
            } else {
                isPlaying = false;
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
