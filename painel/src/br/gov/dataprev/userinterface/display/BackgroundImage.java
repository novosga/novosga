package br.gov.dataprev.userinterface.display;

import javax.swing.JEditorPane;

/**
 * Background
 * @author rogeriolino
 */
public class BackgroundImage extends DisplayElement {
    
    private String url;

    public BackgroundImage(Display display, String url) {
        super(display);
        this.url = url;
    }
    
    public String getUrl() {
        return url;
    }
    
    @Override
    public void add(JEditorPane panel) {
        
    }
    
}
