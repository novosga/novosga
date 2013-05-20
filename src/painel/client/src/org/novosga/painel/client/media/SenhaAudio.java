package org.novosga.painel.client.media;

import org.novosga.painel.model.Senha;

/**
 *
 * @author rogeriolino
 */
public class SenhaAudio {
    
    private Senha senha;
    private String alert;
    private boolean speech;
    private String lang;

    public SenhaAudio(Senha senha, String alert) {
        this.senha = senha;
        this.alert = alert;
        this.speech = false;
    }

    public SenhaAudio(Senha senha, String alert, String lang) {
        this.senha = senha;
        this.alert = alert;
        this.lang = lang;
        this.speech = true;
    }

    public Senha getSenha() {
        return senha;
    }

    public String getAlert() {
        return alert;
    }

    public boolean isSpeech() {
        return speech;
    }

    public String getLang() {
        return lang;
    }
    
}
