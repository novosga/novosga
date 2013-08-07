package org.novosga.painel.client.network;

import java.util.concurrent.ThreadFactory;

/**
 *
 * @author rogeriolino
 */
public class SimpleThreadFactory implements ThreadFactory {
    
   private int counter = 0;
   private String prefix = "";

   public SimpleThreadFactory(String prefix) {
     this.prefix = prefix;
   }

   @Override
   public Thread newThread(Runnable r) {
     return new Thread(r, prefix + "-" + (counter++));
   }
}