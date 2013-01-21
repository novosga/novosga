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
package br.gov.dataprev.controladorpainel;

import java.beans.PropertyVetoException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;

import com.mchange.v2.c3p0.ComboPooledDataSource;

/**
 * Mantem um pool de conexões SQL para reutilização através da biblioteca
 * C3P0.<BR>
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class SQLConnectionPool {

    private static final Logger LOG = Logger.getLogger(SQLConnectionPool.class.getName());
    private static final SQLConnectionPool INSTANCE = new SQLConnectionPool();

    public static SQLConnectionPool getInstance() {
        return INSTANCE;
    }
    private final ComboPooledDataSource _cpds;

    private SQLConnectionPool() {
        _cpds = new ComboPooledDataSource();
        _cpds.setAutoCommitOnClose(true);

        /*
         * Inicia utilizando 10 conexões SQLs
         * Minimo de conexões: 10
         * Máximo de conexões: 50
         */
        _cpds.setInitialPoolSize(10);
        _cpds.setMinPoolSize(10);
        _cpds.setMaxPoolSize(50);

        /*
         * Nunca desistir do Acquire (valor = 0)
         * Aguardar 10 ms antes de tentar novamente
         * Quando não houver conexões, criar mais 5 (se o limite permitir)
         */
        _cpds.setAcquireRetryAttempts(0);
        _cpds.setAcquireRetryDelay(10);
        _cpds.setAcquireIncrement(5);

        /*
         * Não testar uma conexão logo após cria-la.
         * Testar conexões que estão IDLE a cada 1 hora.
         * Usar a tabela C3P0TestTable para testes de conexão.
         */
        _cpds.setTestConnectionOnCheckin(false);
        _cpds.setTestConnectionOnCheckout(false);
        _cpds.setAutomaticTestTable("C3P0TestTable");
        _cpds.setIdleConnectionTestPeriod(15);
        _cpds.setMaxIdleTime(0);
        _cpds.setMaxStatementsPerConnection(20);

        /*
         * Uma falha ao obter conexão, não deve ser considerada fatal.
         * O server não pode parar.
         */
        _cpds.setBreakAfterAcquireFailure(false);

        String driver = ConfigManager.getInstance().getJdbcDriver();
        try {
            Class.forName(driver);
        } catch (ClassNotFoundException e) {
            LOG.log(Level.SEVERE, "Falha ao tentar utilizar o driver especificado: (" + driver + "), verifique o nome especificado e se a biblioteca do driver está na pasta de bibliotecas (lib/).", e);
            System.exit(6);
        }

        try {
            _cpds.setDriverClass(driver);
        } catch (PropertyVetoException e) {
            LOG.log(Level.SEVERE, "Falha ao tentar utilizar o driver especificado: (" + driver + ")", e);
            System.exit(4);
        }

        _cpds.setJdbcUrl(ConfigManager.getInstance().getJdbcUrl());
        _cpds.setUser(ConfigManager.getInstance().getJdbcUser());
        _cpds.setPassword(ConfigManager.getInstance().getJdbcPass());
    }

    public void test() throws SQLException {
        _cpds.getConnection().close();
    }

    public Connection getConnection() throws SQLException {
        return _cpds.getConnection();
    }

    public void close() throws SQLException {
        _cpds.close();
    }
}
