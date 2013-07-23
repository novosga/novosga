package org.novosga.painel.client.layout;

import java.io.File;
import java.io.FilenameFilter;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLDecoder;
import java.util.logging.Level;
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
    
    public static final String EXT_MP4 = "mp4";
    public static final String EXT_AVI = "avi";
    public static final String EXT_HSL = "m3u8";
    
    private StackPane root;
    private MediaView mediaView;
    private MediaPlayer mediaPlayer;
    private String[] medias;
    private int mediaIndex = 0;
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
        String mediaUrl = painel.getMain().getConfig().get(PainelConfig.KEY_SCREENSAVER_URL).getValue();
        try {
            File md = new File(new URL(mediaUrl).getFile());
            // se for um diretorio, varre o diretorio a procura de medias
            if (md.isDirectory()) {
                File[] files = md.listFiles(new FilenameFilter() {
                    @Override
                    public boolean accept(File dir, String name) {
                        String filename = name.toLowerCase();
                        return filename.endsWith(EXT_MP4) || filename.endsWith(EXT_AVI) || filename.endsWith(EXT_HSL);
                    }
                });
                medias = new String[files.length];
                for (int i = 0; i < files.length; i++) {
                    medias[i] = files[i].toURI().toString();
                }
            } else {
                // apontando direto para o arquivo
                medias = new String[]{ mediaUrl };
            }
        } catch (MalformedURLException ex) {
            Logger.getLogger(VideoLayout.class.getName()).log(Level.SEVERE, null, ex);
        }
        setContent();
        return root;
    }
    
    public void setContent() {
        root.getChildren().clear();
        if (medias != null) {
            root.getChildren().add(createMediaView());
        } else {
            root.getChildren().add(errorLabel());
        }
        historico = createHistorico();
        historico.setId("historico");
        AnchorPane content = new AnchorPane();
        content.getChildren().add(historico);
        root.getChildren().add(content);
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
    
    private MediaView createMediaView() {
        String url = medias[mediaIndex];
        if (mediaPlayer == null || !mediaPlayer.getMedia().getSource().equals(url)) {
            mediaPlayer = createMediaPlayer(url, medias.length == 1);
            final Runnable recreate = new Runnable() {
                @Override
                public void run() {
                    LOG.severe("Recriando conteudo (MediaPlayer)");
                    setContent();
                    doUpdate();
                }
            };
            mediaPlayer.setOnError(recreate);
            mediaPlayer.setOnHalted(recreate);
            mediaPlayer.setOnStalled(recreate);
            // se chegou ao final da media verifica se tem mais para pular para a proxima
            mediaPlayer.setOnEndOfMedia(new Runnable() {
                @Override
                public void run() {
                    if (medias.length > 1) {
                        mediaIndex++;
                        if (mediaIndex >= medias.length) {
                            mediaIndex = 0;
                        }
                        setContent();
                        doUpdate();
                    }
                }
            });
            mediaView = new MediaView(mediaPlayer);
        } else {
            if (!mediaPlayer.getStatus().equals(MediaPlayer.Status.PLAYING)) {
                mediaPlayer.play();
            }
        }
        mediaView.setFitWidth(painel.getDisplay().getWidth());
        return mediaView;
    }
    
    private MediaPlayer createMediaPlayer(String url, boolean loop) {
        Media media = new Media(url);
        media.setOnError(new Runnable() {
            @Override
            public void run() {
                root.getChildren().add(errorLabel());
            }
        });
        final MediaPlayer mp = new MediaPlayer(media);
        mp.setAutoPlay(true);
        if (loop) {
            mp.setCycleCount(MediaPlayer.INDEFINITE);
        }
        mp.setOnReady(new Runnable() {
            @Override
            public void run() {
                if (mp.getStatus() != Status.PLAYING) {
                    mp.play();
                }
            }
        });
        return mp;
    }
    
    private Label errorLabel() {
        Label error = new Label(Main._("video_nao_encontrado"));
        error.setFont(Font.font(FontLoader.DROID_SANS, 18));
        error.setStyle("-fx-text-fill: #fff");
        return error;
    }
    
}
