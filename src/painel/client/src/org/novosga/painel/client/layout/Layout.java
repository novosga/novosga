package org.novosga.painel.client.layout;

import com.sun.javafx.tk.FontMetrics;
import com.sun.javafx.tk.Toolkit;
import javafx.event.Event;
import javafx.event.EventHandler;
import javafx.event.EventType;
import javafx.scene.control.ContextMenu;
import javafx.scene.control.Label;
import javafx.scene.control.MenuItem;
import javafx.scene.control.SeparatorMenuItem;
import javafx.scene.input.MouseButton;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.Pane;
import javafx.scene.paint.Color;
import javafx.scene.text.Font;
import org.novosga.painel.client.Main;
import org.novosga.painel.client.PainelFx;

/**
 *
 * @author rogeriolino
 */
public abstract class Layout {
    
    protected final PainelFx painel;
    protected final ContextMenu contextMenu = new ContextMenu();
    
    public Layout(final PainelFx painel) {
        this.painel = painel;
        
        SeparatorMenuItem separator = new SeparatorMenuItem();
        
        MenuItem configurar = new MenuItem(Main._("configurar"));
        configurar.addEventHandler(EventType.ROOT, new EventHandler<Event>() {
            @Override
            public void handle(Event t) {
                painel.getMain().getController().show();
            }
        });
        MenuItem ocultar = new MenuItem(Main._("ocultar_painel"));
        ocultar.addEventHandler(EventType.ROOT, new EventHandler<Event>() {
            @Override
            public void handle(Event t) {
                painel.getMain().getPainel().hide();
            }
        });
        MenuItem sair = new MenuItem(Main._("sair"));
        sair.addEventHandler(EventType.ROOT, new EventHandler<Event>() {
            @Override
            public void handle(Event t) {
                System.exit(0);
            }
        });
        contextMenu.getItems().addAll(configurar, ocultar, separator, sair);
    }
    
    protected Color color(String key) {
        return Color.web(painel.getMain().getConfig().get(key).getValue());
    }
    
    protected String colorHex(String key) {
        return painel.getMain().getConfig().get(key).getValue();
    }
    
    protected float stringWidth(Label label) {
        return stringWidth(label.getFont(), label.getText());
    }
    
    protected float stringWidth(Font font, String text) {
        FontMetrics fm = Toolkit.getToolkit().getFontLoader().getFontMetrics(font);
        return fm.computeStringWidth(text);
    }
    
    protected float charWidth(Label label) {
        return stringWidth(label) / label.getText().length();
    }
    
    protected int calculateFontSize(Label label) {
        return calculateFontSize(label.getFont(), label.getText(), label.getPrefWidth());
    }
    
    protected int calculateFontSize(Font font, String text, double maxWidth) {
        float stringWidth = stringWidth(font, text);
        double widthRatio = maxWidth / (double) stringWidth;
        int fontSize = (int) (font.getSize() * widthRatio);
        fontSize *= .9;
        return fontSize;
    }
    
    public final Pane create() {
        Pane pane = doCreate();
        return pane;
    }
    
    public final void update() {
        doUpdate();
        if (painel.getStage() != null && painel.getStage().getScene() != null) {
            painel.getStage().getScene().setOnMouseReleased(new EventHandler<MouseEvent>() {
                @Override
                public void handle(MouseEvent mouseEvent) {
                    // if(cm.isShowing())
                    contextMenu.hide();
                    if (mouseEvent.getButton() == MouseButton.SECONDARY) {
                        contextMenu.show(painel.getStage(), mouseEvent.getScreenX(), mouseEvent.getScreenY());
                    }
                }
            });
        }
    }
    
    protected abstract Pane doCreate();
    protected abstract void doUpdate();
    public abstract void destroy();
    public abstract void applyTheme();
        
}
