package org.novosga.painel.client.layout;

import java.util.logging.Logger;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.layout.AnchorPane;
import javafx.scene.layout.Pane;
import javafx.scene.layout.StackPane;
import javafx.scene.media.Media;
import javafx.scene.media.MediaPlayer;
import javafx.scene.media.MediaPlayer.Status;
import javafx.scene.media.MediaView;
import javafx.scene.text.Font;
import org.novosga.painel.client.Main;
import org.novosga.painel.client.PainelFx;
import org.novosga.painel.client.config.PainelConfig;
import org.novosga.painel.client.fonts.FontLoader;

/**
 *
 * @author rogeriolino
 */
public abstract class VideoLayout<T extends Pane> extends ScreensaverLayout {
    
    private static final int PADDING = 5;
    private static final Logger LOG = Logger.getLogger(VideoLayout.class.getName());
    
    private StackPane root;
    private MediaView mediaView;
    private MediaPlayer mediaPlayer;
    private T historico;

    public VideoLayout(PainelFx painel) {
        super(painel);
    }
    
    protected abstract T createHistorico();
    protected abstract int boxCount();
    protected abstract double boxWidth();
    protected abstract double boxHeight();
    
    @Override
    protected Pane doCreate() {
        root = new StackPane();
        root.setAlignment(Pos.CENTER);
        AnchorPane content = new AnchorPane();
        String mediaUrl = painel.getMain().getConfig().get(PainelConfig.KEY_SCREENSAVER_URL).getValue();
        root.getChildren().add(getMediaView(mediaUrl));
        historico = createHistorico();
        historico.setId("historico");
        content.getChildren().add(historico);
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
    protected void doUpdate() {
        historico.getChildren().clear();
        if (painel.getSenhas().isEmpty()) {
            historico.setVisible(false);
        } else {
            // exibindo as ultimas senhas
            int total = boxCount();
            double width = boxWidth();
            double height = boxHeight();
            for (int i = painel.getSenhas().size() - 1, j = 0; i >= 0 && j < total; i--, j++) {
                SenhaBox senha = new SenhaBox(painel.getSenhas().get(i), width, height);
                historico.getChildren().add(senha.getBox());
            }
        }
    }
    
    @Override
    public void applyTheme() {
        root.setStyle("-fx-background-color: #000");
        historico.setStyle("-fx-padding: " + painel.getDisplay().height(PADDING) + "px " + painel.getDisplay().width(PADDING) + "px");
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
