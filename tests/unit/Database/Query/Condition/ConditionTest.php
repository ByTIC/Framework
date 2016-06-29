<?php

namespace Nip\Tests\Database\Query\Condition;

use Nip_DB_Wrapper;

class ConditionTest extends \Codeception\TestCase\Test
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var Nip_DB_Wrapper
	 */
	protected $_object;

	/**
	 * @var \Nip_DB_Query_Select
	 */
	protected $_query;

	public function setUp()
	{
		parent::setUp();
		$this->_object = new Nip_DB_Wrapper('MySQL');
		$this->_query = $this->_object->newQuery();
	}

	public function testParseString()
	{
		$condition = $this->_query->getCondition("name = value");
		$this->assertEquals("name = value", $condition->getString());

		$condition = $this->_query->getCondition("id = ?", 5);
		$this->assertEquals("id = 5", $condition->getString());

		$condition = $this->_query->getCondition("MATCH title AGAINST (?)", "lorem ipsum");
		$this->assertEquals("MATCH title AGAINST ('lorem ipsum')", $condition->getString());

		$condition = $this->_query->getCondition("pos BETWEEN ? AND ?", array(1, 10));
		$this->assertEquals("pos BETWEEN 1 AND 10", $condition->getString());
	}

	public function testAndConditions()
	{
		$condition = $this->_query->getCondition("name LIKE '%lorem%'");
		$condition = $condition->and_($this->_query->getCondition("date > NOW()"));

		$this->assertEquals("name LIKE '%lorem%' AND date > NOW()", $condition->getString());
	}

	public function testOrConditions()
	{
		$condition = $this->_query->getCondition("name LIKE '%lorem%'")->or_($this->_query->getCondition("date > NOW()"));
		$this->assertEquals("name LIKE '%lorem%' OR date > NOW()", $condition->getString());
	}

	public function testAndOrConditions()
	{
		$condition1 = $this->_query->getCondition("name LIKE '%lorem%'")->and_($this->_query->getCondition("date > NOW()"));
		$condition2 = $this->_query->getCondition("name LIKE '%ipsum%'")->and_($this->_query->getCondition("date < NOW()"));
		$condition3 = $condition1->or_($condition2);

		$this->assertEquals("(name LIKE '%lorem%' AND date > NOW()) OR (name LIKE '%ipsum%' AND date < NOW())", $condition3->getString());
	}

	public function testNestedConditions()
	{
		$condition2 = $this->_query->getCondition("date > NOW()")->or_($this->_query->getCondition("date < '24.10.2008'"));
		$condition = $this->_query->getCondition("name LIKE '%lorem%'")->and_($condition2);

		$this->assertEquals("name LIKE '%lorem%' AND (date > NOW() OR date < '24.10.2008')", $condition->getString());
	}

	public function testWhereIn()
	{
		$condition = $this->_query->getCondition("id in ?", array(1, 2, 3));
		$this->assertEquals("id in (1, 2, 3)", $condition->getString());
	}

}