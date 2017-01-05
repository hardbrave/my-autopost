<div class="wrap">
  <h2>Auto Post - <?php _e('Logs', 'my-autopost'); ?>
      <a href="admin.php?page=my-autopost/my-autopost-tasklist.php&saction=new" class="add-new-h2"><?php _e('Add New Task', 'my-autopost'); ?></a>
  </h2>

    <?php if ($msg) { ?>
    <div class="updated fade"><p><?php echo $msg; ?></p></div>
    <?php } ?>

    <?php global $log_list_table; ?>

    <form id="myform" method="post" action="admin.php?page=my-autopost/my-autopost-logs.php" >
        <input type="hidden" name="saction" id="saction" value="" />
        <?php
        $log_list_table->prepare_items();
        $log_list_table->display();
        ?>
    </form>
</div>
