package br.gov.dataprev.userinterface.display;

/**
 *
 * @author rogeriolino
 */
public abstract class Position {
    
    protected Number value;
    protected PositionType type;
    protected DisplayElement element;
    
    public Position(DisplayElement element, int x) {
        this(element, PositionType.FIXED, x);
    }
    
    public Position(DisplayElement element, double percent) {
        this(element, PositionType.RELATIVE, percent);
    }
    
    public Position(DisplayElement element, PositionType type, Number value) {
        this.type = type;
        this.value = value;
        if (this.type.equals(PositionType.RELATIVE)) {
            String msg = "Valor inválido para o posicionamento relativo. Certifique-se que seja uma valor double entre 0 e 1. Valor informado: ";
            try {
                double pct = value.doubleValue();
                if (pct < 0 || pct > 1) {
                    throw new RuntimeException(msg + pct);
                }
            } catch (ClassCastException e) {
                throw new RuntimeException(msg + value);
            }
        }
        this.element = element;
    }
    
    public int getFinalValue() {
        if (type.equals(PositionType.FIXED))  {
            return value.intValue();
        }
        return (int) (getRelativeValue() * value.doubleValue());
    }
    
    protected abstract int getRelativeValue();
    
    public static enum PositionType {
        FIXED,
        RELATIVE;
    }
    
}
