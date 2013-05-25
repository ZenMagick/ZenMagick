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
 * A twig extension for str_replace()
 */
class StrReplaceExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('str_replace', function ($string, $search, $replace, $count = null) {
                return str_replace($search, $replace, $string, $count);
            }),
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'str_replace';
    }

}
