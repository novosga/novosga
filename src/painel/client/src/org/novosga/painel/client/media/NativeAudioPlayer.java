/**
 *
 * Copyright (C) 2009 DATAPREV - Empresa de Tecnologia e Informações da
 * Previdência Social - Brasil
 *
 * Este arquivo é parte do programa SGA Livre - Sistema de Gerenciamento do
 * Atendimento - Versão Livre
 *
 * O SGA é um software livre; você pode redistribuí­-lo e/ou modificá-lo dentro
 * dos termos da Licença Pública Geral GNU como publicada pela Fundação do
 * Software Livre (FSF); na versão 2 da Licença, ou (na sua opnião) qualquer
 * versão.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM NENHUMA
 * GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO ou
 * APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU para maiores
 * detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, escreva para a Fundação do
 * Software Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301
 * USA.
 *
 *
 */
package org.novosga.painel.client.media;

import java.io.File;
import java.io.IOException;
import java.util.logging.Level;
import java.util.logging.Logger;

import javax.sound.sampled.AudioFormat;
import javax.sound.sampled.AudioInputStream;
import javax.sound.sampled.AudioSystem;
import javax.sound.sampled.DataLine;
import javax.sound.sampled.LineUnavailableException;
import javax.sound.sampled.SourceDataLine;
import javax.sound.sampled.UnsupportedAudioFileException;

/**
 * Classe extraida do projeto SGA Livre para ser usada como fallback
 * em caso de problemas com a biblioteca de audio do JavaFX
 * 
 * @author ulysses
 * @author rogeriolino
 */
public class NativeAudioPlayer extends AudioPlayer {

    private static final Logger LOG = Logger.getLogger(NativeAudioPlayer.class.getName());
    
    private static final int BUFFER_SIZE = 64 * 1024;

    protected NativeAudioPlayer() {
    }
    
    @Override
    protected void alert(String alert) {
        play(ALERT_PATH + "/" + alert);
    }

    @Override
    protected void speech(String text, String lang) {
        text = text.toLowerCase();
        File f = new File(VOICE_PATH + "/" + lang + "/" + text + "." + VOICE_EXT);
        if (!f.exists()) {
            throw new RuntimeException("Impossivel vocalizar " + text + ", o arquivo (" + f.getAbsolutePath() + ") não existe.");
        } else {
            play(f, true);
        }
    }
    
    private void play(String filename) {
        this.play(filename, false);
    }

    private void play(String filename, boolean wait) {
        this.play(new File(filename), wait);
    }

    private void play(final File f, final boolean wait) {
        LOG.log(Level.INFO, "Playing ({0})...", f.getAbsolutePath());
        if (!f.exists()) {
            LOG.log(Level.SEVERE, "Erro ao tocar ({0}, arquivo n\u00e3o existe.", f.getAbsolutePath());
        } else {
            Playback playback = new Playback(f);
            if (wait) {
                // executa run neste thread
                playback.run();
            } else {
                // async
                playback.start();
            }
        }
    }

    private class Playback extends Thread {

        private final File _file;

        public Playback(File file) {
            _file = file;
        }

        @Override
        public void run() {
            AudioInputStream audioInputStream = null;
            try {
                audioInputStream = AudioSystem.getAudioInputStream(_file);
            } catch (UnsupportedAudioFileException | IOException e) {
                LOG.log(Level.WARNING, e.getMessage(), e);
                return;
            }

            AudioFormat format = audioInputStream.getFormat();
            SourceDataLine auline = null;
            DataLine.Info info = new DataLine.Info(SourceDataLine.class, format);

            try {
                auline = (SourceDataLine) AudioSystem.getLine(info);
                auline.open(format);
            } catch (LineUnavailableException e) {
                LOG.log(Level.WARNING, e.getMessage(), e);
                return;
            } catch (Exception e) {
                LOG.log(Level.WARNING, e.getMessage(), e);
                return;
            }

            auline.start();
            int nBytesRead = 0;
            byte[] abData = new byte[BUFFER_SIZE];

            try {
                while (nBytesRead != -1) {
                    nBytesRead = audioInputStream.read(abData, 0, abData.length);
                    if (nBytesRead >= 0) {
                        auline.write(abData, 0, nBytesRead);
                    }
                }
            } catch (IOException e) {
                LOG.log(Level.WARNING, e.getMessage(), e);
            } finally {
                auline.drain();
                auline.close();
            }
        }
    }
    
}