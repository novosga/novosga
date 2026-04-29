<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Doctrine\Functions;

use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class RegexReplaceFunction extends FunctionNode
{
    private const NAME = 'REGEX_REPLACE';

    public Node $value;
    public Node $pattern;
    public Node $replacement;

    public function getSql(SqlWalker $sqlWalker): string
    {
        $platform = $sqlWalker->getConnection()->getDatabasePlatform();
        $value = $this->value->dispatch($sqlWalker);
        $pattern = $this->pattern->dispatch($sqlWalker);
        $replacement = $this->replacement->dispatch($sqlWalker);

        if ($platform instanceof PostgreSQLPlatform) {
            // 'g' flag = replace all occurrences
            return "REGEXP_REPLACE({$value}, {$pattern}, {$replacement}, 'g')";
        }
        if ($platform instanceof MySQLPlatform) {
            // replaces all occurrences by default
            return "REGEXP_REPLACE({$value}, {$pattern}, {$replacement})";
        }

        throw NotSupported::new(self::NAME);
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->value = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);

        $this->pattern = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);

        $this->replacement = $parser->StringPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
