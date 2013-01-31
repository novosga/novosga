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

import java.awt.Component;
import java.awt.Cursor;
import java.awt.GridBagConstraints;
import java.awt.GridBagLayout;
import java.awt.Insets;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.MalformedURLException;
import java.net.URL;
import java.nio.charset.Charset;
import java.util.Arrays;
import java.util.LinkedList;
import java.util.StringTokenizer;
import java.util.logging.Level;
import java.util.logging.Logger;

import javax.swing.JButton;
import javax.swing.JCheckBox;
import javax.swing.JComboBox;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JOptionPane;
import javax.swing.JPanel;
import javax.swing.JProgressBar;
import javax.swing.JScrollPane;
import javax.swing.JTextField;
import javax.swing.JTextPane;
import javax.swing.SwingUtilities;

import br.gov.dataprev.estruturadados.ConfiguracaoGlobal;
import br.gov.dataprev.estruturadados.ServCheck;
import br.gov.dataprev.estruturadados.VetServCheck;
import br.gov.dataprev.exec.Painel;
import br.gov.dataprev.painel.imagens.ImagesTable;

/**
 * @author DATAPREV
 * @version 1.0
 * @category Interface
 */
public class CPanel extends JFrame implements ActionListener {

    private static final Logger LOG = Logger.getLogger(CPanel.class.getName());
    private static final long serialVersionUID = 2833342489278580235L;
    private static final String DEFAULT_UNIDADE_LABEL = "Selecione";
    private static CPanel _Instance;
    private JPanel caixa;
    private JButton _salvarButton;
    private JButton _sair;
    private JTextField _IPServerField;
    private JTextPane _tServ;
    private VetServCheck listServ;
    private int width = 330;
    private int height = 480;
    private JComboBox _comboUnidades;
    private JProgressBar _progressBar;
    private LinkedList<Component> _componentesProtegidos = new LinkedList<Component>();

    public static CPanel getInstance() {
        if (_Instance == null) {
            _Instance = new CPanel();
        }
        return _Instance;
    }

    private CPanel() {
        this.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
        this.setSize(this.width, this.height);
        this.setLocationRelativeTo(null);
        this.setTitle("Configuração do painel");
        this.setVisible(true);
        this.setIconImage(ImagesTable.getInstance().getImage("tray.png"));
        this.setLayout(new GridBagLayout());

        GridBagConstraints c = new GridBagConstraints();
        c.gridx = 0;
        c.gridy = 0;
        c.weightx = 1;
        c.weighty = 0;
        c.gridwidth = 2;
        c.insets = new Insets(3, 3, 3, 3);
        c.fill = GridBagConstraints.BOTH;

        String ipServerStr = ConfiguracaoGlobal.getInstance().getIPServer();

        this.add(new JLabel("IP do Servidor"), c);

        _IPServerField = new JTextField();
        _IPServerField.setToolTipText("Digite o IP/Hostname do servidor. Ex: 10.71.0.51");
        _IPServerField.setText(ipServerStr);
        _IPServerField.addActionListener(this);

        c.gridwidth = 1;
        c.gridy++;
        this.add(_IPServerField, c);

        c.gridx++;
        JButton botaoObterServicos = new JButton("Obter unidades");
        botaoObterServicos.setToolTipText("Obtem as unidades do servidor.");
        botaoObterServicos.setActionCommand("carregar");
        botaoObterServicos.addActionListener(this);
        this.add(botaoObterServicos, c);
        c.gridx = 0;
        c.gridwidth = 2;

        _comboUnidades = new JComboBox();
        _comboUnidades.setEditable(false);
        _comboUnidades.setToolTipText("Selecione a unidade");
        _comboUnidades.addItem(DEFAULT_UNIDADE_LABEL);



        c.gridy++;
        this.add(_comboUnidades, c);

        c.gridy++;
        this.add(new JLabel("Serviços"), c);

        _tServ = new JTextPane();
        _tServ.setEditable(false);
        _tServ.setCursor(new Cursor(0));
        _tServ.setToolTipText("Configuração dos Serviços a serem exibidos pelo painel");
        listServ = new VetServCheck();

        c.gridy++;
        c.weighty = 1;
        this.add(new JScrollPane(_tServ), c);
        c.weighty = 0;


        _salvarButton = new JButton("Salvar");
        _salvarButton.setEnabled(false);
        _salvarButton.setToolTipText("Gravar configurações");
        _salvarButton.setMnemonic((!this._IPServerField.getText().equals("")) ? 'G' : 'C');
        _salvarButton.setActionCommand("salvar");
        _salvarButton.addActionListener(this);

        c.gridy++;
        c.gridwidth = 1;
        this.add(this._salvarButton, c);

        _sair = new JButton("Fechar");
        _sair.setToolTipText("Sair da configuração do painel");
        _sair.setMnemonic('S');
        _sair.addActionListener(this);
        _sair.setActionCommand("fechar");

        c.gridx++;
        this.add(_sair, c);
        c.gridwidth = 2;

        _progressBar = new JProgressBar();
        _progressBar.setIndeterminate(true);
        _progressBar.setVisible(false);

        c.gridy++;
        c.gridx = 0;
        this.add(_progressBar, c);

        _componentesProtegidos.add(botaoObterServicos);
        _componentesProtegidos.add(_IPServerField);
        _componentesProtegidos.add(_comboUnidades);
        _componentesProtegidos.add(_salvarButton);
        _componentesProtegidos.add(_sair);

        this.carregarConfiguracoes();
    }

