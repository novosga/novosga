package br.gov.dataprev.userinterface.display;

import java.awt.GraphicsDevice;

/**
 *
 * @author rogeriolino
 */
public class Display {
    
    private final GraphicsDevice device;
    
    private int width;
    private int height;

    public Display(GraphicsDevice device) {
        this.device = device;
        this.width = device.getDisplayMode().getWidth();
        this.height = device.getDisplayMode().getHeight();
    }
    
    public int getWidth() {
        return width;
    }
    
    public int getHeight() {
        return height;
    }

    public GraphicsDevice getDevice() {
        return device;
    }
    
}
