package org.novosga.painel.client.layout;

import javafx.geometry.Pos;
import javafx.scene.layout.AnchorPane;
import javafx.scene.layout.HBox;
import org.novosga.painel.client.PainelFx;

/**
 *
 * @author rogeriolino
 */
public class HVideoLayout extends VideoLayout {

    public HVideoLayout(PainelFx painel) {
        super(painel);
    }
    
    @Override
    protected HBox createHistorico() {
        HBox box = new HBox();
        box.setAlignment(Pos.TOP_LEFT);
        // 100% largura
        box.setPrefWidth(painel.getDisplay().getWidth());
        // 30% altura
        box.setPrefHeight(painel.getDisplay().getHeight() * .3);
        AnchorPane.setBottomAnchor(box, 0.0);
        AnchorPane.setLeftAnchor(box, 0.0);
        return box;
    }
    
    @Override
    protected int boxCount() {
        return (painel.getDisplay().isWide()) ? 4 : 3;
    }
    
    @Override
    protected double boxWidth() {
        return painel.getDisplay().getWidth() / boxCount();
    }
    
    @Override
    protected double boxHeight() {
        return painel.getDisplay().getHeight() * .3;
    }
    
}
