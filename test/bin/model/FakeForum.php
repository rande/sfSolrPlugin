<?php

/**
 * Subclass for representing a row from the 'fake_forum' table.
 *
 *
 *
 * @package plugins.sfLucenePlugin.test.bin.model
 */
class FakeForum extends BaseFakeForum
{
}

sfLucenePropelBehavior::getInitializer()->setupModel('FakeForum');