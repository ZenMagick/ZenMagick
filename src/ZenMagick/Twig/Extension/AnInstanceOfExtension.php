<?php
/**
* This file is part of ZenMagick.
*
* (c) 2013 Johnny Robeson
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*
*/

namespace ZenMagick\Twig\Extension;

/**
 * This is just for a few "instanceof" checks in the
 * current templates. Will likely disappear soon.
 */
class AnInstanceOfExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return array(
            'aninstanceof' => new \Twig_Test_Node('\\ZenMagick\\Twig\\Node\\Expression\\Test\Aninstanceof'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'aninstanceof';
    }

}
