package org.novosga.painel.client.layout;

import org.novosga.painel.model.Senha;
import javafx.animation.KeyFrame;
import javafx.animation.TimelineBuilder;
import javafx.event.ActionEvent;
import javafx.event.EventHandler;
import javafx.scene.control.Label;
import javafx.scene.text.Font;
import javafx.scene.text.FontWeight;
import javafx.util.Duration;
import org.novosga.painel.client.Main;
import org.novosga.painel.client.PainelFx;

/**
 *
 * @author rogeriolino
 */
public abstract class SenhaLayout extends Layout {
    
    protected Label mensagem;
    protected Label senha;
    protected Label guiche;
    protected Label numeroGuiche;

    public SenhaLayout(PainelFx painel) {
        super(painel);
        mensagem = new Label(Main._("atendimento"));
        mensagem.setId("mensagem");
        
        guiche = new Label(Main._("guiche"));
        guiche.setId("guiche");
        
        numeroGuiche = new Label("000");
        numeroGuiche.setId("numero-guiche");
        
        senha = new Label("-----");
        senha.setId("senha");
    }

    public Label getMensagem() {
        return mensagem;
    }

    public Label getSenha() {
        return senha;
    }

    public Label getGuiche() {
        return guiche;
    }

    public Label getNumeroGuiche() {
        return numeroGuiche;
    }
    
    public void onSenha(Senha senha) {
        // atualizando texto
        this.senha.setText(senha.getSenha());
        this.numeroGuiche.setText(senha.getNumeroGuicheAsString());
        this.guiche.setText(senha.getGuiche());
        this.mensagem.setText(senha.getMensagem());
        // aplicando estilo
        String styleClass = senha.getMensagem().toLowerCase();
        this.senha.getStyleClass().clear();
        this.senha.getStyleClass().addAll("label", styleClass);
        this.numeroGuiche.getStyleClass().clear();
        this.numeroGuiche.getStyleClass().addAll("label", styleClass);
        this.guiche.getStyleClass().clear();
        this.guiche.getStyleClass().addAll("label", styleClass);
        this.mensagem.getStyleClass().clear();
        this.mensagem.getStyleClass().addAll("label", styleClass);
        // atualizando o tamanho da font para o maximo possivel dentro do centro
        final Label label = this.senha;
        label.setFont(Font.font(label.getFont().getFamily(), FontWeight.BOLD, calculateFontSize(label)));
        TimelineBuilder.create().keyFrames(new KeyFrame(Duration.seconds(.3), new EventHandler<ActionEvent>() {
            @Override
            public void handle(ActionEvent t) {
                label.setVisible(!label.isVisible());
            }
        })).cycleCount(8).build().play();
    }
    
}
