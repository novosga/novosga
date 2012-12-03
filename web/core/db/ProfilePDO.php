<?php

/**
 * Copyright (C) 2009 DATAPREV - Empresa de Tecnologia e Informações da Previdência Social - Brasil
 *
 * Este arquivo é parte do programa SGA Livre - Sistema de Gerenciamento do Atendimento - Versão Livre
 *
 * O SGA é um software livre; você pode redistribuí­-lo e/ou modificá-lo dentro dos termos da Licença Pública Geral GNU como 
 * publicada pela Fundação do Software Livre (FSF); na versão 2 da Licença, ou (na sua opnião) qualquer versão.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer
 * MERCADO ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU para maiores detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título "LICENCA.txt", junto com este programa, se não, escreva para a 
 * Fundação do Software Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA.
 */

/**
 * Similar ao PDO, mas armazena estatisticas das consultas efetuadas.<br>
 *
 * Para cada consulta efetuada, uma consulta extra é utilziada para armazenar a estatistica.<br>
 * Esta classe só deve ser usada em ambiente de desenvolvimento.<br>
 *
 * @author ulysses
 */
class ProfilePDO {

    private $pdo;

    public function  __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Toda chamada de metodo: ex $pdo->abc() irá cair aqui.
     *
     * @param string $name O nome todo metodo chamado
     * @param array $arguments Os argumentos passados na chamada
     * @return void
     */
    public function __call($name, $arguments) {
        if ($this->pdo != null && method_exists($this->pdo, $name)) {

            // se esta chamando $pdo->prepare()
            if ($name == "prepare") {
                // retorna o PDOStatementWrapper do statement retornado pelo PDO
                $pdoStatement = call_user_func_array(array($this->pdo, $name), $arguments);
                
                return new PDOStatementWrapper($pdoStatement, $this->pdo);
            }
            else {
                // passa a chamada pro PDO normalmente
                return call_user_func_array(array($this->pdo, $name), $arguments);
            }
        }
        else {
            throw new Exception("Attempt to call inexistent method: $name");
        }
    }
}

class PDOStatementWrapper {

    /** PDOStatement encpsulado por este wrapper */
    private $pdoStatement;

    /** PDO real(não encapsulado) */
    private $pdo;

    public function  __construct($pdoStatement, $pdo) {
        $this->pdoStatement = $pdoStatement;
        $this->pdo = $pdo;
    }

    /**
     * Toda chamada de metodo: ex $pdo->abc() irá cair aqui.
     *
     * @param string $name O nome todo metodo chamado
     * @param array $arguments Os argumentos passados na chamada
     * @return void
     */
    public function __call($name, $arguments) {
        if ($this->pdoStatement != null && method_exists($this->pdoStatement, $name)) {

            // se esta chamando $statement->execute()
            if ($name == "execute") {
                $sql = "SELECT sp_incrementar_query_count(:query)";
                
                $statement = $this->pdo->prepare($sql);
                $statement->bindValue(":query", $this->pdoStatement->queryString, PDO::PARAM_STR);
                $statement->execute();

                return call_user_func_array(array($this->pdoStatement, $name), $arguments);
            }

            return call_user_func_array(array($this->pdoStatement, $name), $arguments);
        }
        else {
            throw new Exception("Attempt to call inexistent method: $name");
        }
    }
}

