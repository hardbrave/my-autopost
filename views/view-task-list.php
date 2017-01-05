<div class="wrap">
    <h2>Auto Post
        <a href="admin.php?page=my-autopost/my-autopost-tasklist.php&saction=new" class="add-new-h2"><?php _e('Add New Task', 'my-autopost'); ?></a>
    </h2>

    <?php if ($suc_msg): ?>
        <div class="updated fade"><p><?php echo $suc_msg; ?></p></div>
    <?php endif ?>

    <?php if ($err_msg): ?>
        <div class="updated fade"><p><?php echo $err_msg; ?></p></div>
    <?php endif ?>

    <?php
    if ($fetch_task_id) {
        my_autopost_fetch($fetch_task_id);
    }
    ?>

    <?php $task_list_table->views(); ?>

    <form id="myform" method="post" action="admin.php?page=my-autopost/my-autopost-tasklist.php">
        <input type="hidden" name="saction" id="saction" value="" />
        <?php
        $task_list_table->prepare_items();
        $task_list_table->display();
        ?>
    </form>
</div>
