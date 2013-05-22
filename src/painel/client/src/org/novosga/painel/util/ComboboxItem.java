package org.novosga.painel.util;

/**
 * 
 * @author rogeriolino
 */
public class ComboboxItem {
    
    private String key;
    private String label;

    public ComboboxItem(String key, String label) {
        this.key = key;
        this.label = label;
    }

    public ComboboxItem(Integer key, String label) {
        this(key + "", label);
    }
    
    public String getKey() {
        return key;
    }

    public void setKey(String key) {
        this.key = key;
    }

    public String getLabel() {
        return label;
    }

    public void setLabel(String label) {
        this.label = label;
    }
    
    @Override
    public String toString() {
        return label;
    }
    
}
