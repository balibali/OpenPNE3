<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

class openpneSendDailyNewsTask extends opBaseSendMailTask
{
  protected function configure()
  {
    parent::configure();
    $this->namespace        = 'openpne';
    $this->name             = 'send-daily-news';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [openpne:send-birthday-mail|INFO] task does things.
Call it with:

  [php symfony openpne:send-birthday-mail|INFO]
EOF;

    $this->addOptions(
      array(
        new sfCommandOption('app', null, sfCommandOption::PARAMETER_OPTIONAL, '@deprecated pc_frontend only', null),
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    if (isset($options['app']) && $options['app'] !== 'pc_frontend')
    {
      throw new Exception('invalid option');
    }

    $this->sendDailyNews('pc_frontend');
  }

  private function sendDailyNews($app)
  {
    $dailyNewsName = 'dailyNews';
    $context = sfContext::createInstance($this->createConfiguration($app, 'prod'), $app);

    $gadgets = Doctrine::getTable('Gadget')->retrieveGadgetsByTypesName($dailyNewsName);
    $gadgets = $gadgets[$dailyNewsName.'Contents'];

    $targetMembers = Doctrine::getTable('Member')->findAll();
    foreach ($targetMembers as $member)
    {
      $address = $member->getEmailAddress();

      $dailyNewsConfig = $member->getConfig('daily_news');
      if (null !== $dailyNewsConfig && 0 === (int)$dailyNewsConfig)
      {
        continue;
      }

      if (1 === (int)$dailyNewsConfig && !$this->isDailyNewsDay())
      {
        continue;
      }

      $filteredGadgets = array();
      if ($gadgets)
      {
        foreach ($gadgets as $gadget)
        {
          if ($gadget->isEnabled($member))
          {
            $filteredGadgets[] = array(
              'component' => array('module' => $gadget->getComponentModule(), 'action' => $gadget->getComponentAction()),
              'gadget' => $gadget,
              'member' => $member,
            );
          }
        }
      }

      $params = array(
        'member'  => $member,
        'gadgets' => $filteredGadgets,
        'subject' => $context->getI18N()->__('デイリーニュース'),
        'today'   => time(),
      );

      opMailSend::sendTemplateMail('dailyNews', $address, opConfig::get('admin_mail_address'), $params, $context);
    }
  }

  protected function isDailyNewsDay()
  {
    $day = date('w') - 1;
    if (0 > $day)
    {
      $day = 7;
    }

    return in_array($day, opConfig::get('daily_news_day'));
  }
}
