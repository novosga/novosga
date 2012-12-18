/**
 *
 * Copyright (C) 2009 DATAPREV - Empresa de Tecnologia e Informações da
 * Previdência Social - Brasil
 *
 * Este arquivo é parte do programa SGA Livre - Sistema de Gerenciamento do
 * Atendimento - Versão Livre
 *
 * O SGA é um software livre; você pode redistribuí­-lo e/ou modificá-lo dentro
 * dos termos da Licença Pública Geral GNU como publicada pela Fundação do
 * Software Livre (FSF); na versão 2 da Licença, ou (na sua opnião) qualquer
 * versão.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM NENHUMA
 * GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO ou
 * APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU para maiores
 * detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, escreva para a Fundação do
 * Software Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301
 * USA.
 *
 *
 */
package br.gov.dataprev.userinterface;

import java.awt.AWTException;
import java.awt.Cursor;
import java.awt.DisplayMode;
import java.awt.Font;
import java.awt.FontFormatException;
import java.awt.GraphicsDevice;
import java.awt.GraphicsEnvironment;
import java.awt.Image;
import java.awt.Point;
import java.awt.Toolkit;
import java.awt.Window;
import java.io.IOException;
import java.util.logging.Level;
import java.util.logging.Logger;

import javax.swing.JEditorPane;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JWindow;

import br.gov.dataprev.estruturadados.ConfLayout;
import br.gov.dataprev.estruturadados.ConfiguracaoGlobal;
import br.gov.dataprev.estruturadados.VetSenhas;
import br.gov.dataprev.painel.event.PainelMouseListener;
import br.gov.dataprev.userinterface.display.Display;
import br.gov.dataprev.userinterface.display.Label;

/**
 * @author DATAPREV
 * @version 1.0
 * @category Interface
 */
public class Web {

    private static final Logger LOG = Logger.getLogger(Web.class.getName());
    public static final String[] FONTS_FILES = {"DroidSans-Bold.ttf", "Vera.ttf", "VeraBd.ttf", "VeraBI.ttf", "VeraIt.ttf", "VeraMoBd.ttf", "VeraMoBI.ttf", "VeraMoIt.ttf", "VeraMono.ttf", "VeraSe.ttf", "VeraSeBd.ttf"};
    public static final String FONT_NAME = "Droid Sans Bold";
    public static final String FONT_NAME2 = "Bitstream Vera Sans";
    private Window caixa;
    private Thread contadorDePulso;
    private JEditorPane painel;
    private int senha;
    private VetSenhas listaSenhas;
    private double multiplicadorLargura;
    private double multiplicadorAltura;
    private Robo _robo;
    private Label lSenha;
    private Label lServ;
    private Label lMesa;
    private Label lNumMesa;
    //private String confPainel;
    private Font fontBase;
    private double tamServ;
    private double tamMesa;
    private double tamNum;
    private static Web _Instance;

    public static Web getInstance() {
        if (_Instance == null) {
            _Instance = new Web();
        }
        return _Instance;
    }

    /**
     * Contrutor GUI de exibição do painel
     */
    private Web() {
        Font f = null;
        for (String fontName : FONTS_FILES) {
            try {
                f = Font.createFont(Font.TRUETYPE_FONT, Web.class.getResourceAsStream("fonts/" + fontName));
                GraphicsEnvironment.getLocalGraphicsEnvironment().registerFont(f);
            } catch (FontFormatException e) {
                throw new RuntimeException("Erro interno carregando fonte padrão. (" + fontName + ")", e);
            } catch (IOException e) {
                throw new RuntimeException("Erro interno carregando fonte padrão. (" + fontName + ")", e);
            }
        }

        fontBase = new Font(Web.FONT_NAME, Font.BOLD, 235);

        LOG.fine("Painel Font Family: " + fontBase.getFamily());

        try {
            this.listaSenhas = new VetSenhas(30);
            // inicio do contador de tempo
            this.contadorDeTempo();
            this.init();
        } catch (Exception e) {
            LOG.log(Level.SEVERE, "Existe outro painel do SGA em execução.", e);
            Mensagem.showMensagem("O Painel SGA já está sendo executado.", "PAINEL", 1);
        }
    }

    public void setVisible(boolean b) {
        this.caixa.setVisible(b);
    }

    public boolean isVisible() {
        return this.caixa.isVisible();
    }
    
    public Label getLabelSenha() {
        return lSenha;
    }

