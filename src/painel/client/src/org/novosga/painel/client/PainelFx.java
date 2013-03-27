package org.novosga.painel.client;

import org.novosga.painel.client.config.PainelConfig;
import org.novosga.painel.model.Senha;
import java.util.Calendar;
import java.util.LinkedList;
import java.util.List;
import java.util.Queue;
import java.util.concurrent.ConcurrentLinkedQueue;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.animation.KeyFrame;
import javafx.animation.TimelineBuilder;
import javafx.application.Application;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.event.EventHandler;
import javafx.scene.Scene;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.Pane;
import javafx.stage.Screen;
import javafx.stage.Stage;
import javafx.stage.StageStyle;
import javafx.stage.WindowEvent;
import javafx.util.Duration;
import org.novosga.painel.client.display.Display;
import org.novosga.painel.client.layout.Layout;
import org.novosga.painel.client.layout.SenhaLayout;
import org.novosga.painel.client.layout.ScreensaverLayout;
import org.novosga.painel.client.layout.SimpleSenhaLayout;
import org.novosga.painel.client.layout.VideoLayout;
import org.novosga.painel.client.media.AudioPlayer;

/**
 *
 * @author rogeriolino
 */
public class PainelFx extends Application {
    
    private static final int MAX_SENHAS = 10;
    private static final Logger LOG = Logger.getLogger(PainelFx.class.getName());
    
    private Main main;
    private Layout currentLayout;
    private SenhaLayout senhaLayout;
    private ScreensaverLayout screenSaverLayout;
    private Display display;
    private Stage stage;
    private long lastUpdate;
    private Senha senha;
    private List<Senha> senhas = new LinkedList<Senha>();
    private Queue<Senha> bufferChamada = new ConcurrentLinkedQueue<Senha>();
    private final PainelFx self;
    
    public PainelFx(Main main) {
        self = this;
        this.main = main;
    }
    
    public Display getDisplay() {
        return display;
    }

    public Senha getSenha() {
        return senha;
    }

    public List<Senha> getSenhas() {
        return senhas;
    }

    public Main getMain() {
        return main;
    }
    
    @Override
    public void start(final Stage stage) throws Exception {
        this.stage = stage;
        stage.initStyle(StageStyle.UNDECORATED);
        stage.setTitle("PainelFX");
        stage.setOnCloseRequest(new EventHandler<WindowEvent>() {
            @Override
            public void handle(WindowEvent t) {
                self.hide();
            }
        });
        
        // loop infinito
        TimelineBuilder.create().keyFrames(new KeyFrame(Duration.millis(100), new EventHandler<ActionEvent>() {
            @Override
            public void handle(ActionEvent t) {
                long time = Calendar.getInstance().getTimeInMillis();
                // processando fila de senha
                processQueue();
                // verificando screensaver
                if (currentLayout != null && !currentLayout.equals(screenSaverLayout)) {
                    Integer screenSaverTimeout = main.getConfig().get(PainelConfig.KEY_SCREENSAVER_TIMEOUT, Integer.class).getValue();
                    if (screenSaverTimeout > 0) {
                        if (time - lastUpdate > screenSaverTimeout * 1000) {
                            self.changeLayout(screenSaverLayout);
                        }
                    }
                }
            }
        })).cycleCount(-1).build().play();

        detectScreen();
        senhaLayout = new SimpleSenhaLayout(this);
        screenSaverLayout = new VideoLayout(this);
    }
    
    private void detectScreen() {
        Screen screen = Screen.getPrimary();
        ObservableList<Screen> screens = Screen.getScreens();
        Integer screenId = main.getConfig().get(PainelConfig.KEY_MONITOR_ID, Integer.class).getValue();
        if (screenId > 0 && screens.size() > screenId) {
            screen = screens.get(screenId);
        }
        display = new Display(screen);
    }
    
