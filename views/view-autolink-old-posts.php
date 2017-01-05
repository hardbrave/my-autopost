<div class="wrap">
    <h2>Auto Link
        <a href="admin.php?page=my-autopost/my-autopost-autolinks.php&saction=new" class="add-new-h2"><?php _e('Add New Keyword', 'my-autopost'); ?></a>
    </h2>

    <?php if(!empty($objects)) : ?>
        <ul>
            <?php foreach ($objects as $object) {
                my_autopost_link_post($object, $autolinks);
                echo '<li>#'. $object->ID .' '. $object->post_title .'</li>';
                unset($object);
            } ?>
        </ul>
        <p><?php _e("If your browser doesn't start loading the next page automatically click this link:", 'my-autopost'); ?>
            <a href="dmin.php?page=my-autopost/my-autopost-autolinks.php&saction=auto_link&n=<?php echo $n + $page_num; ?>"><?php _e('Next content', 'my-autopost'); ?></a>
        </p>
        <script type="text/javascript">
            function nextPage() {
                location.href = "admin.php?page=my-autopost/my-autopost-autolinks.php&saction=auto_link&n=<?php echo $n + $page_num; ?>";
            }
            window.setTimeout('nextPage()', 300 );
        </script>
    <?php else : ?>
        <p><strong>All done! </strong></p>
    <?php endif ?>
</div>

