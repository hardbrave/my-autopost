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
    <h2>Auto Post - Setting : <?php echo $task_config->name; ?>
        <a href="admin.php?page=my-autopost/my-autopost-tasklist.php&saction=new" class="add-new-h2"><?php _e('Add New Task', 'my-autopost'); ?></a>
    </h2>
    <div class="clear"></div>

    <a href="admin.php?page=my-autopost/my-autopost-tasklist.php" class="button"><?php _e('Return', 'my-autopost'); ?></a>
    &nbsp;
    <a href="admin.php?page=my-autopost/my-autopost-tasklist.php&saction=test_fetch&task_id=<?php echo $task_config->id; ?>" class="button-primary"><?php _e('Test Fetch', 'my-autopost'); ?></a>
    <br/>
    <br/>

    <?php if ($suc_msg): ?>
        <div class="updated fade"><p><?php echo $suc_msg; ?></p></div>
    <?php endif ?>

    <?php if ($err_msg): ?>
        <div class="error fade"><p><?php echo $err_msg; ?></p></div>
    <?php endif ?>

    <?php
    if ($show_test_url_list) {
        my_autopost_test_url_list($task_config->id);
    } else if ($show_test_fetch) {
        my_autopost_test_fetch($task_config->id);
    } else if ($show_test_crawl) {
        my_autopost_test_crawl($task_config->id, $test_url);
    }
     ?>

    <form id="myform"  method="post" action="admin.php?page=my-autopost/my-autopost-tasklist.php">
        <input type="hidden" name="saction" id="saction" value="">
        <input type="hidden" name="sub_saction" id="sub_saction" value="">
        <input type="hidden" name="task_id" value="<?php echo $task_config->id; ?>">

        <!--基本设置-->
        <div class="postbox">
            <h3 class="hndle" style="cursor:pointer;"><?php _e('Basic Settings', 'my-autopost'); ?></h3>
            <div class="inside" <?php my_autopost_invisibled(!$show_basic_box);?> >
                <table width="100%">
                    <!--任务名称-->
                    <tr>
                        <td width="18%"><?php _e('Task Name', 'my-autopost'); ?>:</td>
                        <td><input type="text" name="task_name" id="task_name" size="80" value="<?php echo $task_config->name; ?>"></td>
                    </tr>

                    <!--文章类型-->
                    <tr>
                        <td style="padding:10px 0 10px 0;"><?php _e('Post Type', 'my-autopost'); ?>:</td>
                        <td style="padding:10px 0 10px 0;">
                            <input type="radio" name="post_type" value="post" onchange="changePostType()" <?php if ($task_config->post_type == 'post') echo 'checked'; ?> /> <?php _e('Post'); ?>
                            &nbsp;&nbsp;
                            <input type="radio" name="post_type" value="page" onchange="changePostType()" <?php if ($task_config->post_type == 'page') echo 'checked='; ?> /> <?php _e('Page'); ?>
                            <?php foreach ($custom_post_types as $post_type) { ?>
                                &nbsp;&nbsp;
                                <input type="radio" name="post_type" value="<?php echo $post_type->name; ?>" onchange="changePostType()" <?php if($task_config->post_type==$post_type->name) echo 'checked="true"'; ?> /> <?php echo  $post_type->label; ?>
                            <?php } ?>
                        </td>
                    </tr>

                    <!--文章目录-->
                    <?php if ($task_config->post_type =='page'): ?>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                    <?php else: ?>
                    <tr>
                        <td><?php echo __('Taxonomy', 'my-autopost');  ?>:</td>
                        <td>
                            <?php my_autopost_show_category($task_config->post_type, explode(',', $task_config->cat)); ?>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <!--文章形式-->
                    <?php if ($format_name) : ?>
                    <tr>
                        <td style="padding:0 0 10px 0;"><?php _e('Post Format', 'my-autopost');  ?>:</td>
                        <td style="padding:0 0 10px 0;">
                            <input type="radio" name="post_format" value="" <?php checked(empty($task_config->post_format)); ?> /> <?php echo $format_name['standard']; ?>
                            <?php foreach ($post_formats[0]  as $post_format) { ?>
                                &nbsp;&nbsp;
                                <input type="radio" name="post_format" value="<?php echo $post_format; ?>" <?php checked($task_config->post_format == $post_format); ?> /> <?php echo $format_name[$post_format]; ?>
                            <?php } ?>

                        </td>
                    </tr>
                    <?php endif ?>

                    <!--作者-->
                    <tr>
                        <td><?php _e('Author', 'my-autopost'); ?>:</td>
                        <td>
                            <select name="author" >
                                <?php foreach ($users as $user) { ?>
                                    <option value="<?php echo $user->ID; ?>" <?php selected($user->ID == $task_config->author); ?> ><?php echo $user->display_name; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>

                    <!--更新时间间隔-->
                    <tr>
                        <td><?php _e('Update Interval', 'my-autopost'); ?>:</td>
                        <td>
                            <input type="text" name="update_interval" id="update_interval" size="2" value="<?php echo $task_config->update_interval; ?>"> <?php _e('Minute', 'my-autopost'); ?> <span class="gray">( <?php _e('How long Intervals detect whether there is a new article can be updated', 'my-autopost'); ?> )</span>
                        </td>
                    </tr>

                    <!--发布时间间隔-->
                    <tr>
                        <td><?php _e('Published Date Interval', 'my-autopost'); ?>:</td>
                        <td>
                            <input type="text" name="published_interval" id="published_interval" size="2" value="<?php echo $task_config->published_interval; ?>"> <?php _e('Minute', 'my-autopost'); ?> <span class="gray">( <?php _e('The published date interval between each post', 'my-autopost'); ?> )</span>
                        </td>
                    </tr>

                    <!--定时发布-->
                    <tr>
                        <td><?php _e('Post Scheduled','my-autopost'); ?>:</td>
                        <td>
                            <select id="post_scheduled" name="post_scheduled">
                                <option value="0" <?php selected($post_scheduled[0] == 0); ?>><?php _e('No'); ?></option>
                                <option value="1" <?php selected($post_scheduled[0] == 1); ?>><?php _e('Yes'); ?></option>
                            </select>
		                    <span id="post_scheduled_more" <?php if ($post_scheduled[0] == 0)   echo 'style="display:none;"' ?> >
	                            <input type="text" name="post_scheduled_hour" id="hh" size="2" maxlength="2"  value="<?php echo ($post_scheduled[1] < 10) ? '0'.$post_scheduled[1] : $post_scheduled[1];?>" />
			                    :
                                <input type="text" name="post_scheduled_minute" id="mn" size="2" maxlength="2" value="<?php echo ($post_scheduled[2] < 10) ? '0'.$post_scheduled[2] : $post_scheduled[2];?>" />
		                    </span>

                        </td>
                    </tr>

                    <!--字符集-->
                    <tr>
                        <td style="height:28px;"><?php _e('Charset','my-autopost'); ?>:</td>
                        <td>
                            <input class="charset" type="radio" name="charset" value="0"  <?php checked($task_config->page_charset == 'UTF-8'); ?>> UTF-8
                            <input class="charset" type="radio" name="charset" value="1"  <?php checked($task_config->page_charset != 'UTF-8'); ?>> <?php _e('Other', 'my-autopost'); ?>
                            <span id="otherSet" <?php if ($task_config->page_charset == 'UTF-8') echo 'style="display:none;"' ?>>
                                <input type="text" name="page_charset" id="page_charset"  value="<?php if($task_config->page_charset!='UTF-8') echo $task_config->page_charset; ?>">
                            </span>
                        </td>
                    </tr>

                    <!--下载远程图片-->
                    <tr>
                        <td><?php _e('Download Remote Images', 'my-autopost'); ?>:</td>
                        <td>
                            <select id="download_img" name="download_img">
                                <option value="0" ><?php _e('No'); ?></option>
                                <option value="1" ><?php _e('Yes'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!--设置特色图片-->
                    <tr>
                        <td><?php _e('Set Featured Image', 'my-autopost'); ?>:</td>
                        <td>
                            <select id="set_featured_image" name="set_featured_image">
                                <option value="0" ><?php _e('No'); ?></option>
                                <option value="1" ><?php _e('Yes'); ?></option>
                            </select>
                            <span id="set_featured_image_div" style="display:none;">
                                <p><?php _e('Set images as the featured image automatically', 'my-autopost'); ?>
                                &nbsp;&nbsp;&nbsp;
                                <?php _e('Index', 'my-autopost'); ?>:<input type="text" size="1" name="set_featured_image_index" value="1" /></p>
		                    </span>
                        </td>
                    </tr>

                    <!--发表状态-->
                    <tr>
                        <td><?php _e('Publish Status', 'my-autopost'); ?>:</td>
                        <td>
                            <select id="publish_status" name="publish_status">
                                <option value="0" <?php selected($auto_set[2] == 0); ?>><?php _e('Published'); ?></option>
                                <option value="1" <?php selected($auto_set[2] == 1); ?>><?php _e('Draft'); ?></option>
                                <option value="2" <?php selected($auto_set[2] == 2); ?>><?php _e('Pending Review'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!--手动选择性采集-->
                    <tr>
                        <td><?php _e('Manually Selective Extraction', 'my-autopost'); ?>:</td>
                        <td>
                            <select id="manually_extraction" name="manually_extraction">
                                <option value="0" <?php selected($task_config->m_extract == 0); ?>><?php _e('No'); ?></option>
                                <option value="1" <?php selected($task_config->m_extract == 1); ?>><?php _e('Yes'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!--检测已经抓取方式-->
                    <tr>
                        <td style="padding:10px 0 10px 0;"><?php _e('Check Extracted Method', 'my-autopost'); ?>:</td>
                        <td style="padding:10px 0 10px 0;">
                            <input type="radio" name="check_duplicate"  value="0" <?php checked($task_config->check_duplicate == 0); ?> /> <?php _e('URL', 'my-autopost'); ?>
                            &nbsp;&nbsp;&nbsp;
                            <input type="radio" name="check_duplicate"  value="1" <?php checked($task_config->check_duplicate == 1); ?> /> <?php _e('URL + Title', 'my-autopost'); ?>
                        </td>
                    </tr>

                    <tr><td colspan="2"><hr/></td></tr>

                    <!--使用代理服务器-->
                    <tr>
                        <td><?php _e('Use Proxy', 'my-autopost'); ?>:</td>
                        <td>
                            <select id="use_proxy" name="use_proxy">
                                <option value="0" <?php selected($proxy[0] == 0); ?>><?php _e('No'); ?></option>
                                <option value="1" <?php selected($proxy[0] == 1); ?>><?php _e('Yes'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!--隐藏IP-->
                    <tr>
                        <td><?php _e('Hide IP', 'my-autopost'); ?>:</td>
                        <td>
                            <select id="hide_ip" name="hide_ip">
                                <option value="0" <?php selected($proxy[1] == 0); ?>><?php _e('No'); ?></option>
                                <option value="1" <?php selected($proxy[1] == 1); ?>><?php _e('Yes'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr><td colspan="2"><hr/></td></tr>

                    <!--当抓取文章URL出错时设置该URL状态-->
                    <tr>
                        <td colspan="2"><?php _e('When extract error set the status to', 'my-autopost'); ?>:
                            &nbsp;&nbsp;
                            <select id="err_status" name="err_status">
                                <option value="1" <?php selected($task_config->err_status == 1); ?>><?php _e('Not set', 'my-autopost'); ?></option>
                                <option value="0" <?php selected($task_config->err_status == 0); ?>><?php _e('Pending Extraction', 'my-autopost'); ?></option>
                                <option value="-1" <?php selected($task_config->err_status == -1); ?>><?php _e('Ignored', 'my-autopost'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <input type="button" class="button-primary"  value="<?php _e('Save Changes'); ?>"  onclick="edit()"/>
                        </td>
                    </tr>

                </table>
            </div>
        </div>
        <div class="clear"></div>

        <!--文章来源设置-->
        <div class="postbox">
            <h3 class="hndle" style="cursor:pointer;"><?php _e('Article Source Settings', 'my-autopost'); ?></h3>
            <div class="inside" <?php my_autopost_invisibled(!$show_source_box); ?> >
                <!--文章列表网址-->
                <table width="100%">
                    <tr>
                        <td>
                            <input type="hidden" id="source_type" <?php my_autopost_value(true, $task_config->source_type); ?> />
                            <input class="source_type" type="radio" name="source_type" value="0" <?php checked($task_config->source_type == 0); ?> /> <?php _e('Manually specify', 'my-autopost'); ?> <b><?php _e('The URL of Article List', 'my-autopost'); ?></b> &nbsp;
                            <input class="source_type" type="radio" name="source_type" value="1" <?php checked($task_config->source_type == 1); ?> /> <?php _e('Batch generate', 'my-autopost'); ?> <b><?php _e('The URL of Article List', 'my-autopost'); ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div id="url_area1" <?php my_autopost_invisibled($task_config->source_type != 0); ?> >
                                <textarea name="urls" id="urls" rows="8" style="width:100%"><?php if(($task_config->source_type)==0){foreach($urls as $url)echo $url->url."\n"; } ?></textarea>
                                <br/>
                                <span class="gray"><?php _e('You can add multiple URLs, each URL begin at a new line','my-autopost'); ?></span>
                            </div>

                            <div id="url_area2" <?php my_autopost_invisibled($task_config->source_type != 1); ?> >
                                <input type="text" name="url" id="url" style="width:100%" value="<?php if(($task_config->source_type)==1){foreach($urls as $url)echo $url->url."\n"; } ?>" />
                                <br/>
                                <span class="gray"><?php echo __('For example', 'my-autopost'); ?>：http://my-autopost.org/html/test/list_(*).html</span><br/>
                                (*) <?php echo __('From','my-autopost'); ?> <input type="text" name="start_num" id="start_num" value="<?php echo $task_config->start_num; ?>" size="1"> <?php echo __('To','my-autopost'); ?> <input type="text" name="end_num" id="end_num" value="<?php echo $task_config->end_num; ?>" size="1">
                            </div>
                        </td>
                    </tr>
                    <tr><td></td></tr>
                    <tr>
                        <td> <input type="checkbox" name="reverse_sort" id="reverse_sort" <?php checked($task_config->reverse_sort == 1); ?> /> <?php _e('Reverse the sort of articles','my-autopost'); ?> <span class="gray">(<?php _e('Click Test to see the difference','my-autopost'); ?>)</span> </td>
                    </tr>
                </table>

                <!--文章网址匹配规则-->
                <h4><?php _e('Article URL matching rules', 'my-autopost'); ?></h4>
                <input class="a_match_type" type="radio" name="a_match_type" value="0" <?php checked($task_config->a_match_type == 0); ?> /><?php _e('Use URL wildcards match pattern', 'my-autopost'); ?>
                &nbsp;
                <input class="a_match_type" type="radio" name="a_match_type" value="1" <?php checked($task_config->a_match_type == 1); ?> /><?php _e('Use CSS Selector', 'my-autopost'); ?>

                <div id="a_selector_0" <?php my_autopost_invisibled($task_config->a_match_type != 0); ?> >
                    <?php _e('Article URL','my-autopost'); ?>:
                    <input type="text" name="a_selector_0" id="a_selector_0" size="80" <?php my_autopost_value($task_config->a_match_type == 0, $task_config->a_selector); ?> ><br/>
                    <span class="gray"><?php _e('The articles URL, (*) is wildcards', 'my-autopost'); ?>, <?php _e('For example', 'my-autopost'); ?>: http://www.domain.com/article/(*)/</span>
                </div>

                <div id="a_selector_1" <?php my_autopost_invisibled($task_config->a_match_type != 1); ?> >
                    <?php _e('The Article URLs CSS Selector', 'my-autopost'); ?>:
                    <input type="text" name="a_selector_1" id="a_selector_1" size="80" <?php my_autopost_value($task_config->a_match_type == 1, $task_config->a_selector); ?> ><br/>
                    <span class="gray"><?php _e('Must select to the HTML &lta> tag', 'my-autopost'); ?>, <?php _e('For example', 'my-autopost'); ?>: #list a</span>
                </div>

                <input type="button" class="button-primary"  value="<?php _e('Save Changes'); ?>"  onclick="save_url_list()"/>
                <input type="button" class="button"  value="<?php _e('Test', 'my-autopost'); ?>"  onclick="test_url_list()"/>

            </div>
        </div>
        <div class="clear"></div>

        <!--文章抓取设置-->
        <div class="postbox">
            <h3 class="hndle" style="cursor:pointer;"><?php _e('Article Extraction Settings', 'my-autopost'); ?></h3>
            <div class="inside" <?php my_autopost_invisibled(!$show_crawl_box); ?> >
                <table>
                    <tr>
                        <td><strong><?php _e('The Article Title Matching Rules','my-autopost'); ?></strong></td>
                    </tr>
                    <tr>
                        <td>
                            <input type="hidden" id="title_match_type" value="<?php echo $task_config->title_match_type; ?>">
                            <input class="title_match_type" type="radio" name="title_match_type" value="0" <?php checked($task_config->title_match_type == 0); ?> /><?php _e('Use CSS Selector', 'my-autopost'); ?>
                            &nbsp;
                            <input class="title_match_type" type="radio" name="title_match_type" value="1" <?php checked($task_config->title_match_type == 1); ?> /><?php _e('Use Wildcards Match Pattern', 'my-autopost'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div id="title_match_0"  <?php my_autopost_invisibled($task_config->title_match_type != 0); ?>>
                                <?php _e('CSS Selector', 'my-autopost'); ?>:
                                <input type="text" name="title_selector_0" id="title_selector_0" size="40" <?php my_autopost_value($task_config->title_match_type== 0, htmlspecialchars($task_config->title_selector)); ?> >
                                <span class="gray"><?php _e('For example','my-autopost'); ?>: #title h1</span>
                            </div>
                            <div id="title_match_1"  <?php my_autopost_invisibled($task_config->title_match_type != 1); ?>>
                                <?php _e('Matching Rule', 'my-autopost'); ?>:
                                <input type="text" name="title_selector_1" id="title_selector_1" size="65" <?php my_autopost_value($task_config->title_match_type== 1, htmlspecialchars($task_config->title_selector)); ?> >
                                <br/>
                                <span class="gray">"<?php _e('Starting unique HTML(*)End unique HTML','my-autopost'); ?>"&nbsp;&nbsp;&nbsp;<?php _e('For example','my-autopost'); ?>: &lttitle>(*)&lt/title></span>
                            </div>
                        </td>
                    </tr>
                </table>
                <br/>

                <strong><?php _e('The Article Content Matching Rules', 'my-autopost'); ?></strong>
                <table id="cmr" class="autoposttable" >
                    <tr>
                        <td>
                            <input type="hidden" id="content_match_type_0" value="<?php echo $content_match_type[0]; ?>">
                            <div>
                                <input class="content_match_type_0" type="radio" name="content_match_type_0" value="0" <?php checked($content_match_type[0] == 0); ?> />
                                <?php _e('Use CSS Selector', 'my-autopost'); ?> &nbsp;
                                <input class="content_match_type_0" type="radio" name="content_match_type_0" value="1" <?php checked($content_match_type[0] == 1);; ?> />
                                <?php _e('Use Wildcards Match Pattern', 'my-autopost'); ?> &nbsp;
                                <input type="checkbox" name="outer_0" id="outer_0" <?php checked($outer[0] == 1); ?> /> <?php  _e('Contain The Outer HTML Text', 'my-autopost'); ?>
                            </div>
                            <div id="content_match_0_0"  <?php  my_autopost_invisibled($content_match_type[0] != 0); ?>>
                                <?php _e('CSS Selector','my-autopost'); ?>:
                                <input type="text" name="content_selector_0_0" id="content_selector_0_0" size="40" <?php my_autopost_value($content_match_type[0] == 0, htmlspecialchars($content_selector[0])); ?>">
                                <span class="index" id="index_0"><?php _e('Index', 'my-autopost'); ?></span>
                                <span id="index_num_0" <?php my_autopost_invisibled($index[0] == 0); ?> >:
		                            <input type="text" name="index_0" size="1" value="<?php echo $index[0]; ?>" />
		                        </span>
                                <br/>
                                <span class="gray"><?php _e('For example', 'my-autopost'); ?>: #entry</span>
                            </div>
                            <div id="content_match_1_0"  <?php my_autopost_invisibled($content_match_type[0] != 1); ?>>
                                <?php _e('Matching Rule', 'my-autopost'); ?>:
                                <input type="text" name="content_selector_1_0" id="content_selector_1_0" size="65" <?php my_autopost_value($content_match_type[0] == 1, htmlspecialchars($content_selector[0])); ?> "><br/>
                                <span class="gray">"<?php _e('Starting unique HTML(*)End unique HTML','my-autopost'); ?>"
                                    &nbsp;&nbsp;&nbsp;<?php _e('For example','my-autopost'); ?>: &ltdiv id="entry">(*)&lt/div>&lt!-- end entry -->
                                </span>
                            </div>
                        </td>
                    </tr>
            <?php
            $cmr_num = count($content_selector);
            if ($cmr_num > 1) :
                for ($i = 1; $i < $cmr_num; $i++) : ?>
                    <tr id="cmr<?php echo $i; ?>">
                        <td>
                            <div>
                                <input class="content_match_type_<?php echo $i; ?>" type="radio" name="content_match_type_<?php echo $i; ?>" value="0" <?php checked($content_match_type[$i] == 0); ?> />
                                <?php _e('Use CSS Selector', 'my-autopost'); ?>&nbsp;
                                <input class="content_match_type_<?php echo $i; ?>" type="radio" name="content_match_type_<?php echo $i; ?>" value="1" <?php checked($content_match_type[$i] == 1); ?> />
                                <?php _e('Use Wildcards Match Pattern', 'my-autopost'); ?>&nbsp;
                                <input type="checkbox" name="outer_<?php echo $i; ?>" id="outer_<?php echo $i; ?>" <?php checked($outer[$i] == 1); ?> />
                                 <?php _e('Contain The Outer HTML Text','wp-autopost'); ?>
                            </div>
                            <span id="content_match_0_<?php echo $i; ?>"  <?php my_autopost_invisibled($content_match_type[$i] != 0); ?>>
                                <?php _e('CSS Selector', 'my-autopost'); ?>:
                                <input type="text" name="content_selector_0_<?php echo $i; ?>" id="content_selector_0_<?php echo $i; ?>" size="40" <?php my_autopost_value($content_match_type[$i] == 0, htmlspecialchars($content_selector[$i])); ?>">
	                            <span class="index" id="index_<?php echo $i; ?>"><?php _e('Index', 'my-autopost'); ?></span>
                                <span id="index_num_<?php echo $i; ?>" <?php my_autopost_invisibled($index[$i] == 0); ?> >:
		                            <input type="text" name="index_<?php echo $i; ?>" size="1" value="<?php echo $index[$i]; ?>" />
                                </span>
                            </span>
	                        <span id="content_match_1_<?php echo $i; ?>"  <?php my_autopost_invisibled($content_match_type[$i] != 1); ?>>
	                            <?php _e('Matching Rule', 'my-autopost'); ?>:
	                            <input type="text" name="content_selector_1_<?php echo $i; ?>" id="content_selector_1_<?php echo $i; ?>" size="65" <?php my_autopost_value($content_match_type[$i] == 1, htmlspecialchars($content_selector[$i])); ?>" />
                            </span>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('To: ', 'my-autopost'); ?>
                            <select name="objective_<?php echo $i; ?>" id="objective_<?php echo $i; ?>" >
                                <option value="0" <?php selected(0, $objective[$i]) ; ?>><?php _e('Post Content', 'my-autopost'); ?></option>
                                <option value="2" <?php selected(2, $objective[$i]); ?>><?php _e('Post Excerpt', 'my-autopost'); ?></option>
                                <option value="3" <?php selected(3, $objective[$i]); ?>><?php _e('Post Tags', 'my-autopost'); ?></option>
                                <option value="4" <?php selected(4, $objective[$i]); ?>><?php _e('Featured Image'); ?></option>
                                <option value="1" <?php selected(1, $objective[$i]); ?>><?php _e('Post Date', 'my-autopost'); ?></option>
                            </select>
                            &nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="button"  value="<?php _e('Delete'); ?>"  onclick="deleteRowCmr(<?php echo $i; ?>)"/>
                        </td>
                    </tr>
                <?php  endfor ?>
            <?php endif ?>
                </table>

                <a class="button" onclick="addMoreMR()"/><?php _e('Add More Matching Rules', 'my-autopost'); ?></a>
                <input type="hidden" name="cmr_num" id="cmr_num"  value="<?php echo $cmr_num-1; ?>" />
                <input type="hidden" name="cmr_tr_last_index" id="cmr_tr_last_index"  value="<?php echo $cmr_num; ?>" />

                <div>
                    <br/><input type="button" class="button-primary"  value="<?php _e('Save Changes'); ?>"  onclick="save_crawl()"/>
                    <input type="button" class="button"  value="<?php _e('Test', 'my-autopost'); ?>"  onclick="show_test_crawl()"/>
                </div>

                <div id="test_crawl" style="display:none;">
                    <?php _e('Enter the URL of test crawl', 'my-autopost'); ?>:<input type="text" name="test_url" id="test_url" value="<?php echo $task_config->content_test_url; ?>" size="100" />
                    <input type="button" class="button-primary"  value="<?php _e('Submit'); ?>"  onclick="test_crawl()"/>
                </div>

            </div>
        </div>
        <div class="clear"></div>

    </form>

</div>


<script type="text/javascript">
    jQuery(document).ready(function($){
        $('#post_scheduled').change(function(){
            if($(this).val() == 0){
                $("#post_scheduled_more").hide();
            }else{
                $("#post_scheduled_more").show();
            }
        });

        $('.charset').change(function(){
            if($(this).val() == 0){
                $("#otherSet").hide();
            }else{
                $("#otherSet").show();
            }
        });

        $('#set_featured_image').change(function(){
            if ($(this).val() == 0) {
                $("#set_featured_image_div").hide();
            } else {
                $("#set_featured_image_div").show();
            }
        });

        $('.source_type').change(function(){
            if($(this).val() == 0){
                $("#url_area1").show();
                $("#url_area2").hide();
            }else{
                $("#url_area2").show();
                $("#url_area1").hide();;
            }
        });

        $('#add_source_url').change(function(){
            if(document.getElementById("add_source_url").checked==true){
                $("#source_url_custom_fields").show();
            }else{
                $("#source_url_custom_fields").hide();
            }
        });

        $('#post_filter').change(function(){
            if(document.getElementById("post_filter").checked==true){
                $("#post_filter_div").show();
            }else{
                $("#post_filter_div").hide();
            }
        });

        $('.a_match_type').change(function(){
            if($(this).val() == 0){
                $("#a_selector_0").show();
                $("#a_selector_1").hide();
            }else{
                $("#a_selector_1").show();
                $("#a_selector_0").hide();
            }
        });

        $('.title_match_type').change(function(){
            var sSwitch = $(this).val();
            $("#title_match_type").val(sSwitch);
            if(sSwitch == 0){
                $("#title_match_0").show();
                $("#title_match_1").hide();
            }else{
                $("#title_match_1").show();
                $("#title_match_0").hide();
            }
        });

        var cmr_num = <?php echo $cmr_num; ?>;
        for (var i = 0; i < cmr_num; i++) {
            $('.content_match_type_'+i).change(function(i){
                return function(){
                    var sSwitch = $(this).val();
                    $("#content_match_type_"+i).val(sSwitch);
                    if (sSwitch == 0) {
                        $("#content_match_0_"+i).show();
                        $("#content_match_1_"+i).hide();
                    } else {
                        $("#content_match_1_"+i).show();
                        $("#content_match_0_"+i).hide();
                    }
                };
            }(i));
        }

        $('.index').click(function(){
            $(this).next('span').toggle();
        });

    });

    jQuery("h3.hndle").click(function() {
        jQuery(this).next(".inside").slideToggle('fast');
    });

    function save_url_list() {
        jQuery("#saction").val('save_url_list');
        jQuery("#myform").submit();
    }
    function test_url_list() {
        jQuery("#saction").val('test_url_list');
        jQuery("#myform").submit();
    }

    function save_crawl() {
        if (jQuery("#title_match_type").val() == 0 && !jQuery("#title_selector_0").val().trim()) {
            alert("<?php _e('Please enter The Article Title Matching Rules!', 'my-autopost'); ?>");
            return;
        }
        if (jQuery("#title_match_type").val() == 1 && !jQuery("#title_selector_1").val().trim()) {
            alert("<?php _e('Please enter The Article Title Matching Rules!', 'my-autopost'); ?>");
            return;
        }
        if (jQuery("#content_match_type_0").val() == 0 && !jQuery("#content_selector_0_0").val().trim()) {
            alert("<?php _e('Please enter The Article Content Matching Rules!', 'my-autopost'); ?>");
            return;
        }
        if (jQuery("#content_match_type_0").val() == 1 && !jQuery("#content_selector_1_0").val().trim()) {
            alert("<?php _e('Please enter The Article Content Matching Rules!', 'my-autopost'); ?>");
            return;
        }
        jQuery("#saction").val('save_crawl');
        jQuery("#myform").submit();
    }

    function show_test_crawl() {
        jQuery("#test_crawl").show();
    }

    function test_crawl() {
        if (!jQuery('#test_url').val()){
            alert("<?php _e('Please enter the URL of test!', 'my-autopost'); ?>");
            return;
        }
        jQuery("#saction").val('test_crawl');
        jQuery("#myform").submit();
    }

    function changePostType() {
        jQuery("#saction").val('edit_submit');
        jQuery("#sub_saction").val('change_post_type');
        jQuery("#myform").submit();
    }

    function edit() {
        if (!jQuery("#task_name").val()) {
            alert("<?php _e('Please enter the title of task!', 'my-autopost'); ?>");
            return;
        }
        jQuery("#saction").val('edit_submit');
        jQuery("#myform").submit();
    }

    function addMoreMR() {
        var cmr_num = parseInt(jQuery("#cmr_num").val());
        jQuery("#cmr_num").val(++cmr_num);

        var row_id = parseInt(jQuery("#cmr_tr_last_index").val());
        jQuery("#cmr_tr_last_index").val(row_id + 1);

        var new_td = '<tr id="cmr'+cmr_num+'"><td>'+
            '<div>'+
            '<input class="content_match_type_'+cmr_num+'" type="radio" name="content_match_type_'+cmr_num+'" value="0"  checked="true" /><?php _e("Use CSS Selector", "my-autopost"); ?>&nbsp;&nbsp;&nbsp;'+
            '<input class="content_match_type_'+cmr_num+'" type="radio" name="content_match_type_'+cmr_num+'" value="1" /><?php _e("Use Wildcards Match Pattern", "my-autopost"); ?>&nbsp;&nbsp;&nbsp;'+
            '<input type="checkbox" name="outer_'+cmr_num+'" /> <?php _e("Contain The Outer HTML Text","wp-autopost"); ?>'+
            '</div>'+
            '<span id="content_match_0_'+cmr_num+'" >'+
            '<?php _e("CSS Selector", "my-autopost"); ?>: <input type="text" name="content_selector_0_'+cmr_num+'" id="content_selector_0_'+cmr_num+'" size="40" value="">'+
            ' <span class="index" id="index_'+cmr_num+'"><?php echo __("Index", "my-autopost"); ?></span>'+
            '<span id="index_num_'+cmr_num+'" style="display:none;">: <input type="text" name="index_'+cmr_num+'" size="1" value="0" /></span>'+
            ' </span>'+
            '<span id="content_match_1_'+cmr_num+'"  style="display:none;" >'+
            '<?php _e("Matching Rule", "my-autopost"); ?>: <input type="text" name="content_selector_1_'+cmr_num+'" id="content_selector_1_'+cmr_num+'" size="65" value="">'+
            '</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
            '<?php _e("To: ", "my-autopost"); ?>'+
            '<select name="objective_'+cmr_num+'" id="objective_'+cmr_num+'" >'+
            '<option value="0" ><?php echo __('Post Content','wp-autopost'); ?></option>'+
            '<option value="2" ><?php echo __('Post Excerpt','wp-autopost'); ?></option>'+
            '<option value="3" ><?php _e('Post Tags', 'my-autopost'); ?></option>'+
            '<option value="4" ><?php _e('Featured Image'); ?></option>'+
            '<option value="1" ><?php _e('Post Date', 'my-autopost'); ?></option>'+
            '</select> &nbsp;&nbsp;&nbsp;&nbsp;'+
            '<input type="button" class="button"  value="<?php _e('Delete'); ?>" onclick="deleteRowCmr('+row_id+')"/>'+
            '</td></tr>';

        jQuery("#cmr tbody").last('tr').append(new_td);

        jQuery(function($){
            $('.content_match_type_'+cmr_num).change(function() {
                if($(this).val() == 0){
                    $("#content_match_0_"+cmr_num).show();
                    $("#content_match_1_"+cmr_num).hide();
                }else{
                    $("#content_match_1_"+cmr_num).show();
                    $("#content_match_0_"+cmr_num).hide();
                }
            });

            $('.index').click(function(){
                $(this).next('span').toggle();
            });

        });
    }

    function deleteRowCmr(rowid) {
        jQuery("#cmr" + rowid).remove();
    }
</script>
