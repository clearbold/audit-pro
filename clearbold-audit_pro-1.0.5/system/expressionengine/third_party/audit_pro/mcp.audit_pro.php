<?php if (! defined('BASEPATH')) exit('Invalid file request');

/**
 * Audit Pro Module CP Class for EE2
 *
 * @package   Audit Pro
 * @author    Mark J. Reeves <mjr@clearbold.com>
 * @copyright Copyright (c) 2013 Clearbold, LLC
 */
class Audit_pro_mcp {

    function __construct()
    {
        $this->EE =& get_instance();
        /**
        * PRO
        **/
        $this->base_url = BASE.AMP.'C=addons_modules&amp;M=show_module_cp&amp;module='.'audit_pro';
        /**
        * /PRO
        **/
    }

    function index()
    {
        $this->EE->view->cp_page_title =  lang('audit_pro_module_name');

        $this->EE->load->library('table');
        $this->EE->table->set_columns(array(
            'site_id' => array('header' => '<span style="white-space: nowrap;">Site ID</span>', 'sort' => false),
            'member_id' => array('header' => '<span style="white-space: nowrap;">Member ID</span>', 'sort' => false),
            'username' => array('header' => '<span style="white-space: nowrap;">Username</span>', 'sort' => false),
            'group_name' => array('header' => '<span style="white-space: nowrap;">Member Group</span>', 'sort' => false),
            'item_type' => array('header' => '<span style="white-space: nowrap;">Item Type</span>', 'sort' => false),
            'item_id' => array('header' => '<span style="white-space: nowrap;">Item ID</span>', 'sort' => false),
            'item_title' => array('header' => '<span style="white-space: nowrap;">Item Name</span>', 'sort' => false),
            'ip_address' => array('header' => '<span style="white-space: nowrap;">IP Address</span>', 'sort' => false),
            'timestamp' => array('header' => '<span style="white-space: nowrap;">Timestamp</span>', 'sort' => false),
            'user_agent' => array('header' => '<span style="white-space: nowrap;">User Agent</span>', 'sort' => false)
        ));
        /**
        * PRO
        **/
        $this->EE->table->set_base_url('C=addons_modules&M=show_module_cp&module=audit_pro');
        /**
        * PRO
        **/
        $data = $this->EE->table->datasource('_datasource');
        $data['archive_link'] = $this->base_url.AMP.'method=archive_log_data';
        $data['notifications_link'] = $this->base_url.AMP.'method=manage_notifications';
        $data['base_url'] = $this->base_url;
        $data['keywords'] = '';
        if (isset($_POST['audit_pro_keywords']))
            $data['keywords'] = $_POST['audit_pro_keywords'];
        $this->EE->cp->load_package_js('script');
        return $this->EE->load->view('mcp_audit_pro_header', $data, TRUE);
        // . $data['table_html'] . $data['pagination_html'];
    }

