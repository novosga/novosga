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

import java.util.logging.Level;
import java.util.logging.Logger;

import javax.swing.JFrame;
import javax.swing.JProgressBar;
import javax.swing.SwingUtilities;

import br.gov.dataprev.estruturadados.ConfiguracaoGlobal;
import br.gov.dataprev.exec.Painel;

/**
 * @author ulysses
 *
 */
@SuppressWarnings("serial")
public class Loader extends JFrame {

    private static final Logger LOG = Logger.getLogger(Loader.class.getName());
    private JProgressBar _progressBar = new JProgressBar();

    public Loader() {
        this.setSize(400, 60);
        this.setTitle("Painel");
        this.setLocationRelativeTo(null);
        this.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);

        this.add(_progressBar);
    }

    public void recadastrarPainel() {
        Runnable r = new Runnable() {
            @Override
            public void run() {
                try {
                    int unidadeId = ConfiguracaoGlobal.getInstance().getUnidadeId();
                    int[] servicos = ConfiguracaoGlobal.getInstance().getServicos();
                    Painel.getListener().cadastrarPainel(unidadeId, servicos);
                    Web.getInstance().setVisible(true);
                    LOG.info("Painel cadastrado com sucesso.");
                } catch (Exception exc) {
                    LOG.log(Level.SEVERE, "Falha cadastrando painel.", exc);
                    Mensagem.showMensagem("Falha cadastrando painel.\nMotivo: " + exc.getMessage(), "ERRO", 0, exc);
                    CPanel.getInstance().setVisible(true);
                }
            }
        };


        this.executaOperacaoBloqueante(new SwingRunnable(r), "Cadastrando Painel");
    }

    private void executaOperacaoBloqueante(final Runnable r, final String operacao) {
        Runnable async = new Runnable() {
            @Override
            public void run() {
                SwingUtilities.invokeLater(
                        new Runnable() {
                            @Override
                            public void run() {
                                _progressBar.setIndeterminate(true);
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
                                _progressBar.setVisible(false);
                                Loader.this.setVisible(false);
                            }
                        });
            }
        };
        Thread t = new Thread(async, "Thread de Operação Assíncrona");
        t.start();
    }
}
