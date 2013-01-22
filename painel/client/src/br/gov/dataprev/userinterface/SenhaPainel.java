package br.gov.dataprev.userinterface;

import br.gov.dataprev.estruturadados.ConfLayout;
import br.gov.dataprev.painel.audio.AudioPlayer;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.SwingUtilities;

/**
 * @author DATAPREV
 * @version 1.0
 * @category Interface
 */
public class SenhaPainel implements Runnable {

    private static final Logger LOG = Logger.getLogger(Web.class.getName());
    private final Web web;
    private final String _msgEspecial;
    private final String _senha;
    private final String _guiche;
    private final String _numeroGuiche;
    private final int intervalo = 1;

    public SenhaPainel(Web web, String msgEspecial, char charServico, int senha, String guiche, int numeroGuiche) {
        this.web = web;
        _msgEspecial = msgEspecial;
        String senhaStr = String.valueOf(senha);
        // completa com os zeros necessarios
        for (int i = senhaStr.length(); i < 4; i++) {
            senhaStr = "0" + senhaStr;
        }
        _senha = charServico + senhaStr;
        _guiche = guiche + ':';
        String numGuicheStr = String.valueOf(numeroGuiche);
        // completa com os zeros necessarios
        for (int i = numGuicheStr.length(); i < 3; i++) {
            numGuicheStr = "0" + numGuicheStr;
        }
        _numeroGuiche = numGuicheStr;
    }

    /* (non-Javadoc)
     * @see java.lang.Runnable#run()
     */
    @Override
    public void run() {
        web.exibirSenha(_msgEspecial, _guiche, _numeroGuiche, _senha);
        PiscaLayout t = new PiscaLayout();
        t.start();
        try {
            AudioPlayer.getInstance().play(AudioPlayer.ALERTS_PATH, ConfLayout.getInstance().getSom());
        } catch (Throwable tw) {
            LOG.log(Level.SEVERE, "Erro tocando som: " + ConfLayout.getInstance().getSom(), tw);
        }
        if (ConfLayout.getInstance().isVocalizarSenhas()) {
            try {
                AudioPlayer.getInstance().getVocalizador().vocalizar("senha", true);
                for (int i = 0; i < _senha.length(); i++) {
                    AudioPlayer.getInstance().getVocalizador().vocalizar(String.valueOf(_senha.charAt(i)), true);
                }
                AudioPlayer.getInstance().getVocalizador().vocalizar("guiche", true);
                for (int i = 0; i < _numeroGuiche.length(); i++) {
                    AudioPlayer.getInstance().getVocalizador().vocalizar(String.valueOf(_numeroGuiche.charAt(i)), true);
                }
            } catch (Exception e1) {
                LOG.log(Level.SEVERE, "Erro durante vocalização de senha", e1);
            }
        }
        try {
            // Adormece o  thread para garantir que a senha atual terá tempo suficiente de exibição
            Thread.sleep(intervalo * 1000);
        } catch (InterruptedException e) {
            // nunca deverá acontecer
            e.printStackTrace();
        }
    }

    @Override
    public String toString() {
        return "Senha[Msg: " + _msgEspecial + " Senha: " + _senha + " GuicheMsg: " + _guiche + " GuicheNum: " + _numeroGuiche + "]";
    }

    // metodo que faz a senha piscar
    private class PiscaLayout extends Thread {

        @Override
        public void run() {
            final Runnable r = new Runnable() {
                @Override
                public void run() {
                    //System.err.println("PISCA: "+!lSenha.isVisible());
                    web.getLabelSenha().getJLabel().setVisible(!web.getLabelSenha().getJLabel().isVisible());
                }
            };
            // o total deve ser PAR, senão o texto fica escondido ao final do loop
            for (int i = 0; i < 6; i++) {
                // alteracoes em componentes da Swing devem ser feitos pelo Thread da Swing
                SwingUtilities.invokeLater(r);
                // Aguarda um tempo antes de alterar o estado
                // criando o efeito de pisca-pisca
                try {
                    Thread.sleep(300);
                } catch (InterruptedException e) {
                    // nada
                }
            }
        }
    }
    
}