    function _datasource($state)
    {
        //var_dump($state['offset']);
        $offset = 0;
        if ($state['offset'] != 0)
            $offset = (int)$state['offset'];
        $per_page = 20;
        $base_url = $this->_full_url();
        $results;
        $count_results;
        if (isset($_POST['audit_pro_keywords']) or isset($_GET['audit_pro_keywords']))
        {
            $keywords = ( isset($_POST['audit_pro_keywords'])) ? $_POST['audit_pro_keywords'] : $_GET['audit_pro_keywords'];
            $base_url = $this->_full_url() . AMP . 'audit_pro_keywords=' . $keywords;
            $sql = sprintf("SELECT * FROM exp_audit_log
                        WHERE member_id = %s
                        OR username LIKE '%s'
                        OR item_id = %s
                        OR item_title LIKE '%s'
                        ORDER BY timestamp desc",
                        $this->EE->db->escape((int)$keywords),
                        '%' . $this->EE->db->escape_like_str((string)$keywords) . '%',
                        $this->EE->db->escape((int)$keywords),
                        '%' . $this->EE->db->escape_like_str((string)$keywords) . '%');

            $results = $this->EE->db->query($sql." LIMIT ?,?",array($offset,$per_page));
            $count_results = $this->EE->db->query($sql);
        }
        else
        {
            $results = $this->EE->db->query("SELECT * FROM exp_audit_log ORDER BY timestamp desc LIMIT ?,?",array($offset,$per_page));
            $count_results = $this->EE->db->query("SELECT * FROM exp_audit_log ORDER BY timestamp desc");
        }

        $total_rows = $count_results->num_rows();

        $rows = array();
        foreach($results->result_array() as $row)
        {
            $site_id = $row['site_id'];
            $member_id = $row['member_id'];
            $username = $row['username'];
            $group_name = $row['group_name'];
            $item_type = '';
            switch ($row['item_type'])
            {
                case 'cp_login':
                    $item_type = 'Control Panel Login';
                    break;
                case 'cp_logout':
                    $item_type = 'Control Panel Logout';
                    break;
                case 'login':
                    $item_type = 'Front-end Login';
                    break;
                case 'logout':
                    $item_type = 'Front-end Logout';
                    break;
                case 'entry_delete':
                    $item_type = 'Entry Deleted';
                    break;
                case 'entry_update':
                    $item_type = 'Entry Updated';
                    break;
                case 'new_entry':
                    $item_type = 'Entry Created';
                    break;
                case 'template_edit':
                    $item_type = 'Template Edited';
                    break;
                case 'member_create':
                    $item_type = 'Member Created';
                    break;
                case 'member_delete':
                    $item_type = 'Member Deleted';
                    break;
                case 'member_edit':
                    $item_type = 'Member Edited';
                    break;
            }
            $item_id = $row['item_id'];
            $item_title = $row['item_title'];
            $ip_address = $row['ip_address'];
            $timestamp = $this->EE->localize->human_time($row['timestamp']);
            $user_agent = $row['user_agent'];

            $rows[] = array(
                'site_id' => $site_id,
                'member_id' => $member_id,
                'username' => $username,
                'group_name' => $group_name,
                'item_type' => $item_type,
                'item_id' => $item_id,
                'item_title' => $item_title,
                'ip_address' => $ip_address,
                'timestamp' => $timestamp,
                'user_agent' => $user_agent
            );
        }
        /*echo $offset . '<br />';
        echo $base_url . '<br />';
        echo $per_page . '<br />';
        echo $total_rows . '<br />';*/
        return array(
            'rows' => $rows,
            'pagination' => array(
                'page_query_string' => TRUE,
                'base_url'    => $base_url,
                'per_page'   => $per_page,
                'total_rows' => $total_rows
            )
        );
    }

