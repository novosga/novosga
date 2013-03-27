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
package org.novosga.painel.server.db;

import org.novosga.painel.server.Painel;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * @author ulysses
 *
 */
public class PainelDAO {

    private static final Logger LOG = Logger.getLogger(PainelDAO.class.getName());
    private boolean _existeNoBanco = false;
    private final Painel _painel;

    public PainelDAO(Painel painel, boolean existeNoBanco) {
        _painel = painel;
        _existeNoBanco = existeNoBanco;
    }

    public synchronized void salvar() {
        if (_existeNoBanco) {
            this.atualizarNoBanco();
        } else {
            this.inserirNoBanco();
        }
    }

    /**
     *
     */
    private synchronized void atualizarNoBanco() {
        Connection con = null;
        try {
            con = SQLConnectionPool.getInstance().getConnection();
            con.setAutoCommit(false); // inicia transação

            PreparedStatement ps = con.prepareStatement("UPDATE paineis SET id_uni = ? WHERE host = ?");
            ps.setInt(1, _painel.getApsId());
            ps.setInt(2, _painel.getIntHost());
            ps.executeUpdate();
            ps.close();

            ps = con.prepareStatement("DELETE FROM paineis_servicos WHERE host = ?");
            ps.setInt(1, _painel.getIntHost());
            ps.executeUpdate();
            ps.close();

            ps = con.prepareStatement("INSERT INTO paineis_servicos (host, id_uni, id_serv) VALUES (?, ?, ?)");
            for (byte servico : _painel.getServicos()) {
                ps.setInt(1, _painel.getIntHost());
                ps.setInt(2, _painel.getApsId());
                ps.setInt(3, servico & 0xFF);

                ps.executeUpdate();
            }
            ps.close();

            con.commit(); // commit na transação
        } catch (SQLException e) {
            LOG.log(Level.SEVERE, "Falha inserindo painel no banco. Motivo: " + e.getMessage(), e);
        } finally {
            try {
                con.close();
            } catch (Exception e) {
                // nada
            }
        }
    }

    private synchronized void inserirNoBanco() {
        Connection con = null;
        try {
            con = SQLConnectionPool.getInstance().getConnection();
            con.setAutoCommit(false); // inicia transação

            PreparedStatement ps = con.prepareStatement("INSERT INTO paineis (id_uni, host) VALUES (?, ?)");
            ps.setInt(1, _painel.getApsId());
            ps.setInt(2, _painel.getIntHost());
            ps.executeUpdate();
            _existeNoBanco = true;
            ps.close();

            ps = con.prepareStatement("DELETE FROM paineis_servicos WHERE host = ?");
            ps.setInt(1, _painel.getIntHost());
            ps.executeUpdate();
            ps.close();

            ps = con.prepareStatement("INSERT INTO paineis_servicos (host, id_uni, id_serv) VALUES (?, ?, ?)");
            for (byte servico : _painel.getServicos()) {
                ps.setInt(1, _painel.getIntHost());
                ps.setInt(2, _painel.getApsId());
                ps.setInt(3, servico & 0xFF);
                ps.executeUpdate();
            }
            ps.close();

            con.commit(); // commit na transação
        } catch (SQLException e) {
            try {
                // tenta dar um rollback embora não seja estritamente ncessário (na falta de commit o banco assume rollback)
                con.rollback();
            } catch (SQLException e1) {
                // nada
            }
            LOG.log(Level.SEVERE, "Falha inserindo painel no banco. Motivo: " + e.getMessage(), e);
        } finally {
            try {
                con.close();
            } catch (Exception e) {
                // nada
            }
        }
    }

    public synchronized void removerDoBanco() {
        Connection con = null;
        try {
            con = SQLConnectionPool.getInstance().getConnection();

            con.setAutoCommit(false);

            PreparedStatement ps = con.prepareStatement("DELETE FROM paineis_servicos WHERE host = ?");
            ps.setInt(1, _painel.getIntHost());
            ps.executeUpdate();
            ps.close();


            ps = con.prepareStatement("DELETE FROM paineis WHERE host = ?");
            ps.setInt(1, _painel.getIntHost());
            ps.executeUpdate();
            ps.close();


            con.commit();
            _existeNoBanco = false;
        } catch (SQLException e) {
            LOG.log(Level.SEVERE, "Falha removendo painel do banco. Motivo: " + e.getMessage(), e);
            try {
                con.rollback();
            } catch (SQLException e1) {
                // nada
            }
        } finally {
            try {
                con.close();
            } catch (Exception e) {
                // nada
            }
        }
    }
}
