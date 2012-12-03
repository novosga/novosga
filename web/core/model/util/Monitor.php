<?php
namespace core\model\util;

use \core\model\Model;
use \core\model\Servico;

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
 * Classe Monitor
 * 
 * Para gerar o Monitor
 * 
 */
class Monitor extends Model {

    private $menu = array();
    private $servicos = array();
    private $total_senhas = 0;
    
    # construtor
    public function __construct($menu, $servicos, $total) {
        $this->set_menu($menu);
        $this->setServicos($servicos);
        $this->set_total_senhas($total);
    }

    /**
     * Retorna o menu do Monitor (array de Menu).
     * @return array
     */
    public function get_menu() {
        return $this->menu;
    }
    
    /**
     * Define o menu do Monitor
     *
     * @param array $menu
     */
    public function set_menu($menu) {
        if (is_array($menu)) {
            $this->menu = $menu;
        } else {
            throw new Exception(_('Erro ao definir lista de menu. Deve ser um array.'));
        }
    }
    
    /**
     * Retorna a lista dos servicos do Monitor
     * @return array
     */
    public function getServicos() {
        return $this->servicos;
    }

    /**
     * Retorna a lista dos servicos do Monitor delimitada pelos parametros
     * (inicio e fim).
     *
     * @param int $ini
     * @param int $fim
     * @return array
     */
    public function getServicos_at($ini, $fim) {
        $servicos = array();
        $aux = $this->getServicos();
        $i = 0;
        foreach ($aux as $s) {
            if ($i >= $ini && $i < $fim) {
                $servicos[$s->getId()] = $s;
            } else if ($i >= $fim) {
                break;
            }
            $i++;
        }
        return $servicos;
    }
    
    /**
     * Define os servicos do Monitor
     * @param array $servicos
     */
    public function setServicos($servicos) {
        if (is_array($servicos)) {
            $this->servicos = $servicos;
        } else {
            throw new Exception(_('Erro ao definir lista de servicos. Deve ser um array.'));
        }
    }
    
    /**
     * Retorna o total de senhas do Menu
     * @return int
     */
    public function get_total_senhas() {
        return $this->total_senhas;
    }
    
    /**
     * Define o total de senhas do Menu
     * @param int $total
     */
    public function set_total_senhas($total) {
        $total = (int) $total;
        if ($total >= 0) {
            $this->total_senhas = $total;
        } else {
            throw new Exception(_('Erro ao definir total de senhas. Deve ser um inteiro positivo.'));
        }
    }
    
}
