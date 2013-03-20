package org.novosga.painel.client.layout;

import com.sun.javafx.tk.FontMetrics;
import com.sun.javafx.tk.Toolkit;
import javafx.scene.control.Label;
import javafx.scene.layout.Pane;
import javafx.scene.paint.Color;
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
    
    protected Color color(String key) {
        return Color.web(painel.getMain().getConfig().get(key).getValue());
    }
    
    protected String colorHex(String key) {
        return painel.getMain().getConfig().get(key).getValue();
    }
    
    protected float stringWidth(Label label) {
        FontMetrics fm = Toolkit.getToolkit().getFontLoader().getFontMetrics(label.getFont());
        return fm.computeStringWidth(label.getText());
    }
    
    protected float charWidth(Label label) {
        return stringWidth(label) / label.getText().length();
    }
    
    protected int calculateFontSize(Label label) {
        float stringWidth = stringWidth(label);
        double widthRatio = label.getPrefWidth() / (double) stringWidth;
        int fontSize = (int) (label.getFont().getSize() * widthRatio);
        fontSize *= .9;
        return fontSize;
    }
    
    public abstract Pane create();
    public abstract void update();
    public abstract void destroy();
    public abstract void applyTheme();
        
}
