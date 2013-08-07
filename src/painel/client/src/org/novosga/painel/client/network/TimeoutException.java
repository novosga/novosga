package org.novosga.painel.client.network;

import java.io.IOException;

/**
 * Exception que representa timeout em operações de I/O
 *
 * @author ulysses
 */
@SuppressWarnings("serial")
class TimeoutException extends IOException {

    public TimeoutException(String message) {
        super(message);
    }

}