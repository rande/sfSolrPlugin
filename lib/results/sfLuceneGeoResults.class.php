<?php

/*
 * This file is part of the sfLucenePlugin package
 * (c) 2010 - Julien Lirochon <julien@lirochon.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class sfLuceneGeoResults extends sfLuceneFacetsResults
{
  public function __construct(sfLuceneResponse $response, sfLucene $search, $unit = sfLuceneGeoCriteria::UNIT_KILOMETERS)
  {
    parent::__construct($response, $search);

    $this->convertGeoDistances($unit);
  }

  /**
   * For each result, converts geo_distance field value to the specified unit
   * (localsolr internally works in miles) 
   *
   * @author  Julien Lirochon <julien@lirochon.net>
   * @param   int $unit
   * @return  void
   */
  protected function convertGeoDistances($unit = sfLuceneGeoCriteria::UNIT_KILOMETERS)
  {
    $ratio = sfLuceneGeoCriteria::getUnitRatio($unit);

    if ($ratio != 1)
    {
      foreach($this->results->response->docs as $index => $doc)
      {
        if (isset($doc->{sfLuceneGeoCriteria::DISTANCE_FIELD}))
        {
          $this->results->response->docs[$index]->{sfLuceneGeoCriteria::DISTANCE_FIELD} = $doc->{sfLuceneGeoCriteria::DISTANCE_FIELD} / $ratio;
        }
      }
    }
  }
}
