package org.novosga.painel.client.layout;

import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.layout.BorderPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Pane;
import javafx.scene.text.Font;
import javafx.scene.text.FontPosture;
import javafx.scene.text.FontWeight;
import org.novosga.painel.client.PainelFx;
import org.novosga.painel.client.config.PainelConfig;
import org.novosga.painel.client.fonts.FontLoader;

/**
 *
 * @author rogeriolino
 */
public class SimpleSenhaLayout extends SenhaLayout {

    private BorderPane root;
    private int fontSize;
    private int fontSize2;
    private int fontSize3;
    private HBox topBox;
    private HBox bottomBox;

    public SimpleSenhaLayout(PainelFx painel) {
        super(painel);
    }

    @Override
    public Pane create() {
        root = new BorderPane();
        topBox = new HBox();
        bottomBox = new HBox();
        int paddingX = (int) painel.getDisplay().width(20);
        // top
        topBox.setPadding(new Insets(0, 0, 0, paddingX));
        topBox.getChildren().add(mensagem);
        root.setTop(topBox);
        // center
        root.setCenter(senha);
        // bottom
        bottomBox.setAlignment(Pos.BOTTOM_LEFT);
        bottomBox.setPadding(new Insets(0, paddingX, 0, paddingX));
        bottomBox.getChildren().add(guiche);
        bottomBox.getChildren().add(numeroGuiche);
        root.setBottom(bottomBox);
        return root;
    }

    @Override
    public void destroy() {
    }

    @Override
    public void update() {
        fontSize = (int) painel.getDisplay().height(80);
        fontSize2 = (int) painel.getDisplay().height(100);
        fontSize3 = (int) painel.getDisplay().height(120);
        updateMensagem().updateSenha().updateGuiche().updateNumeroGuiche();
    }

    @Override
    public void applyTheme() {
        root.setStyle("-fx-background-color: " + colorHex(PainelConfig.KEY_COR_FUNDO));
        mensagem.setStyle("-fx-text-fill: " + colorHex(PainelConfig.KEY_COR_MENSAGEM));
        senha.setStyle("-fx-text-fill: " + colorHex(PainelConfig.KEY_COR_SENHA));
        guiche.setStyle("-fx-text-fill: " + colorHex(PainelConfig.KEY_COR_GUICHE));
        numeroGuiche.setStyle("-fx-text-fill: " + colorHex(PainelConfig.KEY_COR_GUICHE));
    }

    private SimpleSenhaLayout updateMensagem() {
        mensagem.setFont(Font.font(FontLoader.DROID_SANS, FontWeight.NORMAL, FontPosture.REGULAR, fontSize));
        mensagem.setAlignment(Pos.CENTER_LEFT);
        mensagem.setPrefWidth(painel.getDisplay().getWidth());
        mensagem.setPrefHeight(fontSize);
        return this;
    }

    private SimpleSenhaLayout updateSenha() {
        senha.setAlignment(Pos.CENTER);
        int centerHeight = (int) (painel.getDisplay().getHeight() - (fontSize + fontSize3));
        senha.setPrefHeight(centerHeight);
        senha.setPrefWidth(painel.getDisplay().getWidth());
        // cria primeiro com um tamanho qualquer so para definir o tamanho medio dos caracteres
        senha.setFont(Font.font(FontLoader.BITSTREAM_VERA_SANS, FontWeight.BOLD, 12));
        // atualizando o tamanho da font para o maximo possivel dentro do centro
        senha.setFont(Font.font(senha.getFont().getFamily(), FontWeight.BOLD, calculateFontSize(senha)));
        root.setCenter(senha);
        return this;
    }

    private SimpleSenhaLayout updateGuiche() {
        guiche.setAlignment(Pos.BOTTOM_LEFT);
        guiche.setPrefWidth(painel.getDisplay().getWidth() / 2);
        guiche.setPrefHeight(fontSize3);
        guiche.setFont(Font.font(FontLoader.DROID_SANS, fontSize2));
        guiche.setLayoutY(20);
        return this;
    }

    private SimpleSenhaLayout updateNumeroGuiche() {
        numeroGuiche.setLayoutX(100);
        numeroGuiche.setAlignment(Pos.BOTTOM_RIGHT);
        numeroGuiche.setPrefWidth(painel.getDisplay().getWidth() / 2);
        numeroGuiche.setPrefHeight(fontSize3);
        numeroGuiche.setFont(Font.font(FontLoader.DROID_SANS, FontWeight.BOLD, fontSize3));
        return this;
    }
}
