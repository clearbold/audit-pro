<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="left" width="50%"><div class="cp_button"><a href="<?php echo $notifications_link ?>">Manage Notifications</a></div></td>
        <td align="right" width="50%"><div class="cp_button" style="float: right;"><a href="<?php echo $archive_link ?>">Archive Log Data</a></div></td>
    </tr>
</table>

<fieldset style="margin-bottom: 12px;">
    <legend>Search Log Entries</legend>
    <form action="<?php echo $base_url ?>" method="post" name="audit_pro_search_form" id="audit-pro-search-form">
        <input type="hidden" name="XID" value="<?php echo XID_SECURE_HASH?>" />
        <p style="width: 30%;"><label for="keywords" class="js_hide">Keywords </label><input type="text" name="audit_pro_keywords" id="" maxlength="100" class="field" placeholder="Keyword or ID" value="<?php echo $keywords ?>" />
        <input type="submit" name="submit-button" value="Search" class="submit" id="search_button" />&nbsp;&nbsp;<a href="<?php echo $base_url ?>">Clear</a></p>
        <p>Search current log entries by Username, Entry/Template/Member ID or simple keyword search against Entry Titles. Multiple keywords will return exact phrase matches.</p>
    </form>
</fieldset><div class="clear_left"></div>
<?php
if ($keywords != '')
{
    ?><p style="font-weight: bold;">Search results for &lsquo;<?php echo $keywords ?>&rsquo;:</p><?php
} ?>
<?php echo $table_html ?>
<?php echo $pagination_html ?>