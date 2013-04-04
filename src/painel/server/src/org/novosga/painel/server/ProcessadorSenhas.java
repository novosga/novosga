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
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
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

    /**
     * Busca as senhas para serem enviadas aos paineis, que ainda nao foram enviadas (dt_envio IS NULL)
     * @return
     * @throws SQLException 
     */
    private PreparedStatement preparaSelectSenhas() throws SQLException {
        Connection con = SQLConnectionPool.getInstance().getConnection();
        return con.prepareStatement("SELECT * FROM painel_senha WHERE dt_envio IS NULL");
    }
    
    /**
     * Cria PreparedStatement para atualizar os registros de senhas enviadas
     * @return
     * @throws SQLException 
     */
    private PreparedStatement preparaUpdateSenhas(List<Integer> ids) throws SQLException {
        Connection con = SQLConnectionPool.getInstance().getConnection();
        Calendar c = Calendar.getInstance();
        String date = c.get(Calendar.YEAR) + "-" + String.format("%02d", c.get(Calendar.MONTH) + 1) + "-" + String.format("%02d", c.get(Calendar.DAY_OF_MONTH));
        String hour = String.format("%02d", c.get(Calendar.HOUR_OF_DAY)) + ":" + String.format("%02d", c.get(Calendar.MINUTE))+ ":" + String.format("%02d", c.get(Calendar.SECOND));
        StringBuilder sb = new StringBuilder("UPDATE painel_senha SET dt_envio = '").append(date).append(" ").append(hour).append("' WHERE contador IN (");
        if (ids.isEmpty()) {
            ids.add(0);
        }
        for (int i = 0; i < ids.size();) {
            sb.append(ids.get(i));
            if (++i < ids.size()) {
                sb.append(",");
            }
        }
        sb.append(")");
        PreparedStatement stmt = con.prepareStatement(sb.toString());
        return stmt;
    }

    @Override
    public void run() {
        PreparedStatement psSel;
        //BigInteger bi = new BigInteger("0");
        int maxContador = Integer.MIN_VALUE;
        try {
            psSel = this.preparaSelectSenhas();
        } catch (SQLException e) {
            LOG.log(Level.SEVERE, "Erro obtendo conexão/preparando consulta para processamento de senhas. Processamento abortado.", e);
            return;
        }
        ResultSet rset;
        String msgEspecial, guicheStr;
        int idUnidade, contador, num_senha, guiche, id_serv;
        char sig_serv;
        List<Integer> ids = new ArrayList<Integer>();
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
                        ids.add(contador);
                        LOG.info("Enviada senha " + sig_serv + String.format("%04d", num_senha) + " para os paineis cadastrados (id="+ contador +")");
                    } catch (Throwable t) {
                        LOG.log(Level.SEVERE, "Falha despachando senha.", t);
                    }
                }
                rset.close();
                // atualizando senhas como enviadas
                if (ids.size() > 0) {
                    this.preparaUpdateSenhas(ids).executeUpdate();
                    ids.clear();
                }
            } catch (SQLException e) {
                LOG.log(Level.SEVERE, "Falha ao selecionar senhas da tabela, tentando re-preparar a consulta.", e);
                try {
                    psSel = this.preparaSelectSenhas();
                } catch (SQLException e1) {
                    LOG.log(Level.SEVERE, "Falha tentando re-preparar a consulta de senhas.", e);
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
