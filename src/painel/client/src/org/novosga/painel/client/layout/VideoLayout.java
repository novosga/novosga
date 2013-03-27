package org.novosga.painel.client.layout;

import java.util.logging.Logger;
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
    
    private static final int PADDING = 5;
    private static final Logger LOG = Logger.getLogger(VideoLayout.class.getName());
    
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
        String mediaUrl = painel.getMain().getConfig().get(PainelConfig.KEY_SCREENSAVER_URL).getValue();
        root.getChildren().add(getMediaView(mediaUrl));
        bottomBox = new VBox();
        bottomBox.setAlignment(Pos.CENTER_LEFT);
        bottomBox.setPrefWidth(painel.getDisplay().getWidth());
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
        // exibindo as ultimas senhas
        if (painel.getSenhas().size() > 0) {
            StringBuilder sb = new StringBuilder();
            double padding = painel.getDisplay().width(PADDING) * 2;
            int maxChars = (int) ((bottomBox.getWidth() - padding) / (charWidth(senhas) + padding));
            for (int i = painel.getSenhas().size() - 1; i >= 0; i--) {
                // concatenando as senhas com 3 zeros a esquerda
                String senha = painel.getSenhas().get(i).getSenha(3);
                if (sb.toString().length() + senha.length() >= maxChars) {
                    break;
                }
                sb.append(senha).append(" ");
            }
            senhas.setText(sb.toString().trim());
        }
    }
    
    @Override
    public void applyTheme() {
        root.setStyle("-fx-background-color: #000");
        bottomBox.setStyle("-fx-background-color: rgba(0,0,0,.5); -fx-padding: " + painel.getDisplay().height(PADDING) + "px " + painel.getDisplay().width(PADDING) + "px");
        senhas.setStyle("-fx-text-fill: " + colorHex(PainelConfig.KEY_COR_SENHA));
        ultimasSenhas.setStyle("-fx-text-fill: " + colorHex(PainelConfig.KEY_COR_MENSAGEM));
    }
    
    private MediaView getMediaView(String url) {
        if (mediaPlayer == null || !mediaPlayer.getMedia().getSource().equals(url)) {
            createMediaPlayer(url);
            final Runnable recreate = new Runnable() {
                @Override
                public void run() {
                    LOG.severe("Recriando MediaPlayer");
                    createMediaPlayer(mediaPlayer.getMedia().getSource());
                }
            };
            mediaPlayer.setOnError(recreate);
            mediaPlayer.setOnHalted(recreate);
            mediaPlayer.setOnStalled(recreate);
        } else {
            if (!mediaPlayer.getStatus().equals(MediaPlayer.Status.PLAYING)) {
                mediaPlayer.play();
            }
        }
        mediaView.setFitWidth(painel.getDisplay().getWidth());
        return mediaView;
    }
    
    private void createMediaPlayer(String url) {
        Media media = new Media(url);
        media.setOnError(new Runnable() {
            @Override
            public void run() {
                Label error = new Label(Main._("video_nao_encontrado"));
                error.setFont(Font.font(FontLoader.DROID_SANS, 18));
                error.setStyle("-fx-text-fill: #fff");
                root.getChildren().add(error);
            }
        });
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
    }
    
}
