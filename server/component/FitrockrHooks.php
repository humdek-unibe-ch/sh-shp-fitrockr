<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../component/BaseHooks.php";
require_once __DIR__ . "/../../../../component/style/BaseStyleComponent.php";
require_once __DIR__ . "/fitrockrAPI/FitrockrAPIModel.php";

/**
 * The class to define the hooks for the plugin.
 */
class FitrockrHooks extends BaseHooks
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the hooks.
     * @param object $services
     *  The service handler instance which holds all services
     * @param object $params
     *  Various params
     */
    public function __construct($services, $params = array())
    {
        parent::__construct($services, $params);
    }

    /* Private Methods *********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the card to send activation email to the user.
     */
    public function output_view_fitrockr_user()
    {
        new FitrockrUserComponent($this->services, array("uid" => $this->router->get_param_by_name('uid')));
        $api = new FitrockrAPIModel($this->services, array("uid" => $this->router->get_param_by_name('uid')));
        $api->get_daily_summaries($this->router->get_param_by_name('uid'));
        $api->get_activities($this->router->get_param_by_name('uid'));
    }

    /**
     * Render edit mode for Fitrockr user
     * @param object $args
     * Params passed to the method
     */
    public function output_edit_fitrockr_user($args)
    {
        // $field = $this->get_param_by_name($args, 'field');

        // $mode = $args["hookedClassInstance"]->mode;
        $mode = $this->get_private_property(array(
            "hookedClassInstance" => $args['hookedClassInstance'],
            "propertyName" => "mode"
        ));
        if ($mode == FITROCKR_UPDATE_USER) {
            new FitrockrUserComponent($this->services, array(
                "uid" => $this->router->get_param_by_name('uid'),
                "mode" => $mode
            ));
        } else {
            $this->execute_private_method($args);
        }
    }

    /**
     * Check if the user has access to edit Fitrockr user
     * @param object $args
     * Params passed to the method
     * @return bool 
     * Return true if the user has access or false if they dont
     */
    public function has_access($args)
    {
        $mode = $this->get_private_property(array(
            "hookedClassInstance" => $args['hookedClassInstance'],
            "propertyName" => "mode"
        ));
        if (in_array($mode, array(FITROCKR_UPDATE_USER))) {
            $args['methodName'] = 'has_access';
            return $this->execute_parent_method($args);
        } else {
            return $this->execute_private_method($args);
        }
    }

    /**
     * Check if the user has access to edit Fitrockr user
     * @param object $args
     * Params passed to the method
     * @return bool 
     * Return true if the user has access or false if they dont
     */
    public function create_fitrockr_user($args)
    {
        $res = $this->execute_private_method($args);
        if($res){
            $api = new FitrockrAPIModel($this->services, array("uid" => $this->router->get_param_by_name('uid')));
            $api->create_fitrockr_user();
        }
        return $res;
    }

    /**
     * Clean Fitrockr user data
     *
     * @param object $args
     * Params passed to the method
     * @return bool
     *  True on success, false on failure.
     */
    public function clear_fitrockr_user_data($args)
    {
        $res = $this->execute_private_method($args);
        if(!$res){
            // something went wrong with the base clear function or no permissions for the operation
            return;
        }
        $uid = $this->get_param_by_name($args, 'uid');
        $res = $this->db->remove_by_fk('users_fitrockr', 'id_users', $uid);
        $this->transaction->add_transaction(transactionTypes_delete, transactionBy_by_user, $_SESSION['id_user'], TABLE_USERS_FITROCKR, $uid, false, json_encode($res));
        return $res;
    }
}
?>
