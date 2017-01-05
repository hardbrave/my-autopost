<style>
    .postbox h3 {
        font-family: Georgia, "Times New Roman", "Bitstream Charter", Times, serif;
        font-size: 15px;
        padding: 10px 10px;
        margin: 0;
        line-height: 1;
    }
</style>

<div class="wrap">
    <div class="icon32" id="icon-my-autopost"><br/></div>
    <h2>Auto Post - <?php _e('Options', 'my-autopost'); ?></h2>
    <div class="clear"></div>
    <br/>

    <?php if ($msg) { ?>
    <div id="message" class="updated fade"><p><?php echo $msg; ?></p></div>
    <?php } ?>

    <form id="myform" method="post" action="admin.php?page=my-autopost/my-autopost-options.php">
        <!--更新选项-->
        <div class="postbox">
            <h3 class="hndle" style="cursor:pointer;"><?php _e('Update Option', 'my-autopost'); ?></h3>
            <div class="inside">
                <table width="100%">
                    <tr>
                        <td width="15%"><?php _e('Update Method', 'my-autopost'); ?>:</td>
                        <td>
                            <div>
                                <select name="update_method" id="update_method" onchange="showCron(this.value)">
                                    <option value="0" <?php if ($update_method == 0)  echo 'selected="true"'; ?> >
                                        <?php _e('Automatically check for updates after pages load', 'my-autopost'); ?>
                                    </option>
                                    <option value="1" <?php if ($update_method == 1)  echo 'selected="true"'; ?> >
                                        <?php echo __('Cron job or manual updates', 'my-autopost'); ?>
                                    </option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <div id="cron" <?php if ($update_method != 1) echo 'style="display:none;"' ?> >
                                <p><?php _e('If you want to use a cron job, you can perform scheduled updates by sending regularly-scheduled requests to','my-autopost'); ?>
                                    <code><a href="<?php echo $update_post_url;  ?>" target="_blank" ><?php echo $update_post_url;  ?></a></code>
                                    <?php _e('For example, inserting the following line in your crontab:','my-autopost'); ?></p>
                                <p><pre style="font-size: 0.80em"><code>*/10 * * * * /usr/bin/curl --silent <?php echo $update_post_url;  ?></code></pre></p>
                                <p><?php echo __('will check in every 10 minutes and check for updates on any activated tasks that are ready to be polled for updates.','my-autopost'); ?></p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td width="10%"><?php echo __('Time Limit on Updates','my-autopost'); ?>:</td>
                        <td><input type="text" name="time_limit" id="time_limit" value="<?php echo $time_limit; ?>" size="10" /><?php _e(' seconds','my-autopost'); ?>
                            <span class="gray">( <?php _e('Recommend the use of 0, which means that no time limit.','my-autopost'); ?> )</span>
                        </td>
                    </tr>

                    <tr>
                        <td width="20%"><?php _e('How many tasks can run simultaneously','my-autopost'); ?>:</td>
                        <td>
                            <select name="run_only_one_task" >
                                <option value="1" <?php if ($run_only_one_task == 1 || $run_only_one_task == null) echo 'selected="true"'; ?>> 1 </option>
                                <option value="0" <?php if ($run_only_one_task == 0)  echo 'selected="true"'; ?>><?php _e('Unlimited','my-autopost'); ?></option>
                            </select>
                            <span class="gray">( <?php _e('If Unlimited, may affect server performance','my-autopost'); ?> )</span>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2"><input type="submit" class="button-primary"  name="submit_update"  value="<?php _e('Save Changes'); ?>" /></td>
                    </tr>
                </table>
            </div>
        </div>

        <!--远程图片下载选项-->
        <div class="postbox">
            <h3 class="hndle" style="cursor:pointer;"><?php _e('Remote Images Download Option', 'my-autopost'); ?></h3>
            <div class="inside">
                <table width="100%">
                    <tr>
                        <td width="170"><?php echo __('Min Width Image to Download', 'my-autopost'); ?>:</td>
                        <td><input type="text" name="img_min_width" id="img_min_width" value="<?php echo $down_img_min_width; ?>" size="10" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="submit" class="button-primary"  name="submit_remote_img"  value="<?php _e('Save Changes'); ?>" /></td>
                    </tr>
                </table>
            </div>
        </div>

        <!--远程附件下载选项-->
        <div class="postbox">
            <h3 class="hndle" style="cursor:pointer;"><a name="RemoteAttachmentDownloadOption"></a><?php _e('Remote Attachment Download Option', 'my-autopost'); ?></h3>
            <div class="inside">
                <table width="100%">
                    <tr>
                        <td>
                            <?php _e('The following match types can be downloaded','my-autopost'); ?>
                            <br/>
                            <span class="gray"><?php _e('You can add multiple match types, each begin at a new line','my-autopost'); ?></span>
                            <br/>
			                <span class="gray"><?php _e('For example','my-autopost'); ?>: <i><b>.zip</b></i>&nbsp;&nbsp;<i><b>.doc</b></i>&nbsp;&nbsp;<i><b>attachment.php?aid=(*)</b></i></span>
		                    <textarea name="download_types" id="download_types" rows="16" style="width:100%"><?php foreach ($download_types as $download_type) echo $download_type."\r\n"; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="submit" class="button-primary"  name="submit_remote_attach"  value="<?php _e('Save Changes'); ?>" /></td>
                    </tr>
                </table>
            </div>
        </div>

        <!--其他选项-->
        <div class="postbox">
            <h3 class="hndle" style="cursor:pointer;"><?php _e('Other Option','my-autopost'); ?></h3>
            <div class="inside">
                <table>
                    <tr>
                        <td><?php _e('Automatically remove the HTML comments','my-autopost'); ?>:</td>
                        <td>
                            <select name="del_comment" id="del_comment">
                                <option value="1" <?php if ($del_comment == 1)  echo 'selected="true"'; ?> >
                                    <?php _e('Yes'); ?>
                                </option>
                                <option value="0" <?php if ($del_comment == 0)  echo 'selected="true"'; ?> >
                                    <?php _e('No'); ?>
                                </option>
                            </select>
                            <span class="gray">( <?php _e('Remove html element like &lt!-- *** -->','my-autopost'); ?> )</span>
                        </td>
                    </tr>

                    <tr>
                        <td><?php  _e('Automatically remove the HTML ID attribute','my-autopost'); ?>:</td>
                        <td>
                            <select name="del_attr_id" id="del_attr_id">
                                <option value="1" <?php if($del_attr_id == 1)  echo 'selected="true"'; ?> >
                                    <?php _e('Yes'); ?>
                                </option>
                                <option value="0" <?php if($del_attr_id == 0)  echo 'selected="true"'; ?> >
                                    <?php _e('No'); ?>
                                </option>
                            </select>
                            <span class="gray">( <?php _e('Remove html element like id=" *** "','my-autopost'); ?> )</span>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('Automatically remove the HTML CLASS attribute','my-autopost'); ?>:</td>
                        <td>
                            <select name="del_attr_class" id="del_attr_class">
                                <option value="1" <?php if ($del_attr_class == 1)  echo 'selected="true"'; ?> >
                                    <?php _e('Yes'); ?>
                                </option>
                                <option value="0" <?php if ($del_attr_class == 0)  echo 'selected="true"'; ?> >
                                    <?php _e('No'); ?>
                                </option>
                            </select>
                            <span class="gray">( <?php _e('Remove html element like class=" *** "','my-autopost'); ?> )</span>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo __('Automatically remove the HTML STYLE attribute','my-autopost'); ?>:</td>
                        <td>
                            <select name="del_attr_style" id="del_attr_style">
                                <option value="1" <?php if ($del_attr_style == 1)  echo 'selected="true"'; ?> >
                                    <?php _e('Yes'); ?>
                                </option>
                                <option value="0" <?php if ($del_attr_style == 0)  echo 'selected="true"'; ?> >
                                    <?php _e('No'); ?>
                                </option>
                            </select>
                            <span class="gray">( <?php _e('Remove html element like style=" *** "','my-autopost'); ?> )</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="submit" class="button-primary"  name="submit_other"  value="<?php _e('Save Changes'); ?>" />
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </form>

</div>

<script type="text/javascript">
    jQuery(function($) {
        $("h3.hndle").click(function(){$(this).next(".inside").slideToggle('fast');});
    });
    function showCron(v) {
        if (v == 1)
            jQuery('#cron').show('fast');
        else jQuery('#cron').hide();
    }
</script>
