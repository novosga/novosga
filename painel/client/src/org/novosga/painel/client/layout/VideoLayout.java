package org.novosga.painel.client.layout;

import org.novosga.painel.model.Senha;
import java.io.File;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.layout.BorderPane;
import javafx.scene.layout.Pane;
import javafx.scene.layout.VBox;
import javafx.scene.media.Media;
import javafx.scene.media.MediaPlayer;
import javafx.scene.media.MediaView;
import javafx.scene.text.Font;
import javafx.scene.text.FontWeight;
import org.novosga.painel.client.PainelFx;
import org.novosga.painel.client.config.PainelConfig;
import org.novosga.painel.client.fonts.FontLoader;

/**
 *
 * @author rogeriolino
 */
public class VideoLayout extends ScreensaverLayout {
    
    private BorderPane root;
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
        root = new BorderPane();
        File file = new File("media/video/promo1.mp4");
        if (file.exists()) {
            String url = file.toURI().toString();
            root.setTop(createMedia(url));
        } else {
            root.setTop(new Label("Media not found"));
        }
        bottomBox = new VBox();
        bottomBox.setAlignment(Pos.CENTER_LEFT);
        ultimasSenhas = new Label("Últimas senhas:");
        ultimasSenhas.setAlignment(Pos.CENTER_LEFT);
        bottomBox.getChildren().add(ultimasSenhas);
        senhas = new Label("-");
        senhas.setAlignment(Pos.CENTER_LEFT);
        bottomBox.getChildren().add(senhas);
        root.setBottom(bottomBox);
        return root;
    }
    
    @Override
    public void destroy() {
        mediaPlayer.stop();
        mediaPlayer = null;
        mediaView = null;
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
        senhas.setFont(Font.font(FontLoader.BITSTREAM_VERA_SANS, FontWeight.BOLD, fontSize2));
        senhas.setPrefHeight(fontSize2);
        senhas.setPrefWidth(painel.getDisplay().getWidth());
    }
    
    @Override
    public void applyTheme() {
        String bg = configColor(PainelConfig.KEY_COR_FUNDO);
        root.setStyle("-fx-background-color: " + bg);
        senhas.setStyle(labelStyle(configColor(PainelConfig.KEY_COR_SENHA), bg, 10, 10, 0, 10));
        ultimasSenhas.setStyle(labelStyle(configColor(PainelConfig.KEY_COR_MENSAGEM), bg, 10));
    }
    
    private String labelStyle(String color, String bgColor, int padding) {
        return labelStyle(color, bgColor, padding, padding);
    }
    
    private String labelStyle(String color, String bgColor, int padV, int padH) {
        return labelStyle(color, bgColor, padV, padH, padV, padH);
    }
    
    private String labelStyle(String color, String bgColor, int padTop, int padRight, int padBottom, int padLeft) {
        padTop = (int) painel.getDisplay().height(padTop);
        padBottom = (int) painel.getDisplay().height(padBottom);
        padRight = (int) painel.getDisplay().width(padRight);
        padLeft = (int) painel.getDisplay().width(padLeft);
        return "-fx-text-fill: " + color + "; -fx-background-color: " + bgColor + "; -fx-padding: " + padTop + "px " + padRight + "px" + padBottom + "px" + padLeft + "px";
    }
    
    private MediaView createMedia(String url) {
        mediaPlayer = new MediaPlayer(new Media(url));
        mediaPlayer.setAutoPlay(false);
        mediaPlayer.setCycleCount(MediaPlayer.INDEFINITE);
        mediaPlayer.setOnReady(new Runnable() {
            @Override
            public void run() {
                mediaPlayer.play();
            }
        });
        mediaView = new MediaView(mediaPlayer);
        mediaView.setFitWidth(painel.getDisplay().getWidth());
        return mediaView;
    }
    
}
