package org.novosga.painel.client;

import java.awt.SystemTray;
import java.net.MalformedURLException;
import java.net.URL;
import org.novosga.painel.client.config.PainelConfig;
import org.novosga.painel.model.Senha;
import java.util.Calendar;
import java.util.LinkedList;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.animation.KeyFrame;
import javafx.animation.TimelineBuilder;
import javafx.application.Application;
import javafx.application.Platform;
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
import org.novosga.painel.client.layout.HVideoLayout;
import org.novosga.painel.client.layout.Layout;
import org.novosga.painel.client.layout.SenhaLayout;
import org.novosga.painel.client.layout.ScreensaverLayout;
import org.novosga.painel.client.layout.SimpleSenhaLayout;
import org.novosga.painel.client.layout.VVideoLayout;
import org.novosga.painel.client.media.AudioPlayer;

/**
 *
 * @author rogeriolino
 */
public class PainelFx extends Application {
    
    private static final int MAX_SENHAS = 10;
    private static final Logger LOG = Logger.getLogger(PainelFx.class.getName());
    
    private Main main;
    private Scene scene;
    private Layout currentLayout;
    private SenhaLayout senhaLayout;
    private ScreensaverLayout screenSaverLayout;
    private Display display;
    private Stage stage;
    private long lastUpdate;
    private Senha senha;
    private List<Senha> senhas = new LinkedList<>();
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
        TimelineBuilder.create().keyFrames(new KeyFrame(Duration.millis(1000), new EventHandler<ActionEvent>() {
            @Override
            public void handle(ActionEvent t) {
                long time = Calendar.getInstance().getTimeInMillis();
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
        screenSaverLayout = createScreeSaverLayout();
    }
    
    private ScreensaverLayout createScreeSaverLayout() {
        int id = main.getConfig().get(PainelConfig.KEY_SCREENSAVER_LAYOUT, Integer.class).getValue();
        switch (id) {
            case 1:
                return new HVideoLayout(this);
            case 2:
                return new VVideoLayout(this);
            default:
                throw new RuntimeException("Nenhum layout definido para o id " + id);
        }
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

    public Stage getStage() {
        return stage;
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
        // se nao tiver suporte a tray, minimiza
        if (SystemTray.isSupported()) {
            stage.hide();
        } else {
            stage.setIconified(true);
        }
    }
    
    public void changeLayout(Layout layout) {
        // destruindo o layout anterior
        if (currentLayout != null) {
            currentLayout.destroy();
        }
        currentLayout = layout;
        Pane root = currentLayout.create();
        if (root.getOnMouseClicked() == null) {
            root.setOnMouseClicked(new EventHandler<MouseEvent>() {
                @Override
                public void handle(MouseEvent t) {
                    if (t.isMiddleButtonDown() || t.getButton().ordinal() == 2) {
                        self.hide();
                    }
                }
            });
        }
        if (root.getId() == null || root.getId().isEmpty()) {
            root.setId("root");
        }
        currentLayout.applyTheme();
        if (scene == null) {
            scene = new Scene(root, display.getWidth(), display.getHeight());
            try {
                scene.getStylesheets().add(new URL("file:data/ui/css/style.css").toExternalForm());
            } catch (MalformedURLException e) {
                LOG.severe("Arquivo css não encontrado");
            }
        } else {
            scene.setRoot(root);
        }
        stage.setScene(scene);
        update();
    }
    
    public void chamaSenha(final Senha senha) {
        try {
            Integer tamanho = main.getConfig().get(PainelConfig.KEY_TAMANHO_NUMERO, Integer.class).getValue();
            senha.setTamanhoNumero(tamanho);
        } catch (NumberFormatException e) {
            LOG.log(Level.SEVERE, "Erro ao definir tamanho da senha. O valor precisa ser um numero inteiro. Verifique o arquivo de configuracao.");
        }
        if (!senhas.contains(senha)) {
            senhas.add(senha);
            if (senhas.size() > MAX_SENHAS * 2) {
                senhas = senhas.subList(senhas.size() - MAX_SENHAS, senhas.size());
            }
        } else {
            // readiciona a senha para ficar no final do historico
            senhas.remove(senha);
            senhas.add(senha);
        }
        final boolean audioFx = main.getConfig().get(PainelConfig.KEY_JFX_AUDIO, Boolean.class).getValue();
        Platform.runLater(new Runnable() {
            @Override
            public void run() {
                // volta para o layout de exibição de senha
                if (currentLayout == null || !currentLayout.equals(senhaLayout)) {
                    changeLayout(senhaLayout);
                }
                senhaLayout.onSenha(senha);
                if (audioFx) {
                    playAlert(senha);
                }
            }
        });
        if (!audioFx) {
            playAlert(senha);
        }
        try {
            int duration = 3000;
            // se vocalizar estiver ativo, espera mais tempo (relativo ao tamanho da senha)
            if (main.getConfig().get(PainelConfig.KEY_SOUND_VOICE, Boolean.class).getValue()) {
                duration += 3000; // palavras "senha" e "guiche"
                // tamanho da ultima senha
                if (this.senha != null) {
                    duration += 500 * String.valueOf(this.senha.getNumero()).length();
                }
            }
            Thread.sleep(duration);
        } catch (Exception e) {
        }
        this.senha = senha;
        lastUpdate = Calendar.getInstance().getTimeInMillis();
    }
    
    private void playAlert(Senha senha) {
        PainelConfig config = main.getConfig();
        String alert = config.get(PainelConfig.KEY_SOUND_ALERT).getValue();
        boolean speech = config.get(PainelConfig.KEY_SOUND_VOICE, Boolean.class).getValue();
        String lang = config.get(PainelConfig.KEY_LANGUAGE).getValue();
        boolean jfx = main.getConfig().get(PainelConfig.KEY_JFX_AUDIO, Boolean.class).getValue();
        AudioPlayer.getInstance(jfx).call(senha, alert, speech, lang);
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
