<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Form\Extension\Core\Type;

require_once __DIR__ . '/LocalizedTestCase.php';

use Symfony\Component\Form\DateTimeField;
use Symfony\Component\Form\DateField;
use Symfony\Component\Form\TimeField;

class DateTimeTypeTest extends LocalizedTestCase
{
    public function testSubmit_dateTime()
    {
        $form = $this->factory->create('datetime', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'date_widget' => 'choice',
            'time_widget' => 'choice',
            'input' => 'datetime',
        ));

        $form->bind(array(
            'date' => array(
                'day' => '2',
                'month' => '6',
                'year' => '2010',
            ),
            'time' => array(
                'hour' => '3',
                'minute' => '4',
            ),
        ));

        $dateTime = new \DateTime('2010-06-02 03:04:00 UTC');

        $this->assertDateTimeEquals($dateTime, $form->getData());
    }

    public function testSubmit_string()
    {
        $form = $this->factory->create('datetime', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'input' => 'string',
            'date_widget' => 'choice',
            'time_widget' => 'choice',
        ));

        $form->bind(array(
            'date' => array(
                'day' => '2',
                'month' => '6',
                'year' => '2010',
            ),
            'time' => array(
                'hour' => '3',
                'minute' => '4',
            ),
        ));

        $this->assertEquals('2010-06-02 03:04:00', $form->getData());
    }

    public function testSubmit_timestamp()
    {
        $form = $this->factory->create('datetime', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'input' => 'timestamp',
            'date_widget' => 'choice',
            'time_widget' => 'choice',
        ));

        $form->bind(array(
            'date' => array(
                'day' => '2',
                'month' => '6',
                'year' => '2010',
            ),
            'time' => array(
                'hour' => '3',
                'minute' => '4',
            ),
        ));

        $dateTime = new \DateTime('2010-06-02 03:04:00 UTC');

        $this->assertEquals($dateTime->format('U'), $form->getData());
    }

    public function testSubmit_withSeconds()
    {
        $form = $this->factory->create('datetime', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'date_widget' => 'choice',
            'time_widget' => 'choice',
            'input' => 'datetime',
            'with_seconds' => true,
        ));

        $form->setData(new \DateTime('2010-06-02 03:04:05 UTC'));

        $input = array(
            'date' => array(
                'day' => '2',
                'month' => '6',
                'year' => '2010',
            ),
            'time' => array(
                'hour' => '3',
                'minute' => '4',
                'second' => '5',
            ),
        );

        $form->bind($input);

        $this->assertDateTimeEquals(new \DateTime('2010-06-02 03:04:05 UTC'), $form->getData());
    }

    public function testSubmit_differentTimezones()
    {
        $form = $this->factory->create('datetime', null, array(
            'data_timezone' => 'America/New_York',
            'user_timezone' => 'Pacific/Tahiti',
            'date_widget' => 'choice',
            'time_widget' => 'choice',
            // don't do this test with DateTime, because it leads to wrong results!
            'input' => 'string',
            'with_seconds' => true,
        ));

        $dateTime = new \DateTime('2010-06-02 03:04:05 Pacific/Tahiti');

        $form->bind(array(
            'date' => array(
                'day' => (int)$dateTime->format('d'),
                'month' => (int)$dateTime->format('m'),
                'year' => (int)$dateTime->format('Y'),
            ),
            'time' => array(
                'hour' => (int)$dateTime->format('H'),
                'minute' => (int)$dateTime->format('i'),
                'second' => (int)$dateTime->format('s'),
            ),
        ));

        $dateTime->setTimezone(new \DateTimeZone('America/New_York'));

        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), $form->getData());
    }

    public function testSubmit_stringSingleText()
    {
        $form = $this->factory->create('datetime', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'input' => 'string',
            'widget' => 'single_text',
        ));

        $form->bind('2010-06-02 03:04:05');

        $this->assertEquals('2010-06-02 03:04:00', $form->getData());
        $this->assertEquals('2010-06-02 03:04:00', $form->getClientData());
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testDifferentWidgets()
    {
        $form = $this->factory->create('datetime', null, array(
            'date_widget' => 'single_text',
            'time_widget' => 'choice',
        ));
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testDefinedOnlyOneWidget()
    {
        $form = $this->factory->create('datetime', null, array(
            'date_widget' => 'single_text',
        ));
    }
}
