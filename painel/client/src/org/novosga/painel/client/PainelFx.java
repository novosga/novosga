package org.novosga.painel.client;

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
    
    public PainelFx(Main main) {
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
        final PainelFx self = this;
        this.stage = stage;
        stage.initStyle(StageStyle.UNDECORATED);
        stage.setTitle("PainelFX");
        stage.setOnCloseRequest(new EventHandler<WindowEvent>() {
            @Override
            public void handle(WindowEvent t) {
                self.hide();
            }
        });
        
        TimelineBuilder.create().keyFrames(new KeyFrame(Duration.seconds(1), new EventHandler<ActionEvent>() {
            @Override
            public void handle(ActionEvent t) {
                if (currentLayout != null && !currentLayout.equals(screenSaverLayout)) {
                    long time = Calendar.getInstance().getTimeInMillis();
                    // converting to milis
                    final Integer screenSaverTimeout = main.getConfig().get(PainelConfig.KEY_SCREENSAVER_TIMEOUT, Integer.class).getValue() * 60 * 1000;
                    if (time - lastUpdate > screenSaverTimeout) {
                        self.changeLayout(screenSaverLayout);
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
        Integer screenId = main.getConfig().get(PainelConfig.KEY_VIDEO_ID, Integer.class).getValue();
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
                    stage.hide();
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
        lastUpdate = Calendar.getInstance().getTimeInMillis();
        if (!senhas.contains(senha)) {
            senhas.add(senha);
            if (senhas.size() > MAX_SENHAS * 2) {
                senhas = senhas.subList(senhas.size() - MAX_SENHAS, senhas.size());
            }
        }
        // volta para o layout de exibição de senha
        if (currentLayout == null || !currentLayout.equals(senhaLayout)) {
            changeLayout(senhaLayout);
        }
        // layout
        senhaLayout.getMensagem().setText(senha.getMensagem());
        senhaLayout.getSenha().setText(senha.getSenha());
        senhaLayout.getNumeroGuiche().setText(senha.getNumeroGuicheAsString());
        senhaLayout.getGuiche().setText(senha.getGuiche());
        senhaLayout.onSenha(senha);
        // sound
        playAlert(senha);
    }
    
    public void playAlert(Senha senha) {
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
        //set the position to one of the "slave"-monitors:
        stage.setX(display.getX());
        stage.setY(display.getY());
        //set the dimesions to the screen size:
        stage.setWidth(display.getWidth());
        stage.setHeight(display.getHeight());
        currentLayout.update();
        lastUpdate = Calendar.getInstance().getTimeInMillis();
    }
    
}
