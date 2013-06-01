<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace ZenMagick\plugins\cron\jobs;

/**
 * A cron job to send birthday emails to registered users.
 *
 * <p>This job can be configured via the following settings:</p>
 * <dl>
 *  <dt>plugins.cron.jobs.birthday.offset</dt>
 *  <dd>
 *    Date offset in days. Blank for actual birthday, or a signed int; example: <em>-2</em>, <em>+1</em>. Please note that the
 *    sign (+/-) is mandatory.
 *    The default is an empty string.
 *  </dd>
 *  <dt>plugins.cron.jobs.birthday.template</dt>
 *  <dd>
 *    Name of the email template to use for the email.
 *    The default is <em>birthday</em>.
 *  </dd>
 * </dl>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class BirthdayEmailCronJob implements CronJobInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $container = $this->container;
        $settingsService = $container->get('settingsService');

        $offset = $settingsService->get('plugins.cron.jobs.birthday.offset', '');
        $template = $settingsService->get('plugins.cron.jobs.birthday.template', 'birthday');

        $sql = "SELECT * FROM %table.customers%
                WHERE MONTH(customers_dob) = MONTH(curdate())
                  AND DAYOFMONTH(customers_dob) = DAYOFMONTH(curdate()) " . $offset;
        $results = \ZMRuntime::getDatabase()->fetchAll($sql, array(), 'customers', 'ZenMagick\StoreBundle\Entity\Account');
        foreach ($results as $account) {
            $context = array('account' => $account);

            $message = $container->get('messageBuilder')->createMessage($template, true, $request, $context);
            $subject = $container->get('translator')->trans("It's your birthday %name%", array('%name%' => $account->getFirstName()));
            $message->setSubject($subject)->setTo($account->getEmail(), $account->getFullName())->setFrom($settingsService->get('storeEmail'));
            $container->get('mailer')->send($message);
        }

        return true;
    }

}
