<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

class Revision49_DeleteMobileFunctions extends Doctrine_Migration_Base
{
  public function up()
  {
    $this->dropTable('blacklist');

    $this->removeColumn('activity_data', 'is_pc');
    $this->removeColumn('activity_data', 'is_mobile');
  }

  public function postUp()
  {
    $conn = Doctrine_Manager::connection();
    $conn->exec('DELETE FROM gadget WHERE type LIKE ?', array('mobile%'));
    $conn->exec('DELETE FROM navigation WHERE type LIKE ?', array('mobile_%'));
    $conn->exec('DELETE FROM notification_mail WHERE name LIKE ?', array('mobile_%'));
    $conn->exec('DELETE FROM sns_term WHERE application = ?', array('mobile_frontend'));

    $conn->exec('DELETE FROM member_config WHERE name IN (?, ?, ?)',
      array(
        'mail_address_hash',
        'mobile_uid',
        'mobile_cookie_uid',
      ));

    $conn->exec('DELETE FROM sns_config WHERE name LIKE ?', array('mobile_%'));
    $conn->exec('DELETE FROM sns_config WHERE name IN (?, ?, ?, ?, ?, ?)',
      array(
        'enable_mobile',
        'enable_pc',
        'external_mobile_login_url',
        'retrieve_uid',
        'font_size',
        'is_check_mobile_ip',
      ));
    $conn->exec('UPDATE sns_config SET value = 1 WHERE name = ? AND value > 1', array('enable_registration'));
  }
}
