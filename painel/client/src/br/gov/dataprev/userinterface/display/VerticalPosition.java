package br.gov.dataprev.userinterface.display;

/**
 *
 * @author rogeriolino
 */
public class VerticalPosition extends Position {

    public VerticalPosition(DisplayElement element, int x) {
        super(element, x);
    }

    public VerticalPosition(DisplayElement element, double percent) {
        super(element, percent);
    }

    @Override
    protected int getRelativeValue() {
        return element.getDisplay().getHeight();
    }
    
}