    private void carregarConfiguracoes() {
        String serverHostStr = ConfiguracaoGlobal.getInstance().getIPServer();
        if (serverHostStr != null && serverHostStr.trim().length() > 0) {
            this.obterUnidades();
            boolean unidadeEncontrada = false;

            for (int i = 0; !unidadeEncontrada && i < _comboUnidades.getItemCount(); i++) {
                if (_comboUnidades.getItemAt(i) instanceof ComboItem) {
                    ComboItem ci = (ComboItem) _comboUnidades.getItemAt(i);
                    if (ci.getUnidadeId() == ConfiguracaoGlobal.getInstance().getUnidadeId()) {
                        _comboUnidades.setSelectedIndex(i);
                        unidadeEncontrada = true;
                        Mensagem.showMensagem("Unidade: " + ci.getNomeUnidade(), "Unidade");
                    }
                    Mensagem.showMensagem(ci.getNomeUnidade() + ": " + ci.getUnidadeId() + " != " + ConfiguracaoGlobal.getInstance().getUnidadeId(), "Unidade");
                }
            }

            if (unidadeEncontrada) {
                int[] servicos = ConfiguracaoGlobal.getInstance().getServicos();
                Arrays.sort(servicos);

                for (int i = 0; i < listServ.size(); i++) {
                    final int idServ = listServ.getServCheck(i).getId();
                    boolean servicoEncontrado = Arrays.binarySearch(servicos, idServ) >= 0;
                    listServ.setCheck(i, servicoEncontrado);
                }
            }
        }
    }

    private void obterUnidades() {
        ConfiguracaoGlobal.getInstance().setIPServer(_IPServerField.getText());
        if (!ConfiguracaoGlobal.getInstance().getIPServer().isEmpty()) {
            Runnable operacao = new Runnable() {
                @Override
                public void run() {
                    try {
                        Painel.getListener().obterURLs();
                        String urlUnidades = ConfiguracaoGlobal.getInstance().getUrlUnidades();
                        if (urlUnidades != null) {
                            CPanel.getInstance().preencheUnidades(urlUnidades);
                        }
                    } catch (Exception e) {
                        LOG.log(Level.SEVERE, "Falha obtendo unidades.", e);
                        Mensagem.showMensagem("Falha obtendo unidades.\nMotivo: " + e.getMessage(), "ERRO", 0, e);
                    }
                }
            };
            CPanel.this.executaOperacaoBloqueante(operacao, "Contactando servidor.");
        }
    }

    private void selecionarUnidade(final ComboItem ci, boolean force) {
        if (!force) {
            int result = JOptionPane.showConfirmDialog(CPanel.this.caixa, "Confirma unidade:\n" + ci.toString(), "Confirmação", JOptionPane.YES_NO_OPTION, JOptionPane.INFORMATION_MESSAGE, ImagesTable.INFO_ICON);
            force = result == JOptionPane.YES_OPTION;
        }

        if (force) {
            _progressBar.setVisible(true);

            Runnable r = new Runnable() {
                @Override
                public void run() {
                    try {
                        preencheServicos(ci.getUnidadeId());
                    } catch (MalformedURLException e) {
                        Mensagem.showMensagem("A URL enviada pelo servidor é inválida, entre em contato com o Administrador.\nURL: " + ConfiguracaoGlobal.getInstance().getUrlServicos() + "\nMotivo: " + e.getMessage(), "ERRO", 0, e);
                        e.printStackTrace();
                    } catch (IOException e) {
                        Mensagem.showMensagem("Falha cadastrando painel.\nMotivo: " + e.getMessage(), "ERRO", 0, e);
                        e.printStackTrace();
                    }
                }
            };

            CPanel.this.executaOperacaoBloqueante(r, "Obtendo serviços");

        }
    }

