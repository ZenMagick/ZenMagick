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

use ZenMagick\Base\Beans;

/**
 * A twig extension to create Beans
 *
 * This is just an experiment in creating twig extensions
 *
 * All uses should end up as symfony form widgets
 */
class BeanExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'bean' => new \Twig_Function_Method($this, 'getBean'),
            'bean_obj2map' => new \Twig_Function_Method($this, 'obj2map'),
        );
    }

    /**
     * Get a Bean object from a bean definition
     *
     * @see Beans::getBean
     * @param string $def A Bean definition
     * @return mixed
     */
    public function getBean($def)
    {
        return Beans::getBean($def);
    }

    /**
     * Turn an object into a map (array)
     *
     * @see Beans::obj2map
     * @param  object obj
     * @param  array  properties
     * @param  bool   useGeneric
     * @return array
     */
    public function obj2Map($obj, $properties = null, $addGeneric = true)
    {
        return Beans::obj2map($obj, $properties, $addGeneric);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'bean';
    }

}
