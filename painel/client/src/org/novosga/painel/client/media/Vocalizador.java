package org.novosga.painel.client.media;

import java.io.File;
import java.util.logging.Logger;

/**
 * @author ulysses
 */
public class Vocalizador {

    private static final Logger LOG = Logger.getLogger(Vocalizador.class.getName());
    public static final String VOICE_PATH = AudioPlayer.AUDIO_PATH + "voice";

    public void vocalizar(String str, boolean wait) throws Exception {
        str = str.toLowerCase();
        File f = new File(VOICE_PATH, str + ".wav");
        if (!f.exists()) {
            throw new Exception("Impossivel vocalizar " + str + ", o arquivo (" + f.getAbsolutePath() + ") não existe.");
        } else {
            AudioPlayer.getInstance().play(f, wait);
        }
    }
}