    /*
     * Inicialização da GUI
     */
    private void init() {
        GraphicsDevice[] sds = GraphicsEnvironment.getLocalGraphicsEnvironment().getScreenDevices();
        GraphicsDevice device = GraphicsEnvironment.getLocalGraphicsEnvironment().getDefaultScreenDevice();
        if (ConfiguracaoGlobal.getInstance().isDualVideo() && sds.length > 1) {
            for (GraphicsDevice gd : sds) {
                if (gd != device) {
                    device = gd;
                    break;
                }
            }
        }
        Display display = new Display(device);

        LOG.info("Painel ativo em: " + device.getIDstring());
        try {
            setRobo(new Robo(device));
            getRobo().setDesativarProtecaoTela(ConfLayout.getInstance().getDesativarProtecaoTela());
            LOG.info("Proteção de tela desativada.");
        } catch (AWTException e) {
            LOG.log(Level.WARNING, "Não foi possível desativar proteção de tela.", e);
        }

        this.setMultiplicadores(display);

        if (System.getProperty("args").contains("-developer")) {
            this.caixa = new JFrame();
            JFrame temp = (JFrame) this.caixa;
            temp.setUndecorated(true);
            temp.setAlwaysOnTop(true);
        } else {
            LOG.info("Suporte a exibição em tela cheia: " + device.isFullScreenSupported());
            DisplayMode dm = device.getDisplayMode();

            LOG.info("Modo de exibição atual: " + dm.getWidth() + "x" + dm.getHeight() + " " + dm.getBitDepth() + "bpp " + dm.getRefreshRate() + "hz");

            boolean fullscreenOk = this.criaFullscreen(device);
            if (!fullscreenOk) {
                this.emularFullScreen(device);
            }
        }


        this.painel = new JEditorPane();

        String msgServ = "Atendimento", numSenha = "A0139", guiche = "Guichê:", numero = "000";

        this.senha = this.listaSenhas.ultimoChamado();
        try {
            if (this.senha < 0) {
                this.senha = 0;
                this.listaSenhas.getSenha(this.senha).setStatus(true);
            }
            msgServ = this.listaSenhas.getSenha(this.senha).getMsgEspecial();
            numSenha = this.listaSenhas.getSenha(this.senha).getSenha();
            guiche = this.listaSenhas.getSenha(this.senha).getGuiche();
            numero = this.listaSenhas.getSenha(this.senha).getNumGuiche();
        } catch (Exception er) {
            System.out.println("Fila vazia");
        }

        this.painel.setEditable(false);
        this.painel.setBorder(null);
        this.painel.setBounds(0, 0, display.getWidth(), display.getHeight());

        // servico
        tamServ = 80;
        this.lServ = new Label(display, (int) (tamServ * multiplicadorLargura), msgServ);
        this.lServ.setWidth(1.0);
        this.lServ.setHeight((int) (tamServ * 1.2 * multiplicadorAltura));
        this.lServ.setPosition(15, 5);
        this.lServ.add(painel);

        // guiche
        tamNum = 210;
        tamMesa = 120;
        if (guiche.length() >= 6 && numero.length() >= 3) {
            tamNum = 150;
            tamMesa = 100;
        }
        
        int guicheAltura = (int) Math.max(tamNum, tamMesa);
        guicheAltura *= 1.2 * multiplicadorAltura;
        
        // guiche - nome
        this.lMesa = new Label(display, (int) (tamMesa * this.multiplicadorLargura), guiche);
        this.lMesa.setWidth(1.0);
        this.lMesa.setHeight(guicheAltura);
        this.lMesa.setX(15);
        this.lMesa.setY(display.getHeight() - this.lMesa.getHeight());
        this.lMesa.add(this.painel);

        // guiche - numero
        this.lNumMesa = new Label(display, (int) (tamNum * this.multiplicadorLargura), numero);
        this.lNumMesa.setWidth(display.getWidth() - 15);
        this.lNumMesa.setHeight(guicheAltura);
        this.lNumMesa.setY(display.getHeight() - this.lNumMesa.getHeight());
        this.lNumMesa.setX(0); 
        this.lNumMesa.add(this.painel);
        this.lNumMesa.getJLabel().setHorizontalAlignment(JLabel.RIGHT);
        
        // senha
        this.lSenha = new Label(display, 14, numSenha);
        this.lSenha.setFontFamily(FONT_NAME2);
        this.lSenha.setWidth(display.getWidth());
        this.lSenha.setHeight(display.getHeight() - guicheAltura - lServ.getHeight());
        this.lSenha.setX(0);
        this.lSenha.setY((int) (this.lServ.getHeight() * 1.1));
        this.lSenha.add(this.painel);
        this.lSenha.setFontSize(calcFontSize(lSenha));
        this.lSenha.getJLabel().setHorizontalAlignment(JLabel.CENTER);

        aplicarLayout();
        this.caixa.add(this.painel);

        setAlwaysOnTop(true);
        this.caixa.setVisible(true);
        this.caixa.setSize(display.getWidth(), display.getHeight());

        this.painel.addMouseListener(new PainelMouseListener());

        this.exibirSenha(msgServ, guiche, "000", "-----");
    }
    
