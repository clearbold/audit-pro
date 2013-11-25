<?php if (! defined('BASEPATH')) exit('Invalid file request');

/**
 * Audit Update Class
 *
 * @package   Audit Pro
 * @author    Mark J. Reeves <mjr@clearbold.com>
 * @copyright Copyright (c) 2013 Clearbold, LLC
 */
class Audit_pro_upd {

    var $version = '1.0.5';

    /**
     * Constructor
     */
    function __construct()
    {
        $this->EE =& get_instance();
    }

    // --------------------------------------------------------------------

    /**
     * Install
     */
    function install()
    {
        $this->EE->load->dbforge();
        $this->EE->db->insert('modules', array(
            'module_name'        => 'Audit_pro',
            'module_version'     => $this->version,
            'has_cp_backend'     => 'y',
            'has_publish_fields' => 'n'
        ));
        /**
        * PRO
        **/
        /*
        CREATE TABLE `exp_audit_notifications` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `email_address` varchar(255) DEFAULT NULL,
          `is_sms` char(1) DEFAULT NULL,
          `member_id` int(10) DEFAULT NULL,
          `item_type` varchar(100) DEFAULT NULL,
          `channel_id` int(10) DEFAULT NULL,
          `item_id` int(10) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
        */

        $fields = array(
            'id'                     =>  array('type'=>'int','constraint'=>'11','unsigned'=>TRUE,'null'=>FALSE,'auto_increment'=>TRUE),
            'email_address'         =>  array('type'=>'varchar','constraint'=>'255'),
            'is_sms'         =>  array('type'=>'char','constraint'=>'1'),
            'member_id'             =>  array('type'=>'int','constraint'=>'10','unsigned'=>TRUE),
            'group_id'             =>  array('type'=>'int','constraint'=>'10','unsigned'=>TRUE),
            'item_type'         =>  array('type'=>'varchar','constraint'=>'100'),
            'channel_id'             =>  array('type'=>'int','constraint'=>'10','unsigned'=>TRUE),
            'item_id'             =>  array('type'=>'int','constraint'=>'10','unsigned'=>TRUE)
        );

        $this->EE->dbforge->add_field($fields);
        $this->EE->dbforge->add_key('id', TRUE);
        $this->EE->dbforge->create_table('audit_notifications', TRUE);

        $this->EE->db->insert('actions', array(
            'class'                     => 'Audit_pro',
            'method'                => 'archive_log_data'
        ));
        /**
        * /PRO
        **/

        return TRUE;
    }

    /**
     * Uninstall
     */
    function uninstall()
    {
        $this->EE->load->dbforge();
        $this->EE->db->where('module_name', 'Audit_pro')->delete('modules');
        $this->EE->db->where('class', 'Audit_pro')->delete('actions');

        $this->EE->dbforge->drop_table('audit_notifications');

        return TRUE;
    }

    /**
     * Update
     */
    function update($current = '')
    {
        // necessary to get EE to update the version number
        return TRUE;
    }

}