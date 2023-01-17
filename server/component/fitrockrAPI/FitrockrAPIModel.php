<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseModel.php";
/**
 * This class is used to prepare all API calls related to Fitrockr
 */
class FitrockrAPIModel extends BaseModel
{

    /* Private Properties *****************************************************/

    /**
     * The settings for the Fitrockr instance
     */
    private $fitrockr_settings;


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
        $this->fitrockr_settings = $this->db->fetch_page_info(SH_MODULE_FITROCKR);
    }

    /* Private Methods *********************************************************/



    /* Public Methods *********************************************************/

    public function test()
    {
        $data = array(
            "request_type" => "GET",
            "URL" => 'https://api.fitrockr.com/v1/users/62d50fbe98393b62303987ec/profile',
            "header" => array(
                "Content-Type: application/json",
                "X-API-Key: 2bcb7e13-f0ee-4896-9ac0-e464f4547ea8",
                "X-Tenant: unibe"
            ),
            // "post_params" => json_encode($flow)
        );
        $res = $this->execute_curl_call($data);
    }

    /**
     * Create fitrockr user in fitrockr system and assign the fitrockr id to selfhelp for making the link between these users
     * @param int $id_users
     * Selfhelp user id
     */
    public function create_fitrockr_user($id_users)
    {
        $fitrockrUserModel = new FitrockrUserModel($this->services, array("uid" => $id_users));
        if ($fitrockrUserModel->get_fitrockr_user_data()) {
            // fitrockr usr already created
            return;
        }
        if (isset($this->fitrockr_settings['fitrockr_api_key']) && isset($this->fitrockr_settings['fitrockr_api_tenant'])) {
            if (isset($this->fitrockr_settings['fitrockr_create_user']) && $this->fitrockr_settings['fitrockr_create_user']) {
                $selected_user = $fitrockrUserModel->get_selected_user();
                $post_params = array(
                    "id" => null,
                    "firstName" => $selected_user['code'],
                    "lastName" => $_POST['name'],
                    "profilePicUrl" => null,
                    "basalMetabolism" => null,
                    "gender" => $_POST['gender'] == 1 ? 'm' : 'f',
                    "country" => "CH",
                    "city" => "Bern",
                    "language" => "de",
                    "timeZone" => "GMT+1",
                    "email" => $selected_user['email'],
                    "yearOfBirth" => $_POST['year']['value'],
                    "height" => $_POST['height']['value'],
                    "heightUOM" => null,
                    "weight" => $_POST['weight']['value'],
                    "weightUOM" => null,
                    "lastSync" => null,
                    "trackerName" => null,
                    "active" => null,
                    "usualSleepStartTime" => null,
                    "usualSleepEndTime" => null,
                    "imperialUnits" => false,
                    "location" => null
                );
                $data = array(
                    "request_type" => "POST",
                    "URL" => FITROCKR_URL_CREATE_USER,
                    "header" => array(
                        "Content-Type: application/json",
                        "X-API-Key: " . $this->fitrockr_settings['fitrockr_api_key'],
                        "X-Tenant: " . $this->fitrockr_settings['fitrockr_api_tenant']
                    ),
                    "post_params" => json_encode($post_params)
                );
                $res = $this->execute_curl_call($data);
                if (isset($res['id'])) {
                    // user created successfully
                    $fitrockrUserModel->insert_fitrockr_user(array("fitrockr_user_id" => $res['id']));
                    $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_user, $id_users, TABLE_USERS_FITROCKR, $id_users, false, "Assign Fitrockr id: " . $res['id'] . " to Selfhelp user: " . $id_users);
                } else {
                    $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_user, $id_users, TABLE_USERS_FITROCKR, null, false, "Error: " . json_encode($res));
                }
            }
        } else {
            // Fitrcockr settings are not set
            $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_user, $id_users, TABLE_USERS_FITROCKR, null, false, "Error: Fitrockr settings are not assigned");
        }
    }
}
