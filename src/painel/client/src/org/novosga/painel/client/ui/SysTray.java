package org.novosga.painel.client.ui;

import java.awt.AWTException;
import java.awt.Menu;
import java.awt.MenuItem;
import java.awt.PopupMenu;
import java.awt.SystemTray;
import java.awt.TrayIcon;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.net.URL;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import javafx.application.Platform;
import javax.swing.ImageIcon;
import javax.swing.UIManager;
import javax.swing.UnsupportedLookAndFeelException;
import org.novosga.painel.client.Main;

/**
 * 
 * @author rogeriolino
 */
public class SysTray implements ActionListener {

    private static final Logger LOG = Logger.getLogger(SysTray.class.getName());
    private static Main _main;

    
    public SysTray(Main main, URL icon) {
        _main = main;
        if (SystemTray.isSupported()) {
            setLookAndFeel();
            LOG.fine("O sistema possui suporte a ícone de bandeja.");
            // cria o menu popup
            PopupMenu popup = new PopupMenu();
            addMenuItem(popup, "exibir_painel", "exibir");
            addMenuItem(popup, "configurar", "configurar");
            addMenuItem(popup, "sobre", "sobre");
            popup.addSeparator();
            addMenuItem(popup, "sair", "sair");

            // constroi o system tray
            TrayIcon trayIcon = new TrayIcon(new ImageIcon(icon).getImage(), "Painel Novo SGA", popup);
            // Ajusta ao tamanho do respectivo Sistema Operacional automaticamente
            trayIcon.setImageAutoSize(true);
            // adiciona imagem do system tray
            try {
                SystemTray.getSystemTray().add(trayIcon);
                LOG.fine("Ícone de bandeja exibido com sucesso.");
            } catch (AWTException e) {
                throw new RuntimeException(Main._("erro_systray", e.getMessage()));
            }
        } else {
            throw new RuntimeException(Main._("erro_systray_nao_suportado"));
        }
    }
    
    private void addMenuItem(Menu menu, String label, String action) {
        MenuItem item = new MenuItem(Main._(label));
        item.setActionCommand(action);
        item.addActionListener(this);
        menu.add(item);
    }

    @Override
    public void actionPerformed(ActionEvent e) {
        String cmd = e.getActionCommand();
        if (cmd.equals("configurar")) {
            Platform.runLater(new Runnable() {
                @Override
                public void run() {
                    SysTray._main.getController().show();
                }
            });
        } else if (cmd.equals("exibir")) {
            Platform.runLater(new Runnable() {
                @Override
                public void run() {
                    SysTray._main.getPainel().show();
                }
            });
        } else if (cmd.equals("sobre")) {
            String title = Main._("sobre");
            String msg = "Novo Painel v" + Main.version + "\n";
            msg += "Esse software faz parte do projeto Novo SGA.\n\n";
            msg += "Visite: http://novosga.org";
            JOptionPane.showMessageDialog(null, msg, title, JOptionPane.INFORMATION_MESSAGE);
        } else if (cmd.equals("sair")) {
            int r = JOptionPane.showConfirmDialog(null, Main._("sair_realmente"), "Info", JOptionPane.YES_NO_OPTION);
            if (r == 0) {
                System.exit(0);
            }
        }
    }
    
    private void setLookAndFeel() {
        try {
            UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
        } catch (ClassNotFoundException | InstantiationException | IllegalAccessException | UnsupportedLookAndFeelException ex) {
            Logger.getLogger(SysTray.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
}
