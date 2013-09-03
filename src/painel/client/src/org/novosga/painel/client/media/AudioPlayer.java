package org.novosga.painel.client.media;

import java.util.logging.Level;
import java.util.logging.Logger;
import org.novosga.painel.client.Main;
import org.novosga.painel.model.Senha;

/**
 *
 * @author rogeriolino
 */
public abstract class AudioPlayer {
    
    private static final Logger LOG = Logger.getLogger(AudioPlayer.class.getName());
    
    public static final String AUDIO_PATH = "data/media/audio/";
    public static final String ALERT_PATH = AUDIO_PATH + "alert";
    public static final String VOICE_EXT = "wav";
    public static final String VOICE_PATH = AUDIO_PATH + "voice";
        
    private static AudioPlayer instance;

    public static synchronized AudioPlayer getInstance(boolean jfxLib) {
        if (instance == null) {
            if (jfxLib) {
                instance = new JFXAudioPlayer();
                LOG.info("Instanciado AudioPlayer utilizando JavaFX lib");
            } else {
                instance = new NativeAudioPlayer();
                LOG.info("Instanciado AudioPlayer utilizando Java nativo");
            }
        }
        return instance;
    }
    
    protected abstract void alert(String alert);
    protected abstract void speech(String text, String lang);
    
    public void call(final Senha senha, final String alert, final boolean speech, final String lang) {
        doCall(senha, alert, speech, lang);
    }
    
    protected final void doCall(Senha senha, String alert, boolean speech, String lang) {
        alert(alert);
        if (speech) {
            try {
                speech("senha", lang);
                speech(senha.getSigla(), lang);
                String numero = senha.getNumeroAsString();
                for (int i = 0; i < numero.length(); i++) {
                    speech(numero.charAt(i), lang);
                }
                speech("guiche", lang);
                numero = String.valueOf(senha.getNumeroGuiche());
                for (int i = 0; i < numero.length(); i++) {
                    speech(numero.charAt(i), lang);
                }
            } catch (Exception e1) {
                LOG.log(Level.SEVERE, Main._("erro_vocalizacao"), e1);
            }
        }
    }
    
    public void speech(char c, String lang) {
        speech(String.valueOf(c), lang);
    }
    
}
