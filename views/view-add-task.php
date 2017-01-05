<div class="wrap">
    <div class="icon32" id="icon-wp-autopost"><br/></div>
    <h2>Auto Post - New Task</h2>
    <form id="myform"  method="post" action="admin.php?page=my-autopost/my-autopost-tasklist.php" >
        <input type="hidden" name="saction" id="saction" value="do_add">
        <br/>
        <table>
            <tbody id="the-list">
            <tr>
                <td width="10%"><?php echo __('Task Name', 'my-autopost'); ?>:</td>
                <td><input type="text" name="task_name" id="task_name" value=""></td>
            </tr>
            </tbody>
        </table>
        <p class="submit"><input type="button" class="button-primary" value="<?php _e('Submit'); ?>"  onclick="addNew()"/>
        <a href="admin.php?page=my-autopost/my-autopost-tasklist.php" class="button"><?php _e('Return', 'my-autopost'); ?></a></p>
    </form>
</div>

<script type="text/javascript">
    function addNew() {
        if (jQuery("#task_name").val() == '') {
            alert("<?php echo __('Please enter the name of task!', 'my-autopost'); ?>")
            return;
        }
        jQuery("#myform").submit();
    }
</script>



