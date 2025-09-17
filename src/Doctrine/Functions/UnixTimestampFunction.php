<?php

namespace App\Doctrine\Functions;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class UnixTimestampFunction extends FunctionNode
{
    private const NAME = 'UNIX_TIMESTAMP';

    public Node $date;

    public function getSql(SqlWalker $sqlWalker): string
    {
        $platform = $sqlWalker->getConnection()->getDatabasePlatform();

        if ($platform instanceof MySQLPlatform) {
            return 'UNIX_TIMESTAMP(' . $this->date->dispatch($sqlWalker) . ')';
        }
        if ($platform instanceof PostgreSQLPlatform) {
            return 'EXTRACT(EPOCH FROM ' . $this->date->dispatch($sqlWalker) . '::timestamp)';
        }

        throw Exception::notSupported(self::NAME);
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
