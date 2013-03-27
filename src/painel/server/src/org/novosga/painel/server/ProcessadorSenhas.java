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
package org.novosga.painel.server;

import org.novosga.painel.server.db.SQLConnectionPool;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Thread responsavel por monitorar o banco e obter senhas a serem despachadas.
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class ProcessadorSenhas extends Thread {

    private static final Logger LOG = Logger.getLogger(ProcessadorSenhas.class.getName());
    private static final ProcessadorSenhas INSTANCE = new ProcessadorSenhas();

    public static ProcessadorSenhas getInstance() {
        return INSTANCE;
    }

    private ProcessadorSenhas() {
        super("Thread Processador/Despachador de Senhas");
        this.setPriority(Thread.MAX_PRIORITY);
    }

    private PreparedStatement preparaDeleteSenhas() throws SQLException {
        Connection con = SQLConnectionPool.getInstance().getConnection();
        return con.prepareStatement("DELETE FROM painel_senha WHERE contador <= ?");
    }

    private PreparedStatement preparaSelectSenhas() throws SQLException {
        Connection con = SQLConnectionPool.getInstance().getConnection();
        return con.prepareStatement("SELECT contador, id_uni, id_serv, msg_senha, num_senha, sig_senha, nm_local, num_guiche FROM painel_senha");
    }

    public void run() {
        PreparedStatement psDel = null, psSel = null;
        //BigInteger bi = new BigInteger("0");
        int maxContador = Integer.MIN_VALUE;
        try {
            psSel = this.preparaSelectSenhas();
            psDel = this.preparaDeleteSenhas();
        } catch (SQLException e) {
            LOG.log(Level.SEVERE, "Erro obtendo conexão/preparando consulta para processamento de senhas.", e);
        }
        // remove senhas que acumularam enquanto o controlador estava inativo
        try {
            Connection con = SQLConnectionPool.getInstance().getConnection();
            con.createStatement().execute("DELETE FROM painel_senha");
        } catch (SQLException e) {
            LOG.log(Level.SEVERE, "Erro limpando senhas antigas existentes.", e);
        }
        ResultSet rset;
        int idUnidade;
        String msgEspecial, guicheStr;
        int contador, num_senha, guiche, id_serv;
        char sig_serv;
        while (true) {
            try {
                rset = psSel.executeQuery();
                while (rset.next()) {
                    contador = rset.getInt("contador");
                    idUnidade = rset.getInt("id_uni");
                    msgEspecial = rset.getString("msg_senha");
                    id_serv = rset.getInt("id_serv");
                    sig_serv = rset.getString("sig_senha").charAt(0);
                    num_senha = rset.getInt("num_senha");
                    guicheStr = rset.getString("nm_local");
                    guiche = rset.getInt("num_guiche");
                    if (contador > maxContador) {
                        maxContador = contador;
                    }
                    try {
                        GerenciadorPaineis.getInstance().despacharSenha(idUnidade, msgEspecial, id_serv, sig_serv, num_senha, guicheStr, guiche);
                    } catch (Throwable t) {
                        LOG.log(Level.SEVERE, "Falha despachando senha.", t);
                    }
                }
                rset.close();
            } catch (SQLException e) {
                LOG.log(Level.SEVERE, "Falha ao selecionar senhas da tabela, tetando re-preparar a consulta.", e);
                try {
                    psSel = this.preparaSelectSenhas();
                } catch (SQLException e1) {
                    LOG.log(Level.SEVERE, "Falha tentando re-preparar a consulta de senhas.", e);
                }
            }
            try {
                psDel.setInt(1, maxContador);
                psDel.executeUpdate();
            } catch (SQLException e) {
                LOG.log(Level.SEVERE, "Falha ao remover senhas processadas.", e);
                try {
                    psDel = this.preparaDeleteSenhas();
                } catch (SQLException e1) {
                    LOG.log(Level.SEVERE, "Falha tentando re-preparar a consulta para remover senhas.", e);
                }
            }
            try {
                Thread.sleep(ConfigManager.getInstance().getSleepInterval());
            } catch (InterruptedException e) {
                // nada
            }
        }
    }
}
