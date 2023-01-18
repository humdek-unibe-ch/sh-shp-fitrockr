<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../fitrockrUser/FitrockrUserModel.php";
/**
 * This class is used to prepare all API calls related to Fitrockr
 */
class FitrockrAPIModel extends FitrockrUserModel
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
    public function __construct($services, $params)
    {
        parent::__construct($services, $params);
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
     */
    public function create_fitrockr_user()
    {
        if ($this->get_fitrockr_user_data()) {
            // fitrockr usr already created
            return;
        }
        $selected_user = $this->get_selected_user();
        if (isset($this->fitrockr_settings['fitrockr_api_key']) && isset($this->fitrockr_settings['fitrockr_api_tenant'])) {
            if (isset($this->fitrockr_settings['fitrockr_create_user']) && $this->fitrockr_settings['fitrockr_create_user']) {                
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
                    $this->insert_fitrockr_user(array("fitrockr_user_id" => $res['id']));
                    $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_user, $selected_user['id'], TABLE_USERS_FITROCKR, $selected_user['id'], false, "Assign Fitrockr id: " . $res['id'] . " to Selfhelp user: " . $selected_user['id']);
                } else {
                    $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_user, $selected_user['id'], TABLE_USERS_FITROCKR, null, false, "Error: " . json_encode($res));
                }
            }
        } else {
            // Fitrcockr settings are not set
            $this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_user, $selected_user['id'], TABLE_USERS_FITROCKR, null, false, "Error: Fitrockr settings are not assigned");
        }
    }
}