    public void show() {
        if (!stage.isShowing()) {
            detectScreen();
            changeLayout(senhaLayout);
            stage.show();
        }
    }
    
    public void hide() {
        // destruindo o layout anterior
        if (currentLayout != null) {
            currentLayout.destroy();
        }
        stage.hide();
    }
    
    public void changeLayout(Layout layout) {
        // destruindo o layout anterior
        if (currentLayout != null) {
            currentLayout.destroy();
        }
        currentLayout = layout;
        Pane root = currentLayout.create();
        root.setOnMouseClicked(new EventHandler<MouseEvent>() {
            @Override
            public void handle(MouseEvent t) {
                if (t.isMiddleButtonDown() || t.getButton().ordinal() == 2) {
                    self.hide();
                }
            }
        });
        root.setId("root");
        currentLayout.applyTheme();
        Scene scene = new Scene(root, display.getWidth(), display.getHeight());
        scene.getStylesheets().add(PainelFx.class.getResource("style.css").toExternalForm());
        stage.setScene(scene);
        update();
    }
    
    public void chamaSenha(final Senha senha) {
        this.senha = senha;
        if (!senhas.contains(senha)) {
            senhas.add(senha);
            if (senhas.size() > MAX_SENHAS * 2) {
                senhas = senhas.subList(senhas.size() - MAX_SENHAS, senhas.size());
            }
        }
        // adiciona senha na fila para ser processada depois
        bufferChamada.add(senha);
        LOG.info("Adicionada senha na fila de processamento: " + senha.toString());
    }
    
    /**
     * Processa a senha da fila, dando um tempo entre as chamadas
     */
    private void processQueue() {
        // se vocalizar estiver ativo, espera mais
        int duration = (main.getConfig().get(PainelConfig.KEY_SOUND_VOICE, Boolean.class).getValue()) ? 6000 : 3000;
        long time = Calendar.getInstance().getTimeInMillis();
        if (time - lastUpdate > duration) {
            try {
                Senha senha = bufferChamada.remove();
                LOG.info("Processando senha: " + senha.toString());
                // volta para o layout de exibição de senha
                if (currentLayout == null || !currentLayout.equals(senhaLayout)) {
                    changeLayout(senhaLayout);
                }
                playAlert(senha);
                senhaLayout.onSenha(senha);
                lastUpdate = Calendar.getInstance().getTimeInMillis();
            } catch (Exception e) {
            }
        }
    }
    
    private void playAlert(Senha senha) {
        AudioPlayer player = AudioPlayer.getInstance();
        PainelConfig config = main.getConfig();
        player.play(config.get(PainelConfig.KEY_SOUND_ALERT).getValue());
        if (config.get(PainelConfig.KEY_SOUND_VOICE, Boolean.class).getValue()) {
            try {
                String lang = config.get(PainelConfig.KEY_LANGUAGE).getValue();
                player.getVocalizador().vocalizar("senha", lang, true);
                player.getVocalizador().vocalizar(String.valueOf(senha.getSigla()), lang, true);
                String numero = String.valueOf(senha.getNumero());
                for (int i = 0; i < numero.length(); i++) {
                    player.getVocalizador().vocalizar(String.valueOf(numero.charAt(i)), lang, true);
                }
                player.getVocalizador().vocalizar("guiche", lang, true);
                numero = String.valueOf(senha.getNumeroGuiche());
                for (int i = 0; i < numero.length(); i++) {
                    player.getVocalizador().vocalizar(String.valueOf(numero.charAt(i)), lang, true);
                }
            } catch (Exception e1) {
                LOG.log(Level.SEVERE, Main._("erro_vocalizacao"), e1);
            }
        }
    }
    
    private void update() {
        stage.setX(display.getX());
        stage.setY(display.getY());
        stage.setWidth(display.getWidth());
        stage.setHeight(display.getHeight());
        currentLayout.update();
        lastUpdate = Calendar.getInstance().getTimeInMillis();
    }
    
}
