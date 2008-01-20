<?php


abstract class BaseFakeForum extends BaseObject  implements Persistent {


	
	protected static $peer;


	
	protected $id;


	
	protected $coolness;

	
	protected $collFakeForumI18ns;

	
	protected $lastFakeForumI18nCriteria = null;

	
	protected $alreadyInSave = false;

	
	protected $alreadyInValidation = false;

  
  protected $culture;

	
	public function getId()
	{

		return $this->id;
	}

	
	public function getCoolness()
	{

		return $this->coolness;
	}

	
	public function setId($v)
	{

						if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = FakeForumPeer::ID;
		}

	} 
	
	public function setCoolness($v)
	{

		if ($this->coolness !== $v) {
			$this->coolness = $v;
			$this->modifiedColumns[] = FakeForumPeer::COOLNESS;
		}

	} 
	
	public function hydrate(ResultSet $rs, $startcol = 1)
	{
		try {

			$this->id = $rs->getInt($startcol + 0);

			$this->coolness = $rs->getFloat($startcol + 1);

			$this->resetModified();

			$this->setNew(false);

						return $startcol + 2; 
		} catch (Exception $e) {
			throw new PropelException("Error populating FakeForum object", $e);
		}
	}

	
	public function delete($con = null)
	{

    foreach (sfMixer::getCallables('BaseFakeForum:delete:pre') as $callable)
    {
      $ret = call_user_func($callable, $this, $con);
      if ($ret)
      {
        return;
      }
    }


		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(FakeForumPeer::DATABASE_NAME);
		}

		try {
			$con->begin();
			FakeForumPeer::doDelete($this, $con);
			$this->setDeleted(true);
			$con->commit();
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	

    foreach (sfMixer::getCallables('BaseFakeForum:delete:post') as $callable)
    {
      call_user_func($callable, $this, $con);
    }

  }
	
	public function save($con = null)
	{

    foreach (sfMixer::getCallables('BaseFakeForum:save:pre') as $callable)
    {
      $affectedRows = call_user_func($callable, $this, $con);
      if (is_int($affectedRows))
      {
        return $affectedRows;
      }
    }


		if ($this->isDeleted()) {
			throw new PropelException("You cannot save an object that has been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(FakeForumPeer::DATABASE_NAME);
		}

		try {
			$con->begin();
			$affectedRows = $this->doSave($con);
			$con->commit();
    foreach (sfMixer::getCallables('BaseFakeForum:save:post') as $callable)
    {
      call_user_func($callable, $this, $con, $affectedRows);
    }

			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	protected function doSave($con)
	{
		$affectedRows = 0; 		if (!$this->alreadyInSave) {
			$this->alreadyInSave = true;


						if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = FakeForumPeer::doInsert($this, $con);
					$affectedRows += 1; 										 										 
					$this->setId($pk);  
					$this->setNew(false);
				} else {
					$affectedRows += FakeForumPeer::doUpdate($this, $con);
				}
				$this->resetModified(); 			}

			if ($this->collFakeForumI18ns !== null) {
				foreach($this->collFakeForumI18ns as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			$this->alreadyInSave = false;
		}
		return $affectedRows;
	} 
	
	protected $validationFailures = array();

	
	public function getValidationFailures()
	{
		return $this->validationFailures;
	}

	
	public function validate($columns = null)
	{
		$res = $this->doValidate($columns);
		if ($res === true) {
			$this->validationFailures = array();
			return true;
		} else {
			$this->validationFailures = $res;
			return false;
		}
	}

	
	protected function doValidate($columns = null)
	{
		if (!$this->alreadyInValidation) {
			$this->alreadyInValidation = true;
			$retval = null;

			$failureMap = array();


			if (($retval = FakeForumPeer::doValidate($this, $columns)) !== true) {
				$failureMap = array_merge($failureMap, $retval);
			}


				if ($this->collFakeForumI18ns !== null) {
					foreach($this->collFakeForumI18ns as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}


			$this->alreadyInValidation = false;
		}

		return (!empty($failureMap) ? $failureMap : true);
	}

	
	public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = FakeForumPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->getByPosition($pos);
	}

	
	public function getByPosition($pos)
	{
		switch($pos) {
			case 0:
				return $this->getId();
				break;
			case 1:
				return $this->getCoolness();
				break;
			default:
				return null;
				break;
		} 	}

	
	public function toArray($keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = FakeForumPeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getId(),
			$keys[1] => $this->getCoolness(),
		);
		return $result;
	}

	
	public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = FakeForumPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->setByPosition($pos, $value);
	}

	
	public function setByPosition($pos, $value)
	{
		switch($pos) {
			case 0:
				$this->setId($value);
				break;
			case 1:
				$this->setCoolness($value);
				break;
		} 	}

	
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = FakeForumPeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setCoolness($arr[$keys[1]]);
	}

	
	public function buildCriteria()
	{
		$criteria = new Criteria(FakeForumPeer::DATABASE_NAME);

		if ($this->isColumnModified(FakeForumPeer::ID)) $criteria->add(FakeForumPeer::ID, $this->id);
		if ($this->isColumnModified(FakeForumPeer::COOLNESS)) $criteria->add(FakeForumPeer::COOLNESS, $this->coolness);

		return $criteria;
	}

	
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(FakeForumPeer::DATABASE_NAME);

		$criteria->add(FakeForumPeer::ID, $this->id);

		return $criteria;
	}

	
	public function getPrimaryKey()
	{
		return $this->getId();
	}

	
	public function setPrimaryKey($key)
	{
		$this->setId($key);
	}

	
	public function copyInto($copyObj, $deepCopy = false)
	{

		$copyObj->setCoolness($this->coolness);


		if ($deepCopy) {
									$copyObj->setNew(false);

			foreach($this->getFakeForumI18ns() as $relObj) {
				$copyObj->addFakeForumI18n($relObj->copy($deepCopy));
			}

		} 

		$copyObj->setNew(true);

		$copyObj->setId(NULL); 
	}

	
	public function copy($deepCopy = false)
	{
				$clazz = get_class($this);
		$copyObj = new $clazz();
		$this->copyInto($copyObj, $deepCopy);
		return $copyObj;
	}

	
	public function getPeer()
	{
		if (self::$peer === null) {
			self::$peer = new FakeForumPeer();
		}
		return self::$peer;
	}

	
	public function initFakeForumI18ns()
	{
		if ($this->collFakeForumI18ns === null) {
			$this->collFakeForumI18ns = array();
		}
	}

	
	public function getFakeForumI18ns($criteria = null, $con = null)
	{
				if ($criteria === null) {
			$criteria = new Criteria();
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collFakeForumI18ns === null) {
			if ($this->isNew()) {
			   $this->collFakeForumI18ns = array();
			} else {

				$criteria->add(FakeForumI18nPeer::ID, $this->getId());

				FakeForumI18nPeer::addSelectColumns($criteria);
				$this->collFakeForumI18ns = FakeForumI18nPeer::doSelect($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(FakeForumI18nPeer::ID, $this->getId());

				FakeForumI18nPeer::addSelectColumns($criteria);
				if (!isset($this->lastFakeForumI18nCriteria) || !$this->lastFakeForumI18nCriteria->equals($criteria)) {
					$this->collFakeForumI18ns = FakeForumI18nPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastFakeForumI18nCriteria = $criteria;
		return $this->collFakeForumI18ns;
	}

	
	public function countFakeForumI18ns($criteria = null, $distinct = false, $con = null)
	{
				if ($criteria === null) {
			$criteria = new Criteria();
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		$criteria->add(FakeForumI18nPeer::ID, $this->getId());

		return FakeForumI18nPeer::doCount($criteria, $distinct, $con);
	}

	
	public function addFakeForumI18n(FakeForumI18n $l)
	{
		$this->collFakeForumI18ns[] = $l;
		$l->setFakeForum($this);
	}

  public function getCulture()
  {
    return $this->culture;
  }

  public function setCulture($culture)
  {
    $this->culture = $culture;
  }

  public function getTitle($culture = null)
  {
    return $this->getCurrentFakeForumI18n($culture)->getTitle();
  }

  public function setTitle($value, $culture = null)
  {
    $this->getCurrentFakeForumI18n($culture)->setTitle($value);
  }

  public function getDescription($culture = null)
  {
    return $this->getCurrentFakeForumI18n($culture)->getDescription();
  }

  public function setDescription($value, $culture = null)
  {
    $this->getCurrentFakeForumI18n($culture)->setDescription($value);
  }

  protected $current_i18n = array();

  public function getCurrentFakeForumI18n($culture = null)
  {
    if (is_null($culture))
    {
      $culture = is_null($this->culture) ? sfPropel::getDefaultCulture() : $this->culture;
    }

    if (!isset($this->current_i18n[$culture]))
    {
      $obj = FakeForumI18nPeer::retrieveByPK($this->getId(), $culture);
      if ($obj)
      {
        $this->setFakeForumI18nForCulture($obj, $culture);
      }
      else
      {
        $this->setFakeForumI18nForCulture(new FakeForumI18n(), $culture);
        $this->current_i18n[$culture]->setCulture($culture);
      }
    }

    return $this->current_i18n[$culture];
  }

  public function setFakeForumI18nForCulture($object, $culture)
  {
    $this->current_i18n[$culture] = $object;
    $this->addFakeForumI18n($object);
  }


  public function __call($method, $arguments)
  {
    if (!$callable = sfMixer::getCallable('BaseFakeForum:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method BaseFakeForum::%s', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }


} 