<div class="wrap">
    <div class="icon32" id="icon-wp-autopost"><br/></div>
    <h2><?php _e('Proxy Options', 'my-autopost'); ?></h2>

    <?php if ($msg) { ?>
    <div class="error"><p><?php echo $msg; ?></p></div>
    <?php } ?>

    <form action="admin.php?page=my-autopost/my-autopost-proxy.php" method="post">
        <table class="form-table">
            <tr>
                <th scope="row"><label><?php _e('Hostname / IP', 'my-autopost');?>:</label></th>
                <td>
                    <input type="text" name="ip" value="<?php echo $proxy['ip']; ?>" size="60"/>
                </td>
            </tr>
            <tr>
                <th scope="row"><label><?php _e('Port', 'my-autopost');?>:</label></th>
                <td>
                    <input type="text" name="port" value="<?php echo $proxy['port']; ?>"  size="60"/>
                </td>
            </tr>
            <tr>
                <th scope="row"><label><?php _e('User', 'my-autopost');?> (<i><?php _e('optional', 'my-autopost');?></i>) :</label></th>
                <td>
                    <input type="text" name="user" value="<?php echo $proxy['user']; ?>"  size="60"/>
                </td>
            </tr>
            <tr>
                <th scope="row"><label><?php _e('Password', 'my-autopost');?> (<i><?php _e('optional', 'my-autopost');?></i>) :</label></th>
                <td>
                    <input type="text" name="password" value="<?php echo $proxy['password']; ?>"  size="60"/>
                </td>
            </tr>
        </table>

        <p class="submit"><input type="submit" name="save_setting" class="button-primary" value="<?php _e('Save Changes'); ?>" ></p>

        <table class="form-table" width="100%">
            <tr>
                <td colspan="2"><?php _e('URL', 'my-autopost');?> : <input type="text" name="url" value=""  size="90"/> <input type="submit" name="test_proxy" class="button" value="<?php echo __('Test','wp-autopost'); ?>" ></td>
            </tr>
            <?php if($show){ ?>
                <tr>
                    <td colspan="2" >
                        <textarea cols="180" rows="5"><?php echo htmlspecialchars($result); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="border-width:2px;border-style:solid;border-color:#dfdfdf">
                        <iframe src="<?php echo $fileUrl; ?>"  width="100%" height="600" frameborder="0"  ></iframe>
                    </td>
                </tr>

            <?php } ?>
        </table>

    </form>
</div>