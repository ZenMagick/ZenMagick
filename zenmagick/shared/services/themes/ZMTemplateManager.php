<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
?>
<?php


/**
 * Template stuff.
 *
 * <p>This is a collection of things to make templating easier.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.themes
 */
class ZMTemplateManager extends ZMObject {
    const PAGE_TOP = 'top';
    const PAGE_BOTTOM = 'bottom';
    const PAGE_NOW = 'now';

    private $leftColEnabled_;
    private $rightColEnabled_;
    private $leftColBoxes_;
    private $rightColBoxes_;
    private $tableMeta_;
    private $cssFiles_;
    private $jsFiles_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->leftColEnabled_ = true;
        $this->rightColEnabled_ = true;
        $this->leftColBoxes_ = null;
        $this->rightColBoxes_ = null;
        $this->tableMeta_ = array();
        $this->cssFiles_ = array();
        $this->jsFiles_ = array();
        ZMEvents::instance()->attach($this);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('TemplateManager');
    }


    /**
     * Enable/disable the left column.
     *
     * @param boolean bool If <code>true</code> the left column will be displayed.
     */
    public function setLeftColEnabled($bool) { $this->leftColEnabled_ = $bool; }

    /**
     * Enable/disable the right column.
     *
     * @param boolean bool If <code>true</code> the right column will be displayed.
     */
    public function setRightColEnabled($bool) { $this->rightColEnabled_ = $bool; }

    /**
     * Set the boxes for the left column.
     *
     * @param array boxes List of box names to be displayed in the left column.
     */
    public function setLeftColBoxes($boxes) { if (is_array($boxes)) $this->leftColBoxes_ = $boxes; }

    /**
     * Set the boxes for the right column.
     *
     * @param array boxes List of box names to be displayed in the right column.
     */
    public function setRightColBoxes($boxes) { if (is_array($boxes)) $this->rightColBoxes_ = $boxes; }

    /**
     * Checks if the left column is active.
     *
     * @return boolean <code>true</code> if the column is active, <code>false</code> if not.
     */
    public function isLeftColEnabled() { return $this->leftColEnabled_; }

    /**
     * Checks if the right column is active.
     *
     * @return boolean <code>true</code> if the column is active, <code>false</code> if not.
     */
    public function isRightColEnabled() { return $this->rightColEnabled_; }

    /**
     * Get the box names for the left column.
     *
     * @return array Name of all boxes to be displayed.
     */
    public function getLeftColBoxNames() {
        if (null != $this->leftColBoxes_) {
            return $this->leftColBoxes_;
        }

        $theme = Runtime::getTheme();

        $sql = "SELECT DISTINCT layout_box_name from " . TABLE_LAYOUT_BOXES . "
                WHERE layout_box_location = 0
                  AND layout_box_status = '1'
                  AND layout_template = :themeId
                ORDER BY layout_box_sort_order";
        $boxes = array();
        foreach (Runtime::getDatabase()->query($sql, array('themeId' => Runtime::getThemeId()), TABLE_LAYOUT_BOXES) as $boxInfo) {
            // boxes use .php
            $box = str_replace('.php', ZMSettings::get('zenmagick.mvc.templates.ext'), $boxInfo['name']);
            $boxes[] = $box;
        }

        return $boxes;
    }

    /**
     * Get the box names for the right column.
     *
     * @return array Name of all boxes to be displayed.
     */
    public function getRightColBoxNames() {
        if (null != $this->rightColBoxes_) {
            return $this->rightColBoxes_;
        }

        $theme = Runtime::getTheme();

        $sql = "SELECT DISTINCT layout_box_name from " . TABLE_LAYOUT_BOXES . "
                WHERE layout_box_location = 1
                  AND layout_box_status = '1'
                  AND layout_template = :themeId
                ORDER BY layout_box_sort_order";
        $boxes = array();
        foreach (Runtime::getDatabase()->query($sql, array('themeId' => Runtime::getThemeId()), TABLE_LAYOUT_BOXES) as $boxInfo) {
            // boxes use .php
            $box = str_replace('.php', ZMSettings::get('zenmagick.mvc.templates.ext'), $boxInfo['name']);
            $boxes[] = $box;
        }

        return $boxes;
    }

    /**
     * Get the field length of a particular column.
     *
     * @param string table The database table name.
     * @param string field The field/column name.
     * @return int The field length.
     */
    public function getFieldLength($table, $field) {
        if (!isset($this->tableMeta_[$table])) {
            $this->tableMeta_[$table] = ZMRuntime::getDatabase()->getMetaData($table);
        }

        return $this->tableMeta_[$table][$field]['maxLen'];
    }

    /**
     * Find the product template for a given product.
     *
     * @param int productId The product id.
     * @return string The template name to be used to display product details.
     */
    public function getProductTemplate($productId) {
        // default
        $template = 'product';

        $sql = "SELECT products_type 
                FROM " . TABLE_PRODUCTS . "
                WHERE products_id = :productId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('productId' => $productId), TABLE_PRODUCTS);
        if (null !== $result) {
            $typeId = $result['type'];
            $sql = "SELECT type_handler 
                    FROM " . TABLE_PRODUCT_TYPES . "
                    WHERE type_id = :id";
            $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $typeId), TABLE_PRODUCT_TYPES);
            if (null !== $result) {
                $template = $result['handler'];
            }
        }

        return $template . '_info';
    }

    /**
     * Event handler to inject JavaScript and CSS resources.
     */
    public function onZMFinaliseContents($args) {
        if (0 == count($this->cssFiles_) && 0 == count($this->jsFiles_)) {
            return null;
        }

        $request = $args['request']; 
        $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';

        $css = '';
        foreach ($this->cssFiles_ as $info) {
            // merge in defaults
            $attr = '';
            $info['attr'] = array_merge(array('rel' => 'stylesheet', 'type' => 'text/css', 'prefix' => '', 'suffix' => ''), $info['attr']);
            foreach ($info['attr'] as $name => $value) {
                if (null !== $value && !in_array($name, array('prefix', 'suffix'))) {
                    $attr .= ' '.$name.'="'.$value.'"';
                }
            }
            $css .= $info['attr']['prefix'];
            $css .= '<link '.$attr.' href="'.$this->resolveThemeResource($request, $info['filename']).'"'.$slash.'>';
            $css .= $info['attr']['suffix']."\n";
        }

        // first build separate lists to allow group processing
        $jsTopList = array();
        $jsBottomList = array();
        foreach ($this->jsFiles_ as $filename => $info) {
            if (!$info['done']) {
                if (self::PAGE_TOP == $info['position']) {
                    $jsTopList[] = $info;
                } else if (self::PAGE_BOTTOM == $info['position']) {
                    $jsBottomList[] = $info;
                }
                $this->jsFiles_[$filename]['done'] = true;
            }
        }

        $jsTop = '';
        $jsBottom = '';
        if (null == ($jsTop = $this->handleResources($jsTopList, 'js', self::PAGE_TOP))) {
            $jsTop = '';
            foreach ($jsTopList as $info) {
                $jsTop .= '<script type="text/javascript" src="'.$this->resolveThemeResource($request, $info['filename']).'"></script>'."\n";
            }
        }
        if (null == ($jsBottom = $this->handleResources($jsBottomList, 'js', self::PAGE_BOTTOM))) {
            $jsBottom = '';
            foreach ($jsBottomList as $info) {
                $jsBottom .= '<script type="text/javascript" src="'.$this->resolveThemeResource($request, $info['filename']).'"></script>'."\n";
            }
        }

        $contents = $args['contents'];
        $contents = preg_replace('/<\/head>/', $css.$jsTop . '</head>', $contents, 1);
        $contents = preg_replace('/<\/body>/', $jsBottom . '</body>', $contents, 1);
        $args['css'] = $css;
        $args['jsTop'] = $jsTop;
        $args['jsBottom'] = $jsBottom;
        $args['contents'] = $contents;
        return $args;
    }

    /**
     * Resolve theme resource.
     *
     * @param request The current request.
     * @param string resource The url.
     * @return string The resolved final URL.
     */
    public function resolveThemeResource($request, $resource) {
        if ('/' == $resource[0]) {
            // absolute; for example plugin URL
            return $resource;
        }
        return Runtime::getTheme()->themeURL($resource);
    }

    /**
     * Empty callback method for group processing.
     *
     * @param array List of file infos.
     * @param string type The type; either <code>css</code> or <code>js</code>.
     * @param string location The location; either <code>ZMTemplateManager::PAGE_TOP</code> or <code>ZMTemplateManager::PAGE_BOTTOM</code>.
     * @return string Fully processed script code or null.
     */
    public function handleResources($files, $type, $location) {
        return null;
    }

    /**
     * Add the given CSS file to the final contents.
     *
     * @param string filename A relative CSS filename.
     * @param array attr Optional attribute map; special keys 'prefix' and 'suffix' may be used to wrap.
     */
    public function cssFile($filename, $attr=array()) {
        $this->cssFiles_[$filename] = array('filename' => $filename, 'attr' => $attr);
    }

    /**
     * Add the given JavaScript file to the final contents.
     *
     * @param string filename A relative JavaScript filename.
     * @param string position Optional position; either <code>PAGE_TOP</code> (default), or <code>PAGE_BOTTOM</code>.
     */
    public function jsFile($filename, $position=self::PAGE_TOP) {
        if (array_key_exists($filename, $this->jsFiles_)) {
            // check if we need to do anything else or update the position
            if ($this->jsFiles_[$filename]['done']) {
                ZMLogging::instance()->log('skipping '.$filename.' as already done', ZMLogging::TRACE);
                return;
            }
            if (self::PAGE_BOTTOM == $this->jsFiles_[$filename]['position']) {
                if (self::PAGE_TOP == $position) {
                    ZMLogging::instance()->log('upgrading '.$filename.' to PAGE_TOP', ZMLogging::TRACE);
                    return;
                }
            }
            // either it's now or same as already registered
        }
        $this->jsFiles_[$filename] = array('filename' => $filename, 'position' => $position, 'done' => false);
        if (self::PAGE_NOW == $position) {
            $this->jsFiles_[$filename]['done'] = true;
            echo '<script type="text/javascript" src="',$this->resolveThemeResource($request, $filename),'"></script>',"\n";
        }
    }

}
