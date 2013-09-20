<?php
namespace novosga\contrib\DoctrineExt\MsSql;

use \Doctrine\ORM\Query\Lexer;
use \Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * DateDiffFunction ::= "DATEDIFF" "(" ArithmeticPrimary "," ArithmeticPrimary ")"
 */
class DateDiff extends FunctionNode {
    
    public $firstDateExpression = null;
    public $secondDateExpression = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstDateExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondDateExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'DATEDIFF(s, ' .
            $this->firstDateExpression->dispatch($sqlWalker) . ', ' .
            $this->secondDateExpression->dispatch($sqlWalker) .
        ')';
    }
}