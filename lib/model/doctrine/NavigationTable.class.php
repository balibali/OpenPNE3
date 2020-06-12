<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

class NavigationTable extends Doctrine_Table
{
  public function retrieveByType($type)
  {
    return $this->createQuery('n')
      ->where('n.type = ?', $type)
      ->leftJoin('n.Translation t')
      ->orderBy('n.sort_order')
      ->execute();
  }

  public function getTypesByAppName($appName)
  {
    switch($appName)
    {
      case 'smartphone' :
        return array(
          'smartphone_insecure',
          'smartphone_default',
        );
      case 'backend' :
        return array(
          'backend_side'
        );
      default :
        return array(
          'insecure_global',
          'secure_global',
          'default',
          'friend',
          'community',
        );
    }
  }
}
