<form method="post" action="<?php echo $form_url ?>" id="audit-pro-manage-notifcations-form">
    <input type="hidden" name="XID" value="<?php echo XID_SECURE_HASH?>" />
    <p style="margin-bottom: 24px;">
        <a href="<?php echo $base_url ?>">&larr;&nbsp;&nbsp;Back to Audit Pro</a>
    </p>
    <div class="cp_button"><a href="#" id="add-notification-link">Add Notification</a></div>
    <style type="text/css">
        fieldset {
            margin-bottom: 12px;
        }
        thead th {
            cursor: default !important;
        }
        .audit-errors p {
            color: red;
        }
        td {
            background: none !important;
        }
        td.stripe {
            background: rgb(244, 246, 246) !important;
        }
        .where-label,
        .channel,
        .item-id,
        .label-entry-id,
        .label-template-id {
            display: none;
        }
    </style>
    <script type="text/javascript">
        var newForm = "<table class=\"mainTable padTable\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\
<thead>\
<tr>\
<th width=\"10%\"></th><th colspan=\"2\">Notification Settings</th></tr>\
</thead>\
<tbody>\
<tr>\
    <td class=\"directive\" rowspan=\"4\" valign=\"top\" width=\"10%\"><a href=\"#\" class=\"remove-notification\">Remove</a></td>\
    <td class=\"directive text-field stripe\" valign=\"top\" width=\"10%\">Send notification to:</td>\
    <td valign=\"top\" class=\"stripe\">\
        <p><label for=\"email_address_newNumber\" class=\"js_hide\">Email Address:</label> <input type=\"email\" id=\"email_address_newNumber\" name=\"email_address[]\" class=\"field\" placeholder=\"Email Address\" value=\"<?php echo $member_email ?>\" /></p>\
        <p><label for=\"is_sms_newNumber\"><input type=\"checkbox\" id=\"is_sms_newNumber\" name=\"is_sms[]\" value=\"1\" /><input type=\"hidden\" name=\"is_sms[]\" value=\"\"> Send short messages for <a target=\"_blank\" href=\"http://www.emailtextmessages.com/\">SMS inboxes</a>?</label> (This triggers a shorter message and does not guarantee it will reach your cell phone/SMS.)</p>\
    </td>\
</tr>\
<tr>\
    <td class=\"directive\" valign=\"top\">When:</td>\
    <td valign=\"top\">\
        <p>\
            <label for=\"username_newNumber\" class=\"js_hide\">Username</label> <select id=\"username_newNumber\" name=\"username[]\">\
            <option value=\"0\">Any User</option>\
            <?php
            foreach ($members->result_array() as $member)
            {
            ?>
            <option value=\"<?php echo $member['member_id'] ?>\" ><?php echo $member['username'] ?></option>\
            <?php
            }
            ?>
            </select>\
        </p>\
        <p>\
            <label for=\"group_newNumber\" class=\"js_hide\">Member Group</label> <select id=\"group_newNumber\" name=\"group[]\">\
            <option value=\"0\">Any Group</option>\
            <?php
            foreach ($groups->result_array() as $group)
            {
            ?>
            <option value=\"<?php echo $group['group_id'] ?>\" ><?php echo $group['group_title'] ?></option>\
            <?php
            }
            ?>
            </select>\
        </p>\
    </td>\
