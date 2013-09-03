package org.novosga.painel.client;

import java.awt.SplashScreen;
import org.novosga.painel.client.config.PainelConfig;
import org.novosga.painel.model.Senha;
import org.novosga.painel.client.ui.SysTray;
import java.io.FileInputStream;
import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.text.MessageFormat;
import java.util.HashMap;
import java.util.Locale;
import java.util.Map;
import java.util.ResourceBundle;
import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.Logger;
import javafx.application.Application;
import javafx.application.Platform;
import javafx.scene.image.Image;
import javafx.stage.Stage;
import javax.swing.JOptionPane;
import org.novosga.painel.client.fonts.FontLoader;
import org.novosga.painel.client.ui.Controller;
import org.novosga.painel.client.network.PacketListener;
import org.novosga.painel.client.network.PacketListenerFactory;
import org.novosga.painel.client.network.SimpleThreadFactory;
import org.novosga.painel.event.SenhaEvent;

/**
 * Novo SGA Painel Main class
 * @author rogeriolino
 */
public class Main extends Application {
    
    private static final Logger LOG = Logger.getLogger(Main.class.getName());
    private static final ResourceBundle bundle = ResourceBundle.getBundle("org.novosga.painel.i18n.messages", Locale.getDefault());
    public static final Map<String,String> locales = new HashMap<>();
    public static final Map<Integer,String> intervals = new HashMap<>();
    {
        locales.put("en", "English");
        locales.put("es", "Español");
        locales.put("pt", "Português");
        
        intervals.put(0, _("nao_exibir_filme"));
        intervals.put(10, _("segundos", 10));
        intervals.put(30, _("segundos", 30));
        intervals.put(45, _("segundos", 45));
        intervals.put(60, _("minutos", 1));
        intervals.put(120, _("minutos", 2));
        intervals.put(180, _("minutos", 3));
    }
    
    public static final String version = "{version}";
    public static final String DEFAULT_PROTOCOL = "UDP";
    public static final int DEFAULT_RECEIVE_PORT = 8888;
    public static final int DEFAULT_SEND_PORT = 9999;
    
    private PacketListener listener;
    private PainelService service;
    private PainelFx painel;
    private Controller controller;
    private PainelConfig config;

    @Override
    public void start(Stage stage) throws Exception {
        LOG.log(Level.INFO, "Iniciando PaineFX...");
        
        Platform.setImplicitExit(false);
        config = new PainelConfig();
        
        try {
            // Carregando configurações do Logger
            LogManager.getLogManager().readConfiguration(new FileInputStream("data/log/logger.properties"));
        } catch (IOException | SecurityException e) {
            LOG.log(Level.SEVERE, _("erro_carregando_log"), e);
        }
        
        // Carrega a configuracao do painel
        boolean configOk = config.load();
        
        FontLoader.registerAll();
        
        // Inicia o servidor para receber mensagens
        String protocol = config.get(PainelConfig.KEY_PROTOCOL).getValue();
        try {
            painel = new PainelFx(this);
            Stage painelStage = new Stage();
            painelStage.initOwner(stage);
            painel.start(painelStage);
            
            listener = PacketListenerFactory.create(
                    protocol, 
                    config.get(PainelConfig.KEY_PORT_RECEIVE, Integer.class).getValue(),
                    config.get(PainelConfig.KEY_PORT_SEND, Integer.class).getValue(),
                    config.get(PainelConfig.KEY_SERVER).getValue()
            );
            listener.setOnNovaSenhaEvent(new SenhaEvent() {
                @Override
                public void handle(Senha senha) {
                    painel.chamaSenha(senha);
                }
            });
            
            listener.inicia();
            
            service = new PainelService(this);
            controller = new Controller(this, bundle);
            controller.getStage().initOwner(stage);
            if (!listener.getServer().isEmpty()) {
                try {
                    service.registerAndLoad(listener.getServer(), new Runnable() {
                        @Override
                        public void run() {
                            Platform.runLater(new Runnable() {
                                @Override
                                public void run() {
                                    controller.update();
                                }
                           });
                        }
                    });
                } catch (Exception e) {
                    LOG.log(Level.SEVERE, "Erro ao registrar painel: " + e.getMessage(), e);
                }
            }
            
            if (configOk) {
                painel.show();
            } else {
                controller.show();
            }
            try {
                URL iconUrl = new URL("file:data/ui/img/tray.png");
                // adicionando icone as janelas
                Image icon = new Image(iconUrl.toExternalForm());
                controller.getStage().getIcons().add(icon);
                painel.getStage().getIcons().add(icon);
                try {
                    // Adiciona o painel na banjeida do sistema
                    new SysTray(this, iconUrl);
                } catch (Exception e) {
                    LOG.warning(e.getMessage());
                }
            } catch (MalformedURLException e) {
                LOG.warning("Imagem do ícone das janelas e systray não encontrada!");
            }
        } catch (Exception e) {
            LOG.log(Level.SEVERE, e.getMessage(), e);
            final String message = e.getMessage();
            Platform.runLater(new Runnable() {
                @Override
                public void run() {
                    JOptionPane.showMessageDialog(null, message, _("erro"), JOptionPane.ERROR_MESSAGE);
                    System.exit(1);
                }
            });
        } finally {
            if (SplashScreen.getSplashScreen() != null) {
                SplashScreen.getSplashScreen().close();
            }
        }
        
        // agendando garbage collector a cada X minutos
        Executors.newScheduledThreadPool(1, new SimpleThreadFactory("GcThread")).scheduleAtFixedRate(new Runnable() {
            @Override
            public void run() {
                LOG.info("Executando garbage collector...");
                System.gc();
                LOG.info("Garbage collector executado!");
            }
        }, 0, 15, TimeUnit.MINUTES);
    }

    public PainelFx getPainel() {
        return painel;
    }

    public Controller getController() {
        return controller;
    }

    public PainelConfig getConfig() {
        return config;
    }

    public PacketListener getListener() {
        return listener;
    }

    public PainelService getService() {
        return service;
    }

    /**
     * The main() method is ignored in correctly deployed JavaFX application.
     * main() serves only as fallback in case the application can not be
     * launched through deployment artifacts, e.g., in IDEs with limited FX
     * support. NetBeans ignores main().
     *
     * @param args the command line arguments
     */
    public static void main(String[] args) {
        launch(args);
    }
    
    public static String _(String message, Object ...args) {
        if (bundle.containsKey(message)) {
            String s = bundle.getString(message);
            return MessageFormat.format(s, args);
        }
        return message;
    }
    
}
