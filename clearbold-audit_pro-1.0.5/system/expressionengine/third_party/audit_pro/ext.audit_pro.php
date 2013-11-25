<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Extension for Audit
 *
 * This file must be in your /system/third_party/audit directory of your ExpressionEngine installation
 *
* @package   Audit Pro
 * @author    Mark J. Reeves <mjr@clearbold.com>
 * @copyright Copyright (c) 2013 Clearbold, LLC
 */
class Audit_pro_ext {

    var $name       = 'Audit Pro';
    var $version        = '1.0.5';
    var $description    = 'Logs user activity to enable audit/review.';
    var $settings_exist = 'n';
    var $docs_url       = ''; // 'http://expressionengine.com/user_guide/';

    var $settings        = array();

    var $member_id = 0;
    var $username = '-';
    var $group_id = 0;
    var $group_name = '-';
    var $user_agent = '';
    var $ip_address = '';
    var $item_id = 0;
    var $item_type = '';
    var $item_title = '-';
    var $site_id = 1;
    var $timestamp = 0; // Fill this in

    /**
     * Constructor
     *
     * @param   mixed   Settings array or empty string if none exist.
     */
    function __construct($settings='')
    {
        $this->EE =& get_instance();

        $this->settings = $settings;

        $this->member_id = $this->EE->session->userdata['member_id'];
        $this->username = $this->EE->session->userdata['username'];
        $this->group_id = $this->EE->session->userdata['group_id'];
        $this->user_agent = $this->EE->session->userdata['user_agent'];
        $this->ip_address = $this->EE->session->userdata['ip_address'];
        $this->site_id = $this->EE->config->item('site_id');
        $this->site_label = $this->EE->config->item('site_label');
        $this->timestamp = $this->EE->localize->now;

        $this->set_group_name();
    }

    function set_group_name()
    {
        $results = $this->EE->db->query("SELECT group_id FROM exp_members WHERE member_id = ?",array($this->member_id));
        if ($results->num_rows>0)
        {
            $group_results = $this->EE->db->query("SELECT group_title FROM exp_member_groups WHERE group_id = ?",array($results->row('group_id')));
            $this->group_name = $group_results->row('group_title');
            $this->group_id = $results->row('group_id');
        }
    }

    function cp_login()
    {
        $this->set_group_name();
        $item_type = 'cp_login';
        $insert_data = array(
            'site_id'           =>  $this->site_id,
            'member_id'    =>  $this->member_id,
            'username'      =>  $this->username,
            'group_name'  =>  $this->group_name,
            'item_type'      =>  $item_type,
            'ip_address'     =>  $this->ip_address,
            'timestamp'     =>  $this->timestamp,
            'user_agent'     =>  $this->user_agent
        );
        $this->EE->db->insert('audit_log', $insert_data);
        $this->_send_notification($item_type);
    }

    function cp_logout()
    {
        $results = $this->EE->db->query("SELECT m.member_id, m.username, g.group_title FROM exp_members m JOIN exp_member_groups g on m.group_id = g.group_id WHERE member_id = ?",$this->member_id);
        $group_name = $results->row('group_title');
        $item_type = 'cp_logout';
        $insert_data = array(
            'site_id'           =>  $this->site_id,
            'member_id'    =>  $this->member_id,
            'username'      =>  $this->username,
            'group_name'  =>  $group_name,
            'item_type'      =>  $item_type,
            'ip_address'     =>  $this->ip_address,
            'timestamp'     =>  $this->timestamp,
            'user_agent'     =>  $this->user_agent
        );
        $this->EE->db->insert('audit_log', $insert_data);
        $this->_send_notification($item_type);
    }

    function login()
    {
        $this->set_group_name();
        $item_type = 'login';
        $insert_data = array(
            'site_id'           =>  $this->site_id,
            'member_id'    =>  $this->member_id,
            'username'      =>  $this->username,
            'group_name'  =>  $this->group_name,
            'item_type'      =>  $item_type,
            'ip_address'     =>  $this->ip_address,
            'timestamp'     =>  $this->timestamp,
            'user_agent'     =>  $this->user_agent
        );
        $this->EE->db->insert('audit_log', $insert_data);
        $this->_send_notification($item_type);
        return;
    }

