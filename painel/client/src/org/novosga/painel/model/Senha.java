package org.novosga.painel.model;

/**
 * Ticket model
 * Modelo representando a senha
 * @author rogeriolino
 */
public class Senha {

    private String mensagem;
    private char sigla;
    private int numero;
    private String guiche;
    private int numeroGuiche;
    private boolean status;

    public Senha(String mensagem, char sigla, int numero, String guiche, int numeroGuiche) {
        this.mensagem = mensagem;
        this.sigla = sigla;
        this.numero = numero;
        this.guiche = guiche;
        this.numeroGuiche = numeroGuiche;
    }

    public boolean isStatus() {
        return status;
    }

    public void setStatus(boolean status) {
        this.status = status;
    }

    public String getMensagem() {
        return mensagem;
    }

    public void setMensagem(String mensagem) {
        this.mensagem = mensagem;
    }

    public char getSigla() {
        return sigla;
    }

    public void setSigla(char sigla) {
        this.sigla = sigla;
    }

    public int getNumero() {
        return numero;
    }

    public void setNumero(int numero) {
        this.numero = numero;
    }
    
    /**
     * Returns the ticket code (char + number with zero fill): A0001
     * @return 
     */
    public String getSenha() {
        return sigla + String.format("%04d", numero);
    }
    
    public String getGuiche() {
        return guiche;
    }

    public void setGuiche(String guiche) {
        this.guiche = guiche;
    }

    public int getNumeroGuiche() {
        return numeroGuiche;
    }

    public void setNumeroGuiche(int numeroGuiche) {
        this.numeroGuiche = numeroGuiche;
    }

    public String getNumeroGuicheAsString() {
        return String.format("%03d", numeroGuiche);
    }
    
    @Override
    public boolean equals(Object o) {
        try {
            Senha senha = (Senha) o;
            return getSenha().equals(senha.getSenha());
        } catch (Exception e) {
            return false;
        }
    }

    @Override
    public int hashCode() {
        int hash = 7;
        hash = 71 * hash + this.sigla;
        hash = 71 * hash + this.numero;
        return hash;
    }
    
    @Override
    public String toString() {
        return getSenha();
    }
    
}
