package org.novosga.painel.client.config;

import java.io.File;
import org.novosga.painel.config.ConfigParameter;
import org.novosga.painel.config.AppConfig;
import java.util.ArrayList;
import java.util.List;
import org.novosga.painel.client.Main;

/**
 * @author rogeriolino
 */
public class PainelConfig extends AppConfig {

    public static final String KEY_LANGUAGE = "Language";
    public static final String KEY_TIMEOUT_UDP = "UnidadeId";
    public static final String KEY_UNIDADE = "UnidadeId";
    public static final String KEY_SERVICOS = "Servicos";
    public static final String KEY_SERVER = "IPServidor";
    public static final String KEY_MONITOR_ID = "VideoID";
    public static final String KEY_SCREENSAVER_TIMEOUT = "ScreensaverTimeout";
    public static final String KEY_SCREENSAVER_URL = "ScreensaverUrl";
    public static final String KEY_PROTOCOL = "Procolo";
    public static final String KEY_PORT_SEND = "PortaEnvio";
    public static final String KEY_PORT_RECEIVE = "PortaRecebimento";
    public static final String KEY_SOUND_ALERT = "Som";
    public static final String KEY_SOUND_VOICE = "Vocalizar";
    public static final String KEY_COR_FUNDO = "CorFundo";
    public static final String KEY_COR_MENSAGEM = "CorMensagem";
    public static final String KEY_COR_SENHA = "CorSenha";
    public static final String KEY_COR_GUICHE = "CorGuiche";
    
    private List<ConfigParameter> parameters;
    {
        parameters = new ArrayList<ConfigParameter>();
        parameters.add(new ConfigParameter<String>(KEY_LANGUAGE, "pt"));
        parameters.add(new ConfigParameter<Integer>(KEY_UNIDADE, 0));
        parameters.add(new ConfigParameter<Integer[]>(KEY_SERVICOS, new Integer[]{0}));
        parameters.add(new ConfigParameter<String>(KEY_SERVER, ""));
        parameters.add(new ConfigParameter<Integer>(KEY_MONITOR_ID, 0));
        parameters.add(new ConfigParameter<Integer>(KEY_SCREENSAVER_TIMEOUT, 30));
        parameters.add(new ConfigParameter<String>(KEY_SCREENSAVER_URL, new File("media/video/promo1.mp4").toURI().toString()));
        parameters.add(new ConfigParameter<String>(KEY_PROTOCOL, Main.DEFAULT_PROTOCOL));
        parameters.add(new ConfigParameter<Integer>(KEY_PORT_SEND, Main.DEFAULT_SEND_PORT));
        parameters.add(new ConfigParameter<Integer>(KEY_PORT_RECEIVE, Main.DEFAULT_RECEIVE_PORT));
        parameters.add(new ConfigParameter<String>(KEY_SOUND_ALERT, "alert.wav"));
        parameters.add(new ConfigParameter<Boolean>(KEY_SOUND_VOICE, true));
        parameters.add(new ConfigParameter<String>(KEY_COR_FUNDO, "#0055a3"));
        parameters.add(new ConfigParameter<String>(KEY_COR_MENSAGEM, "#ffffff"));
        parameters.add(new ConfigParameter<String>(KEY_COR_SENHA, "#eeff00"));
        parameters.add(new ConfigParameter<String>(KEY_COR_GUICHE, "#ffffff"));
    }

    @Override
    protected String filename() {
        return "painel.conf";
    }

    @Override
    protected List<ConfigParameter> parameters() {
        return parameters;
    }
    
    
}
