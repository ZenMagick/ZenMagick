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
namespace ZenMagick\StoreBundle\Themes;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ZenMagick\Base\Beans;

/**
 * Template stuff.
 *
 * <p>This is a collection of things to make templating easier.</p>
 *
 * @author DerManoMann
 */
class TemplateManager
{
    const PAGE_TOP = 'top';
    const PAGE_BOTTOM = 'bottom';
    const PAGE_NOW = 'now';

    private $container;
    private $em;
    private $leftColEnabled;
    private $rightColEnabled;
    private $leftColBoxes;
    private $rightColBoxes;
    private $tableMeta;
    private $themeService;

    /**
     * Create new instance.
     */
    public function __construct(EntityManager $em, ThemeService $themeService, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $em;
        $this->themeService = $themeService;
        $this->leftColEnabled = true;
        $this->rightColEnabled = true;
        $this->leftColBoxes = null;
        $this->rightColBoxes = null;
        $this->tableMeta = array();
    }

    /**
     * Enable/disable the left column.
     *
     * @param boolean bool If <code>true</code> the left column will be displayed.
     */
    public function setLeftColEnabled($bool)
    {
        $this->leftColEnabled = $bool;

        return $this;
    }

    /**
     * Enable/disable the right column.
     *
     * @param boolean bool If <code>true</code> the right column will be displayed.
     */
    public function setRightColEnabled($bool)
    {
        $this->rightColEnabled = $bool;

        return $this;
    }

    /**
     * Set the boxes for the left column.
     *
     * @param array boxes List of box names to be displayed in the left column.
     */
    public function setLeftColBoxes($boxes)
    {
        if (is_array($boxes)) $this->leftColBoxes = $boxes;
        return $this;
    }

    /**
     * Set the boxes for the right column.
     *
     * @param array boxes List of box names to be displayed in the right column.
     */
    public function setRightColBoxes($boxes)
    {
        if (is_array($boxes)) $this->rightColBoxes = $boxes;
        return $this;
    }

    /**
     * Checks if the left column is active.
     *
     * @return boolean <code>true</code> if the column is active, <code>false</code> if not.
     */
    public function isLeftColEnabled()
    {
        return $this->leftColEnabled;
    }

    /**
     * Checks if the right column is active.
     *
     * @return boolean <code>true</code> if the column is active, <code>false</code> if not.
     */
    public function isRightColEnabled()
    {
        return $this->rightColEnabled;
    }

    /**
     * Get enabled box names by position.
     *
     * @param int $position 0 or 1 for left or right respectively
     * @todo move to repository
     */
    protected function getActiveBoxNames($position)
    {
        $repository = $this->em->getRepository('StoreBundle:LayoutBox');
        $themeId = $this->themeService->getActiveTheme()->getId();

        $args = array('location' => $position, 'status' => 1, 'themeId' =>  $themeId);
        $boxes = $repository->findBy($args, array('sortOrder' => 'ASC'));

        $names = array();
        foreach ($boxes as $box) {
            $names[] = $box->getActualName();
        }

        return $names;
    }

    /**
     * Get the box names for the left column.
     *
     * @return array Name of all boxes to be displayed.
     */
    public function getLeftColBoxNames()
    {
        if (null != $this->leftColBoxes) {
            return $this->leftColBoxes;
        }

        return $this->getActiveBoxNames(0);
    }

    /**
     * Get the box names for the right column.
     *
     * @return array Name of all boxes to be displayed.
     */
    public function getRightColBoxNames()
    {
        if (null != $this->rightColBoxes) {
            return $this->rightColBoxes;
        }

        return $this->getActiveBoxNames(1);
    }

    /**
     * Get the field length of a particular column.
     *
     * @param string table The database table name.
     * @param string field The field/column name.
     * @return int The field length.
     */
    public function getFieldLength($table, $field)
    {
        if (!isset($this->tableMeta[$table])) {
            $sm = $this->em->getConnection()->getSchemaManager();
            $tableDetails = $sm->listTableDetails($table);
            foreach ($tableDetails->getColumns() as $column) {
                $length = $column->getLength() ?: 3;
                $this->tableMeta[$table][$column->getName()] = $length;
            }
        }

        return $this->tableMeta[$table][$field];
    }

    /**
     * Find the product template for a given product.
     *
     * @param int productId The product id.
     * @return string The template name to be used to display product details.
     */
    public function getProductTemplate($productId)
    {
        // default
        $template = 'product';

        $sql = "SELECT products_type
                FROM %table.products%
                WHERE products_id = :productId";
        $result = \ZMRuntime::getDatabase()->querySingle($sql, array('productId' => $productId), 'products');
        if (null !== $result) {
            $typeId = $result['type'];
            $sql = "SELECT type_handler
                    FROM %table.product_types%
                    WHERE type_id = :id";
            $result = \ZMRuntime::getDatabase()->querySingle($sql, array('id' => $typeId), 'product_types');
            if (null !== $result) {
                $template = $result['handler'];
            }
        }

        return $template . '_info';
    }
    /**
     * Fetch/generate the contents for a given block group id.
     *
     * @param string group The group id.
     * @param array args Optional parameter; default is an empty array.
     * @return string The contents.
     */
    public function fetchBlockGroup($groupId, $args=array())
    {
        $contents = '';
        $request = $this->container->get('request');
        foreach ($this->container->get('blockManager')->getBlocksForId($request, $groupId, $args) as $block) {
//            Runtime::getLogging()->debug(sprintf('render block, template: %s', $block->getTemplate()));
            $contents .= $block->render($request, $this->container->get('defaultView'));
        }

        return $contents;
    }

    /**
     * Render a widget.
     *
     * @param mixed widget Either a <code>Widget</code> instance or a widget bean definition.
     * @param string name Optional name; default is <code>null</code> for none.
     * @param string value Optional value; default is <code>null</code> for none.
     * @param mixed args Optional parameter; a map of widget properties;  default is <code>null</code>.
     * @return string The widget contents.
     */
    public function widget($widget, $name=null, $value=null, $args=null)
    {
        $wObj = $widget;
        if (is_string($widget)) {
            $wObj = Beans::getBean($widget);
        }
        if (!($wObj instanceof Widget)) {
            Runtime::getLogging()->debug('invalid widget: '.$widget);

            return '';
        }
        if (null !== $name) {
            $wObj->setName($name);
            if (null === $args || !array_key_exists('id', $args)) {
                // no id set, so default to name
                $wObj->setId($name);
            }
        }
        if (null !== $value) {
            $wObj->setValue($value);
        }
        if (null !== $args) {
            Beans::setAll($wObj, $args);
        }

        return $wObj->render($this->container->get('request'), $this->container->get('defaultView'));
    }

}
