<div class="wrap">
  <h2>Auto Post - <?php _e('Posts'); ?>
      <a href="admin.php?page=my-autopost/my-autopost-tasklist.php&saction=new" class="add-new-h2"><?php _e('Add New Task', 'my-autopost'); ?></a>
  </h2>

  <?php if ($msg): ?>
  <div class="updated fade"><p><?php echo $msg; ?></p></div>
  <?php endif ?>

  <?php global $post_list_table; ?>

  <?php $post_list_table->views(); ?>

  <form id="myform" method="post" action="admin.php?page=my-autopost/my-autopost-posts.php" >
    <input type="hidden" name="saction" id="saction" value="" />
    <?php
    $post_list_table->prepare_items();
    $post_list_table->search_box(__('Search'), 'post');
    $post_list_table->display();
    ?>
  </form>

</div>