    private void preencheServicos(int idUnidade) throws MalformedURLException, IOException {
        LOG.fine("URL Servicos com unidade: " + ConfiguracaoGlobal.getInstance().getUrlServicos().replace("%id_unidade%", "" + idUnidade));
        URL con = new URL(ConfiguracaoGlobal.getInstance().getUrlServicos().replace("%id_unidade%", "" + idUnidade));
        BufferedReader load = new BufferedReader(new InputStreamReader(con.openStream(), Charset.forName("UTF-8")));

        String linha;
        this.listServ.clear();
        this._tServ.removeAll();

        final JCheckBox todos = new JCheckBox();
        todos.setBorder(null);
        todos.setBounds(0, 0, 15, 15);
        todos.addActionListener(new ActionListener() {
            public void actionPerformed(ActionEvent e) {
                boolean marca = (todos.isSelected()) ? true : false;
                for (int i = 1; i < listServ.size(); i++) {
                    listServ.setCheck(i, marca);
                }
            }
        });
        todos.setVisible(true);
        this.listServ.inserir(new ServCheck(-1, "Marcar todos", "", todos));

        int i = 1;
        int cont = 1;

        int id;
        String desc, sigla;
        while ((linha = load.readLine()) != null) {
            StringTokenizer str = new StringTokenizer(linha, "#");
            try {
                id = Integer.parseInt(str.nextToken());

                sigla = str.nextToken();
                desc = str.nextToken();


                JCheckBox check = new JCheckBox();
                check.setBorder(null);
                check.setBounds(0, 15 * i, 15, 15);
                check.setVisible(true);
                if (ConfiguracaoGlobal.getInstance().seInteressaPorServico(id)) {
                    check.setSelected(true);
                    cont++;
                }
                this.listServ.inserir(new ServCheck(id, desc, sigla, check));
                i++;
            } catch (NumberFormatException e) {
                // servidor enviou um ID inválido (nunca deve acontecer)
                e.printStackTrace();
            }
        }
        for (i = 0; i < this.listServ.size(); i++) {
            _tServ.add(this.listServ.getServCheck(i).getCheck());
            _tServ.add(this.listServ.getServCheck(i).getSigla());
            _tServ.add(this.listServ.getServCheck(i).getDescricao());

            _tServ.setText(this._tServ.getText() + "\n");
        }
        if (cont == listServ.size()) {
            listServ.setCheck(0, true);
        }

        _salvarButton.setEnabled(true);
    }