</tr>\
<tr>\
    <td class=\"directive stripe\" valign=\"top\">Does this:</td>\
    <td valign=\"top\" class=\"stripe\">\
        <p>\
            <label for=\"item_type_newNumber\" class=\"js_hide\">Choose an Item</label>\
            <select id=\"item_type_newNumber\" class=\"item_type\" name=\"item_type[]\">\
            <option value=\"cp_login\">Logs in via the Control Panel</option>\
            <option value=\"cp_logout\">Logs out via the Control Panel</option>\
            <option value=\"login\">Logs in via the Front-end</option>\
            <option value=\"logout\">Logs out via the Front-end</option>\
            <option value=\"entry_update\">Updates an Entry</option>\
            <option value=\"entry_delete\">Deletes an Entry</option>\
            <option value=\"new_entry\">Creates an Entry</option>\
            <option value=\"template_edit\">Edits a Template</option>\
            <option value=\"member_create\">Creates a Member</option>\
            <option value=\"member_delete\">Deletes a Member</option>\
            <option value=\"member_edit\">Edits a Member</option>\
            </select>\
        </p>\
    </td>\
</tr>\
<tr class=\"where_newNumber\">\
    <td class=\"directive\" valign=\"top\"><span class=\"where-label\">Where:</span></td>\
    <td valign=\"top\">\
        <p class=\"channel\">\
            <label for=\"channel_newNumber\">Channel =</label> <select id=\"channel_newNumber\" name=\"channel[]\">\
            <option value=\"0\">Any Channel</option>\
            <?php
            foreach ($channels->result_array() as $channel)
            {
            ?>
            <option value=\"<?php echo $channel['channel_id'] ?>\"><?php echo $channel['channel_title'] ?></option>\
            <?php
            }
            ?>
            </select>\
        </p>\
        <p class=\"item-id\">\
            <label for=\"item_id_newNumber\" class=\"label-entry-id\">Entry ID = </label> <label for=\"item_id\" class=\"label-template-id\">Template ID = </label> <input type=\"text\" id=\"item_id_newNumber\" name=\"item_id[]\" placeholder=\"Any\" style=\"width: 80px;\" />\
        </p>\
    </td>\
</tr>\
</tbody>\
</table>";
        var numNotifications = 0;
        $(document).ready(function(){

            numNotifications = $('#notification-forms table').length;

            $('#add-notification-link').click(function(){
                numNotifications++;
                $('#no-notifications').fadeOut(100);
                //$('#notification-forms').prepend(newForm.replace(/newNumber/g, numNotifications));
                newFormElement = newForm.replace(/newNumber/g, numNotifications);
                $(newFormElement).hide().prependTo('#notification-forms').fadeIn(400);
            })

            $('.remove-notification').live('click',(function(e){
                e.preventDefault();
                table = $(this).parent('td').parent('tr').parent('tbody').parent('table');
                numNotifications--;
                table.find('td').add(table.find('th')).fadeOut(400,
                    function(){
                        table.remove();
                        if (numNotifications==0)
                        {
                            $('#no-notifications').fadeIn(100);
                            $('form#audit-pro-manage-notifcations-form').submit();
                        }
                    });
            }))

            $('.item_type').live('change', function(){
                whereLabel = $(this).parent('p').parent('td').parent('tr').next('tr').children('td').find('.where-label');
                itemId = $(this).parent('p').parent('td').parent('tr').next('tr').children('td').find('p.item-id');
                channel = $(this).parent('p').parent('td').parent('tr').next('tr').children('td').find('p.channel');
                labelEntryId = $(this).parent('p').parent('td').parent('tr').next('tr').children('td').find('.label-entry-id');
                labelTemplateId = $(this).parent('p').parent('td').parent('tr').next('tr').children('td').find('.label-template-id');

                whereLabel.add(itemId).add(channel).add(labelEntryId).add(labelTemplateId).hide();

                if ( $(this).val()=='entry_update' ||
                        $(this).val()=='entry_delete' ||
                        $(this).val()=='new_entry' ||
                        $(this).val()=='template_edit'
                         )
                {
                    whereLabel.show();
                }
                if ( $(this).val()=='entry_update' ||
                        $(this).val()=='entry_delete' ||
                        $(this).val()=='new_entry'
                         )
                {
                    channel.show();
                }
                if ( $(this).val()=='entry_update' ||
                        $(this).val()=='entry_delete'
                         )
                {
                    labelEntryId.show();
                    itemId.show();
                }
                if ( $(this).val()=='template_edit' )
                {
                    labelTemplateId.show();
                    itemId.show();
                }
            })

        });
    </script>
    <div class="clear_left"></div>
    <?php
    if (isset($errors))
    {
        ?>
        <div class="audit-errors">
            <?php echo $errors ?>
        </div>
        <?php
    }
    ?>
    <div id="notification-forms">
    <?php
    $i = 0;
    if (count($forms)==0)
    {
        ?><p id="no-notifications">No notifications have been created yet.</p><?php
    }
    foreach ($forms as $form)
    {
        $i++;
        ?>
        <table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
            <thead>
            <tr>
            <th width="10%"></th><th colspan="2">Notification Settings</th></tr>
            </thead>
            <tbody>
            <tr>
                <td class="directive" rowspan="4" valign="top" width="10%"><a href="#" class="remove-notification">Remove</a></td>
                <td class="directive text-field stripe" valign="top" width="10%">Send notification to:</td>
                <td valign="top" class="stripe">
                    <p><label for="email_address_<?php echo $i ?>" class="js_hide">Email Address:</label> <input type="email" id="email_address_<?php echo $i ?>" name="email_address[]" class="field" placeholder="Email Address" value="<?php echo $form['email_address'] ?>" /></p>
                    <p><label for="is_sms_<?php echo $i ?>"><input type="checkbox" id="is_sms_<?php echo $i ?>" name="is_sms[]" value="1" <?php echo ($form['is_sms']=='1') ? 'checked' : ''; ?> /><input type="hidden" name="is_sms[]" value=""> Send short messages for <a target="_blank" href="http://www.emailtextmessages.com/">SMS inboxes</a>?</label> (This triggers a shorter message and does not guarantee it will reach your cell phone/SMS.)</p>
                </td>
            </tr>
            <tr>
                <td class="directive" valign="top">When:</td>
                <td valign="top">
                    <p>
                        <label for="username_<?php echo $i ?>" class="js_hide">Username</label> <select id="username_<?php echo $i ?>" name="username[]">
                        <option value="0">Any User</option>
                        <?php
                        foreach ($members->result_array() as $member)
                        {
                        ?>
                        <option value="<?php echo $member['member_id'] ?>" <?php echo ($form['username'] == $member['member_id']) ? 'selected' : ''; ?>><?php echo $member['username'] ?></option>
                        <?php
                        }
                        ?>
                        </select>
                    </p>
                    <p>
                        <label for="group_<?php echo $i ?>" class="js_hide">Member Group</label> <select id="group_<?php echo $i ?>" name="group[]">
                        <option value="0">Any Group</option>
                        <?php
                        foreach ($groups->result_array() as $group)
                        {
                        ?>
                        <option value="<?php echo $group['group_id'] ?>" <?php echo ($form['group'] == $group['group_id']) ? 'selected' : ''; ?>><?php echo $group['group_title'] ?></option>
                        <?php
                        }
                        ?>
                        </select>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="directive stripe" valign="top">Does this:</td>
                <td valign="top" class="stripe">
                    <p>
                        <label for="item_type_<?php echo $i ?>" class="js_hide">Choose an Item</label>
                        <select id="item_type_<?php echo $i ?>" class="item_type" name="item_type[]">
                        <option value="cp_login" <?php echo ($form['item_type'] == 'cp_login') ? 'selected' : ''; ?>>Logs in via the Control Panel</option>
                        <option value="cp_logout" <?php echo ($form['item_type'] == 'cp_logout') ? 'selected' : ''; ?>>Logs out via the Control Panel</option>
                        <option value="login" <?php echo ($form['item_type'] == 'login') ? 'selected' : ''; ?>>Logs in via the Front-end</option>
                        <option value="logout" <?php echo ($form['item_type'] == 'logout') ? 'selected' : ''; ?>>Logs out via the Front-end</option>
                        <option value="entry_update" <?php echo ($form['item_type'] == 'entry_update') ? 'selected' : ''; ?>>Updates an Entry</option>
                        <option value="entry_delete" <?php echo ($form['item_type'] == 'entry_delete') ? 'selected' : ''; ?>>Deletes an Entry</option>
                        <option value="new_entry" <?php echo ($form['item_type'] == 'new_entry') ? 'selected' : ''; ?>>Creates an Entry</option>
                        <option value="template_edit" <?php echo ($form['item_type'] == 'template_edit') ? 'selected' : ''; ?>>Edits a Template</option>
                        <option value="member_create" <?php echo ($form['item_type'] == 'member_create') ? 'selected' : ''; ?>>Creates a Member</option>
                        <option value="member_delete" <?php echo ($form['item_type'] == 'member_delete') ? 'selected' : ''; ?>>Deletes a Member</option>
                        <option value="member_edit" <?php echo ($form['item_type'] == 'member_edit') ? 'selected' : ''; ?>>Edits a Member</option>
                        </select>
                    </p>
                </td>
            </tr>
            <tr class="where_<?php echo $i ?>">
                <td class="directive" valign="top"><span class="where-label">Where:</span></td>
                <td valign="top">
                    <p class="channel">
                        <label for="channel_<?php echo $i ?>">Channel =</label> <select id="channel_<?php echo $i ?>" name="channel[]">
                        <option value="0">Any Channel</option>
                        <?php
                        foreach ($channels->result_array() as $channel)
                        {
                        ?>
                        <option value="<?php echo $channel['channel_id'] ?>" <?php echo ($form['channel'] == $channel['channel_id']) ? 'selected' : ''; ?>><?php echo $channel['channel_title'] ?></option>
                        <?php
                        }
                        ?>
                        </select>
                    </p>
                    <p class="item-id">
                        <label for="item_id_<?php echo $i ?>" class="label-entry-id">Entry ID = </label> <label for="item_id" class="label-template-id">Template ID = </label> <input type="text" id="item_id_<?php echo $i ?>" name="item_id[]" placeholder="Any" value="<?php echo $form['item_id'] ?>" style="width: 80px;" />
                    </p>
                </td>
            </tr>
            <script type="text/javascript">
                if ( $('#item_type_<?php echo $i ?>').val()=='entry_update' ||
                        $('#item_type_<?php echo $i ?>').val()=='entry_delete' ||
                        $('#item_type_<?php echo $i ?>').val()=='new_entry' ||
                        $('#item_type_<?php echo $i ?>').val()=='template_edit'
                         )
                {
                    $('.where_<?php echo $i ?>').find('.where-label').show();
                }
                if ( $('#item_type_<?php echo $i ?>').val()=='entry_update' ||
                        $('#item_type_<?php echo $i ?>').val()=='entry_delete' ||
                        $('#item_type_<?php echo $i ?>').val()=='new_entry'
                         )
                {
                    $('.where_<?php echo $i ?>').find('p.channel').show();
                }
                if ( $('#item_type_<?php echo $i ?>').val()=='entry_update' ||
                        $('#item_type_<?php echo $i ?>').val()=='entry_delete'
                         )
                {
                    $('.where_<?php echo $i ?>').find('.label-entry-id').show();
                    $('.where_<?php echo $i ?>').find('p.item-id').show();
                }
                if ( $('#item_type_<?php echo $i ?>').val()=='template_edit' )
                {
                    $('.where_<?php echo $i ?>').find('.label-template-id').show();
                    $('.where_<?php echo $i ?>').find('p.item-id').show();
                }
            </script>
            </tbody>
        </table>
        <?php
    }
    ?>
    </div>
    <input type="submit" name="submit-button" value="Save All" class="submit"  />
</form>
<div class="clear_right"></div>