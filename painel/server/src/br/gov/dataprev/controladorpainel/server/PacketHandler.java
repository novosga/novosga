/**
 *
 */
package br.gov.dataprev.controladorpainel.server;

import br.gov.dataprev.controladorpainel.ConfigManager;
import java.net.InetSocketAddress;
import java.nio.ByteBuffer;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.logging.Logger;

import br.gov.dataprev.controladorpainel.recebidos.CadastroPainelMsg;
import br.gov.dataprev.controladorpainel.recebidos.ClienteMsg;
import br.gov.dataprev.controladorpainel.recebidos.DesativarPainel;
import br.gov.dataprev.controladorpainel.recebidos.PainelVivo;
import br.gov.dataprev.controladorpainel.recebidos.SolicitaURLs;

/**
 * Define que tipo de mensagem esta contida em um determinado pacote recebido
 *
 * @author ulysses
 *
 */
public class PacketHandler {

    public static final int VERSAO_PROTOCOLO = 1;
    private static final Logger LOG = Logger.getLogger(PacketHandler.class.getName());
    private ExecutorService _executor;

    public PacketHandler(int minThreads) {
        // Cria no minimo MIN_THREADS no máxmimo (Numero de cores da maquina)
        int nThreads = Math.max(minThreads, Runtime.getRuntime().availableProcessors());
        _executor = Executors.newFixedThreadPool(nThreads);
    }

    public void processaDados(final InetSocketAddress origem, ByteBuffer buf) {
        if (buf.remaining() > 0) {
            int opcode = buf.get() & 0xFF;
            ClienteMsg m = null;
            switch (opcode) {
                case 0:
                    m = new CadastroPainelMsg(origem);
                    break;
                case 1:
                    m = new PainelVivo(origem);
                    break;
                case 2:
                    m = new SolicitaURLs(origem);
                    break;
                case 3:
                    m = new DesativarPainel(origem);
                    break;
                default:
                    LOG.warning("Pacote Desconhecido: " + opcode + " - Origem: " + origem + " - Tamanho: " + (buf.remaining() + 1));
                    break;
            }
            // se o pacote eh conhecido
            if (m != null) {
                // se o pacote for interpretado com sucesso
                if (m.read(buf)) {
                    //LOG.info("Received: "+m);
                    _executor.execute(m);
                }
            }
        } else {
            LOG.warning("Recebido pacote " + ConfigManager.getInstance().getNetworkProtocol() + " com " + buf.remaining() + " bytes de dados, descartando");
        }
    }
}