    private boolean criaFullscreen(GraphicsDevice device) {
        if (!device.isFullScreenSupported() || !device.isDisplayChangeSupported()) {
            return false;
        }

        this.caixa = new JFrame(device.getDefaultConfiguration());
        device.setFullScreenWindow(this.caixa);
        device.setDisplayMode(device.getDisplayMode());
        LOG.info("Tela Cheia OK: " + (device.getFullScreenWindow().equals(this.caixa)));

        return device.getFullScreenWindow() == this.caixa;
    }

    private void emularFullScreen(GraphicsDevice device) {
        LOG.info("Emulando Tela Cheia...");
        this.caixa = new JWindow(device.getDefaultConfiguration());
        device.setFullScreenWindow(this.caixa);
    }

    /**
     * abilita ou desabilita ponteiro de mouse
     *
     * @param flag
     */
    private void setCursor(boolean flag) {
        if (flag) {
            // apaga o cursor do mouse passando uma imagem inexistente
            Image imagemCursor = Toolkit.getDefaultToolkit().getImage("INVALIDA.gif");
            Cursor cursorTransparente = Toolkit.getDefaultToolkit().createCustomCursor(imagemCursor, new Point(0, 0), "");
            this.caixa.setCursor(cursorTransparente);
        } else {
            this.caixa.setCursor(Cursor.getDefaultCursor());
        }
    }

    /**
     * Desaloca o objeto web da mem�ria
     */
    public void close() {
        this.caixa.dispose();
    }

    private void contadorDeTempo() {
        this.contadorDePulso = new Thread(new UDPListener(), "UDP Receptor");
        this.contadorDePulso.setDaemon(true);
        this.contadorDePulso.start();
    }

    public void exibirSenha(String _msgEspecial, String _guiche, String _numeroGuiche, String _senha) {
        lServ.setText(_msgEspecial);
        lMesa.setText(_guiche);
        lNumMesa.setText(_numeroGuiche);
        String numSenha = _senha;

        lSenha.setText(numSenha);
        lSenha.setFontSize(calcFontSize(lSenha));
    }
    
    private int calcFontSize(Label label) {
        JLabel jl = label.getJLabel();
        int stringWidth = jl.getFontMetrics(jl.getFont()).stringWidth(label.getText());
        int charWidth = (int) (stringWidth / label.getText().length());
        double widthRatio = label.getWidth() / (double) stringWidth;
        int fontSize = (int) (jl.getFont().getSize() * widthRatio);
        fontSize *= .9;
        System.out.println("TEXT: " + label.getText() + ", StringWidth: " + stringWidth + ", LabelWidth: " + label.getWidth() + ", CharWidth: " + charWidth + ", RATIO: " + widthRatio + ", FontSize: " + fontSize);
        return fontSize;
    }

    public void aplicarLayout(ConfLayout confLayout) {
        painel.setBackground(confLayout.getCorFundo());
        lServ.setColor(confLayout.getCorMsgEspecial());
        lSenha.setColor(confLayout.getCorSenha());
        lMesa.setColor(confLayout.getCorGuiche());
        lNumMesa.setColor(confLayout.getCorGuiche());
    }

    private void aplicarLayout() {
        this.aplicarLayout(new ConfLayout());
    }

    /**
     * Verifica se existe alguma chamada de senha
     *
     * @return
     */
    public int controleChamada() {
        return this.listaSenhas.proximoFila();
    }

    /**
     * abilita e desabilita exibição no topo abilita e desabilita o ponteiro do
     * mouse
     *
     * @param flag
     */
    public void setAlwaysOnTop(boolean flag) {
        this.setCursor(true);
        this.caixa.setAlwaysOnTop(true);
        if (!ConfiguracaoGlobal.getInstance().isDualVideo()) {
            this.setCursor(flag);
            this.caixa.setAlwaysOnTop(flag);
        }
    }

    private void setMultiplicadores(Display display) {
        this.multiplicadorLargura = display.getWidth() / 800.0;
        this.multiplicadorAltura = display.getHeight() / 600.0;
    }

    private void setRobo(Robo robo) {
        _robo = robo;
    }

    public Robo getRobo() {
        return _robo;
    }

}