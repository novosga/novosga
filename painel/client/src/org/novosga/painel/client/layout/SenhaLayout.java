package org.novosga.painel.client.layout;

import org.novosga.painel.model.Senha;
import com.sun.javafx.tk.FontMetrics;
import com.sun.javafx.tk.Toolkit;
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
        numeroGuiche.setId("numero");
        
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
        final Label label = this.senha;
        this.senha.setText(senha.getSenha());
        this.numeroGuiche.setText(senha.getNumeroGuicheAsString());
        this.guiche.setText(senha.getGuiche());
        this.mensagem.setText(senha.getMensagem());
        // atualizando o tamanho da font para o maximo possivel dentro do centro
        label.setFont(Font.font(label.getFont().getFamily(), FontWeight.BOLD, calculateFontSize(label)));
        TimelineBuilder.create().keyFrames(new KeyFrame(Duration.seconds(.3), new EventHandler<ActionEvent>() {
            @Override
            public void handle(ActionEvent t) {
                label.setVisible(!label.isVisible());
            }
        })).cycleCount(8).build().play();
    }
    
    protected int calculateFontSize(Label label) {
        FontMetrics fm = Toolkit.getToolkit().getFontLoader().getFontMetrics(label.getFont());
        float stringWidth = fm.computeStringWidth(label.getText());
        int charWidth = (int) (stringWidth / label.getText().length());
        double widthRatio = label.getPrefWidth() / (double) stringWidth;
        int fontSize = (int) (label.getFont().getSize() * widthRatio);
        fontSize *= .9;
        return fontSize;
    }
    
}
