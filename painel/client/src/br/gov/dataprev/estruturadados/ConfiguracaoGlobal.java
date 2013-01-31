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
package br.gov.dataprev.estruturadados;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.Properties;

import br.gov.dataprev.exec.Painel;

/**
 * @author ulysses
 *
 */
public class ConfiguracaoGlobal {

    private static final String ARQUIVO_CONFIG_GLOBAL = "painel.conf";
    private static final int TIMEOUT_UDP_PADRAO = 10;
    private static ConfiguracaoGlobal INSTANCE;
    private String _ipServ;
    private int _unidadeId;
    private String _protocol;
    private int _port;
    private int[] _servicos;
    /**
     * Tempo que se deve esperar pela confirmação de cadastro do Painel pelo
     * servidor, se esse tempo estourar é considerado que o pacote se perdeu ou
     * o servidor está offline.
     */
    private int _timeoutOperacoesUDP = TIMEOUT_UDP_PADRAO;
    private boolean _dualVideo;
    private String _urlUnidades;
    private String _urlServicos;

    /**
     * Usada para (re)carregar a config global do Painel.
     *
     * @throws FileNotFoundException Se o arquivo de configuração não existe
     * @throws IOException Em caso de erro lendo o arquivo.
     */
    public static void carrega() throws FileNotFoundException, IOException {
        INSTANCE = new ConfiguracaoGlobal();
        ConfiguracaoGlobal.getInstance().carregaConfiguracao();
    }

    /**
     * Obtem um configuração previamente carrega com o método
     * ConfiguracaoGlobal.carrega()
     *
     * @return Uma ConfiguracaoGlobal do painel.
     */
    public static ConfiguracaoGlobal getInstance() {
        return INSTANCE;
    }

    private ConfiguracaoGlobal() {
    }

    /**
     * @throws FileNotFoundException Se o arquivo de configuração não existe
     * @throws IOException Em caso de erro lendo o arquivo.
     */
    private void carregaConfiguracao() throws FileNotFoundException, IOException {
        Properties config = new Properties();
        File file = new File(Painel.getWorkingDirectory(), ARQUIVO_CONFIG_GLOBAL);
        if (!file.exists()) {
            file.createNewFile();
        }
        config.load(new FileInputStream(file));

        _unidadeId = Integer.parseInt(config.getProperty("UnidadeId", "0"));
        _protocol = config.getProperty("Protocolo", "UDP");
        _port = Integer.parseInt(config.getProperty("Porta", "9999"));

        String servicosStr = config.getProperty("Servicos");
        if (servicosStr != null) {
            String[] servicosStrArray = servicosStr.split(",");
            int[] servicos = new int[servicosStrArray.length];

            for (int i = 0; i < servicos.length; i++) {
                servicos[i] = Integer.parseInt(servicosStrArray[i]);
            }
            _servicos = servicos;
        } else {
            _servicos = new int[0];
        }

        _ipServ = config.getProperty("IPServidor");
        _dualVideo = Boolean.parseBoolean(config.getProperty("DualVideo", "true"));
        _timeoutOperacoesUDP = Integer.parseInt(config.getProperty("TimeoutOperacoesUDP", "" + TIMEOUT_UDP_PADRAO));
    }

    public void salvarConfiguracao() throws FileNotFoundException, IOException {
        Properties config = new Properties();
        config.setProperty("IPServidor", _ipServ);
        config.setProperty("Protocolo", _protocol);
        config.setProperty("Porta", String.valueOf(_port));
        config.setProperty("UnidadeId", String.valueOf(this.getUnidadeId()));
        config.setProperty("Servicos", this.getServicosStr());
        config.setProperty("DualVideo", String.valueOf(_dualVideo));
        config.setProperty("TimeoutOperacoesUDP", String.valueOf(_timeoutOperacoesUDP));
        config.store(new FileOutputStream(new File(Painel.getWorkingDirectory(), ARQUIVO_CONFIG_GLOBAL)), "Configurações globais do Painel");
    }

    /**
     *
     * @return
     */
    public String getIPServer() {
        return _ipServ;
    }

    public String getIpServ() {
        return _ipServ;
    }

    public void setIpServ(String _ipServ) {
        this._ipServ = _ipServ;
    }

    public String getProtocol() {
        return _protocol;
    }

    public void setProtocol(String _protocol) {
        this._protocol = _protocol;
    }

    public int getPort() {
        return _port;
    }

    public void setPort(int _port) {
        this._port = _port;
    }

    /**
     *
     * @param ip
     */
    public void setIPServer(String ip) {
        this._ipServ = ip;
    }

    /**
     * Verifica se está setado para duas placas de vídeo
     *
     * @return
     */
    public boolean isDualVideo() {
        return _dualVideo;
    }

    /**
     *
     * @param dualVideo
     */
    public void setDualVideo(boolean dualVideo) {
        _dualVideo = dualVideo;
    }

    /**
     * @param urlUnidades the urlUnidades to set
     */
    public void setUrlUnidades(String urlUnidades) {
        _urlUnidades = urlUnidades;
    }

    /**
     * @return the urlUnidades
     */
    public String getUrlUnidades() {
        return _urlUnidades;
    }

    /**
     * @param urlServicos the urlServicos to set
     */
    public void setUrlServicos(String urlServicos) {
        this._urlServicos = urlServicos;
    }

    /**
     * @return the urlServicos
     */
    public String getUrlServicos() {
        return _urlServicos;
    }

    /**
     * @param timeoutOperacoesUDP the timeoutOperacoesUDP to set
     */
    public void setTimeoutOperacoesUDP(int timeoutOperacoesUDP) {
        _timeoutOperacoesUDP = timeoutOperacoesUDP;
    }

    /**
     * @return the timeoutCadastroPainel
     */
    public int getTimeoutOperacoesUDP() {
        return _timeoutOperacoesUDP;
    }

    /**
     * verifica se o ID de serviço existe
     *
     * @param num
     * @return
     */
    public boolean seInteressaPorServico(int servico) {
        for (int i = 0; _servicos != null && i < _servicos.length; i++) {
            if (_servicos[i] == servico) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return
     */
    public int[] getServicos() {
        return _servicos;
    }

    /**
     *
     * @param servico
     */
    public void setServicos(int[] servico) {
        _servicos = servico;
    }

    private String getServicosStr() {
        StringBuilder sb = new StringBuilder();
        if (_servicos.length > 0) {
            sb.append(_servicos[0]);
        }
        for (int i = 1; _servicos != null && i < _servicos.length; i++) {
            sb.append(',');
            sb.append(_servicos[i]);
        }
        return sb.toString();
    }

    /**
     * @param unidadeId the unidadeId to set
     */
    public void setUnidadeId(int unidadeId) {
        _unidadeId = unidadeId;
    }

    /**
     * @return the unidadeId
     */
    public int getUnidadeId() {
        return _unidadeId;
    }
}
