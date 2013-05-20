package org.novosga.painel.client.ui;

import java.awt.AWTException;
import java.awt.MenuItem;
import java.awt.PopupMenu;
import java.awt.SystemTray;
import java.awt.TrayIcon;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import javafx.application.Platform;
import javax.swing.ImageIcon;
import org.novosga.painel.client.Main;

/**
 * @author DATAPREV
 * @category Interface
 */
public class SysTray implements ActionListener {

    private static final Logger LOG = Logger.getLogger(SysTray.class.getName());
    private static Main _main;

    /**
     *
     * @throws AWTException
     */
    public SysTray(Main main) {
        _main = main;
        if (SystemTray.isSupported()) {
            LOG.fine("O sistema possui suporte a ícone de bandeja.");

            SystemTray sys = SystemTray.getSystemTray();

            // cria o menu popup
            PopupMenu popup = new PopupMenu();

            MenuItem miExibe = new MenuItem(Main._("exibir_painel"));
            miExibe.setActionCommand("exibir");
            miExibe.addActionListener(this);
            popup.add(miExibe);

            MenuItem miConf = new MenuItem(Main._("configurar"));
            miConf.setActionCommand("configurar");
            miConf.addActionListener(this);
            popup.add(miConf);

            MenuItem miSobre = new MenuItem(Main._("sobre"));
            miSobre.setActionCommand("sobre");
            miSobre.addActionListener(this);
            popup.add(miSobre);

            popup.addSeparator();

            MenuItem miSair = new MenuItem(Main._("sair"));
            miSair.setActionCommand("sair");
            miSair.addActionListener(this);
            popup.add(miSair);

            // constroi o system tray
            try {
                URL icon = new URL(new URL("file:"), "ui/img/tray.png");
                TrayIcon trayIcon = new TrayIcon(new ImageIcon(icon).getImage(), "Painel Novo SGA", popup);
                // Ajusta ao tamanho do respectivo Sistema Operacional automaticamente
                trayIcon.setImageAutoSize(true);
                // adiciona imagem do system tray
                try {
                    sys.add(trayIcon);
                    LOG.fine("Ícone de bandeja exibido com sucesso.");
                } catch (AWTException e) {
                    throw new RuntimeException(Main._("erro_systray", e.getMessage()));
                }
            } catch (MalformedURLException e) {
                LOG.severe("Imagem do Systray não encontrada!");
            }
        } else {
            throw new RuntimeException(Main._("erro_systray_nao_suportado"));
        }
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
            int r = JOptionPane.showConfirmDialog(null, Main._("sair_realmente"));
            if (r == 0) {
                System.exit(0);
            }
        }
    }
}
