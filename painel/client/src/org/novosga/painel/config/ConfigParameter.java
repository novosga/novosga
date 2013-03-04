package org.novosga.painel.config;

/**
 *
 * @author rogeriolino
 */
public class ConfigParameter<T> {
    
    private final String key;
    private T value;

    public ConfigParameter(String key, T defaultValue) {
        if (key == null) {
            throw new IllegalArgumentException("The parameter key cannot be null");
        }
        this.key = key;
        this.setValue(defaultValue);
    }

    public String getKey() {
        return key;
    }

    public T getValue() {
        return value;
    }

    public void setValue(T value) {
        if (value == null) {
            throw new IllegalArgumentException("The parameter value cannot be null");
        }
        this.value = value;
    }
    
    /**
     * Retorna o valor do parametro é igual ao informado ou se contain esse valor,
     * no caso de array
     * @param v
     * @return 
     */
    public boolean is(Object v) {
        if (v == null && value == null) {
            return true;
        } 
        if (v != null && value != null) {
            if (value instanceof Object[]) {
                Object[] arr = (Object[]) value;
                for (int i = 0; i < arr.length; i++) {
                    if (arr[i].equals(v)) {
                        return true;
                    }
                }
            } else {
                return value.equals(v);
            }
        }
        return false;
    }
    
    public Class getType() {
        return value.getClass();
    }
    
    @Override
    public String toString() {
        StringBuilder sb = new StringBuilder();
        if (value != null) {
            if (value instanceof Object[]) {
                Object[] arr = (Object[]) value;
                if (arr.length > 0) {
                    sb.append(arr[0]);
                    for (int i = 1; i < arr.length; i++) {
                        sb.append(',').append(arr[i].toString());
                    }
                }
            } else {
                sb.append(value.toString());
            }
        }
        return sb.toString();
    }
    
}
