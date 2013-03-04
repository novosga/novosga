package org.novosga.painel.util;

/**
 * 
 * @author rogeriolino
 */
public class ComboboxItem {
    
    private Integer key;
    private String label;

    public ComboboxItem(Integer key, String label) {
        this.key = key;
        this.label = label;
    }
    
    public Integer getKey() {
        return key;
    }

    public void setKey(Integer key) {
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