    function logout()
    {
        $item_type = 'logout';
        $insert_data = array(
            'site_id'           =>  $this->site_id,
            'member_id'    =>  $this->member_id,
            'username'      =>  $this->username,
            'group_name'  =>  '-',
            'item_type'      =>  $item_type,
            'ip_address'     =>  $this->ip_address,
            'timestamp'     =>  $this->timestamp,
            'user_agent'     =>  $this->user_agent
        );
        $this->EE->db->insert('audit_log', $insert_data);
        $this->_send_notification($item_type);
        return;
    }

    function entry_delete()
    {
        $deletes = $this->EE->input->post('delete');
        $item_type = 'entry_delete';
        foreach ($deletes as $delete)
        {
            $results = $this->EE->db->query("SELECT entry_id, title, c.channel_id FROM exp_channel_titles t
                            JOIN exp_channels c on t.channel_id = c.channel_id WHERE entry_id = ?",array($delete));

            $item_id = $results->row('entry_id');
            $item_title = $results->row('title');
            $channel_id = $results->row('channel_id');

            $insert_data = array(
                'site_id'           =>  $this->site_id,
                'member_id'    =>  $this->member_id,
                'username'      =>  $this->username,
                'group_name'  =>  $this->group_name,
                'item_type'      =>  $item_type,
                'item_id'          =>  $item_id,
                'item_title'       =>  $item_title,
                'ip_address'     =>  $this->ip_address,
                'timestamp'     =>  $this->timestamp,
                'user_agent'     =>  $this->user_agent
            );
            $this->EE->db->insert('audit_log', $insert_data);
            $this->_send_notification($item_type, $item_id, 0, $item_title, $channel_id);
        }
    }

    function entry_multi_update($id, $data)
    {
        $results = $this->EE->db->query("SELECT * FROM exp_channel_titles t
                        JOIN exp_channels c
                        ON t.channel_id = c.channel_id
                        WHERE t.entry_id = ?",array($id));
        $channel_id = $results->row('channel_id');
        $entry_title = $data['title'];
        $item_type = 'entry_update';
        $insert_data = array(
            'site_id'           =>  $this->site_id,
            'member_id'    =>  $this->member_id,
            'username'      =>  $this->username,
            'group_name'  =>  $this->group_name,
            'item_type'      =>  $item_type,
            'item_id'          =>  $id,
            'item_title'       =>  $entry_title,
            'ip_address'     =>  $this->ip_address,
            'timestamp'     =>  $this->timestamp,
            'user_agent'     =>  $this->user_agent
        );
        $this->EE->db->insert('audit_log', $insert_data);
        $this->_send_notification($item_type, $id, 1, $entry_title, $channel_id);
    }

    function entry_publish_update($meta, $data)
    {
        $item_type = 'entry_update';
        $entry_id =$data['entry_id'];
        $create_edit = 1;
        if ($entry_id ==0)
        {
            $item_type = 'new_entry';

            $results = $this->EE->db->query("SHOW TABLE STATUS LIKE 'exp_channel_titles'");
            $auto_increment = $results->row('Auto_increment');
            $entry_id = 1 + $auto_increment;
            $create_edit = 0;
        }

        $item_id = $entry_id;
        $item_title = $meta['title'];
        $channel_id = $meta['channel_id'];

        $insert_data = array(
            'site_id'           =>  $this->site_id,
            'member_id'    =>  $this->member_id,
            'username'      =>  $this->username,
            'group_name'  =>  $this->group_name,
            'item_type'      =>  $item_type,
            'item_id'          =>  $entry_id,
            'item_title'       =>  $meta['title'],
            'ip_address'     =>  $this->ip_address,
            'timestamp'     =>  $this->timestamp,
            'user_agent'     =>  $this->user_agent
        );
        $this->EE->db->insert('audit_log', $insert_data);
        $this->_send_notification($item_type, $entry_id, $create_edit, $item_title, $channel_id);
    }

    function template_edit($template_id, $message)
    {
        $results = $this->EE->db->query("SELECT * FROM exp_templates t
                        JOIN exp_template_groups g
                        ON t.group_id = g.group_id
                        WHERE t.template_id = ?",array($template_id));
        $template_name = $results->row('template_name');
        $template_group = $results->row('group_name');

        $item_type = 'template_edit';
        $insert_data = array(
            'site_id'           =>  $this->site_id,
            'member_id'    =>  $this->member_id,
            'username'      =>  $this->username,
            'group_name'  =>  $this->group_name,
            'item_type'      =>  $item_type,
            'item_id'          =>  $template_id,
            'item_title'       =>  $template_group . '/' . $template_name,
            'ip_address'     =>  $this->ip_address,
            'timestamp'     =>  $this->timestamp,
            'user_agent'     =>  $this->user_agent
        );
        $this->EE->db->insert('audit_log', $insert_data);
        $this->_send_notification($item_type, $template_id, 0, $template_group . '/' . $template_name);
    }

    function member_create($member_id, $data)
    {
        $item_type = 'member_create';
        $member_name = $data['username'];
        $insert_data = array(
            'site_id'           =>  $this->site_id,
            'member_id'    =>  $this->member_id,
            'username'      =>  $this->username,
            'group_name'  =>  $this->group_name,
            'item_type'      =>  $item_type,
            'item_id'          =>  $member_id,
            'item_title'       =>  $member_name,
            'ip_address'     =>  $this->ip_address,
            'timestamp'     =>  $this->timestamp,
            'user_agent'     =>  $this->user_agent
        );
        $this->EE->db->insert('audit_log', $insert_data);
        $this->_send_notification($item_type, $member_id, 0, $member_name);
    }

    function member_delete($member_ids)
    {
        $item_type = 'member_delete';
        foreach ($member_ids as $member)
        {
            $results = $this->EE->db->query("SELECT member_id, username FROM exp_members WHERE member_id = ?",array($member));
            $member_name = $results->row('username');
            $insert_data = array(
                'site_id'           =>  $this->site_id,
                'member_id'    =>  $this->member_id,
                'username'      =>  $this->username,
                'group_name'  =>  $this->group_name,
                'item_type'      =>  $item_type,
                'item_id'          =>  $member,
                'item_title'       =>  $member_name,
                'ip_address'     =>  $this->ip_address,
                'timestamp'     =>  $this->timestamp,
                'user_agent'     =>  $this->user_agent
            );
            $this->EE->db->insert('audit_log', $insert_data);
            $this->_send_notification($item_type, $member, 0, $member_name);
        }
        return $member_ids;
    }

    function member_edit($member_id, $data) {
        $item_type = 'member_edit';
        $results = $this->EE->db->query("SELECT member_id, username FROM exp_members WHERE member_id = ?",$member_id);
        $member_name = $results->row('username');
        $insert_data = array(
            'site_id'           =>  $this->site_id,
            'member_id'    =>  $this->member_id,
            'username'      =>  $this->username,
            'group_name'  =>  $this->group_name,
            'item_type'      =>  $item_type,
            'item_id'          =>  $member_id,
            'item_title'       =>  $member_name,
            'ip_address'     =>  $this->ip_address,
            'timestamp'     =>  $this->timestamp,
            'user_agent'     =>  $this->user_agent
        );
        $this->EE->db->insert('audit_log', $insert_data);
        $this->_send_notification($item_type, $member_id, 0, $member_name);
    }

    function _send_notification($item_type = '', $item_id = 0, $create_edit = 0, $item_title = '', $channel_id = 0)
    {
        $results = $this->EE->db->query("SELECT * FROM exp_audit_notifications WHERE item_type = ?",array($item_type));

        if ($results->num_rows() == 0)
        {
            return false;
        }
        $this->EE->load->library('email');
        $this->EE->load->helper('text');
        $this->EE->email->wordwrap = true;
        $this->EE->email->mailtype = 'text';
        $from = '';
        $recipient = '';
        $email_subject = '';
        $email_msg = '';

        //var_dump($results);
        foreach($results->result_array() as $notification)
        {
            // If stored item type does not match this item type, exit
            if ($notification['item_type'] != $item_type)
                return false;
            // If stored member ID is to be checked (not 0) and does not match this user, exit
            if ($notification['member_id'] != $this->member_id && $notification['member_id'] != 0)
                return false;

            if ($notification['group_id'] != $this->group_id && $notification['group_id'] != 0)
                return false;
            // If stored item ID is to be checked (not 0) and does not match this item ID, exit
            if ($notification['item_id'] != 0 && $notification['item_id'] != $item_id)
                return false;
            // If stored Channel ID is to be checked (not 0) and does not match this channel ID, exit
            if ($notification['channel_id'] != 0 && $notification['channel_id'] != $channel_id)
                return false;
            // Now we have something to work with!

            $this->EE->email->initialize();
            $this->EE->email->from($this->EE->config->item('webmaster_email'), $this->EE->config->item('webmaster_name'));
            $this->EE->email->to($notification['email_address']);
            switch($item_type)
            {
                case 'cp_login':
                    // Assume member matches, Just send the email
                    $email_msg = "A user has logged in via the Control Panel.";
                    if ($notification['is_sms']==1)
                    {
                        $this->EE->email->subject($this->site_label . ": " . $email_msg);
                        $email_msg = '';
                        break;
                    }
                    $email_msg .= "\n\nUsername: " . $this->username;
                    $this->EE->email->subject($this->site_label . ': Audit Pro Notification: Control Panel Login');
                break;

                case 'cp_logout':
                    // Assume member matches, Just send the email
                    $email_msg = "A user has logged out via the Control Panel.";
                    if ($notification['is_sms']==1)
                    {
                        $this->EE->email->subject($this->site_label . ": " . $email_msg);
                        $email_msg = '';
                        break;
                    }
                    $email_msg .= "\n\nUsername: " . $this->username;
                    $this->EE->email->subject($this->site_label . ': Audit Pro Notification: Control Panel Logout');
                break;

                case 'login':
                    // Assume member matches, Just send the email
                    $email_msg = "A user has logged in via the Front-end.";
                    if ($notification['is_sms']==1)
                    {
                        $this->EE->email->subject($this->site_label . ": " . $email_msg);
                        $email_msg = '';
                        break;
                    }
                    $email_msg .= "\n\nUsername: " . $this->username;
                    $this->EE->email->subject($this->site_label . ': Audit Pro Notification: Front-end Login');
                break;

                case 'logout':
                    // Assume member matches, Just send the email
                    $email_msg = "A user has logged out via the Front-end.";
                    if ($notification['is_sms']==1)
                    {
                        $this->EE->email->subject($this->site_label . ": " . $email_msg);
                        $email_msg = '';
                        break;
                    }
                    $email_msg .= "\n\nUsername: " . $this->username;
                    $this->EE->email->subject($this->site_label . ': Audit Pro Notification: Front-end Logout');
                break;

                case 'entry_delete':
                    // Assume entry ID matches, does channel match?
                    $email_msg = "A user has deleted an entry.";
                    if ($notification['is_sms']==1)
                    {
                        $this->EE->email->subject($this->site_label . ": " . $email_msg);
                        $email_msg = '';
                        break;
                    }
                    $email_msg .= "\n\nUsername: " . $this->username;
                    $email_msg .= "\n\nEntry ID: " . $item_id;
                    $email_msg .= "\n\nEntry Title: " . $item_title;
                    $this->EE->email->subject($this->site_label . ': Audit Pro Notification: Entry Deleted');
                break;

                case 'entry_update':
                    // Assume entry ID matches, does channel match?
                    $results = $this->EE->db->query("SELECT * FROM exp_channels c
                                    WHERE c.channel_id = ?",array($channel_id));
                    $channel_name = $results->row('channel_name');
                    $channel_title = $results->row('channel_title');

                    $email_msg = "A user has updated an entry.";
                    if ($notification['is_sms']==1)
                    {
                        $this->EE->email->subject($this->site_label . ": " . $email_msg);
                        $email_msg = '';
                        break;
                    }
                    $email_msg .= "\n\nUsername: " . $this->username;
                    $email_msg .= "\n\nChannel: " . $channel_title;
                    $email_msg .= "\n\nEntry ID: " . $item_id;
                    $email_msg .= "\n\nEntry Title: " . $item_title;
                    $this->EE->email->subject($this->site_label . ': Audit Pro Notification: Entry Updated');
                break;

                case 'new_entry':
                    // Don't worry about entry ID, does channel match?
                    $results = $this->EE->db->query("SELECT * FROM exp_channels c
                                    WHERE c.channel_id = ?",array($channel_id));
                    $channel_name = $results->row('channel_name');
                    $channel_title = $results->row('channel_title');

                    $email_msg = "A user has published an entry.";
                    if ($notification['is_sms']==1)
                    {
                        $this->EE->email->subject($this->site_label . ": " . $email_msg);
                        $email_msg = '';
                        break;
                    }
                    $email_msg .= "\n\nUsername: " . $this->username;
                    $email_msg .= "\n\nChannel: " . $channel_title;
                    $email_msg .= "\n\nEntry ID: " . $item_id;
                    $email_msg .= "\n\nEntry Title: " . $item_title;
                    $this->EE->email->subject($this->site_label . ': Audit Pro Notification: Entry Created');
                break;

                case 'template_edit':
                    // Assume entry ID matches, Just send the email
                    $email_msg = "A user has edited a template.";
                    if ($notification['is_sms']==1)
                    {
                        $this->EE->email->subject($this->site_label . ": " . $email_msg);
                        $email_msg = '';
                        break;
                    }
                    $email_msg .= "\n\nUsername: " . $this->username;
                    $email_msg .= "\n\nTemplate ID: " . $item_id;
                    $email_msg .= "\n\nTemplate Name: " . $item_title;
                    $this->EE->email->subject($this->site_label . ': Audit Pro Notification: Template Edited');
                break;

                case 'member_create':
                    // Just send the email
                    $email_msg = "A user has created a new member.";
                    if ($notification['is_sms']==1)
                    {
                        $this->EE->email->subject($this->site_label . ": " . $email_msg);
                        $email_msg = '';
                        break;
                    }
                    $email_msg .= "\n\nUsername: " . $this->username;
                    $email_msg .= "\n\nNew Member ID: " . $item_id;
                    $email_msg .= "\n\nNew Member Name: " . $item_title;
                    $this->EE->email->subject($this->site_label . ': Audit Pro Notification: Member Created');
                break;

                case 'member_delete':
                    // Just send the email
                    $email_msg = "A user has deleted a member.";
                    if ($notification['is_sms']==1)
                    {
                        $this->EE->email->subject($this->site_label . ": " . $email_msg);
                        $email_msg = '';
                        break;
                    }
                    $email_msg .= "\n\nUsername: " . $this->username;
                    $email_msg .= "\n\nDeleted Member ID: " . $item_id;
                    $email_msg .= "\n\nDeleted Member Name: " . $item_title;
                    $this->EE->email->subject($this->site_label . ': Audit Pro Notification: Member Deleted');
                break;

                case 'member_edit':
                    // Just send the email
                    $email_msg = "A user has edited a member.";
                    if ($notification['is_sms']==1)
                    {
                        $this->EE->email->subject($this->site_label . ": " . $email_msg);
                        $email_msg = '';
                        break;
                    }
                    $email_msg .= "\n\nUsername: " . $this->username;
                    $email_msg .= "\n\nEdited Member ID: " . $item_id;
                    $email_msg .= "\n\nEdited Member Name: " . $item_title;
                    $this->EE->email->subject($this->site_label . ': Audit Pro Notification: Member Edited');
                break;
            }
            $email_msg .= "\n\n" . $this->EE->localize->set_human_time($this->timestamp);
            $this->EE->email->message(entities_to_ascii($email_msg));
            $this->EE->email->Send();
        }
    }

    /**
     * Activate Extension
     *
     * This function enters the extension into the exp_extensions table
     *
     * @see http://codeigniter.com/user_guide/database/index.html for
     * more information on the db class.
     *
     * @return void
     */
    function activate_extension()
    {

        // Creating the database table here since we're starting with the logger extension before the CP screen
        $this->EE->load->dbforge();

        /*
        CREATE TABLE `exp_audit_log` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `site_id` int(11) unsigned DEFAULT NULL,
          `member_id` int(11) unsigned DEFAULT NULL,
          `username` varchar(100) DEFAULT NULL,
          `group_name` varchar(100) DEFAULT NULL,
          `item_type` varchar(100) DEFAULT NULL,
          `item_id` int(11) unsigned DEFAULT NULL,
          `item_title` varchar(100) DEFAULT NULL,
          `ip_address` varchar(45) DEFAULT NULL,
          `timestamp` int(11) DEFAULT NULL,
          `user_agent` varchar(500) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;
        */

        $fields = array(
            'id'                     =>  array('type'=>'int','constraint'=>'11','unsigned'=>TRUE,'null'=>FALSE,'auto_increment'=>TRUE),
            'site_id'              =>  array('type'=>'int','constraint'=>'11','unsigned'=>TRUE),
            'member_id'       =>  array('type'=>'int','constraint'=>'11','unsigned'=>TRUE),
            'username'         =>  array('type'=>'varchar','constraint'=>'100'),
            'group_name'     =>  array('type'=>'varchar','constraint'=>'100'),
            'item_type'         =>  array('type'=>'varchar','constraint'=>'100'),
            'item_id'             =>  array('type'=>'int','constraint'=>'11','unsigned'=>TRUE),
            'item_title'          =>  array('type'=>'varchar','constraint'=>'100'),
            'ip_address'        =>  array('type'=>'varchar','constraint'=>'45'),
            'timestamp'        =>  array('type'=>'int','constraint'=>'11'),
            'user_agent'        =>  array('type'=>'varchar','constraint'=>'500'),
            'archive'              =>  array('type'=>'varchar','constraint'=>'5')
        );

        $this->EE->dbforge->add_field($fields);
        $this->EE->dbforge->add_key('id', TRUE);
        $this->EE->dbforge->create_table('audit_log', TRUE);
        /**
        * PRO
        **/
        $this->EE->dbforge->add_field($fields);
        $this->EE->dbforge->add_key('id', TRUE);
        $this->EE->dbforge->create_table('audit_log_archive', TRUE);

        $fields = array(
            'archive'              =>  array('type'=>'varchar','constraint'=>'5')
        );
        $sql = "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_name='exp_audit_log' AND COLUMN_NAME='archive'";
        $results = $this->EE->db->query($sql);
        if ($results->num_rows()==0)
            $this->EE->dbforge->add_column('audit_log', $fields);
        /**
        * /PRO
        **/

        $this->settings = array(

        );

        $hooks = array(
            'cp_login'                             =>  'cp_member_login',
            'cp_logout'                          =>  'cp_member_logout',
            'login'                                  =>  'member_member_login_start',
            'logout'                               =>  'member_member_logout',
            'entry_delete'                      =>  'delete_entries_start',
            'entry_multi_update'           =>  'update_multi_entries_loop',
            'entry_publish_update'        =>  'entry_submission_ready',
            'template_edit'                    =>  'update_template_end',
            'member_create'                 =>  'cp_members_member_create',
            'member_delete'                 =>  'member_delete',
            'member_edit'                      =>  'member_update_end'
            );

        foreach ($hooks as $method => $hook)
        {
            $priority = 10;

            $data = array(
                'class'     => __CLASS__,
                'method'    => $method,
                'hook'      => $hook,
                'settings'  => '',
                'priority'  => $priority,
                'version'   => $this->version,
                'enabled'   => 'y'
                );
            $this->EE->db->insert('extensions', $data);
        }


    }



    /**
     * Disable Extension
     *
     * This method removes information from the exp_extensions table
     *
     * @return void
     */
    function disable_extension()
    {

        $this->EE->load->dbforge();

        // Since this is a log table, let's leave it in place when the add-on is deactivated.
        //$this->EE->dbforge->drop_table('audit_log');

        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->delete('extensions');
    }

    /**
     * Update Extension
     *
     * @access     public
     * @return     void
     */
    public function update_extension($current_version = '')
    {
        // -------------------------------------
        //  Same version? Bail out
        // -------------------------------------

        if ($current_version == '' OR (version_compare($current_version, $this->version) === 0) )
        {
            return FALSE;
        }

        $this->EE->load->dbforge();

        // Enable all available types with this update
        if (version_compare($current_version, '1.0.3', '<'))
        {
            $this->EE->load->dbforge();
            $fields = array(
                'archive'              =>  array('type'=>'varchar','constraint'=>'5')
            );
            $sql = "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_name='exp_audit_log' AND COLUMN_NAME='archive'";
            $results = $this->EE->db->query($sql);
            if ($results->num_rows()==0)
                $this->EE->dbforge->add_column('audit_log', $fields);
        }
        if (version_compare($current_version, '1.0.5', '<'))
        {
            $hooks = array(
                'member_edit'                      =>  'member_update_end'
                );

            foreach ($hooks as $method => $hook)
            {
                $priority = 10;

                $data = array(
                    'class'     => __CLASS__,
                    'method'    => $method,
                    'hook'      => $hook,
                    'settings'  => '',
                    'priority'  => $priority,
                    'version'   => $this->version,
                    'enabled'   => 'y'
                    );
                $this->EE->db->insert('extensions', $data);
            }
        }

        // -------------------------------------
        //  Update version number and new settings
        // -------------------------------------

        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->update('extensions', array(
            'version' => $this->version
        ));
    }
    // END
}
// END CLASS

/* End of file ext.audit.php */
/* Location: ./system/third_party/audit/ext.audit.php */