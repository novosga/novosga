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
 */
package br.gov.dataprev.exec;

import java.io.File;
import java.io.FileNotFoundException;
import java.util.Arrays;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import javax.swing.UIManager;
import br.gov.dataprev.estruturadados.ConfiguracaoGlobal;
import br.gov.dataprev.userinterface.CPanel;
import br.gov.dataprev.userinterface.Loader;
import br.gov.dataprev.userinterface.Mensagem;
import br.gov.dataprev.userinterface.SysTray;
import br.gov.dataprev.userinterface.network.PacketListener;
import br.gov.dataprev.userinterface.network.PacketListenerFactory;

public class Painel {
    
    public static final int PORT = 8888;
    private static PacketListener listener;

    private static final String[] LOG_PROPERTIES = {"java.runtime.name", "java.vm.name", "java.vm.version", "java.vm.vendor", "java.runtime.version", "user.country", "os.name", "os.arch", "os.version", "java.awt.graphicsenv"};
    private static final Logger LOG = Logger.getLogger(Painel.class.getName());

    public static void main(String[] args) {
        // Carregar configurações do Logger
        try {
            LogManager.getLogManager().readConfiguration(Painel.class.getResourceAsStream("logger.properties"));
        } catch (Exception e) {
            // Nunca deve acontecer, ja que o arquivo logger.properties deve estar dentro do proprio .jar
            LOG.log(Level.SEVERE, "Falha carregando configurações do logger, utilizando configurações default.", e);
        }
        LOG.info("args: " + Arrays.toString(args));
        // Loga informações sobre a plataformade execução
        // util para debug
        for (String key : LOG_PROPERTIES) {
            LOG.info(key + ": " + System.getProperty(key));
        }
        // Usa o Look and Feel do sistema
        try {
            UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
        } catch (Exception e) {
            LOG.log(Level.WARNING, "Falha tentando utilizar o LookAndFeel nativo do sistema.", e);
        }
        System.setProperty("args", Arrays.toString(args));
        // Carrega a configuracao do painel
        boolean configOk = false;
        try {
            ConfiguracaoGlobal.carrega();
            configOk = true;
        } catch (FileNotFoundException e1) {
            Mensagem.showMensagem("Configuração do painel não encontrada.\nÉ necessário efetuar a configuração do Painel antes de iniciar seu uso.", "Aviso");
            e1.printStackTrace();
        } catch (Exception e1) {
            Mensagem.showMensagem("Ocorreu um erro carregando as configurações do Painel, verifique se o arquivo de configuração não está em uso e suas permissões.", "ERRO", 0, e1);
            e1.printStackTrace();
        }
        // Inicia o servidor para receber mensagens
        String protocol = ConfiguracaoGlobal.getInstance().getProtocol();
        try {
            listener = PacketListenerFactory.create(protocol);
            listener.inicia();
        } catch (Exception e) {
            Mensagem.showMensagem(e.getMessage(), "ERRO", 0, e);
            System.exit(1);
        }
        // Adiciona o painel na banjeida do sistema
        try {
            new SysTray();
        } catch (Exception e) {
            JOptionPane.showMessageDialog(null, e.getMessage(), "Erro", 0);
        }
        // Carrega o Painel
        if (configOk) {
            Loader loader = new Loader();
            loader.setVisible(true);
            loader.recadastrarPainel();
        } else {
            CPanel.getInstance().setVisible(true);
        }
    }

    public static File getWorkingDirectory() {
        final String applicationName = "PainelSGA";
        final String userHome = System.getProperty("user.home", ".");
        final File workingDirectory;
        final String sysName = System.getProperty("os.name").toLowerCase();

        if (sysName.contains("linux") || sysName.contains("solaris")) {
            workingDirectory = new File(userHome, '.' + applicationName + '/');
        } else if (sysName.contains("windows")) {
            final String applicationData = System.getenv("APPDATA");
            if (applicationData != null) {
                workingDirectory = new File(applicationData, "." + applicationName + '/');
            } else {
                workingDirectory = new File(userHome, '.' + applicationName + '/');
            }
        } else if (sysName.contains("mac")) {
            workingDirectory = new File(userHome, "Library/Application Support/" + applicationName);
        } else {
            workingDirectory = new File(".");
        }
        if (!workingDirectory.exists() && !workingDirectory.mkdirs()) {
            throw new RuntimeException("The working directory could not be created: " + workingDirectory);
        }
        return workingDirectory;
    }
    
    public static PacketListener getListener() {
        return listener;
    }
    
}
