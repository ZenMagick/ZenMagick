<div id="categories">
  <h2>Categories</h2>
  <?php $tree = ZMCategories::instance()->getCategoryTree(); ?>
  <?php echo $macro->categoryTree($tree, true, ZMSettings::get('isUseCategoryPage')) ?>
</div>
