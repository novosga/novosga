package org.novosga.painel.client.ui;

import org.novosga.painel.client.config.PainelConfig;
import java.awt.SystemTray;
import java.io.IOException;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.Map.Entry;
import java.util.ResourceBundle;
import javafx.collections.FXCollections;
import javafx.event.ActionEvent;
import javafx.event.Event;
import javafx.event.EventHandler;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.geometry.Rectangle2D;
import javafx.scene.Node;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.control.CheckBox;
import javafx.scene.control.ColorPicker;
import javafx.scene.control.ComboBox;
import javafx.scene.control.ProgressIndicator;
import javafx.scene.control.TextField;
import javafx.scene.layout.Pane;
import javafx.scene.layout.VBox;
import javafx.scene.paint.Color;
import javafx.stage.Screen;
import javafx.stage.Stage;
import javafx.stage.WindowEvent;
import org.novosga.painel.client.Main;
import org.novosga.painel.util.ComboboxItem;

/**
 *
 * @author rogeriolino
 */
public class Controller implements Initializable {
    
    @FXML
    private Pane root;
    // config geral
    @FXML
    private TextField servidor;
    @FXML
    private ComboBox unidades;
    @FXML
    private Button buscar;
    @FXML
    private CheckBox checkTodos;
    @FXML
    private VBox servicos;
    @FXML
    private Button salvar;
    @FXML
    private Button exibirPainel;
    // tema
    @FXML
    private CheckBox vocalizar;
    @FXML
    private ComboBox language;
    @FXML
    private ComboBox videoId;
    @FXML
    private ComboBox screenSaverTimeout;
    @FXML
    private ColorPicker corFundo;
    @FXML
    private ColorPicker corMensagem;
    @FXML
    private ColorPicker corSenha;
    @FXML
    private ColorPicker corGuiche;
    
    @FXML
    private ProgressIndicator loading;
    
    private Main main;
    private Stage stage;
    private int unidadeAtual;

    public Controller(Main main, ResourceBundle bundle) throws IOException {
        this.main = main;
        URL location = getClass().getResource("main.fxml");
        //ResourceBundle resources = ResourceBundle.getBundle("com.foo.example");
        FXMLLoader fxmlLoader = new FXMLLoader(location, bundle);
        fxmlLoader.setController(this);
        fxmlLoader.load();
    }
    
    public void show() {
        stage.show();
        stage.requestFocus();
    }
    
    public void hide() {
        stage.hide();
    }

    public Stage getStage() {
        return stage;
    }

    public Pane getRoot() {
        return root;
    }
    
    public void update() {
        PainelConfig config = main.getConfig();
        // servicos
        servidor.setText(config.get(PainelConfig.KEY_SERVER).getValue());
        unidadeAtual = config.get(PainelConfig.KEY_UNIDADE, Integer.class).getValue();
        updateUnidades(main.getService().buscarUnidades());
        // som e tema
        vocalizar.setSelected(config.get(PainelConfig.KEY_SOUND_VOICE, Boolean.class).getValue());
        corFundo.setValue(Color.web(config.get(PainelConfig.KEY_COR_FUNDO).getValue()));
        corMensagem.setValue(Color.web(config.get(PainelConfig.KEY_COR_MENSAGEM).getValue()));
        corSenha.setValue(Color.web(config.get(PainelConfig.KEY_COR_SENHA).getValue()));
        corGuiche.setValue(Color.web(config.get(PainelConfig.KEY_COR_GUICHE).getValue()));
    }
        
    public void updateUnidades(Map<Integer,String> items) {
        unidades.getItems().clear();
        unidades.getItems().add(new ComboboxItem(0, "Selecione"));
        unidades.getSelectionModel().select(0);
        for (Map.Entry<Integer,String> entry : items.entrySet()) {
            ComboboxItem item = new ComboboxItem(entry.getKey(), entry.getValue());
            unidades.getItems().add(item);
            if (entry.getKey().equals(unidadeAtual)) {
                unidades.getSelectionModel().select(item);
                updateServicos(main.getService().buscarServicos(entry.getKey()));
            }
        }
    }
    
