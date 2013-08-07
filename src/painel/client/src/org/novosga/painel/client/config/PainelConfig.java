package org.novosga.painel.client.config;

import java.io.File;
import org.novosga.painel.config.ConfigParameter;
import org.novosga.painel.config.AppConfig;
import java.util.ArrayList;
import java.util.List;
import org.novosga.painel.client.Main;
import org.novosga.painel.util.FileUtils;

/**
 * @author rogeriolino
 */
public class PainelConfig extends AppConfig {
    
    public static final String APP_NAME = "PainelSGA";
    public static final String FILE_NAME = "painel.conf";

    public static final String KEY_LANGUAGE = "Language";
    public static final String KEY_TIMEOUT_UDP = "UnidadeId";
    public static final String KEY_UNIDADE = "UnidadeId";
    public static final String KEY_SERVICOS = "Servicos";
    public static final String KEY_SERVER = "IPServidor";
    public static final String KEY_MONITOR_ID = "VideoID";
    public static final String KEY_MAIN_LAYOUT = "MainLayout";
    public static final String KEY_SCREENSAVER_TIMEOUT = "ScreensaverTimeout";
    public static final String KEY_SCREENSAVER_URL = "ScreensaverUrl";
    public static final String KEY_SCREENSAVER_LAYOUT = "ScreensaverLayout";
    public static final String KEY_PROTOCOL = "Procolo";
    public static final String KEY_PORT_SEND = "PortaEnvio";
    public static final String KEY_PORT_RECEIVE = "PortaRecebimento";
    public static final String KEY_SOUND_ALERT = "Som";
    public static final String KEY_SOUND_VOICE = "Vocalizar";
    public static final String KEY_COR_FUNDO = "CorFundo";
    public static final String KEY_COR_MENSAGEM = "CorMensagem";
    public static final String KEY_COR_SENHA = "CorSenha";
    public static final String KEY_COR_GUICHE = "CorGuiche";
    public static final String KEY_TAMANHO_NUMERO = "TamanhoNumero";
    public static final String KEY_JFX_AUDIO = "JFXAudio";
    
    private List<ConfigParameter> parameters;
    {
        parameters = new ArrayList<>();
        parameters.add(new ConfigParameter<>(KEY_LANGUAGE, "pt"));
        parameters.add(new ConfigParameter<>(KEY_UNIDADE, 0));
        parameters.add(new ConfigParameter<>(KEY_SERVICOS, new Integer[]{0}));
        parameters.add(new ConfigParameter<>(KEY_SERVER, ""));
        parameters.add(new ConfigParameter<>(KEY_MONITOR_ID, 0));
        parameters.add(new ConfigParameter<>(KEY_MAIN_LAYOUT, 1));
        parameters.add(new ConfigParameter<>(KEY_SCREENSAVER_TIMEOUT, 30));
        parameters.add(new ConfigParameter<>(KEY_SCREENSAVER_URL, new File("data/media/video/promo1.mp4").toURI().toString()));
        parameters.add(new ConfigParameter<>(KEY_SCREENSAVER_LAYOUT, 1));
        parameters.add(new ConfigParameter<>(KEY_PROTOCOL, Main.DEFAULT_PROTOCOL));
        parameters.add(new ConfigParameter<>(KEY_PORT_SEND, Main.DEFAULT_SEND_PORT));
        parameters.add(new ConfigParameter<>(KEY_PORT_RECEIVE, Main.DEFAULT_RECEIVE_PORT));
        parameters.add(new ConfigParameter<>(KEY_SOUND_ALERT, "alert.wav"));
        parameters.add(new ConfigParameter<>(KEY_SOUND_VOICE, Boolean.TRUE));
        parameters.add(new ConfigParameter<>(KEY_COR_FUNDO, "#0055a3"));
        parameters.add(new ConfigParameter<>(KEY_COR_MENSAGEM, "#ffffff"));
        parameters.add(new ConfigParameter<>(KEY_COR_SENHA, "#eeff00"));
        parameters.add(new ConfigParameter<>(KEY_COR_GUICHE, "#ffffff"));
        parameters.add(new ConfigParameter<>(KEY_TAMANHO_NUMERO, 3));
        parameters.add(new ConfigParameter<>(KEY_JFX_AUDIO, Boolean.TRUE));
    }

    @Override
    protected String filename() {
        return FILE_NAME;
    }

    @Override
    protected File dir() {
        return FileUtils.workingDirectory(APP_NAME);
    }

    @Override
    protected List<ConfigParameter> parameters() {
        return parameters;
    }
    
}
