
<div class="wrap">
    <h2>Auto Link
        <a href="admin.php?page=my-autopost/my-autopost-autolinks.php&saction=new" class="add-new-h2"><?php _e('Add New Keyword', 'my-autopost'); ?></a>
    </h2>
    <form id="myform" method="post" action="admin.php?page=my-autopost/my-autopost-autolinks.php<?php echo ($_GET['paged'] ? "&paged=$_GET[paged]" : ''); ?>" >
        <input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
        <input type="hidden" name="saction" id="saction" value="">
        <br/>
        <table>
            <tbody id="the-list">
            <tr>
                <td width="10%"><?php _e('Keyword', 'my-autopost'); ?>:</td>
                <td><input type="text" name="keyword" id="keyword" <?php my_autopost_value($keyword, $keyword); ?>> * </td>
            </tr>
            <tr>
                <td width="10%"><?php _e('Link', 'my-autopost'); ?>:</td>
                <td><input type="text" name="link" id="link" <?php my_autopost_value($link, $link); ?>" size="100"> * </td>
            </tr>
            <tr>
                <td width="10%"><?php _e('Description', 'my-autopost'); ?>:</td>
                <td><input type="text" name="desc" id="desc" <?php my_autopost_value($desc, $desc); ?>" size="50"></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="checkbox" name="no_follow"  value="1" <?php checked($no_follow); ?> /> <?php _e('No Follow', 'my-autopost'); ?> <a title='<?php _e('This adds a rel= "nofollow" to the link.', 'my-autopost'); ?>'>[?]</a>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="checkbox" name="new_window"  value="1" <?php checked($new_window); ?> /> <?php echo __('New Window','wp-autopost'); ?> <a title='<?php _e('This adds a target="_blank" to the link, forcing a new browser window on clicking.', 'my-autopost'); ?>'>[?]</a>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="checkbox" name="first_match_only"  value="1" <?php checked($first_match_only); ?> /> <?php echo __('First Match Only','wp-autopost'); ?> <a title='<?php _e('Only add links on the first matched.', 'my-autopost'); ?>'>[?]</a>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="checkbox" name="ignore_case"  value="1" <?php checked($ignore_case); ?> /> <?php _e('Ignore Case', 'my-autopost'); ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <?php if ($id) { ?>
                        <input type="checkbox" name="whole_word"  value="1" <?php checked($whole_word); ?> /> <?php _e('Match Whole Word', 'my-autopost'); ?>
                    <?php } else { ?>
                        <input type="checkbox" name="whole_word"  value="1" <?php checked(!(get_bloginfo('language')=='zh-CN')); ?> /> <?php _e('Match Whole Word', 'my-autopost'); ?>
                    <?php } ?>
                    <?php if ((get_bloginfo('language') == 'zh-CN')) echo '(中文请勿勾选)'; ?>
                    <a title='<?php _e('Match whole word only. For language split by "space", like English or other Latin languages.', 'my-autopost'); ?>'>[?]</a>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit"><input type="button" class="button-primary" value="<?php _e('Submit'); ?>"  onclick="addNew()"/>
            <a href="admin.php?page=my-autopost/my-autopost-autolinks.php<?php echo ($_GET['paged'] ? "&paged=$_GET[paged]" : ''); ?>" class="button"><?php _e('Return', 'my-autopost'); ?></a>
        </p>
    </form>
</div>

<script type="text/javascript">
    function addNew() {
        if (jQuery("#Keyword").val() == '' || jQuery("#Link").val() == '') {
            alert("<?php _e('Please enter both a keyword and URL', 'my-autopost'); ?>");
            return;
        }
        <?php if ($id) : ?>
        jQuery("#saction").val('do_edit');
        <?php else : ?>
        jQuery("#saction").val('do_add');
        <?php endif ?>
        jQuery("#myform").submit();
    }
</script>