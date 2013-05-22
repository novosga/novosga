package org.novosga.painel.client;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.URL;
import java.nio.charset.Charset;
import java.util.StringTokenizer;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.util.HashMap;
import java.util.Map;
import org.novosga.painel.client.config.PainelConfig;
import org.novosga.painel.event.ObterUrlEvent;

/**
 * 
 * @author ralfilho
 */
public class PainelService {

    private static final Logger LOG = Logger.getLogger(PainelService.class.getName());
    private static final long serialVersionUID = 2833342489278580235L;
    
    private String urlUnidades;
    private String urlServicos;
    private Map<Integer,String> unidades = new HashMap<Integer,String>();
    private Map<Integer,String> servicos = new HashMap<Integer,String>();
    private Main main;
    
    public PainelService(Main main) throws Exception {
        this.main = main;
    }
    
    public synchronized void register(String server) throws Exception {
        PainelConfig config = main.getConfig();
        int unidade = config.get(PainelConfig.KEY_UNIDADE, Integer.class).getValue();
        Integer[] servicos = config.get(PainelConfig.KEY_SERVICOS, Integer[].class).getValue();
        main.getListener().cadastrarPainel(unidade, servicos);
    }
    
    public synchronized void registerAndLoad(final Runnable onload) throws Exception {
        register(main.getListener().getServer());
        loadUrls(onload);
    }
    
    public synchronized void loadUrls(String server, final Runnable onload) throws Exception {
        main.getListener().setServer(server);
        loadUrls(onload);
    }
    
    public synchronized void loadUrls(final Runnable onload) throws Exception {
        main.getListener().setOnObterUrlEvent(new ObterUrlEvent() {
            @Override
            public void handler(String unidades, String servicos) {
                urlUnidades = unidades;
                urlServicos = servicos;
                onload.run();
            }
        });
        main.getListener().obterURLs();
    }
    
    public synchronized Map<Integer,String> buscarUnidades() {
        LOG.info("Montando unidades a partir de: " + urlUnidades);
        unidades.clear();
        try {
            if (urlUnidades != null && urlUnidades.length() > 0) {
                try {
                    URL urlUni = new URL(urlUnidades);
                    BufferedReader br = new BufferedReader(new InputStreamReader(urlUni.openStream(), Charset.forName("UTF-8")));
                    String linha;
                    while ((linha = br.readLine()) != null) {
                        String[] parts = linha.split("#");
                        if (parts.length >= 3) {
                            try {
                                int id = Integer.parseInt(parts[0]);
                                String codigo = parts[1];
                                String nome = parts[2];
                                unidades.put(id, codigo + " - " + nome);
                            } catch (Exception e) {
                                LOG.log(Level.SEVERE, "Servidor enviou uma linha inválida:\n" + linha, e);
                            }
                        }
                    }
                } catch (Exception ex) {
                    LOG.log(Level.SEVERE, null, ex);
                }
            }
        } catch (Exception e) {
            LOG.severe("Erro ao buscar unidades: " + e.getMessage());
        }
        return unidades;
    }

    public synchronized Map<Integer,String> buscarServicos(int idUnidade) {
        String url = this.urlServicos.replace("%id_unidade%", "" + idUnidade);
        LOG.info("URL Servicos com unidade: " + url);
        servicos.clear();
        try {
            URL con = new URL(url);
            BufferedReader load = new BufferedReader(new InputStreamReader(con.openStream(), Charset.forName("UTF-8")));
            int i = 1;
            int id;
            String linha, desc, sigla;
            while ((linha = load.readLine()) != null) {
                StringTokenizer str = new StringTokenizer(linha, "#");
                try {
                    id = Integer.parseInt(str.nextToken());
                    sigla = str.nextToken();
                    desc = str.nextToken();
                    servicos.put(id, sigla + " - " + desc);
                    i++;
                } catch (NumberFormatException e) {
                    // servidor enviou um ID inválido (nunca deve acontecer)
                    e.printStackTrace();
                }
            }
        } catch (Exception e) {
            LOG.severe("Erro ao busar serviços da unidade " + idUnidade + ": " + e.getMessage());
        }
        return servicos;
    }

}
