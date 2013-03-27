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

import org.novosga.painel.server.task.AgendadorTarefas;
import java.io.FileInputStream;
import java.io.IOException;
import java.text.DateFormat;
import java.text.ParseException;
import java.util.Calendar;
import java.util.Locale;
import java.util.Properties;
import java.util.concurrent.TimeUnit;
import java.util.logging.Level;
import java.util.logging.Logger;

import org.novosga.painel.server.task.LimparPaineisInativos;

/**
 * Gerenciador de configurações, guarda os valores lidos em memória.
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class ConfigManager {

    private static final Logger LOG = Logger.getLogger(ConfigManager.class.getName());
    public static final String PROPERTY_FILE = "server.conf";
    private static ConfigManager _Instance;
    private final int _sleepInterval;
    private final int _timeoutPainel;
    private final int _networkPort;
    private final String _networkProtocol;
    private final String _urlUnidades;
    private final String _urlServicos;
    private final String _jdbcDriver;
    private final String _jdbcUrl;
    private final String _jdbcUser;
    private final String _jdbcPass;
    private final int _removerPaineisIntervalo;
    private String _removerPaineisHora;

    private ConfigManager() throws IOException {
        Properties config = new Properties();
        config.load(new FileInputStream(PROPERTY_FILE));
        requireProperties(config, "jdbcUser", "jdbcPass", "urlUnidades", "urlServicos");
        
        _networkPort = Integer.parseInt(config.getProperty("port"));
        _networkProtocol = config.getProperty("protocol", "UDP");
        _jdbcDriver = config.getProperty("jdbcDriver", "com.mysql.jdbc.Driver");
        _jdbcUrl = config.getProperty("jdbcUrl", "jdbc:mysql://localhost/sga");
        _jdbcUser = config.getProperty("jdbcUser");
        _jdbcPass = config.getProperty("jdbcPass");
        _urlUnidades = config.getProperty("urlUnidades");
        _urlServicos = config.getProperty("urlServicos");
        _removerPaineisHora = config.getProperty("removerPaineisHora", "23:00:00");
        
        Locale loc = new Locale("pt", "BR");
        Calendar current = Calendar.getInstance(loc);

        DateFormat df = DateFormat.getTimeInstance(DateFormat.SHORT, loc);
        if (!_removerPaineisHora.equalsIgnoreCase("false")) {
            try {
                df.parse(_removerPaineisHora);
                df.getCalendar().set(current.get(Calendar.YEAR), current.get(Calendar.MONTH), current.get(Calendar.DAY_OF_MONTH));

                if (df.getCalendar().before(current)) {
                    df.getCalendar().add(Calendar.DAY_OF_MONTH, 1);
                }

                long diff = df.getCalendar().getTimeInMillis() - current.getTimeInMillis();


                LOG.info("Agendando primeira tarefa de limpeza para " + df.getCalendar().getTime() + " com repetição a cada 24 horas");
                AgendadorTarefas.getInstance().getSes().scheduleAtFixedRate(new LimparPaineisInativos(), diff, 24 * 60 * 60 * 1000, TimeUnit.MILLISECONDS);
            } catch (ParseException e) {
                LOG.log(Level.SEVERE, "Falha interpretando o horário da tarefa de remoção de paineis, tarefa cancelada.");
            }
        } else {
        }

        if (!getUrlServicos().contains("%id_unidade%")) {
            throw new IllegalStateException("Configuração 'urlServicos' deve incluir o token: %id_unidade%");
        }
        int sleepInt = Integer.parseInt(config.getProperty("intervaloConsulta", "100"));
        if (sleepInt < 1) {
            LOG.warning("O valor da config (intervaloConsulta=" + sleepInt + ") não é permitido, apenas valores maiores que zero são permitidos. Forçando o valor para 1.");
            sleepInt = 1;
        }
        _sleepInterval = sleepInt;

        int timeoutPainel = Integer.parseInt(config.getProperty("timeoutPainel", "600"));
        if (timeoutPainel < 15) {
            LOG.warning("O valor da config (timeoutPainel=" + timeoutPainel + ") não é permitido, apenas valores >= 15 são permitidos. Forçando o valor para 15.");
            timeoutPainel = 15;
        }
        _timeoutPainel = timeoutPainel;

        int removerPaineisIntervalo = Integer.parseInt(config.getProperty("removerPaineisIntervalo", "172800")); // 2 Dias
        if (removerPaineisIntervalo < 3600) {
            LOG.warning("O valor da config (removerPaineisIntervalo=" + removerPaineisIntervalo + ") não é permitido, apenas valores >= 3600 são permitidos. Forçando o valor para 3600.");
            removerPaineisIntervalo = 3600;
        }
        _removerPaineisIntervalo = removerPaineisIntervalo;
    }
    
    public static ConfigManager getInstance() {
        if (_Instance == null) {
            try {
                _Instance = new ConfigManager();
            } catch (Exception e) {
                LOG.log(Level.SEVERE, "FATAL: Falha carregando configurações. Motivo: " + e.getLocalizedMessage(), e);
                System.exit(8);
            }
        }
        return _Instance;
    }

    public int getNetworkPort() {
        return _networkPort;
    }

    public String getNetworkProtocol() {
        return _networkProtocol;
    }

    /**
     * @return the sleepInterval
     */
    public final int getSleepInterval() {
        return _sleepInterval;
    }

    /**
     * @return the timeoutPainel
     */
    public int getTimeoutPainel() {
        return _timeoutPainel;
    }

    /**
     * @return the jdbcDriver
     */
    public final String getJdbcDriver() {
        return _jdbcDriver;
    }

    /**
     * @return the jdbcDriver
     */
    public final String getUrlUnidades() {
        return _urlUnidades;
    }

    /**
     * @return the urlServicos
     */
    public String getUrlServicos() {
        return _urlServicos;
    }

    /**
     * @return the jdbcUrl
     */
    public final String getJdbcUrl() {
        return _jdbcUrl;
    }

    /**
     * @return the jdbcUser
     */
    public final String getJdbcUser() {
        return _jdbcUser;
    }

    /**
     * @return the jdbcPass
     */
    public final String getJdbcPass() {
        return _jdbcPass;
    }

    private static void requireProperties(Properties p, String... keys) {
        for (String key : keys) {
            if (!p.containsKey(key)) {
                throw new IllegalStateException("Configuração não definiu valor para: " + key);
            }
        }
    }

    /**
     * @return the removerPaineisIntervalo
     */
    public int getRemoverPaineisIntervalo() {
        return _removerPaineisIntervalo;
    }
}
