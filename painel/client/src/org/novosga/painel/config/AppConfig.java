package org.novosga.painel.config;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.List;
import java.util.Properties;
import org.novosga.painel.client.Main;

/**
 *
 * @author rogeriolino
 */
public abstract class AppConfig {
    
    protected abstract String filename();
    protected abstract List<ConfigParameter> parameters();
    
    
    public void load() throws FileNotFoundException, IOException {
        Properties config = new Properties();
        File file = new File(Main.getWorkingDirectory(), filename());
        if (!file.exists()) {
            file.createNewFile();
        }
        config.load(new FileInputStream(file));
        for (ConfigParameter param : parameters()) {
            String value = config.getProperty(param.getKey(), param.toString());
            param.setValue(parseValue(value, param.getType()));
        }
    }
    
    public void save() throws IOException {
        Properties config = new Properties();
        for (ConfigParameter param : parameters()) {
            config.setProperty(param.getKey(), param.toString());
        }
        config.store(new FileOutputStream(new File(Main.getWorkingDirectory(), filename())), "Novo SGA configuration file");
    }
    
    public ConfigParameter<String> get(String key) {
        return get(key, String.class);
    }
        
    public <T> ConfigParameter<T> get(String key, Class<T> type) {
        for (ConfigParameter param : parameters()) {
            if (param.getKey().equals(key)) {
                return param;
            }
        }
        return null;
    }

    private static <T extends Object> T parseValue(String value, Class<T> type) {
        Object v;
        if (type.isArray()) {
            T[] arr;
            Class<T> arrType = (Class<T>) ((Class<Object[]>) type).getComponentType();
            if (!value.isEmpty()) {
                Object[] values = value.toString().split(",");
                arr = (T[]) java.lang.reflect.Array.newInstance(arrType, values.length);
                for (int i = 0; i < values.length; i++) {
                    arr[i] = parseValue(values[i].toString(), arrType);
                }
            } else {
                arr = (T[]) java.lang.reflect.Array.newInstance(arrType, 0);
            }
            v = arr;
        } else if (type.isAssignableFrom(Integer.class)) {
            v = Integer.parseInt(value);
        } else if (type.isAssignableFrom(Double.class)) {
            v = Double.parseDouble(value);
        } else if (type.isAssignableFrom(Boolean.class)) {
            v = Boolean.parseBoolean(value);
        } else {
            v = value;
        }
        return type.cast(v);
    }
    
}
