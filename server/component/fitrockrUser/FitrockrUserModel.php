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
     * The fitrockr_user_data.
     */
    private $fitrockr_user;

    /**
     * Selected user id
     */
    private $uid;

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
    }

    /* Private Methods *********************************************************/

    /**
     * Fetch the fitrockr user info from DB
     * @return object
     * Return the fitrockr user object
     */
    private function fetch_fitrockr_user()
    {
        return $this->db->query_db_first('SELECT * FROM users_fitrockr WHERE `id_users` = :id_users', array(":id_users" => $this->uid));
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
        if ($this->fitrockr_user_data) {
            return $this->update_fitrockr_user($user_data);
        } else {
            return $this->insert_fitrockr_user($user_data);
        };
        return true;
    }

    /**
     * Get the fitrockr user
     * @return object
     * Return the info for the fitrockr user
     */
    public function get_fitrockr_user()
    {
        return $this->fetch_fitrockr_user();
    }

    public function save_daily_summaries($fitrockr_user_id, $data)
    {

        $table_name = FITROCKR_DAILY_SUMMARIES; //the table where we will save the 
        $id_table = $this->services->get_user_input()->get_form_id($table_name, FORM_STATIC);
        $this->db->begin_transaction();
        if ($id_table) {
            // if the table exists, delete all the data for that user in that table
            // we will insert all the data again
            $sql= "DELETE FROM uploadRows
            WHERE id IN (SELECT id_uploadRows FROM (SELECT id_uploadRows
            FROM uploadCells c
            INNER JOIN uploadRows r ON (r.id = c.id_uploadRows)
            INNER JOIN uploadTables t ON (t.id = r.id_uploadTables)
            WHERE c.`value` = :fitrockr_user_id AND t.`name` = :table_name) tmp)";
            $this->db->execute_update_db($sql, array(
                ":fitrockr_user_id" => $fitrockr_user_id,
                ":table_name" => FITROCKR_DAILY_SUMMARIES
            ));
            
        }
        try {   
            if (!$id_table) {
                // does not exists yet; try to create it
                $id_table = $this->db->insert("uploadTables", array(
                    "name" => $table_name
                ));
            }
            if (!$id_table) {
                $this->db->rollback();
                return "postprocess: failed to create new data table";
            } else {
                if ($this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_qualtrics_callback, null, $this->transaction::TABLE_uploadTables, $id_table) === false) {
                    $this->db->rollback();
                    return false;
                }

                foreach ($data as $key => $row) {
                    $id_row = $this->db->insert("uploadRows", array(
                        "id_uploadTables" => $id_table
                    ));
                    if (!$id_row) {
                        $this->db->rollback();
                        return "postprocess: failed to add table rows";
                    }
                    foreach ($row as $col => $value) {
                        $id_col = $this->db->insert("uploadCols", array(
                            "name" => $col,
                            "id_uploadTables" => $id_table
                        ));
                        if (!$id_col) {
                            $this->db->rollback();
                            return "postprocess: failed to add table cols";
                        }
                        $res = $this->db->insert(
                            "uploadCells",
                            array(
                                "id_uploadRows" => $id_row,
                                "id_uploadCols" => $id_col,
                                "value" => $value
                            )
                        );
                        if (!$res) {
                            $this->db->rollback();
                            return "postprocess: failed to add data values";
                        }
                    }
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}
