package org.novosga.painel.client.ui;

import java.awt.AWTException;
import java.awt.MenuItem;
import java.awt.PopupMenu;
import java.awt.SystemTray;
import java.awt.TrayIcon;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
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

            MenuItem miExibe = new MenuItem("Exibir Painel");
            miExibe.setActionCommand("exibir");
            miExibe.addActionListener(this);
            popup.add(miExibe);

            MenuItem miConf = new MenuItem("Configurar");
            miConf.setActionCommand("configurar");
            miConf.addActionListener(this);
            popup.add(miConf);

            MenuItem miSobre = new MenuItem("Sobre");
            miSobre.setActionCommand("sobre");
            miSobre.addActionListener(this);
            popup.add(miSobre);

            popup.addSeparator();

            MenuItem miSair = new MenuItem("Sair");
            miSair.setActionCommand("sair");
            miSair.addActionListener(this);
            popup.add(miSair);

            // constroi o system tray
            URL icon = this.getClass().getResource("tray.png");
            TrayIcon trayIcon = new TrayIcon(new ImageIcon(icon).getImage(), "Painel Novo SGA", popup);

            // Ajusta ao tamanho do respectivo Sistema Operacional automaticamente
            trayIcon.setImageAutoSize(true);

            // adiciona imagem do system tray
            try {
                sys.add(trayIcon);
                LOG.fine("Ícone de bandeja exibido com sucesso.");
            } catch (AWTException e) {
                throw new RuntimeException("Falha ao adicionar o Ícone na bandeja.\nDetalhe: " + e.getMessage());
            }
        } else {
            throw new RuntimeException("Seu sistema não suporta Ícone de bandeja.");
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
            String title = "Sobre";
            String msg = "Novo Painel\n";
            msg += "Esse software faz parte do projeto Novo SGA.\n\n";
            msg += "Website: http://novosga.org";
            JOptionPane.showMessageDialog(null, msg, title, JOptionPane.INFORMATION_MESSAGE);
        } else if (cmd.equals("sair")) {
            int r = JOptionPane.showConfirmDialog(null, "Deseja realmente sair?");
            if (r == 0) {
                System.exit(0);
            }
        }
    }
}
