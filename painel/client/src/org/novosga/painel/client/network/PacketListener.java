package org.novosga.painel.client.network;

import java.awt.Color;
import java.io.IOException;
import java.net.SocketException;
import java.nio.BufferUnderflowException;
import java.nio.ByteBuffer;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.ScheduledFuture;
import java.util.concurrent.TimeUnit;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.novosga.painel.model.Senha;
import javafx.application.Platform;
import org.novosga.painel.event.ObterUrlEvent;
import org.novosga.painel.event.SenhaEvent;

public abstract class PacketListener implements Runnable {
    
    /**
     * Versão do protocolo, independe da versão do programa.<br> Deve ser
     * incrementada se houver uma modificação no formato de alguma das
     * mensagens.
     */
    public static final int VERSAO_PROTOCOLO = 1;

    private static final Logger LOG = Logger.getLogger(PacketListener.class.getName());
    private Thread _thread;
    private Thread _tarefaDesligamento;
    private final ScheduledExecutorService _ses = Executors.newScheduledThreadPool(1);
    private ScheduledFuture<?> _future;
    
    protected final int receivePort;
    protected final int sendPort;
    protected String server;
    private int intervaloSinalDeVida;
    private SenhaEvent onNovaSenhaEvent;
    
    // events
    private ObterUrlEvent onObterUrlEvent;

    public PacketListener(int receivePort, int sendPort, String server) {
        this.receivePort = receivePort;
        this.sendPort = sendPort;
        this.server = server;
    }

    public String getServer() {
        return server;
    }

    public void setServer(String server) {
        this.server = server;
    }

    public SenhaEvent getOnNovaSenhaEvent() {
        return onNovaSenhaEvent;
    }

    public void setOnNovaSenhaEvent(SenhaEvent onNovaSenhaEvent) {
        this.onNovaSenhaEvent = onNovaSenhaEvent;
    }

    public ObterUrlEvent getOnObterUrlEvent() {
        return onObterUrlEvent;
    }

    public void setOnObterUrlEvent(ObterUrlEvent onObterUrlEvent) {
        this.onObterUrlEvent = onObterUrlEvent;
    }
    
    /**
     * Inicia o processo de escuta/recebimento na porta UDP.<br>
     *
     * @throws SocketException Se não foi possivél abrir o socket UDP
     * (possivelmente já existe outro painel usando a porta).
     */
    public void inicia() throws Exception {
        doStart();
        _thread = new Thread(this, "PacketListennerThread");
        _thread.start();
    }
    
    protected abstract void doStart() throws Exception;
    /**
     * Processa as mensagens recebidas via UDP.<br> <br> <ul> <li>O tipo da
     * mensagem é definido pelo primeiro byte.</li> <li>Em seguida o resto da
     * mensagem é lido de acordo com seu tipo.</li> </ul>
     *
     *
     * @param executor Thread que irá executar a ação recebida (se necessário)
     * @param buf Buffer com os dados da mensagem recebida
     */
    protected void lePacote(ExecutorService executor, ByteBuffer buf) {
        int tipoPacote = PacketListener.leByte(buf);
        // obtem o tipo do pacote
        TipoPacoteRecebido tpr = null;
        if (tipoPacote < TipoPacoteRecebido.values().length) {
            tpr = TipoPacoteRecebido.values()[tipoPacote];
        }
        // executa uma acao de acordo com o tipo do pacote
        if (tipoPacote == TipoPacoteRecebido.MSG_SENHA.ordinal()) {
            this.leMessageSenha(executor, buf);
        } else if (tipoPacote == TipoPacoteRecebido.MSG_CONFIRMA_CADASTRO.ordinal()) {
            this.leMsgConfirmaCadastro(executor, buf);
        } else if (tipoPacote == TipoPacoteRecebido.MSG_URLS.ordinal()) {
            this.leMsgUrls(executor, buf);
        } else {
            LOG.severe("Mensagem de tipo desconhecido descartada. Tipo: " + tipoPacote);
        }
    }

    /**
     * Le a mensagem contendo a senha.<br> <br> <ul> <li>A mensagem especial é
     * definida na primeira String.</li> <li>O caractere do serviço é definido
     * no próximo byte.</li> <li>O número da senha é definido no próximo
     * inteiro.</li> <li>O guiche é definido na próxima String.</li> <li>O
     * número do guiche é definido no próximo byte.</li> </ul>
     *
     * @param executor Thread que irá executar a ação recebida
     * @param buf Buffer com os dados da mensagem recebida
     */
    protected void leMessageSenha(ExecutorService executor, ByteBuffer buf) {
        try {
            String mensagem = PacketListener.leString(buf);
            char charServico = (char) PacketListener.leByte(buf);
            int numero = PacketListener.leShort(buf);

            String guiche = PacketListener.leString(buf);
            int numeroGuiche = PacketListener.leByte(buf);
            final Senha senha = new Senha(mensagem, charServico, numero, guiche, numeroGuiche);
            // verificando integridade dos dados
            if (numero > 0 && numeroGuiche > 0 && guiche != null && !guiche.isEmpty()) {
                if (onNovaSenhaEvent != null) {
                    Platform.runLater(new Runnable() {
                        @Override
                        public void run() {
                            onNovaSenhaEvent.handle(senha);
                        }
                   });
                }
            } else {
                LOG.severe("[MSG_SENHA] Ignorando senha a seguir: Dados inválidos.");
            }
            LOG.info("[MSG_SENHA] Senha recebida para exibição: " + senha.toString());
        } catch (BufferUnderflowException e) {
            LOG.log(Level.SEVERE, "Erro lendo pacote MSG_SENHA, não havia dados suficientes na mensagem recebida.", e);
        }
    }

