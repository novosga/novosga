package org.novosga.painel.client.media;

import java.io.File;
import java.util.Queue;
import java.util.concurrent.ConcurrentLinkedQueue;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.scene.media.AudioClip;
import javafx.scene.media.Media;
import javafx.scene.media.MediaPlayer;
import javafx.scene.media.MediaPlayerBuilder;
import static org.novosga.painel.client.media.AudioPlayer.ALERT_PATH;
import static org.novosga.painel.client.media.AudioPlayer.VOICE_EXT;
import static org.novosga.painel.client.media.AudioPlayer.VOICE_PATH;
import org.novosga.painel.model.Senha;

/**
 * Implementação do AudioPlayer utilizando a biblioteca de audio do JavaFX
 * 
 * @author rogeriolino
 */
public class JFXAudioPlayer extends AudioPlayer {

    private static final Logger LOG = Logger.getLogger(JFXAudioPlayer.class.getName());
    
    private MediaPlayer audio;
    private final MediaPlayerBuilder builder;
    private double volume = 1.0;
    private final Queue<String> audios = new ConcurrentLinkedQueue<>();
    private final Runnable playNext;
    
    protected JFXAudioPlayer() {
        builder = MediaPlayerBuilder.create();
        playNext = new Runnable() {
            @Override
            public void run() {
                if (audio != null) {
                    audio.stop();
                    audio.onEndOfMediaProperty().unbind();
                    audio.onErrorProperty().unbind();
                    audio.onReadyProperty().unbind();
                    audio = null;
                }
                play();
            }
        };
    }

    @Override
    protected void alert(String alert) {
        AudioClip a = new AudioClip(new File(ALERT_PATH + "/" + alert).toURI().toString());
        a.setVolume(volume);
        a.play();
    }
    
    @Override
    public void call(Senha senha, String alert, boolean speech, String lang) {
        doCall(senha, alert, speech, lang);
        play();
    }

    @Override
    public void speech(String text, String lang) {
        text = text.toLowerCase();
        String filename = VOICE_PATH + "/" + lang + "/" + text + "." + VOICE_EXT;
        add(filename);
    }

    private void play() {
        if (!audios.isEmpty()) {
            try {
                if (audio == null || !audio.getStatus().equals(MediaPlayer.Status.PLAYING)) {
                    String filename = audios.remove();
                    audio = builder
                            .media(new Media(new File(filename).toURI().toString()))
                            .volume(volume)
                            .onEndOfMedia(playNext)
                            .onError(playNext)
                            .onReady(new Runnable() {
                                @Override
                                public void run() {
                                    audio.play();
                                }
                            })
                            .cycleCount(1)
                            .autoPlay(false)
                            .build();
                    LOG.log(Level.INFO, "Tocando arquivo: {0}. Na fila: {1}", new Object[]{filename, audios.size()});
                }
            } catch (Exception e) {
                LOG.log(Level.SEVERE, "Erro ao tentar tocar audio: " + e.getMessage(), e);
                playNext.run();
            }
        }
    }

    private JFXAudioPlayer add(String filename) {
        // XXX: retirada a verificacao do arquivo porque estava lancando execao: To many open files
        audios.add(filename);
        return this;
    }
    
}
