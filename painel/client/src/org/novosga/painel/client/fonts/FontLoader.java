package org.novosga.painel.client.fonts;

import java.awt.FontFormatException;
import java.io.IOException;
import javafx.scene.text.Font;

/**
 *
 * @author rogeriolino
 */
public class FontLoader {
    
    private static final String[] FONTS = new String[]{
        "DroidSans-Bold.ttf", 
        "Vera.ttf", "VeraBd.ttf", "VeraBI.ttf", "VeraIt.ttf", "VeraMoBd.ttf", "VeraMoBI.ttf", "VeraMoIt.ttf", "VeraMono.ttf", "VeraSe.ttf", "VeraSeBd.ttf"
    };
    
    public static final String DROID_SANS = "Droid Sans Bold";
    public static final String BITSTREAM_VERA_SANS = "Bitstream Vera Sans";
    
    public static Font load(String fontName) throws FontFormatException, IOException {
        return Font.loadFont(FontLoader.class.getResourceAsStream(fontName), 12);
    }

    public static void registerAll() {
        Font f;
        for (String fontName : FONTS) {
            try {
                f = FontLoader.load(fontName);
            } catch (Exception e) {
                throw new RuntimeException("Erro interno carregando fonte: " + fontName, e);
            }
        }
    }
    
}
