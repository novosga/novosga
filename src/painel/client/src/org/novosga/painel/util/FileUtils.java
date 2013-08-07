package org.novosga.painel.util;

import java.io.File;

/**
 *
 * @author rogeriolino
 */
public class FileUtils {
    
    /**
     * Retorna o diretorio de trabalho que tenha permissão de escrita para salvar
     * o arquivo de configuração. Caso não consiga uma exceção será lançada.
     * 
     * Ordem de tentativa:
     *   1. Diretorio atual do jar
     *   2. Diretorio do usuario (de acordo com o SO)
     *   3. Diretorio temporario
     * 
     * @param applicationName
     * @return 
     */
    public static File workingDirectory(String applicationName) {
        // pega o diretorio local, se nao puder escrever tenta o diretorio do usuario ou um temporario
        File workingDirectory = new File(".");
        if (!workingDirectory.canWrite()) {
            try {
                final String userHome = System.getProperty("user.home", ".");
                final String sysName = System.getProperty("os.name").toLowerCase();
                if (sysName.contains("linux") || sysName.contains("solaris")) {
                    workingDirectory = new File(userHome, '.' + applicationName + '/');
                } else if (sysName.contains("windows")) {
                    final String applicationData = System.getenv("APPDATA");
                    if (applicationData != null) {
                        workingDirectory = new File(applicationData, "." + applicationName + '/');
                    } else {
                        workingDirectory = new File(userHome, '.' + applicationName + '/');
                    }
                } else if (sysName.contains("mac")) {
                    workingDirectory = new File(userHome, "Library/Application Support/" + applicationName);
                }
            } catch (java.security.AccessControlException e) {
                try {
                    workingDirectory = new File(System.getProperty("java.io.tmpdir"));
                } catch (java.security.AccessControlException e2) {
                    try {
                        workingDirectory = File.createTempFile("", applicationName);
                    } catch (java.io.IOException e3) {
                    }
                }
            }
            if (workingDirectory == null || (!workingDirectory.exists() && !workingDirectory.mkdirs())) {
                throw new RuntimeException("The working directory could not be created: " + workingDirectory);
            }
        }
        return workingDirectory;
    }
    
}
