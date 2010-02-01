<?php
    $FACET_TYPES = array('categories' => 'Categories', 'manufacturers' => 'Manufacturers', 'prices' => 'Price Range');

    // ***
    function _show_cat_path($category) {
        $path = '';
        if (null != ($parent = $category->getParent())) {
            $path .= _show_cat_path($parent) . ' :: ';
        }
        $path .= $category->getName();
        return $path;
    }

    // ***
    function _req_parms($type, $ids, $FACET_TYPES) {
        $params = array();
        foreach ($FACET_TYPES as $ftype => $name) {
            $params[$ftype] = ZMRequest::instance()->getParameter($ftype, array());
        }
        $merge = array();
        foreach ($params[$type] as $rid) {
            $merge[$rid] = $rid;
        }
        foreach ($ids as $id) {
            $merge[$id] = $id;
        }
        $params[$type] = $merge;

        $s = '';
        foreach ($params as $ptype => $ids) {
            foreach ($ids as $pid) {
                $s .= '&'.$ptype.'[]='.$pid;
            }
        }
        return $s;
    }

    // ***
    function zm_category_tree_ids($categoryId) {
        $ids = array($categoryId);
        foreach (ZMCategories::instance()->getCategoryForId($categoryId)->getChildren() as $child) {
            $childIds = zm_category_tree_ids($child->getId());
            $ids = array_merge($ids, $childIds);
        }
        return $ids;
    }


    $query = array();
    foreach ($FACET_TYPES as $ftype => $name) {
        if (null !== ($value = ZMRequest::instance()->getParameter($ftype))) {
            $query[$ftype] = $value;
        }
    }

    if (0 < count($query)) {
        $current = ZMFacets::instance()->filterWithTypes($query);
    } else {
        $current = ZMFacets::instance()->getFacets();
    }

    /*
     * With categories, we want to display only the selected level (or root categories if
     * none selected).
     * Also, it helps to have aggregated counts for a category sub-tree...
     */
    $categories = ZMRequest::instance()->getParameter('categories');
    if (null === $categories) {
        $categories = array();
        foreach (ZMCategories::instance()->getCategoryTree() as $cat) {
            $categories[] = $cat->getId();
        }
    }
    //var_dump($categories);
?>

<?php foreach ($FACET_TYPES as $type => $name) { $facet = $current[$type]; ?>
  <div>
    <h5><?php echo $name ?></h5>
    <?php foreach ($facet as $id => $info) { ?>
      <?php $noOfEntries = count($info['entries']); ?>
      <?php $ids = array($info['id']); ?>
      <?php if ('categories' == $type || 0 < $noOfEntries) { ?>
        <a href="<?php echo $net->url(null, _req_parms($type, $ids, $FACET_TYPES), false) ?>"><?php echo $info['name'] ?> (<?php echo $noOfEntries ?>)</a><br>
      <?php } ?>
    <?php } ?>
  </div>
<?php } ?>
