<?php

    function _show_cat_path($category) {
        $path = '';
        if (null != ($parent = $category->getParent())) {
            $path .= _show_cat_path($parent) . ' :: ';
        }
        $path .= $category->getName();
        return $path;
    }

    $category = ZMCategories::instance()->getCategoryForId(3);
    //$current = ZMFacets::instance()->filterWithTypes(array('manufacturers' => array(3)));
    //$current = ZMFacets::instance()->filterWithTypes(array('manufacturers' => array(4), 'categories' => $category->getChildIds()));
    $current = ZMFacets::instance()->getFacets();

    foreach ($current as $type => $facet) {
        echo '<h3>'.$type.'</h3>';
        foreach ($facet as $id => $info) {
            if (0 < count($info['entries'])) {
                $name = $info['id'].'/'.$info['name'];
                if ('categories' == $type) {
                    $name = _show_cat_path(ZMCategories::instance()->getCategoryForId($info['id']));
                }
                echo "<BR><u>".$name." (".count($info['entries']).")</u><BR>";
                /*
                foreach ($info['entries'] as $id => $name) {
                    echo "&nbsp;&nbsp;".$name."<BR>";
                }
                 */
            }
        }
    }

?>
