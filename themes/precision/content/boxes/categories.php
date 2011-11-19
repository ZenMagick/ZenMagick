<div id="categories">
  <h2>Categories</h2>
  <?php $tree = $container->get('categoryService')->getCategoryTree($session->getLanguageId()); ?>
  <?php echo $macro->categoryTree($tree, true, $settings->get('isUseCategoryPage')) ?>
</div>
