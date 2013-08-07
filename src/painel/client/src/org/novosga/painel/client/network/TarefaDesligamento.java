package org.novosga.painel.client.network;

/**
 * Tarefa chamada pela JVM quando o programa é finalizado.<br> Esta tarefa
 * noticia o servidor que o painel entrou em estado inativo, com intuito de
 * salvar banda.<br>
 *
 * @author ulysses
 */
class TarefaDesligamento extends Thread {
    
    private final PacketListener listener;
    
    public TarefaDesligamento(PacketListener listener) {
        super("TarefaDesligamentoThread");
        this.listener = listener;
    }

   @Override
   public void run() {
       try {
           this.listener.desregistrarDoServidor();
       } catch (Exception e) {
           // Painel sendo fechado, ignorar erro
       }
   }
   
}