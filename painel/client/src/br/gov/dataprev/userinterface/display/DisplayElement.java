package br.gov.dataprev.userinterface.display;

import javax.swing.JEditorPane;

/**
 * DisplayElement é o item posicionado dentro do monitor: 
 * Número da senha, nome do guiche, ect.
 * @author rogeriolino
 */
public abstract class DisplayElement {
    
    private Position width;
    private Position height;
    private Position x;
    private Position y;
    private Display display;

    public DisplayElement(Display display) {
        this.display = display;
    }

    public int getX() {
        return x.getFinalValue();
    }

    public void setX(int x) {
        this.x = new HorizontalPosition(this, x);
    }

    public void setX(double pct) {
        this.x = new HorizontalPosition(this, pct);
    }

    public int getY() {
        return y.getFinalValue();
    }

    public void setY(int y) {
        this.y = new VerticalPosition(this, y);
    }

    public void setY(double pct) {
        this.y = new VerticalPosition(this, pct);
    }

    public void setPosition(int x, int y) {
        setX(x);
        setY(y);
    }

    public void setPosition(double xPct, double yPct) {
        setX(xPct);
        setY(yPct);
    }

    public int getWidth() {
        return width.getFinalValue();
    }

    /**
     * Define a altura do elemento pixel
     * @param width 
     */
    public void setWidth(int width) {
        this.width = new HorizontalPosition(this, width);
    }

    /**
     * Define a largura do elemento em % (double entre 0 e 1)
     * @param pct 
     */
    public void setWidth(double pct) {
        this.width = new HorizontalPosition(this, pct);
    }

    public int getHeight() {
        return height.getFinalValue();
    }

    /**
     * Define a altura do elemento pixel
     * @param height 
     */
    public void setHeight(int height) {
        this.height = new VerticalPosition(this, height);
    }

    /**
     * Define a altura do elemento em % (double entre 0 e 1)
     * @param pct 
     */
    public void setHeight(double pct) {
        this.height = new VerticalPosition(this, pct);
    }
    
    public void setDimenson(int width, int height) {
        setWidth(width);
        setHeight(height);
    }
    
    public void setDimenson(int widthPct, double heightPct) {
        setWidth(widthPct);
        setHeight(heightPct);
    }

    public Display getDisplay() {
        return display;
    }
    
    public void update(Display display) {
        this.display = display;
    }
    
    public abstract void add(JEditorPane panel);
    
}
