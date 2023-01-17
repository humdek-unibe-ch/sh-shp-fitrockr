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
    private $fitrockr_user_data;

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
        $this->fitrockr_user_data = $this->fetch_fitrockr_user_data();
    }

    /* Private Methods *********************************************************/

    /**
     * Fetch the fitrockr user data from DB
     * @return object
     * Return the fitrockr user object
     */
    private function fetch_fitrockr_user_data()
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
     * Get the fitrockr user data
     * @return object
     * Return the data for the fitrockr user
     */
    public function get_fitrockr_user_data(){
        $this->fitrockr_user_data = $this->fetch_fitrockr_user_data();
        return $this->fitrockr_user_data;
    }
}
