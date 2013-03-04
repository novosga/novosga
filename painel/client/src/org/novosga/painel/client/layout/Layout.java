package org.novosga.painel.client.layout;

import javafx.scene.layout.Pane;
import org.novosga.painel.client.PainelFx;

/**
 *
 * @author rogeriolino
 */
public abstract class Layout {
    
    protected final PainelFx painel;
    
    public Layout(PainelFx painel) {
        this.painel = painel;
    }
    
    public String configColor(String key) {
        return painel.getMain().getConfig().get(key).getValue();
    }
    
    public abstract Pane create();
    public abstract void update();
    public abstract void destroy();
    public abstract void applyTheme();
        
}
