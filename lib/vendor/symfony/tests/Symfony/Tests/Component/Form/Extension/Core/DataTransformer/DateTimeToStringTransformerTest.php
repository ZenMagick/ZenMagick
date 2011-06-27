<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Form\Extension\Core\DataTransformer;

require_once __DIR__ . '/DateTimeTestCase.php';

use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

class DateTimeToStringTransformerTest extends DateTimeTestCase
{
    public function testTransform()
    {
        $transformer = new DateTimeToStringTransformer('UTC', 'UTC', 'Y-m-d H:i:s');

        $input = new \DateTime('2010-02-03 04:05:06 UTC');
        $output = clone $input;
        $output->setTimezone(new \DateTimeZone('UTC'));
        $output = $output->format('Y-m-d H:i:s');

        $this->assertEquals($output, $transformer->transform($input));
    }

    public function testTransform_empty()
    {
        $transformer = new DateTimeToStringTransformer();

        $this->assertSame('', $transformer->transform(null));
    }

    public function testTransform_differentTimezones()
    {
        $transformer = new DateTimeToStringTransformer('Asia/Hong_Kong', 'America/New_York', 'Y-m-d H:i:s');

        $input = new \DateTime('2010-02-03 04:05:06 America/New_York');
        $output = $input->format('Y-m-d H:i:s');
        $input->setTimezone(new \DateTimeZone('Asia/Hong_Kong'));

        $this->assertEquals($output, $transformer->transform($input));
    }

    public function testTransformExpectsDateTime()
    {
        $transformer = new DateTimeToStringTransformer();

        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');

        $transformer->transform('1234');
    }

    public function testReverseTransform()
    {
        $reverseTransformer = new DateTimeToStringTransformer('UTC', 'UTC', 'Y-m-d H:i:s');

        $output = new \DateTime('2010-02-03 04:05:06 UTC');
        $input = $output->format('Y-m-d H:i:s');

        $this->assertDateTimeEquals($output, $reverseTransformer->reverseTransform($input, null));
    }

    public function testReverseTransform_empty()
    {
        $reverseTransformer = new DateTimeToStringTransformer();

        $this->assertSame(null, $reverseTransformer->reverseTransform('', null));
    }

    public function testReverseTransform_differentTimezones()
    {
        $reverseTransformer = new DateTimeToStringTransformer('America/New_York', 'Asia/Hong_Kong', 'Y-m-d H:i:s');

        $output = new \DateTime('2010-02-03 04:05:06 Asia/Hong_Kong');
        $input = $output->format('Y-m-d H:i:s');
        $output->setTimeZone(new \DateTimeZone('America/New_York'));

        $this->assertDateTimeEquals($output, $reverseTransformer->reverseTransform($input, null));
    }

    public function testReverseTransformExpectsString()
    {
        $reverseTransformer = new DateTimeToStringTransformer();

        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException');

        $reverseTransformer->reverseTransform(1234, null);
    }

    public function testReverseTransformExpectsValidDateString()
    {
        $reverseTransformer = new DateTimeToStringTransformer();

        $this->setExpectedException('Symfony\Component\Form\Exception\TransformationFailedException');

        $reverseTransformer->reverseTransform('2010-2010-2010', null);
    }
}
