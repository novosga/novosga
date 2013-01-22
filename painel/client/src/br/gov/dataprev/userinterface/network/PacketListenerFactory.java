
package br.gov.dataprev.userinterface.network;

import br.gov.dataprev.exec.Painel;

public class PacketListenerFactory {
   
    public static PacketListener create(String protocol) {
        if (protocol.equalsIgnoreCase("TCP")) {
            return new TCPListener(Painel.PORT);
        } else if (protocol.equalsIgnoreCase("UDP")) {
            return new UDPListener(Painel.PORT);
        }
        throw new RuntimeException("Erro abrindo socket " + protocol + ", verifique se outro painel não está aberto.");
    }
    
}