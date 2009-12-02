<?php

class sfLuceneTestPatternRouting extends sfPatternRouting
{

  public function setCurrentRouteName($name)
  {
    $this->currentRouteName = $name;
  }
}

