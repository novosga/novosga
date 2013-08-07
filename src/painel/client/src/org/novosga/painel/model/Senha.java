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
    private int tamanhoNumero;

    public Senha(String mensagem, char sigla, int numero, String guiche, int numeroGuiche) {
        this.mensagem = mensagem;
        this.sigla = sigla;
        this.numero = numero;
        this.guiche = guiche;
        this.numeroGuiche = numeroGuiche;
        this.tamanhoNumero = 1;
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
    
    public String getNumeroAsString(int length) {
        return String.format("%0" + length + "d", numero);
    }
    
    public String getNumeroAsString() {
        return getNumeroAsString(tamanhoNumero);
    }

    /**
     * Tamanho da parte numerica da senha. Se 3 entao "001", "011", "111", etc
     * @return 
     */
    public int getTamanhoNumero() {
        return tamanhoNumero;
    }

    public void setTamanhoNumero(int tamanhoNumero) {
        this.tamanhoNumero = tamanhoNumero;
    }
    
    /**
     * Returna o numero da senha (preenchido com 3 casas) 
     * mais a sigla do servico: A001
     * @return 
     */
    public String getSenha() {
        return getSenha(this.tamanhoNumero);
    }
    
    /**
     * Returna o numero da senha mais a sigla do servico. Exemplo: A0001
     * @param length Número de casas (será preenchido com zero a esquerda)
     * @return 
     */
    public String getSenha(int length) {
        return sigla + getNumeroAsString(length);
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

    /**
     * Retorna o número do guiche preenchendo com 3 casas. Exemplo: 001
     * @return 
     */
    public String getNumeroGuicheAsString() {
        return getNumeroGuicheAsString(2);
    }

    /**
     * Retorna o número do guiche. Exemplo: 001
     * @param length Número de casas (será preenchido com zero a esquerda)
     */
    public String getNumeroGuicheAsString(int length) {
        return String.format("%0" + length + "d", numeroGuiche);
    }
    
    @Override
    public boolean equals(Object o) {
        if (o instanceof Senha) {
            Senha senha = (Senha) o;
            return senha.getNumero() == this.getNumero() && senha.getSigla() == this.getSigla();
        }
        return false;
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
