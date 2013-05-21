package org.novosga.painel.client.layout;

import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.layout.VBox;
import javafx.scene.text.Font;
import javafx.scene.text.FontWeight;
import org.novosga.painel.client.PainelFx;
import org.novosga.painel.client.config.PainelConfig;
import org.novosga.painel.client.fonts.FontLoader;
import org.novosga.painel.model.Senha;

/**
 *
 * @author rogeriolino
 */
public abstract class ScreensaverLayout extends Layout {
    
    public ScreensaverLayout(PainelFx painel) {
        super(painel);
    }
    
    protected class SenhaBox {
        
        private double width;
        private double height;
        private Label guiche;
        private Label numero;
        private VBox box;
        
        public SenhaBox(Senha senha, double width, double height) {
            this.width = width;
            this.height = height;
            box = new VBox();
            box.getStyleClass().add("historico-box");
            box.getStyleClass().add(senha.getMensagem().toLowerCase());
            box.setAlignment(Pos.CENTER);
            box.setPrefHeight(this.height);
            // 30% do box
            guiche = new Label(senha.getGuiche() + ": " + senha.getNumeroGuicheAsString(2));
            guiche.getStyleClass().add("historico-guiche");
            guiche.setAlignment(Pos.BOTTOM_CENTER);
            guiche.setPrefWidth(this.width);
            guiche.setFont(Font.font(FontLoader.DROID_SANS, FontWeight.BOLD, height * .3));
            double fontSize = calculateFontSize(guiche);
            guiche.setFont(Font.font(FontLoader.DROID_SANS, FontWeight.BOLD, fontSize));
            guiche.setPrefHeight(fontSize);
            guiche.setStyle("-fx-text-fill: " + colorHex(PainelConfig.KEY_COR_SENHA));
            box.getChildren().add(guiche);
            // 70% do box
            numero = new Label(senha.getSenha());
            numero.getStyleClass().add("historico-senha");
            numero.setAlignment(Pos.TOP_CENTER);
            numero.setPrefWidth(this.width);
            numero.setFont(Font.font(FontLoader.BITSTREAM_VERA_SANS, FontWeight.BOLD, height * .7));
            fontSize = calculateFontSize(numero);
            numero.setFont(Font.font(FontLoader.BITSTREAM_VERA_SANS, FontWeight.BOLD, fontSize));
            numero.setPrefHeight(fontSize);
            numero.setWrapText(false);
            numero.setEllipsisString("");
            numero.setStyle("-fx-text-fill: " + colorHex(PainelConfig.KEY_COR_SENHA));
            box.getChildren().add(numero);
            // setting width
            box.setPrefWidth(this.width);
        }

        public double getWidth() {
            return this.width;
        }

        public double getHeight() {
            return height;
        }

        public Label getGuiche() {
            return guiche;
        }

        public Label getNumero() {
            return numero;
        }

        public VBox getBox() {
            return box;
        }
        
    }
    
}
