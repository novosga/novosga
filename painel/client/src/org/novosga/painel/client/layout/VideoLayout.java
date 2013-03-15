package org.novosga.painel.client.layout;

import org.novosga.painel.model.Senha;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.layout.AnchorPane;
import javafx.scene.layout.Pane;
import javafx.scene.layout.StackPane;
import javafx.scene.layout.VBox;
import javafx.scene.media.Media;
import javafx.scene.media.MediaPlayer;
import javafx.scene.media.MediaPlayer.Status;
import javafx.scene.media.MediaView;
import javafx.scene.text.Font;
import javafx.scene.text.FontWeight;
import org.novosga.painel.client.Main;
import org.novosga.painel.client.PainelFx;
import org.novosga.painel.client.config.PainelConfig;
import org.novosga.painel.client.fonts.FontLoader;

/**
 *
 * @author rogeriolino
 */
public class VideoLayout extends ScreensaverLayout {
    
    private StackPane root;
    private MediaView mediaView;
    private MediaPlayer mediaPlayer;
    private Label ultimasSenhas;
    private Label senhas;
    private VBox bottomBox;

    public VideoLayout(PainelFx painel) {
        super(painel);
    }
    
    @Override
    public Pane create() {
        root = new StackPane();
        root.setAlignment(Pos.CENTER);
        AnchorPane content = new AnchorPane();
        Media media = new Media(painel.getMain().getConfig().get(PainelConfig.KEY_SCREENSAVER_URL).getValue());
        media.setOnError(new Runnable() {
            @Override
            public void run() {
                Label error = new Label(Main._("video_nao_encontrado"));
                error.setFont(Font.font(FontLoader.DROID_SANS, 18));
                error.setStyle("-fx-text-fill: #fff");
                root.getChildren().add(error);
            }
        });
        root.getChildren().add(createMediaView(media));
        bottomBox = new VBox();
        bottomBox.setAlignment(Pos.CENTER_LEFT);
        ultimasSenhas = new Label(Main._("ultimas_senhas") + ":");
        ultimasSenhas.setAlignment(Pos.CENTER_LEFT);
        bottomBox.getChildren().add(ultimasSenhas);
        senhas = new Label("-");
        senhas.setAlignment(Pos.CENTER_LEFT);
        bottomBox.getChildren().add(senhas);
        
        content.getChildren().add(bottomBox);
        AnchorPane.setBottomAnchor(bottomBox, 0.0);
        AnchorPane.setLeftAnchor(bottomBox, 0.0);
        root.getChildren().add(content);
        return root;
    }
    
    @Override
    public void destroy() {
        if (mediaPlayer != null) {
            mediaPlayer.pause();
        }
    }
    
    @Override
    public void update() {
        // exibindo as ultimas senhas
        if (painel.getSenhas().size() > 0) {
            StringBuilder sb = new StringBuilder();
            int j = 0;
            int maxSenhas = 3;
            for (int i = painel.getSenhas().size() - 1; i >= 0 && j < maxSenhas; i--, j++) {
                Senha senha = painel.getSenhas().get(i);
                sb.append(senha.toString()).append(" ");
            }
            senhas.setText(sb.toString().trim());
        }
        // 15% da altura do monitor
        double bottomHeight = painel.getDisplay().getHeight() * .15;
        // 30% da altura do rodape
        int fontSize = (int) (bottomHeight * .3);
        // 70% da altura do rodape
        int fontSize2 = (int) (bottomHeight * .7);
        ultimasSenhas.setFont(Font.font(FontLoader.DROID_SANS, fontSize));
        ultimasSenhas.setPrefHeight(fontSize);
        ultimasSenhas.setPrefWidth(painel.getDisplay().getWidth());
        ultimasSenhas.setAlignment(Pos.CENTER_LEFT);
        senhas.setFont(Font.font(FontLoader.BITSTREAM_VERA_SANS, FontWeight.BOLD, fontSize2));
        senhas.setPrefHeight(fontSize2);
        senhas.setPrefWidth(painel.getDisplay().getWidth());
        senhas.setAlignment(Pos.CENTER_LEFT);
    }
    
    @Override
    public void applyTheme() {
        root.setStyle("-fx-background-color: #000");
        bottomBox.setStyle("-fx-background-color: rgba(0,0,0,.5); -fx-padding: " + painel.getDisplay().height(5) + "px " + painel.getDisplay().width(5) + "px");
        senhas.setStyle("-fx-text-fill: " + colorHex(PainelConfig.KEY_COR_SENHA));
        ultimasSenhas.setStyle("-fx-text-fill: " + colorHex(PainelConfig.KEY_COR_MENSAGEM));
    }
    
    private MediaView createMediaView(Media media) {
        if (mediaPlayer == null || !mediaPlayer.getMedia().getSource().equals(media.getSource())) {
            mediaPlayer = new MediaPlayer(media);
            mediaPlayer.setAutoPlay(true);
            mediaPlayer.setCycleCount(MediaPlayer.INDEFINITE);
            mediaPlayer.setOnReady(new Runnable() {
                @Override
                public void run() {
                    if (mediaPlayer.getStatus() != Status.PLAYING) {
                        mediaPlayer.play();
                    }
                }
            });
            mediaView = new MediaView(mediaPlayer);
        } else {
            mediaPlayer.play();
        }
        mediaView.setFitWidth(painel.getDisplay().getWidth());
        return mediaView;
    }
    
}