    public void updateServicos(Map<Integer,String> items) {
        servicos.getChildren().clear();
        for (Map.Entry<Integer,String> entry : items.entrySet()) {
            CheckBox checkbox = new CheckBox();
            checkbox.setId("servico-" + entry.getKey());
            checkbox.setText(entry.getValue());
            checkbox.setMinWidth((checkbox.getText().length() + 3) * checkbox.getFont().getSize());
            checkbox.setSelected(main.getConfig().get(PainelConfig.KEY_SERVICOS).is(entry.getKey()));
            servicos.getChildren().add(checkbox);
        }
    }

    @Override
    public void initialize(URL url, ResourceBundle rb) {
        loading.setVisible(false);
        checkTodos.setOnAction(new EventHandler<ActionEvent>() {
            @Override
            public void handle(ActionEvent t) {
                boolean checked = ((CheckBox) t.getTarget()).isSelected();
                for (Node node : servicos.getChildren()) {
                    ((CheckBox) node).setSelected(checked);
                }
            }
        });
        unidades.setItems(FXCollections.observableList(new ArrayList<ComboboxItem>()));
        unidades.setOnAction(new EventHandler() {
            @Override
            public void handle(Event t) {
                loading.setVisible(true);
                checkTodos.setSelected(false);
                ComboBox cb = (ComboBox) t.getTarget();
                cb.getSelectionModel().selectedItemProperty();
                ComboboxItem item = (ComboboxItem) cb.getSelectionModel().selectedItemProperty().getValue();
                if (item != null && Integer.parseInt(item.getKey()) > 0) {
                    unidadeAtual = Integer.parseInt(item.getKey());
                    updateServicos(main.getService().buscarServicos(unidadeAtual));
                }
                loading.setVisible(false);
            }
        });
        buscar.setOnAction(new EventHandler<ActionEvent>() {
            @Override
            public void handle(ActionEvent t) {
                loading.setVisible(true);
                servicos.getChildren().clear();
                unidadeAtual = 0;
                try {
                    main.getService().loadUrls(servidor.getText(), new Runnable() {
                        @Override
                        public void run() {
                            updateUnidades(main.getService().buscarUnidades());
                            loading.setVisible(false);
                        }
                    });
                } catch (Exception e) {
                    loading.setVisible(false);
                }
            }
        });
        salvar.setOnAction(new EventHandler<ActionEvent>() {
            @Override
            public void handle(ActionEvent t) {
                loading.setVisible(true);
                try {
                    List<Integer> idServicos = new ArrayList<Integer>();
                    for (Node node : servicos.getChildren()) {
                        if (((CheckBox) node).isSelected()) {
                            try {
                                String id = node.getId().split("-")[1];
                                idServicos.add(Integer.parseInt(id));
                                
                            } catch (Exception e) {
                            }
                        }
                    }
                    PainelConfig config = main.getConfig();
                    config.get(PainelConfig.KEY_SERVER).setValue(servidor.getText());
                    config.get(PainelConfig.KEY_UNIDADE, Integer.class).setValue(unidadeAtual);
                    config.get(PainelConfig.KEY_SERVICOS, Integer[].class).setValue(idServicos.toArray(new Integer[0]));
                    // som e tema
                    config.get(PainelConfig.KEY_LANGUAGE).setValue(((ComboboxItem) language.getSelectionModel().getSelectedItem()).getKey());
                    config.get(PainelConfig.KEY_SOUND_VOICE, Boolean.class).setValue(vocalizar.isSelected());
                    config.get(PainelConfig.KEY_COR_FUNDO).setValue(colorToHex(corFundo.getValue()));
                    config.get(PainelConfig.KEY_COR_MENSAGEM).setValue(colorToHex(corMensagem.getValue()));
                    config.get(PainelConfig.KEY_COR_SENHA).setValue(colorToHex(corSenha.getValue()));
                    config.get(PainelConfig.KEY_COR_GUICHE).setValue(colorToHex(corGuiche.getValue()));
                    config.save();
                    
                    main.getService().register(servidor.getText());
                } catch (Exception e) {
                    e.printStackTrace();
                }
                loading.setVisible(false);
            }
        });
        exibirPainel.setOnAction(new EventHandler<ActionEvent>() {
            @Override
            public void handle(ActionEvent t) {
                main.getPainel().show();
            }
        });
        // language
        language.setItems(FXCollections.observableList(new ArrayList<ComboboxItem>()));
        for (Entry<String,String> entry : Main.locales.entrySet()) {
            language.getItems().add(new ComboboxItem(entry.getKey(), entry.getValue()));
            if (Locale.getDefault().getLanguage().equals(entry.getKey())) {
                main.getConfig().get(PainelConfig.KEY_LANGUAGE).setValue(Locale.getDefault().getLanguage());
            }
        }
        String defaultLang = main.getConfig().get(PainelConfig.KEY_LANGUAGE).getValue();
        for (Object item : language.getItems()) {
            if (defaultLang.equals(((ComboboxItem) item).getKey())) {
                language.getSelectionModel().select(item);
                break;
            }
        }
        language.setOnAction(new EventHandler() {
            @Override
            public void handle(Event t) {
                ComboBox cb = (ComboBox) t.getTarget();
                cb.getSelectionModel().selectedItemProperty();
                ComboboxItem item = (ComboboxItem) cb.getSelectionModel().selectedItemProperty().getValue();
                main.getConfig().get(PainelConfig.KEY_LANGUAGE).setValue(item.getKey());
            }
        });
        // video
        videoId.setItems(FXCollections.observableList(new ArrayList<ComboboxItem>()));
        Integer defaultId = main.getConfig().get(PainelConfig.KEY_VIDEO_ID, Integer.class).getValue();
        for (int i = 0; i < Screen.getScreens().size(); i++) {
            StringBuilder sb = new StringBuilder();
            Rectangle2D b = Screen.getScreens().get(i).getBounds();
            sb.append(i + 1).append(" (").append(b.getWidth()).append(" x ").append(b.getHeight()).append(")");
            ComboboxItem item = new ComboboxItem(i, sb.toString());
            videoId.getItems().add(item);
            if (defaultId.equals(i)) {
                videoId.getSelectionModel().select(item);
            }
        }
        videoId.setOnAction(new EventHandler() {
            @Override
            public void handle(Event t) {
                ComboBox cb = (ComboBox) t.getTarget();
                cb.getSelectionModel().selectedItemProperty();
                ComboboxItem item = (ComboboxItem) cb.getSelectionModel().selectedItemProperty().getValue();
                Integer key = Integer.parseInt(item.getKey());
                if (key >= 0 && key < Screen.getScreens().size()) {
                    main.getConfig().get(PainelConfig.KEY_VIDEO_ID, Integer.class).setValue(key);
                }
            }
        });
        // screen saver
        screenSaverTimeout.setItems(FXCollections.observableList(new ArrayList<ComboboxItem>()));
        Integer defaultTimeout = main.getConfig().get(PainelConfig.KEY_SCREENSAVER_TIMEOUT, Integer.class).getValue();
        for (int i = 1; i <= 10; i++) {
            ComboboxItem item = new ComboboxItem(i, i + " minutos");
            screenSaverTimeout.getItems().add(item);
            if (defaultTimeout.equals(i)) {
                screenSaverTimeout.getSelectionModel().select(item);
            }
        }
        screenSaverTimeout.setOnAction(new EventHandler() {
            @Override
            public void handle(Event t) {
                ComboBox cb = (ComboBox) t.getTarget();
                cb.getSelectionModel().selectedItemProperty();
                ComboboxItem item = (ComboboxItem) cb.getSelectionModel().selectedItemProperty().getValue();
                Integer key = Integer.parseInt(item.getKey());
                if (key > 0 && key <= 10) {
                    main.getConfig().get(PainelConfig.KEY_SCREENSAVER_TIMEOUT, Integer.class).setValue(key);
                }
            }
        });
        // criando stage
        this.stage = new Stage();
        stage.setTitle("Painel | Novo SGA");
        stage.setScene(new Scene(getRoot()));
        // so esconde se suportar systray
        if (SystemTray.isSupported()) {
            final Controller self = this;
            stage.setOnCloseRequest(new EventHandler<WindowEvent>() {
                @Override
                public void handle(WindowEvent t) {
                    self.stage.hide();
                }
            });
        }
    }
        
    public static String colorToHex(Color color) {
        String hex = color.toString();
        return "#" + hex.substring(2, hex.length() - 2);
    }
    
}