    /**
     * Le a mensagem contendo a confirmacao de cadastro.<br> <br> <ul> <li>O
     * intervalo de sinal de vida é definido no primeiro byte.</li> </ul>
     *
     * @param executor Thread que irá executar a ação recebida
     * @param buf Buffer com os dados da mensagem recebida
     */
    protected void leMsgConfirmaCadastro(ExecutorService executor, ByteBuffer buf) {
        try {
            intervaloSinalDeVida = PacketListener.leShort(buf);
            this.agendarSinalDeVida();
            if (_tarefaDesligamento == null) {
                _tarefaDesligamento = new TarefaDesligamento();
                Runtime.getRuntime().addShutdownHook(_tarefaDesligamento);
            }
            postLeMsgConfirmaCadastro();
            LOG.info("[MSG_CONFIRMA_CADASTRO] Confirmação de cadastro no controlador recebida.");
        } catch (BufferUnderflowException e) {
            LOG.log(Level.SEVERE, "Erro lendo pacote MSG_CONFIRMA_CADASTRO, não havia dados suficientes na mensagem recebida.", e);
        }
    }
    
    protected void postLeMsgConfirmaCadastro() {}

    /**
     * Le a mensagem contendo as urls. O Executor e o buffer não são
     * utilizados.<br> <br> <ul> <li>A url das unidades é lida na primeira
     * String.</li> <li>A url dos serviços é lida na segunda String.</li> </ul>
     *
     * @param executor Thread que irá executar a ação recebida
     * @param buf Buffer com os dados da mensagem recebida
     */
    protected void leMsgUrls(ExecutorService executor, ByteBuffer buf) {
        try {
            final String urlUnidades = PacketListener.leString(buf);
            final String urlServicos = PacketListener.leString(buf);

            LOG.info("[MSG_URLS] RECEBIDO: URL Unidades: " + urlUnidades + "\nRECEBIDO: URL Serviços: " + urlServicos);

            if (onObterUrlEvent != null) {
                Platform.runLater(new Runnable() {
                    @Override
                    public void run() {
                        onObterUrlEvent.handler(urlUnidades, urlServicos);
                    }
                });
            }
            
            postLeMsgUrls();
        } catch (BufferUnderflowException e) {
            LOG.log(Level.SEVERE, "Erro lendo pacote MSG_URLS, não havia dados suficientes na mensagem recebida.", e);
        }
    }
    
    protected void postLeMsgUrls() {}

    /**
     * Le um byte do buffer passado e o retorna como um inteiro sem sinal
     * (0-255).
     *
     * @param buf O buffer de onde o byte será lido.
     * @return O valor do byte lido 0-255.
     */
    public static int leByte(ByteBuffer buf) {
        return buf.get() & 0xFF;
    }

    /**
     * Le um short (2 bytes) do buffer passado e o retorna como um inteiro sem
     * sinal (0-65535)
     *
     * @param buf O buffer de onde o short será lido.
     * @return O valor do short lido (0-65535);
     */
    public static int leShort(ByteBuffer buf) {
        return buf.getShort() & 0xFFFF;
    }

    /**
     * Le N bytes a partir da posição atual até encontrar \x00 e retorna uma
     * string de tamanho (N-1).
     *
     *
     * @param buf O buffer de onde a String será lido.
     * @return A String lida do buffer.
     */
    public static String leString(ByteBuffer buf) {
        StringBuilder sb = new StringBuilder();
        char b;
        while ((b = (char) PacketListener.leByte(buf)) != 0) {
            sb.append(b);
        }
        return sb.toString();
    }

    /**
     * Método de conveniencia que le 3 bytes e os considera respectivamente como
     * valores R G B e retorna a cor resultante.
     *
     * @param buf O buffer de onde a cor será lido.
     * @return a cor resultante.
     */
    public static Color leCor(ByteBuffer buf) {
        return new Color(leByte(buf), leByte(buf), leByte(buf));
    }
    
    protected abstract void send(ByteBuffer buffer) throws Exception;

    public synchronized void obterURLs() throws Exception {
        ByteBuffer buf = ByteBuffer.wrap(new byte[1]);
        buf.put((byte) TipoPacoteEnviado.MSG_SOLICITAR_URLS.ordinal());
        preObterUrls();
        send(buf);
        postObterUrls();
    }
    
