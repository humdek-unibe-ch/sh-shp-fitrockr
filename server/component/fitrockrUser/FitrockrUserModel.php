<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/user/UserModel.php";
/**
 * This class is used to prepare all data related to the FitrockrUser component such
 * that the data can easily be displayed in the view of the component.
 */
class FitrockrUserModel extends UserModel
{

    /* Private Properties *****************************************************/

    /**
     * Selected user id
     */
    private $uid;

    /**
     * Request mode
     */
    private $mode;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $params)
    {
        parent::__construct($services, $params['uid']);
        $this->uid = $params['uid'];
        $this->mode = isset($params['mode']) ? $params['mode'] : "";
    }

    /* Private Methods *********************************************************/

    /**
     * Fetch the fitrockr user info from DB
     * @param int $id_users
     * Selfhelp user id
     * @return object
     * Return the fitrockr user object
     */
    private function fetch_fitrockr_user($id_users)
    {
        return $this->db->query_db_first('SELECT * FROM users_fitrockr WHERE `id_users` = :id_users', array(":id_users" => $id_users));
    }    

    /**
     * Update Fitrockr user
     * @param object $user_data
     * The user data that we want to update
     * @return bool
     * Return true when the update is successful and false when it is not
     */
    private function update_fitrockr_user($user_data)
    {
        $res = $this->db->update_by_ids(
            'users_fitrockr',
            array(
                "id_fitrockr" => $user_data['fitrockr_user_id'],
            ),
            array("id_users" => $this->uid)
        );
        $this->transaction->add_transaction(transactionTypes_update, transactionBy_by_user, $_SESSION['id_user'], TABLE_USERS_FITROCKR, $this->uid, false, json_encode($res));
        return $res;
    }




    /* Public Methods *********************************************************/

    /**
     * Insert Fitrockr user
     * @param object $user_data
     * The user data that we want to insert
     * @return bool
     * Return true when the insert is successful and false when it is not
     */
    public function insert_fitrockr_user($user_data)
    {
        $res =  $this->db->insert(
            "users_fitrockr",
            array(
                "id_users" => $this->uid,
                "id_fitrockr" => $user_data['fitrockr_user_id']
            )
        );
        $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_user, $_SESSION['id_user'], TABLE_USERS_FITROCKR, $this->uid, false, json_encode($res));
        return $res;
    }

    /**
     * Save Fitrockr user
     * @param object $user_data
     * The user data that we want to update or insert
     * @return bool
     * Return true when the operations is successful and false when it is not
     */
    public function save_fitrockr_user($user_data)
    {
        if ($this->get_fitrockr_user($this->uid)) {
            return $this->update_fitrockr_user($user_data);
        } else {
            return $this->insert_fitrockr_user($user_data);
        };
        return true;
    }

    /**
     * Get the fitrockr user
     * @param int $id_users
     * Selfhelp user id
     * @return object
     * Return the info for the fitrockr user
     */
    public function get_fitrockr_user($id_users = null)
    {
        if ($id_users == null) {
            $id_users = $this->get_uid();
        }
        return $this->fetch_fitrockr_user($id_users);
    }

    /**
     * Save data from fitrockr request as dataTable
     * @param string $table_name
     * The name of the upload table where the data will be saved
     * @param string $action_by
     * Who initiated the action
     * @param string $fitrockr_user_id
     * The fitrockr user id
     * @param object $data
     * The data that we want to save. It is a format that can be inserted as dataTable
     * @return bool
     * Return the result of the action
     */
    public function save_fitrockr_data($table_name, $action_by, $fitrockr_user_id, $data)
    {
        $id_table = $this->services->get_user_input()->get_dataTable_id($table_name);
        $this->db->begin_transaction();
        if ($id_table) {
            // if the table exists, delete all the data for that user in that table
            // we will insert all the data again
            $sql = "DELETE FROM dataRows
            WHERE id IN (SELECT id_dataRows FROM (SELECT id_dataRows
            FROM dataCells c
            INNER JOIN dataRows r ON (r.id = c.id_dataRows)
            INNER JOIN dataTables t ON (t.id = r.id_dataTables)
            WHERE c.`value` = :fitrockr_user_id AND t.`name` = :table_name) tmp)";
            $this->db->execute_update_db($sql, array(
                ":fitrockr_user_id" => $fitrockr_user_id,
                ":table_name" => $table_name
            ));
        }
        try {
            $res = $this->user_input->save_data($action_by, $table_name, $data);
            $this->db->commit();
            return $res;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Get the request mode if set
     * @return string
     * the request mode
     */
    public function get_mode(){
        return $this->mode;
    }

    /**
     * Fetch fitrockr users
     * @return object
     * Return the fitrockr users
     */
    public function fetch_fitrockr_users(){
        return $this->db->query_db('SELECT * FROM users_fitrockr');
    }
}
