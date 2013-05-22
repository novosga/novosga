package org.novosga.painel.client.layout;

import javafx.application.Application;
import javafx.scene.Scene;
import javafx.stage.Stage;
import javafx.scene.media.Media;
import javafx.scene.media.MediaPlayer;
import javafx.scene.media.MediaPlayer.Status;
import javafx.scene.media.MediaView;
import javafx.scene.layout.*;

public class VideoTester extends Application {

    private final String mediaUrl;
    private MediaPlayer mediaPlayer;
    private Stage stage;

    public VideoTester(String mediaUrl) {
        this.mediaUrl = mediaUrl;
    }

    private void init(Stage primaryStage) {
        stage = primaryStage;
        StackPane root = new StackPane();
        stage.setScene(new Scene(root, 400, 300));
        mediaPlayer = new MediaPlayer(new Media(mediaUrl));
        mediaPlayer.setAutoPlay(true);
        mediaPlayer.setOnReady(new Runnable() {
            @Override
            public void run() {
                mediaPlayer.play();
            }
        });
        MediaView mediaView = new MediaView(mediaPlayer);
        root.getChildren().add(mediaView);
    }

    public void play() {
        Status status = mediaPlayer.getStatus();
        if (status == Status.UNKNOWN || status == Status.HALTED) {
            return;
        }
        if (status == Status.PAUSED || status == Status.STOPPED || status == Status.READY) {
            mediaPlayer.play();
        }
    }

    @Override 
    public void stop() {
        if (mediaPlayer != null) {
            mediaPlayer.stop();
        }
    }

    public void destroy() {
        stop();
        mediaPlayer = null;
        if (stage != null) {
            stage.close();
            stage = null;
        }
    }
    
    @Override 
    public void start(Stage primaryStage) throws Exception {
        init(primaryStage);
        primaryStage.show();
        play();
    }
    
    public static void main(String[] args) { 
        launch(args); 
    }
    
}
