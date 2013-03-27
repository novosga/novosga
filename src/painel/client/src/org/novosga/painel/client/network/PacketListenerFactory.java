package org.novosga.painel.client.network;

public class PacketListenerFactory {
   
    public static PacketListener create(String protocol, int receivePort, int sendPort, String server) {
        if (protocol.equalsIgnoreCase("TCP")) {
            return new TCPListener(receivePort, sendPort, server);
        } else if (protocol.equalsIgnoreCase("UDP")) {
            return new UDPListener(receivePort, sendPort, server);
        }
        throw new RuntimeException("Erro abrindo socket " + protocol + ", verifique se outro painel não está aberto.");
    }
    
}