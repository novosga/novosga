package br.gov.dataprev.userinterface.display;

import br.gov.dataprev.userinterface.Web;
import java.awt.Color;
import java.awt.Font;
import javax.swing.JEditorPane;
import javax.swing.JLabel;

/**
 * Um simples label
 * @author rogeriolino
 */
public class Label extends DisplayElement {
    
    private String text;
    private Color color;
    private int fontSize;
    private String fontFamily = Web.FONT_NAME;
    private JLabel label;

    public Label(Display display, int fontSize) {
        this(display, fontSize, "");
    }

    public Label(Display display, int fontSize, String text) {
        super(display);
        this.fontSize = fontSize;
        this.text = text;
    }

    public int getFontSize() {
        return fontSize;
    }

    public void setFontSize(int fontSize) {
        this.fontSize = fontSize;
        updateFont();
    }

    public String getFontFamily() {
        return fontFamily;
    }

    public void setFontFamily(String fontFamily) {
        this.fontFamily = fontFamily;
        updateFont();
    }

    public String getText() {
        return text;
    }

    public void setText(String text) {
        this.text = text;
        if (label != null) {
            label.setText(this.text);
        }
    }

    public Color getColor() {
        return color;
    }

    public void setColor(Color color) {
        this.color = color;
        if (label != null) {
            label.setForeground(color);
        }
    }
    
    public JLabel getJLabel() {
        return label;
    }
    
    private void updateFont() {
        if (label != null) {
            label.setFont(new Font(fontFamily, Font.BOLD, fontSize));
        }
    }

    @Override
    public void add(JEditorPane panel) {
        label = new JLabel(this.text);
        label.setBounds(getX(), getY(), getWidth(), getHeight());
        label.setFont(new Font(fontFamily, Font.BOLD, fontSize));
        label.setAlignmentX(JLabel.CENTER_ALIGNMENT);
        label.setAlignmentY(JLabel.CENTER_ALIGNMENT);
        label.setVerticalAlignment(JLabel.CENTER);
        panel.add(label);
    }
    
}
