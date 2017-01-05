
<div class="wrap">
  <h2>Auto Link
      <a href="admin.php?page=my-autopost/my-autopost-autolinks.php&saction=new" class="add-new-h2"><?php _e('Add New Keyword', 'my-autopost'); ?></a>
  </h2>

    <?php if ($suc_msg) { ?>
        <div class="updated fade"><p><?php echo $suc_msg; ?></p></div>
    <?php } ?>

    <?php if ($err_msg) { ?>
        <div class="updated fade"><p><?php echo $err_msg; ?></p></div>
    <?php } ?>

    <?php global $autolink_list_table; ?>

    <p><?php _e('Auto Link can automatically add links on keywords when publish post.', 'my-autopost'); ?></p>
    <form id="myform"  method="post" action="admin.php?page=my-autopost/my-autopost-autolinks.php" >
        <input type="hidden" name="saction" id="saction" value="">
        <input type="hidden" name="id" id="id" value="">
        <?php
        $autolink_list_table->prepare_items();
        $autolink_list_table->display();
        ?>
    </form>

    <h3><?php _e('Auto links old content', 'my-autopost'); ?></h3>
    <p><?php
        _e('Auto Link can also add keyword links all existing contents of your blog.', 'my-autopost');
        _e('This feature use keyword list above-mentioned.','wp-autopost');
        ?>
    </p>
    <a class="button-primary" href="admin.php?page=my-autopost/my-autopost-autolinks.php&saction=auto_link"><?php _e('Auto links all old content &raquo;', 'my-autopost'); ?></a>
</div>