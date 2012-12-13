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

import java.awt.Font;
import java.awt.GridBagConstraints;
import java.awt.GridBagLayout;
import java.awt.event.ComponentAdapter;
import java.awt.event.ComponentEvent;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;
import java.util.LinkedList;

import javax.swing.JLabel;
import javax.swing.JPanel;
import javax.swing.event.ChangeEvent;
import javax.swing.event.ChangeListener;

import br.gov.dataprev.estruturadados.ConfLayout;
import br.gov.dataprev.painel.imagens.ImagesTable;

/**
 * @author ulysses
 *
 */
@SuppressWarnings("serial")
public class PainelPane extends JPanel {

    private Font fontMsgEspecial = new Font(Web.FONT_NAME, Font.BOLD, 100);
    private Font fontSenha = new Font(Web.FONT_NAME, Font.BOLD, 270);
    private Font fontGuiche = new Font(Web.FONT_NAME, Font.BOLD, 120);
    private JLabel msg_especial, senha, guiche;
    private JLabel degradeImg;
    private static final int TAMANHO_FONTE_MSG_ESPECIAL = 100;
    private static final int TAMANHO_FONTE_SENHA = 270;
    private static final int TAMANHO_FONTE_GUICHE = 120;
    private final LinkedList<ChangeListener> _changeListeners = new LinkedList<ChangeListener>();

    public PainelPane() {
        this.setLayout(new GridBagLayout());

        //this.setToolTipText("Clique para modificar a cor de fundo");

        GridBagConstraints cons = new GridBagConstraints();
        cons.gridx = 0;
        cons.gridy = 0;
        cons.weightx = 1;
        cons.weighty = 0;
        cons.anchor = GridBagConstraints.WEST;
        cons.fill = GridBagConstraints.VERTICAL;

        cons.weighty = 0.275;
        this.add(this.getLabelMsgEspecial(), cons);

        cons.gridy++;
        cons.weighty = 0.425;
        degradeImg = new JLabel(ImagesTable.getInstance().getImageIcon("degrade_background.png", 200, 65));
        this.add(degradeImg, cons);
        this.add(this.getLabelSenha(), cons);

        cons.gridy++;
        cons.weighty = 0.3;
        this.add(this.getLabelGuiche(), cons);

        //this.add(this.getNumGuiche());

        this.addMouseListener(new MouseAdapter() {
            public void mouseReleased(MouseEvent e) {
                ChangeEvent ce = new ChangeEvent(PainelPane.this);
                for (ChangeListener cl : _changeListeners) {
                    cl.stateChanged(ce);
                }
            }
        });

        this.addComponentListener(new ComponentAdapter() {
            public void componentResized(ComponentEvent e) {
                //System.out.println(e.getComponent().getWidth()+" -> componentResized "+e);
                double multiplicador = e.getComponent().getWidth() / 800.0;

                fontMsgEspecial = new Font(Web.FONT_NAME, Font.BOLD, (int) (TAMANHO_FONTE_MSG_ESPECIAL * multiplicador));
                fontGuiche = new Font(Web.FONT_NAME, Font.BOLD, (int) (TAMANHO_FONTE_GUICHE * multiplicador));
                fontSenha = new Font(Web.FONT_NAME, Font.BOLD, (int) (TAMANHO_FONTE_SENHA * multiplicador));

                msg_especial.setFont(fontMsgEspecial);
                guiche.setFont(fontGuiche);
                senha.setFont(fontSenha);

                int h = (int) (e.getComponent().getHeight() * 0.425);
                //degradeImg.setIcon(ImagesTable.getInstance().getImageIcon("degrade_background.png", senha.getWidth(), h));
            }
        });
    }

    public void addChangeListener(ChangeListener cl) {
        _changeListeners.add(cl);
    }

    public boolean removeChangeListener(ChangeListener cl) {
        return _changeListeners.remove(cl);
    }

    public void aplicarLayout(ConfLayout confLayout) {
        this.setBackground(confLayout.getCorFundo());
        this.msg_especial.setForeground(confLayout.getCorMsgEspecial());
        this.senha.setForeground(confLayout.getCorSenha());
        this.guiche.setForeground(confLayout.getCorGuiche());
        //this.numGuiche.setForeground(confLayout.getCorGuiche());
    }

    /**
     * This method initializes msg_especial
     *
     * @return javax.swing.JLabel
     */
    public JLabel getLabelMsgEspecial() {
        if (this.msg_especial == null) {
            this.msg_especial = new JLabel();
            this.msg_especial.setText("SENHA");
            this.msg_especial.setFont(fontMsgEspecial);
            //this.msg_especial.setBounds(2, -2, 95, 31);
            this.msg_especial.setToolTipText("Clique para modificar a cor da mensagem especial");
            this.msg_especial.addMouseListener(new MouseAdapter() {
                public void mouseReleased(MouseEvent e) {
                    ChangeEvent ce = new ChangeEvent(msg_especial);
                    for (ChangeListener cl : _changeListeners) {
                        cl.stateChanged(ce);
                    }
                }
            });
        }
        return this.msg_especial;
    }

    /**
     * This method initializes senha
     *
     * @return javax.swing.JLabel
     */
    public JLabel getLabelSenha() {
        if (this.senha == null) {
            this.senha = new JLabel();
            this.senha.setText("A0000");
            this.senha.setFont(this.fontSenha);
            //this.senha.setBounds(2, 38, 220, 50);
            this.senha.setToolTipText("Clique para modificar a cor da senha");
            this.senha.addMouseListener(new MouseAdapter() {
                public void mouseReleased(MouseEvent e) {
                    ChangeEvent ce = new ChangeEvent(senha);
                    for (ChangeListener cl : _changeListeners) {
                        cl.stateChanged(ce);
                    }
                }
            });
        }
        return this.senha;
    }

    /**
     * This method initializes guiche
     *
     * @return javax.swing.JLabel
     */
    public JLabel getLabelGuiche() {
        if (this.guiche == null) {
            this.guiche = new JLabel();
            this.guiche.setText("Mesa: 000");
            this.guiche.setFont(this.fontGuiche);
            //this.guiche.setBounds(2, 113, 196, 31);
            this.guiche.setToolTipText("Clique para modificar a cor do guichê");
            this.guiche.addMouseListener(new MouseAdapter() {
                public void mouseReleased(MouseEvent e) {
                    ChangeEvent ce = new ChangeEvent(guiche);
                    for (ChangeListener cl : _changeListeners) {
                        cl.stateChanged(ce);
                    }
                }
            });
        }
        return this.guiche;
    }
}
