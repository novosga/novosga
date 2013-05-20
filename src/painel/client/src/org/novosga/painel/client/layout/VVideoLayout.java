package org.novosga.painel.client.layout;

import javafx.geometry.Pos;
import javafx.scene.layout.AnchorPane;
import javafx.scene.layout.VBox;
import org.novosga.painel.client.PainelFx;

/**
 *
 * @author rogeriolino
 */
public class VVideoLayout extends VideoLayout<VBox> {

    public VVideoLayout(PainelFx painel) {
        super(painel);
    }
    
    @Override
    protected VBox createHistorico() {
        VBox box = new VBox();
        box.setAlignment(Pos.TOP_LEFT);
        // 30% largura
        box.setPrefWidth(painel.getDisplay().getWidth() * .3);
        // 100% altura
        box.setPrefHeight(painel.getDisplay().getHeight());
        AnchorPane.setTopAnchor(box, 0.0);
        AnchorPane.setRightAnchor(box, 0.0);
        return box;
    }
    
    @Override
    protected int boxCount() {
        return 3;
    }
    
    @Override
    protected double boxWidth() {
        return painel.getDisplay().getWidth() * .3;
    }
    
    @Override
    protected double boxHeight() {
        return painel.getDisplay().getHeight() / boxCount();
    }
    
}
