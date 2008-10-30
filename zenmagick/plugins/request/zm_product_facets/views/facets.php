<?php
    $types = array('categories' => 'Categories', 'manufacturers' => 'Manufacturers', 'prices' => 'Price Range');

    function _show_cat_path($category) {
        $path = '';
        if (null != ($parent = $category->getParent())) {
            $path .= _show_cat_path($parent) . ' :: ';
        }
        $path .= $category->getName();
        return $path;
    }

    function _req_parms($type, $id) {
    global $types;

        $params = array();
        foreach ($types as $type => $name) {
            $params[$type] = ZMRequest::getParameter($type);
        }
        $add = true;
        foreach ($params[$type] as $rid) {
            if ($rid == $id) {
                $add = false;
                break;
            }
        }
        if ($add) {
            $params[$type][] = $id;
        }

        $s = '';
        foreach ($params as $type => $ids) {
            foreach ($ids as $pid) {
                $s .= '&'.$type.'[]='.$pid;
            }
        }
        return $s;
    }

    $query = array();
    foreach ($types as $type => $name) {
        if (null !== ($value = ZMRequest::getParameter($type))) {
            $query[$type] = $value;
        }
    }

    //$category = ZMCategories::instance()->getCategoryForId(3);
    //$current = ZMFacets::instance()->filterWithTypes(array('manufacturers' => array(3)));
    //$current = ZMFacets::instance()->filterWithTypes(array('manufacturers' => array(4), 'categories' => $category->getChildIds()));
    if (0 < count($query)) {
        $current = ZMFacets::instance()->filterWithTypes($query);
    } else {
        $current = ZMFacets::instance()->getFacets();
    }

?>

<?php foreach ($types as $type => $name) { $facet = $current[$type]; ?>
  <div>
    <h5><?php echo $name ?></h5>
    <?php foreach ($facet as $id => $info) { ?>
      <a href="<?php $net->url(null, _req_parms($type, $info['id']), false) ?>"><?php echo $info['name'] ?> (<?php echo count($info['entries']) ?>)</a><br>
    <?php } ?>
  </div>
<?php } ?>

<?php 
if (false)
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