    public void preencheUnidades(String urlUnidades) {
        try {
            LOG.fine("Montando unidades a partir de: " + urlUnidades);
            if (urlUnidades != null && urlUnidades.length() > 0) {
                URL urlUni = new URL(urlUnidades);
                BufferedReader br = new BufferedReader(new InputStreamReader(urlUni.openStream(), Charset.forName("UTF-8")));
                String linha;

                for (ActionListener al : _comboUnidades.getActionListeners()) {
                    _comboUnidades.removeActionListener(al);
                }

                _comboUnidades.removeAllItems();
                _comboUnidades.addItem(DEFAULT_UNIDADE_LABEL);
                _comboUnidades.setEnabled(true);
                while ((linha = br.readLine()) != null) {
                    String[] parts = linha.split("#");
                    if (parts.length >= 3) {
                        String idStr = parts[0];
                        String codigoStr = parts[1];
                        String nome = parts[2];

                        try {
                            int id = Integer.parseInt(idStr);

                            ComboItem comboItem = new ComboItem(id, codigoStr, nome);
                            _comboUnidades.addItem(comboItem);
                            if (id == ConfiguracaoGlobal.getInstance().getUnidadeId()) {
                                // selecionar item
                                _comboUnidades.setSelectedIndex(_comboUnidades.getItemCount() - 1);

                                this.selecionarUnidade(comboItem, true);
                            }
                        } catch (NumberFormatException e) {
                            LOG.log(Level.SEVERE, "Servidor enviou uma linha inválida:\n" + linha, e);
                            JOptionPane.showMessageDialog(null, "Servidor enviou uma linha inválida:\n" + linha, "ERRO", JOptionPane.ERROR_MESSAGE);
                        }
                    }
                }

                // Habilita evento ao selecionar unidade
                _comboUnidades.addActionListener(new ActionListener() {
                    @Override
                    public void actionPerformed(ActionEvent e) {

                        Object selectedItem = _comboUnidades.getSelectedItem();
                        if (selectedItem instanceof ComboItem) {
                            CPanel.this.selecionarUnidade((ComboItem) selectedItem, false);
                        }
                    }
                });
            }

            _salvarButton.setEnabled(false);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private void setComponentesEnabled(boolean b) {
        for (Component c : _componentesProtegidos) {
            c.setEnabled(b);
        }
    }

    private void executaOperacaoBloqueante(final Runnable r, final String operacao) {
        Runnable async = new Runnable() {
            @Override
            public void run() {
                SwingUtilities.invokeLater(
                        new Runnable() {
                            @Override
                            public void run() {
                                CPanel.this.setComponentesEnabled(false);
                                _progressBar.setString(operacao);
                                _progressBar.setStringPainted(true);
                                _progressBar.setVisible(true);
                            }
                        });

                try {
                    r.run();
                } catch (Throwable t) {
                    t.printStackTrace();
                }

                SwingUtilities.invokeLater(
                        new Runnable() {
                            @Override
                            public void run() {
                                CPanel.this.setComponentesEnabled(true);
                                _progressBar.setVisible(false);
                            }
                        });
            }
        };
        Thread t = new Thread(async, "Thread de Operação Assíncrona");
        t.start();
    }

    @Override
    public void actionPerformed(ActionEvent e) {
        String cmd = e.getActionCommand();

        if (cmd.equals("carregar")) {
            CPanel.this.obterUnidades();
        } else if (cmd.equals("salvar")) {
            Object selectedItem = _comboUnidades.getSelectedItem();
            if (selectedItem instanceof ComboItem) {
                System.err.println("SALVANDO");
                final ComboItem ci = (ComboItem) selectedItem;
                final int[] servicos = CPanel.this.listServ.getServ();

                Runnable r = new Runnable() {
                    @Override
                    public void run() {
                        try {
                            int unidadeId = ci.getUnidadeId();
                            Painel.getListener().cadastrarPainel(unidadeId, servicos);
                            ConfiguracaoGlobal.getInstance().setServicos(servicos);
                            ConfiguracaoGlobal.getInstance().setUnidadeId(unidadeId);
                            ConfiguracaoGlobal.getInstance().salvarConfiguracao();
                            Mensagem.showMensagem("Painel cadastrado no servidor com sucesso.", "Painel Cadastrado");
                            CPanel.this.setVisible(false);
                        } catch (Exception exc) {
                            Mensagem.showMensagem("Falha cadastrando painel.\nMotivo: " + exc.getMessage(), "ERRO", 0, exc);
                            exc.printStackTrace();
                        }
                    }
                };


                CPanel.this.executaOperacaoBloqueante(r, "Cadastrando Painel");
            }
        } else if (cmd.equals("fechar")) {
            CPanel.this.setVisible(false);
        }
    }

    public static class ComboItem {

        private final int _unidadeId;
        private final String _codigoUnidade;
        private final String _nome;

        public ComboItem(int id, String codigoUnidade, String nome) {
            _unidadeId = id;
            _codigoUnidade = codigoUnidade;
            _nome = nome;
        }

        /**
         * ID <B>interno</B> da unidade auto-gerado pelo sistema, em geral
         * invisível para os usuários.
         *
         * @return o ID interno da unidade
         */
        public int getUnidadeId() {
            return _unidadeId;
        }

        /**
         * Código da unidade, definido pela regra de negócio
         *
         * @return O código da unidade
         */
        public String getCodigoUnidade() {
            return _codigoUnidade;
        }

        /**
         * Nome da unidade
         *
         * @return o Nome da unidade
         */
        public String getNomeUnidade() {
            return _nome;
        }

        @Override
        public String toString() {
            return this.getCodigoUnidade() + " - " + this.getNomeUnidade();
        }
    }
}
