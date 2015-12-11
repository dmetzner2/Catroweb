<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class ChangeYByNStatement extends Statement
{
    const BEGIN_STRING = "change Y by (";
    const END_STRING = ")" . "<br/>";

    public function __construct($statementFactory, $xmlTree, $spaces)
    {
        parent::__construct($statementFactory, $xmlTree, $spaces,
            self::BEGIN_STRING,
            self::END_STRING);
    }

}

?>