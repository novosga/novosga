package org.novosga.painel.client.media;

import java.io.File;

/**
 * @author ulysses
 */
public class Vocalizador {

    public static final String EXT = "mp3";
    public static final String VOICE_PATH = AudioPlayer.AUDIO_PATH + "voice";

    public void vocalizar(String text, String lang, boolean wait) throws Exception {
        text = text.toLowerCase();
        File f = new File(VOICE_PATH + "/" + lang, text + "." + EXT);
        if (!f.exists()) {
            throw new Exception("Impossivel vocalizar " + text + ", o arquivo (" + f.getAbsolutePath() + ") não existe.");
        } else {
            AudioPlayer.getInstance().play(f, wait);
        }
    }
}
