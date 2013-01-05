<?php
namespace core\model\util;

use \core\model\Model;
use \core\model\Usuario;
use \core\model\Servico;

/**
 * 
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
 *
**/

/**
*
* Classe para gerar a Triagem
* 
* Possui o Usuario e os Servicos disponiveis
*
*/
class Triagem extends Model {
        
    private $usuario;
    private $servicos;

    public function __construct($usuario, $servicos) {
        $this->set_usuario($usuario);
        $this->setServicos($servicos);
    }
    
    /**
     * Retorna o Usuario da Triagem
     * @return Usuario
     */
    public function get_usuario() {
        return $this->usuario;
    }
    
    /**
     * Define o Usuario da Triagem
     * @param Usuario $usuario
     */
    public function set_usuario($usuario) {
        $this->usuario = $usuario;
    }

    /**
     * Retorna os Servicos da Triagem
     * @return array
     */
    public function getServicos() {
        return $this->servicos;
    }    

    /**
     * Define os Servicos da Triagem 
     * @param array $servicos
     */
    public function setServicos($servicos) {
        $this->servicos= $servicos;
    }

}
