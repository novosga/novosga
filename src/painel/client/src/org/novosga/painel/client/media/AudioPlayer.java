package org.novosga.painel.client.media;

import java.io.File;
import java.util.Queue;
import java.util.concurrent.ConcurrentLinkedQueue;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.animation.KeyFrame;
import javafx.animation.TimelineBuilder;
import javafx.event.ActionEvent;
import javafx.event.EventHandler;
import javafx.scene.media.AudioClip;
import javafx.util.Duration;
import org.novosga.painel.client.Main;
import org.novosga.painel.model.Senha;


/**
 * @author ulysses
 * @author rogeriolino
 */
public class AudioPlayer {
    
    private static final Logger LOG = Logger.getLogger(AudioPlayer.class.getName());
    
    public static final String AUDIO_PATH = "media/audio/";
    public static final String ALERT_PATH = AUDIO_PATH + "alert";
    public static final String VOICE_EXT = "mp3";
    public static final String VOICE_PATH = AudioPlayer.AUDIO_PATH + "voice";
    private AudioClip audio = null;
    private static final Queue<File> audios = new ConcurrentLinkedQueue<File>();
    
    private static AudioPlayer instance;

    private AudioPlayer() {
        // loop infinito (audio)
        TimelineBuilder.create().keyFrames(new KeyFrame(Duration.millis(100), new EventHandler<ActionEvent>() {
            @Override
            public void handle(ActionEvent t) {
                processAudio();
            }
        })).cycleCount(-1).build().play();
    }

    public static AudioPlayer getInstance() {
        if (instance == null) {
            instance = new AudioPlayer();
        }
        return instance;
    }
    
    private void processAudio() {
        try {
            if (audio == null || !audio.isPlaying()) {
                File f = audios.remove();
                String url = f.toURI().toString();
                audio = new AudioClip(url);
                audio.play();
            }
        } catch (Exception e) {
        }
    }
    
    public void call(Senha senha, String alert, boolean speech, String lang) {
        AudioPlayer player = AudioPlayer.getInstance();
        player.alert(alert);
        if (speech) {
            try {
                player.speech("senha", lang);
                player.speech(senha.getSigla(), lang);
                String numero = String.valueOf(senha.getNumero());
                for (int i = 0; i < numero.length(); i++) {
                    player.speech(numero.charAt(i), lang);
                }
                player.speech("guiche", lang);
                numero = String.valueOf(senha.getNumeroGuiche());
                for (int i = 0; i < numero.length(); i++) {
                    player.speech(numero.charAt(i), lang);
                }
            } catch (Exception e1) {
                LOG.log(Level.SEVERE, Main._("erro_vocalizacao"), e1);
            }
        }
    }
    
    public void alert(String alert) {
        this.play(ALERT_PATH, alert);
    }
    
    public void speech(String text, String lang) throws Exception {
        text = text.toLowerCase();
        File f = new File(VOICE_PATH + "/" + lang, text + "." + VOICE_EXT);
        if (!f.exists()) {
            throw new Exception("Impossivel vocalizar " + text + ", o arquivo (" + f.getAbsolutePath() + ") não existe.");
        } else {
            AudioPlayer.getInstance().play(f);
        }
    }
    
    public void speech(char c, String lang) throws Exception {
        File f = new File(VOICE_PATH + "/" + lang, c + "." + VOICE_EXT);
        if (!f.exists()) {
            throw new Exception("Impossivel vocalizar " + c + ", o arquivo (" + f.getAbsolutePath() + ") não existe.");
        } else {
            AudioPlayer.getInstance().play(f);
        }
    }

    public void play(String baseDir, String filename) {
        this.play(new File(baseDir, filename));
    }

    public void play(final File f) {
        if (!f.exists()) {
            LOG.severe("Erro ao tocar (" + f.getAbsolutePath() + ", arquivo não existe.");
        } else {
            audios.add(f);
        }
    }
    
}
