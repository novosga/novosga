package br.gov.dataprev.userinterface.display;

/**
 *
 * @author rogeriolino
 */
public class HorizontalPosition extends Position {

    public HorizontalPosition(DisplayElement element, int x) {
        super(element, x);
    }

    public HorizontalPosition(DisplayElement element, double percent) {
        super(element, percent);
    }

    @Override
    protected int getRelativeValue() {
        return element.getDisplay().getWidth();
    }
    
}
