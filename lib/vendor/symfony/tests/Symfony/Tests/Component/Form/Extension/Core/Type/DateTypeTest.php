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

use Symfony\Component\Form\DateField;
use Symfony\Component\Form\FormView;

class DateTypeTest extends LocalizedTestCase
{
    protected function setUp()
    {
        parent::setUp();

        \Locale::setDefault('de_AT');
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInvalidWidgetOption()
    {
        $form = $this->factory->create('date', null, array(
            'widget' => 'fake_widget',
        ));
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testInvalidInputOption()
    {
        $form = $this->factory->create('date', null, array(
            'input' => 'fake_input',
        ));
    }

    public function testSubmitFromSingleTextDateTime()
    {
        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'input' => 'datetime',
        ));

        $form->bind('2.6.2010');

        $this->assertDateTimeEquals(new \DateTime('2010-06-02 UTC'), $form->getData());
        $this->assertEquals('02.06.2010', $form->getClientData());
    }

    public function testSubmitFromSingleTextString()
    {
        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'input' => 'string',
        ));

        $form->bind('2.6.2010');

        $this->assertEquals('2010-06-02', $form->getData());
        $this->assertEquals('02.06.2010', $form->getClientData());
    }

    public function testSubmitFromSingleTextTimestamp()
    {
        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'input' => 'timestamp',
        ));

        $form->bind('2.6.2010');

        $dateTime = new \DateTime('2010-06-02 UTC');

        $this->assertEquals($dateTime->format('U'), $form->getData());
        $this->assertEquals('02.06.2010', $form->getClientData());
    }

    public function testSubmitFromSingleTextRaw()
    {
        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'input' => 'array',
        ));

        $form->bind('2.6.2010');

        $output = array(
            'day' => '2',
            'month' => '6',
            'year' => '2010',
        );

        $this->assertEquals($output, $form->getData());
        $this->assertEquals('02.06.2010', $form->getClientData());
    }

    public function testSubmitFromText()
    {
        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'text',
        ));

        $text = array(
            'day' => '2',
            'month' => '6',
            'year' => '2010',
        );

        $form->bind($text);

        $dateTime = new \DateTime('2010-06-02 UTC');

        $this->assertDateTimeEquals($dateTime, $form->getData());
        $this->assertEquals($text, $form->getClientData());
    }

    public function testSubmitFromChoice()
    {
        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'choice',
        ));

        $text = array(
            'day' => '2',
            'month' => '6',
            'year' => '2010',
        );

        $form->bind($text);

        $dateTime = new \DateTime('2010-06-02 UTC');

        $this->assertDateTimeEquals($dateTime, $form->getData());
        $this->assertEquals($text, $form->getClientData());
    }

    public function testSubmitFromChoiceEmpty()
    {
        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'choice',
            'required' => false,
        ));

        $text = array(
            'day' => '',
            'month' => '',
            'year' => '',
        );

        $form->bind($text);

        $this->assertNull($form->getData());
        $this->assertEquals($text, $form->getClientData());
    }

    public function testSetData_differentTimezones()
    {
        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'America/New_York',
            'user_timezone' => 'Pacific/Tahiti',
            // don't do this test with DateTime, because it leads to wrong results!
            'input' => 'string',
            'widget' => 'single_text',
        ));

        $form->setData('2010-06-02');

        $this->assertEquals('01.06.2010', $form->getClientData());
    }

    public function testIsYearWithinRangeReturnsTrueIfWithin()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'years' => array(2010, 2011),
        ));

        $form->bind('2.6.2010');

        $this->assertTrue($form->isYearWithinRange());
    }

    public function testIsYearWithinRangeReturnsTrueIfEmpty()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'years' => array(2010, 2011),
        ));

        $form->bind('');

        $this->assertTrue($form->isYearWithinRange());
    }

    public function testIsYearWithinRangeReturnsTrueIfEmptyChoice()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'choice',
            'years' => array(2010, 2011),
        ));

        $form->bind(array(
            'day' => '1',
            'month' => '2',
            'year' => '',
        ));

        $this->assertTrue($form->isYearWithinRange());
    }

    public function testIsYearWithinRangeReturnsFalseIfNotContained()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'years' => array(2010, 2012),
        ));

        $form->bind('2.6.2011');

        $this->assertFalse($form->isYearWithinRange());
    }

    public function testIsMonthWithinRangeReturnsTrueIfWithin()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'months' => array(6, 7),
        ));

        $form->bind('2.6.2010');

        $this->assertTrue($form->isMonthWithinRange());
    }

    public function testIsMonthWithinRangeReturnsTrueIfEmpty()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'months' => array(6, 7),
        ));

        $form->bind('');

        $this->assertTrue($form->isMonthWithinRange());
    }

    public function testIsMonthWithinRangeReturnsTrueIfEmptyChoice()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'choice',
            'months' => array(6, 7),
        ));

        $form->bind(array(
            'day' => '1',
            'month' => '',
            'year' => '2011',
        ));

        $this->assertTrue($form->isMonthWithinRange());
    }

    public function testIsMonthWithinRangeReturnsFalseIfNotContained()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'months' => array(6, 8),
        ));

        $form->bind('2.7.2010');

        $this->assertFalse($form->isMonthWithinRange());
    }

    public function testIsDayWithinRangeReturnsTrueIfWithin()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'days' => array(6, 7),
        ));

        $form->bind('6.6.2010');

        $this->assertTrue($form->isDayWithinRange());
    }

    public function testIsDayWithinRangeReturnsTrueIfEmpty()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'days' => array(6, 7),
        ));

        $form->bind('');

        $this->assertTrue($form->isDayWithinRange());
    }

    public function testIsDayWithinRangeReturnsTrueIfEmptyChoice()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'choice',
            'days' => array(6, 7),
        ));

        $form->bind(array(
            'day' => '',
            'month' => '1',
            'year' => '2011',
        ));

        $this->assertTrue($form->isDayWithinRange());
    }

    public function testIsDayWithinRangeReturnsFalseIfNotContained()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
            'days' => array(6, 8),
        ));

        $form->bind('7.6.2010');

        $this->assertFalse($form->isDayWithinRange());
    }

    public function testIsPartiallyFilledReturnsFalseIfSingleText()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'single_text',
        ));

        $form->bind('7.6.2010');

        $this->assertFalse($form->isPartiallyFilled());
    }

    public function testIsPartiallyFilledReturnsFalseIfChoiceAndCompletelyEmpty()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'choice',
        ));

        $form->bind(array(
            'day' => '',
            'month' => '',
            'year' => '',
        ));

        $this->assertFalse($form->isPartiallyFilled());
    }

    public function testIsPartiallyFilledReturnsFalseIfChoiceAndCompletelyFilled()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'choice',
        ));

        $form->bind(array(
            'day' => '2',
            'month' => '6',
            'year' => '2010',
        ));

        $this->assertFalse($form->isPartiallyFilled());
    }

    public function testIsPartiallyFilledReturnsTrueIfChoiceAndDayEmpty()
    {
        $this->markTestIncomplete('Needs to be reimplemented using validators');

        $form = $this->factory->create('date', null, array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'widget' => 'choice',
        ));

        $form->bind(array(
            'day' => '',
            'month' => '6',
            'year' => '2010',
        ));

        $this->assertTrue($form->isPartiallyFilled());
    }

    public function testPassDatePatternToView()
    {
        $form = $this->factory->create('date');
        $view = $form->createView();

        $this->assertSame('{{ day }}.{{ month }}.{{ year }}', $view->get('date_pattern'));
    }

    public function testDontPassDatePatternIfText()
    {
        $form = $this->factory->create('date', null, array(
            'widget' => 'single_text',
        ));
        $view = $form->createView();

        $this->assertNull($view->get('date_pattern'));
    }

    public function testPassWidgetToView()
    {
        $form = $this->factory->create('date', null, array(
            'widget' => 'single_text',
        ));
        $view = $form->createView();

        $this->assertSame('single_text', $view->get('widget'));
    }
}
