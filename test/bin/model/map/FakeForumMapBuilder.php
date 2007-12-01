<?php



class FakeForumMapBuilder {

	
	const CLASS_NAME = 'plugins.sfLucenePlugin.test.bin.model.map.FakeForumMapBuilder';

	
	private $dbMap;

	
	public function isBuilt()
	{
		return ($this->dbMap !== null);
	}

	
	public function getDatabaseMap()
	{
		return $this->dbMap;
	}

	
	public function doBuild()
	{
		$this->dbMap = Propel::getDatabaseMap('propel');

		$tMap = $this->dbMap->addTable('fake_forum');
		$tMap->setPhpName('FakeForum');

		$tMap->setUseIdGenerator(true);

		$tMap->addPrimaryKey('ID', 'Id', 'int', CreoleTypes::INTEGER, true, null);

		$tMap->addColumn('COOLNESS', 'Coolness', 'double', CreoleTypes::FLOAT, false, null);

	} 
} 