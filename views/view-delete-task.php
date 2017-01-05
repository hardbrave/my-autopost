<div class="wrap">
    <div class="icon32" id="icon-wp-autopost"><br/></div>
    <h1>Auto Post - Delete Task</h1>
    <p>您将要删除以下任务：</p>
    <ul class="ul-disc">
        <li><strong><?php echo $task_name ?></strong></li>
    </ul>
    <p>您确定要删除这个任务吗？</p>
    <form id="myform"  method="post" action="admin.php?page=my-autopost/my-autopost-tasklist.php" >
        <input type="hidden" name="saction" id="saction" value="do_delete">
        <input type="hidden" name="task_id" id="task_id" value="<?php echo $task_id; ?>">
        <input type="submit" class="button" value="是,删除这个任务" />
        <a href="admin.php?page=my-autopost/my-autopost-tasklist.php" class="button">不,返回到任务列表</a></p>
    </form>
</div>
