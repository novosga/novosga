package org.novosga.painel.event;

import org.novosga.painel.model.Senha;
import java.io.Serializable;

/**
 *
 * @author rogeriolino
 */
public interface SenhaEvent extends Serializable {
    
    public abstract void handle(Senha senha);
    
}
