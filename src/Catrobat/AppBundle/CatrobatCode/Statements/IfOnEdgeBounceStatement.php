<?php

namespace Catrobat\AppBundle\CatrobatCode\Statements;

class IfOnEdgeBounceStatement extends Statement
{
	const BEGIN_STRING = "if on edge, bounce";
	const END_STRING = "" . "<br/>";
	
	public function __construct($statementFactory, $xmlTree, $spaces)
	{
		parent::__construct($statementFactory, $xmlTree, $spaces,
							self::BEGIN_STRING,
							self::END_STRING);
	}
	
}
?>