package org.novosga.painel.client.layout;

import org.novosga.painel.model.Senha;
import javafx.animation.KeyFrame;
import javafx.animation.Timeline;
import javafx.animation.TimelineBuilder;
import javafx.event.ActionEvent;
import javafx.event.EventHandler;
import javafx.scene.control.Label;
import javafx.scene.text.Font;
import javafx.scene.text.FontWeight;
import javafx.util.Duration;
import org.novosga.painel.client.Main;
import org.novosga.painel.client.PainelFx;
import org.novosga.painel.client.config.PainelConfig;

/**
 *
 * @author rogeriolino
 */
public abstract class SenhaLayout extends Layout {
    
    protected Label mensagem;
    protected Label senha;
    protected Label guiche;
    protected Label numeroGuiche;
    
    protected Timeline animation;

    public SenhaLayout(PainelFx painel) {
        super(painel);
        mensagem = new Label(Main._("atendimento"));
        mensagem.setId("mensagem");
        
        guiche = new Label(Main._("guiche"));
        guiche.setId("guiche");
        
        numeroGuiche = new Label("00");
        numeroGuiche.setId("numero-guiche");
        
        Integer tamanho = painel.getMain().getConfig().get(PainelConfig.KEY_TAMANHO_NUMERO, Integer.class).getValue();
        senha = new Label("A" + String.format("%0" + tamanho + "d", 0));
        senha.setId("senha");
        
        animation = TimelineBuilder.create().keyFrames(new KeyFrame(Duration.seconds(.3), new EventHandler<ActionEvent>() {
            @Override
            public void handle(ActionEvent t) {
                senha.setVisible(!senha.isVisible());
            }
        })).cycleCount(8).build();
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
        this.senha.setFont(Font.font(this.senha.getFont().getFamily(), FontWeight.BOLD, calculateFontSize(this.senha)));
        animation.play();
    }
    
}
