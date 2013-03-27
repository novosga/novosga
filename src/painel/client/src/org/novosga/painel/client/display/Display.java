package org.novosga.painel.client.display;

import javafx.stage.Screen;

/**
 *
 * @author rogeriolino
 */
public class Display {
    
    private final Screen screen;
    
    public static final double WIDTH = 800.0;
    public static final double HEIGHT_SQUARE = 600.0;
    public static final double HEIGHT_WIDE = 470.0;
    
    private double x;
    private double y;
    private double width;
    private double height;
    private double widthRatio;
    private double heightRatio;
        

    public Display(Screen screen) {
        this.screen = screen;
        this.x = screen.getBounds().getMinX();
        this.y = screen.getBounds().getMinY();
        this.width = screen.getBounds().getWidth();
        this.height = screen.getBounds().getHeight();
        this.widthRatio = this.width / Display.WIDTH;
        double height = (isWide()) ? Display.HEIGHT_WIDE : Display.HEIGHT_SQUARE;
        this.heightRatio = this.height / height;
    }

    public double getX() {
        return x;
    }

    public double getY() {
        return y;
    }
    
    public double getWidth() {
        return width;
    }
    
    public double getHeight() {
        return height;
    }

    public Screen getScreen() {
        return screen;
    }
    
    public boolean isWide() {
        return width / height > 1.5;
    }
    
    /**
     * Retorna a largura informada na escala atual com base na largura padrão: WIDTH
     * @param w
     * @return 
     */
    public double width(double w) {
        return w * widthRatio;
    }
    
    /**
     * Retorna a altura informada na escala atual com base na altura padrão: HEIGHT_SQUARE || HEIGHT_WIDE
     * @param h
     * @return 
     */
    public double height(double h) {
        return h * heightRatio;
    }
    
}