    protected void preObterUrls() { }
    protected void postObterUrls() { }

    /**
     * Cadastra um painel no servidor.<br> Este método bloqueia até que a
     * resposta do servidor seja recebida ou até que o timeout global para
     * operações UDP seja atingido.<br>
     *
     *
     * @param idUnidade ID da Unidade da qual o Painel espera receber eventos.
     * @param servicos Serviços que o painel deseja exibir
     * @throws TimeoutException Se o timeout da operação foi atingido, indicando
     * que a ação não foi completada.
     * @throws IOException Se ocorreu um erro de I/O no processo de envio do
     * cadastro.
     */
    public synchronized void cadastrarPainel(Integer idUnidade, Integer[] servicos) throws Exception {
        ByteBuffer buf = ByteBuffer.wrap(new byte[4096]);
        buf.put((byte) TipoPacoteEnviado.MSG_CADASTRO_PAINEL.ordinal());
        buf.putInt(PacketListener.VERSAO_PROTOCOLO);
        buf.putInt(idUnidade.intValue());
        buf.put((byte) servicos.length);
        for (Integer s : servicos) {
            buf.put((byte) s.intValue());
        }
        preCadastrarPainel();
        send(buf);
        postCadastrarPainel();
    }
    
    protected void preCadastrarPainel() {}
    protected void postCadastrarPainel() {}

    /**
     * Envia um pacote de sinal de vida para o servidor.<BR> Se o painel falhar
     * em enviar um SinalDeVida dentro do tempo estipulado pelo servidor, será
     * considerado inativo, mesmo que tenha enviado outros tipos de pacotes
     * recentemente.<BR>
     *
     * @throws IOException Se ocorreu um erro de I/O no processo de envio do
     * Sinal de Vida.
     */
    public void enviarSinalDeVida() throws Exception {
        ByteBuffer buf = ByteBuffer.wrap(new byte[3]);
        buf.put((byte) TipoPacoteEnviado.MSG_PAINEL_VIVO.ordinal());
        buf.putShort((short) intervaloSinalDeVida);
        send(buf);
    }

    /**
     * Envia um pacote para o servidor sinalizando que este painel está inativo
     * e não deseja mais receber eventos.<BR>
     *
     * @throws IOException Se ocorreu um erro de I/O no processo de envio da
     * mensagem.
     */
    private void desregistrarDoServidor() throws Exception {
        ByteBuffer buf = ByteBuffer.wrap(new byte[1]);
        buf.put((byte) TipoPacoteEnviado.MSG_DESATIVAR_PAINEL.ordinal());
        send(buf);
    }

    /**
     * Agenda o envio de Sinal De Vida para o servidor (mantem o painel ativo no
     * servidor).<br> A tarefa é agendada com repetição automática baseada no
     * intervalo definido pelo servidor.
     */
    private void agendarSinalDeVida() {
        ScheduledFuture<?> future = _future;
        // se ja existir agendamento
        if (future != null) {
            // cancelar agendamento anterior
            future.cancel(false);
        }
        final Runnable r = new Runnable() {
            @Override
            public void run() {
                try {
                    PacketListener.this.enviarSinalDeVida();
                } catch (Throwable e) {
                    e.printStackTrace();
                    // ?
                }
            }
        };
        // agendar tarefa continua com intervalos de (_intervaloSinalDeVida - 10) segundos
        //_future = _ses.scheduleAtFixedRate(r, 0, _intervaloSinalDeVida - 10, TimeUnit.SECONDS);
		/* 
         * PMV (ralfilho): 
         * Alterado o intervalor para enviar 3x durante o intervalo, o motivo é para evitar timeout caso algum pacote se perca.
         */
        _future = _ses.scheduleAtFixedRate(r, 0, intervaloSinalDeVida / 3, TimeUnit.SECONDS);
    }

    /**
     * Tarefa chamada pela JVM quando o programa é finalizado.<br> Esta tarefa
     * noticia o servidor que o painel entrou em estado inativo, com intuito de
     * salvar banda.<br>
     *
     * @author ulysses
     *
     */
    class TarefaDesligamento extends Thread {

        @Override
        public void run() {
            try {
                PacketListener.this.desregistrarDoServidor();
            } catch (Exception e) {
                // Painel sendo fechado, ignorar erro
            }
        }
    }

    /**
     * Exception que representa timeout em operações de I/O
     *
     * @author ulysses
     *
     */
    @SuppressWarnings("serial")
    class TimeoutException extends IOException {

        /**
         * @param message
         */
        public TimeoutException(String message) {
            super(message);
        }
    }
    
    public static enum TipoPacoteRecebido {
        MSG_SENHA, // 0
        MSG_CONFIRMA_CADASTRO, // 1
        MSG_URLS,              // 2
    }

    public static enum TipoPacoteEnviado {

        MSG_CADASTRO_PAINEL, // 0
        MSG_PAINEL_VIVO, // 1
        MSG_SOLICITAR_URLS, // 2
        MSG_DESATIVAR_PAINEL,	// 3
    }
    
}