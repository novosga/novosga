<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\DBAL\Driver\PDODblib;

use PDO;
use Doctrine\DBAL\Driver\PDOStatement;

/**
 * SQL Server Statement for DBLib
 *
 * @author rogeriolino
 */
class Statement extends PDOStatement
{

    public function bindValue($param, $value, $type = null)
    {
        if (mb_detect_encoding($value, 'UTF-8', true) == 'UTF-8') {
            $value = utf8_decode($value);
        }
        return parent::bindParam($param, $value, $type, null);
    }
    
    /*
     * no linux o driver para o mssql (dblib) nao converte entre utf8(web) + iso-8859-1(banco)
     * entao adiciona um interceptor para converter para utf8 quando imprimir, e
     * para iso-8859-1 quando for salvar
     */
    private function linuxIsoFix(&$rs) {
        if (is_array($rs)) {
            foreach ($rs as $k => $v) {
                $rs[$k] = $this->linuxIsoFix($v);
            }
        } else if (is_string($rs)) {
            if (mb_detect_encoding($rs, 'UTF-8', true) === false) {
                $rs = utf8_encode($rs);
            }
        }
        return $rs;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($fetchMode = null)
    {
        $rs = parent::fetch($fetchMode);
        $this->linuxIsoFix($rs);
        return $rs;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($fetchMode = null)
    {
        $rs = parent::fetchAll($fetchMode);
        $this->linuxIsoFix($rs);
        return $rs;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchColumn($columnIndex = 0)
    {
        $row = $this->fetch(PDO::FETCH_NUM);
        return $row[$columnIndex];
    }

}

