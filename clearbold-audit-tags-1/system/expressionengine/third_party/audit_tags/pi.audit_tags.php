<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
  'pi_name' => 'Audit Tags',
  'pi_version' =>'1.0',
  'pi_author' =>'Mark J. Reeves / Clearbold, LLC',
  'pi_author_url' => 'http://www.clearbold.com/',
  'pi_description' => '',
  'pi_usage' => audit_tags::usage()
  );

class audit_tags {

    public  $return_data = '';
    var $heading;

    var $entry_id = 0;
    var $url_title = '';
    var $member_id = 0;
    var $backspace = 0;

    var $site_id = 0;

    /**
     * Constructor
     *
     *
     *
     * @access public
     * @return void
     */
    public function audit_tags()
    {
        $this->EE =& get_instance();

        $this->site_id = $this->EE->config->item('site_id');
        $this->member_id = $this->EE->session->userdata['member_id'];

        // NOTES
        // if ( ee()->db->table_exists('exp_mailing_lists') )

        // return
    }

    public function entry_log()
    {
        $input = $this->EE->TMPL->tagdata;
        $output = '';

        $this->entry_id = ( $this->EE->TMPL->fetch_param('entry_id') === FALSE ) ? $this->entry_id : $this->EE->TMPL->fetch_param('entry_id');
        $this->url_title = ( $this->EE->TMPL->fetch_param('url_title') === FALSE ) ? $this->url_title : $this->EE->TMPL->fetch_param('url_title');
        $this->backspace = ( $this->EE->TMPL->fetch_param('backspace') === FALSE ) ? $this->backspace : $this->EE->TMPL->fetch_param('backspace');

        $results = $this->EE->db->query("Select distinct t.entry_id, t.title,
            a.timestamp,
            m.member_id, m.username, m.screen_name, m.email
            from exp_audit_log a
            join exp_channel_titles t
            on a.item_id = t.entry_id
            join exp_members m
            on a.member_id = m.member_id
            where item_id = ?
            and (item_type = 'entry_update' or item_type = 'new_entry')
            and a.site_id = ?
            order by timestamp desc",array($this->entry_id, $this->site_id));

        if ( $this->url_title != '' )
        {
            $results = $this->EE->db->query("Select distinct t.entry_id, t.title,
                a.timestamp,
                m.member_id, m.username, m.screen_name, m.email
                from exp_audit_log a
                join exp_channel_titles t
                on a.item_id = t.entry_id
                join exp_members m
                on a.member_id = m.member_id
                where t.url_title = ?
                and (item_type = 'entry_update' or item_type = 'new_entry')
                and a.site_id = ?
                order by timestamp desc",array($this->url_title, $this->site_id));
        }

        if ($results->num_rows() == 0)
            return false;

        $log_entries = array();
        foreach ($results->result_array() as $row)
        {
            $append = array(
                    'audit_timestamp' => $row['timestamp'],
                    'audit_username' => $row['username'],
                    'audit_screen_name' => $row['screen_name'],
                    'audit_email' => $row['email']
                );
            $log_entries[] = $append;
        }

        $output .= $this->EE->TMPL->parse_variables($input, $log_entries);

        // return
        return ( $this->backspace > 0 ) ? substr($output, 0, $this->backspace) : $output;
    }

    public function entry_last_update()
    {
        $input = $this->EE->TMPL->tagdata;
        $output = '';

        $this->entry_id = ( $this->EE->TMPL->fetch_param('entry_id') === FALSE ) ? $this->entry_id : $this->EE->TMPL->fetch_param('entry_id');
        $this->url_title = ( $this->EE->TMPL->fetch_param('url_title') === FALSE ) ? $this->url_title : $this->EE->TMPL->fetch_param('url_title');
        $this->backspace = ( $this->EE->TMPL->fetch_param('backspace') === FALSE ) ? $this->backspace : $this->EE->TMPL->fetch_param('backspace');

        $results = $this->EE->db->query("Select distinct t.entry_id, t.title,
            a.timestamp,
            m.member_id, m.username, m.screen_name, m.email
            from exp_audit_log a
            join exp_channel_titles t
            on a.item_id = t.entry_id
            join exp_members m
            on a.member_id = m.member_id
            where item_id = ?
            and (item_type = 'entry_update' or item_type = 'new_entry')
            and a.site_id = ?
            order by timestamp desc
            limit 0,1",array($this->entry_id, $this->site_id));

        if ( $this->url_title != '' )
        {
            $results = $this->EE->db->query("Select distinct t.entry_id, t.title,
                a.timestamp,
                m.member_id, m.username, m.screen_name, m.email
                from exp_audit_log a
                join exp_channel_titles t
                on a.item_id = t.entry_id
                join exp_members m
                on a.member_id = m.member_id
                where t.url_title = ?
                and (item_type = 'entry_update' or item_type = 'new_entry')
                and a.site_id = ?
                order by timestamp desc
                limit 0,1",array($this->url_title, $this->site_id));
        }

        if ($results->num_rows() == 0)
            return false;

        foreach ($results->result_array() as $row)
        {
            $append = array(
                    'audit_timestamp' => $row['timestamp'],
                    'audit_username' => $row['username'],
                    'audit_screen_name' => $row['screen_name'],
                    'audit_email' => $row['email']
                );
            $output .= $this->EE->TMPL->parse_variables_row($input, $append);
        }

        // return
        return ( $this->backspace > 0 ) ? substr($output, 0, $this->backspace) : $output;
    }

    public function member_last_login()
    {
        $input = $this->EE->TMPL->tagdata;
        $output = '';

        $this->member_id = ( $this->EE->TMPL->fetch_param('member_id') === FALSE ) ? $this->member_id : $this->EE->TMPL->fetch_param('member_id');
        $this->backspace = ( $this->EE->TMPL->fetch_param('backspace') === FALSE ) ? $this->backspace : $this->EE->TMPL->fetch_param('backspace');

        $results = $this->EE->db->query("Select distinct a.timestamp,
            m.username, m.screen_name, m.email
            from exp_audit_log a
            join exp_members m
            on a.member_id = m.member_id
            where (item_type = 'login' or item_type = 'cp_login')
            and a.member_id = ?
            and a.site_id = ?
            order by timestamp desc
            limit 0,1",array($this->member_id, $this->site_id));

        if ($results->num_rows() == 0)
            return false;

        foreach ($results->result_array() as $row)
        {
            $append = array(
                    'audit_timestamp' => $row['timestamp'],
                    'audit_username' => $row['username'],
                    'audit_screen_name' => $row['screen_name'],
                    'audit_email' => $row['email']
                );
            $output .= $this->EE->TMPL->parse_variables_row($input, $append);
        }
        // return
        return ( $this->backspace > 0 ) ? substr(rtrim($output), 0, -1*$this->backspace) : $output;
    }

    // usage instructions
    public function usage()
    {
        ob_start();
?>
-------------------
HOW TO USE
-------------------

Audit Tags outputs logged data captured using Audit or Audit Pro. It supports the following tags:

<h2>Entry Log</h2>
{exp:channel:entries entry_id="42" dynamic="off"}
    <h3>{title}</h3>
{/exp:channel:entries}
{exp:audit_tags:entry_log entry_id="42"}
    {if count==1}<p># records: {total_results}</p>
    <ul>{/if}
        <li>{audit_timestamp format="%l, %F %j, %Y - %g:%i %A"} by {audit_screen_name} ({audit_username}, {audit_email})</li>
    {if count==total_results}</ul>{/if}
{/exp:audit_tags:entry_log}

{exp:audit_tags:entry_log url_title="{segment_2}"}
    {if count==1}<p># records: {total_results}</p>
    <ul>{/if}
        <li>{audit_timestamp format="%l, %F %j, %Y - %g:%i %A"} by {audit_screen_name} ({audit_username}, {audit_email})</li>
    {if count==total_results}</ul>{/if}
{/exp:audit_tags:entry_log}
<hr />

<h2>Entry Last Update</h2>
{exp:channel:entries entry_id="42" dynamic="off"}
    <h3>{title}</h3>
    {exp:audit_tags:entry_last_update entry_id="{entry_id}"}
        {audit_timestamp format="%l, %F %j, %Y - %g:%i %A"} by {audit_screen_name} ({audit_username}, {audit_email})<br />
    {/exp:audit_tags:entry_last_update}
{/exp:channel:entries}
{exp:audit_tags:entry_last_update url_title="{segment_2}"}
    {audit_timestamp format="%l, %F %j, %Y - %g:%i %A"} by {audit_screen_name} ({audit_username}, {audit_email})<br />
{/exp:audit_tags:entry_last_update}
<hr />

<h2>Member Last Login</h2>
{exp:audit_tags:member_last_login member_id="20"}
{audit_screen_name} ({audit_username}, {audit_email}) - {audit_timestamp format="%l, %F %j, %Y - %g:%i %A"}
<hr />
{/exp:audit_tags:member_last_login}

{exp:audit_tags:member_last_login backspace="6"}
{audit_screen_name} ({audit_username}, {audit_email}) - {audit_timestamp format="%l, %F %j, %Y - %g:%i %A"}<br />
{/exp:audit_tags:member_last_login}

    <?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}

/* End of file pi.toc.php */
/* Location: ./system/expressionengine/third_party/toc/pi.toc.php */