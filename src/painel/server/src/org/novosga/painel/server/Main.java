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
import org.novosga.painel.server.network.PacketServer;
import org.novosga.painel.server.network.PacketServerFactory;
import java.io.FileInputStream;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.Logger;


/**
 * Classe principal do Servidor centralizado de paineis.
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class Main {

    private static final String FILENAME = "logging.properties";
    public static final Logger LOG = Logger.getLogger(Main.class.getName());
    
    private static PacketServer _server;

    /**
     * @param args
     */
    public static void main(String[] args) {
        // Carregar configurações do Logger
        try {
            LogManager.getLogManager().readConfiguration(new FileInputStream(FILENAME));
        } catch (Exception e) {
            LOG.log(Level.SEVERE, "Falha carregando configurações do logger, utilizando configurações default.", e);
        }

        LOG.info("Iniciando Controlador de Paineis");
        LOG.info("Carregando Configurações");

        ConfigManager.getInstance();

        LOG.info("Carregando Gerenciador de Conexões SQL...");
        try {
            SQLConnectionPool.getInstance().test();
        } catch (SQLException e) {
            LOG.log(Level.SEVERE, "Erro carregando Gerenciador de Conexões SQL", e);
            System.exit(100);
        }

        LOG.info("Carregando Controlador de Paineis...");
        GerenciadorPaineis.getInstance();

        LOG.info("Iniciando servidor " + ConfigManager.getInstance().getNetworkProtocol());
        _server = PacketServerFactory.create(ConfigManager.getInstance());
        _server.start();
        _server.aguardaInicio();

        LOG.info("Carregando Processador de senhas");
        ProcessadorSenhas.getInstance().start();

        LOG.info("Servidor pronto");
    }
    
    public static PacketServer getServer() {
        return _server;
    }
    
}