    /**
    * PRO
    **/
    function archive_log_data()
    {
        $data = array('archive' => 1);
        $sql = $this->EE->db->update_string('exp_audit_log', $data, "TRUE = TRUE");
        $this->EE->db->query($sql);

        $sql = "INSERT INTO exp_audit_log_archive SELECT * from exp_audit_log WHERE archive = '1'";
        $this->EE->db->query($sql);

        $sql = "DELETE FROM exp_audit_log WHERE archive = 1";
        $this->EE->db->query($sql);

        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=audit_pro');
    }
    function manage_notifications()
    {
        $vars = array();
        $this->EE->view->cp_page_title =  lang('Manage Notifications');
        $this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=audit_pro', $this->EE->lang->line('audit_pro_module_name'));
        $data['channels'] = $this->_channels();
        $data['members'] = $this->_members();
        $data['groups'] = $this-> _groups();
        $data['form_url'] = $this->base_url.AMP.'method=save_notifications';
        $data['member_email'] = $this->EE->session->userdata["email"];
        $data['base_url'] = $this->base_url;
        $forms = array();
        $sql = "SELECT * FROM exp_audit_notifications";
        $results = $this->EE->db->query($sql);
        if ($results->num_rows==0)
        {
            for ($i = 0; $i < 0; $i++)
            {
                $forms[$i]['email_address'] = $this->EE->session->userdata["email"];
                $forms[$i]['is_sms'] = '';
                $forms[$i]['username'] = '';
                $forms[$i]['group'] = '';
                $forms[$i]['item_type'] = '';
                $forms[$i]['channel'] = '';
                $forms[$i]['item_id'] = '';
            }
        }
        else
        {
            $f = 0;
            foreach ($results->result_array() as $form)
            {
                $forms[$f]['email_address'] = $form['email_address'];
                $forms[$f]['is_sms'] = $form['is_sms'];
                $forms[$f]['username'] = $form['member_id'];
                $forms[$f]['group'] = $form['group_id'];
                $forms[$f]['item_type'] = $form['item_type'];
                $forms[$f]['channel'] = $form['channel_id'];
                $forms[$f]['item_id'] = ($form['item_id']==0) ? '' : $form['item_id'];
                $f++;
            }
        }
        $data['forms'] = $forms;
        return $this->EE->load->view('mcp_manage_notifications', $data, TRUE);
    }
    function save_notifications()
    {
        $this->EE->load->helper('form');
        $this->EE->load->library('form_validation');

        $num_forms = 0;
        if (isset($_POST['email_address']))
            $num_forms = count($_POST['email_address']);
        $forms = array();
        for ($i = 0; $i < $num_forms; $i++)
        {
            $email = $this->EE->input->post('email_address');
            $is_sms = $this->EE->input->post('is_sms');
            $username = $this->EE->input->post('username');
            $group = $this->EE->input->post('group');
            $item_type = $this->EE->input->post('item_type');
            $channel = $this->EE->input->post('channel');
            $item_id = $this->EE->input->post('item_id');
            $forms[$i]['email_address'] = $email[$i];
            $forms[$i]['is_sms'] = $is_sms[$i];
            $forms[$i]['username'] = $username[$i];
            $forms[$i]['group'] = $group[$i];
            $forms[$i]['item_type'] = $item_type[$i];
            $forms[$i]['channel'] = $channel[$i];
            $forms[$i]['item_id'] = $item_id[$i];
        }

        $this->EE->form_validation->set_rules("email_address[]", 'Email', 'trim|required|valid_email');

        if ($this->EE->form_validation->run() == FALSE && $num_forms > 0)
        {
            $data['channels'] = $this->_channels();
            $data['members'] = $this->_members();
            $data['groups'] = $this->_groups();
            $data['form_url'] = $this->base_url.AMP.'method=save_notifications';
            $data['member_email'] = $this->EE->session->userdata["email"];
            $data['errors'] = validation_errors();
            $data['forms'] = $forms;
            $data['base_url'] = $this->base_url;

            return $this->EE->load->view('mcp_manage_notifications', $data, TRUE);
        }
        else
        {
            // Purge current notifications and re-insert all forms
            $sql = "TRUNCATE TABLE exp_audit_notifications";
            $this->EE->db->query($sql);

            foreach ($forms as $form)
            {
                $data = array(
                    'email_address'     => $form['email_address'],
                    'is_sms'                => ($form['is_sms'] == '1') ? 1 : 0,
                    'member_id'         => (int)$form['username'],
                    'group_id'         => (int)$form['group'],
                    'item_type'           => $form['item_type'],
                    'channel_id'              => $form['channel'],
                    'item_id'               => $form['item_id']
                );
                $this->EE->db->insert('exp_audit_notifications', $data);
            }
            $this->EE->session->set_flashdata('message_success', 'Notifications saved');
            $this->EE->functions->redirect($this->base_url.AMP.'method=manage_notifications');
        }
    }
    function _channels()
    {
        $sql = "SELECT channel_id, channel_title FROM exp_channels";
        $results = $this->EE->db->query($sql);
        return $results;
    }
    function _members()
    {
        $sql = "SELECT member_id, username FROM exp_members";
        $results = $this->EE->db->query($sql);
        return $results;
    }
    function _groups()
    {
        $sql = "SELECT group_id, group_title FROM exp_member_groups";
        $results = $this->EE->db->query($sql);
        return $results;
    }
    /**
    * /PRO
    **/

    function _full_url()
    {
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
        $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);

        $non_offset_uri = explode('&tbl_offset=',$_SERVER['REQUEST_URI']);
        $non_offset = $non_offset_uri[0];

        return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $non_offset;
    }
}
// END CLASS

/* End of file mcp.module_name.php */
/* Location: ./system/expressionengine/third_party/modules/audit_pro/mcp.audit_pro.php */
