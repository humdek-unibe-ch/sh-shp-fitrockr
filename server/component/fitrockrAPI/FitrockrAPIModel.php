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

    /**
     * Get daily summaries for a user. Execute curl request and prepare the data in a format to be saved as uploadTable
     * @param int $id_user
     * Selfhelp user id
     * @param string $transactionBy
     * Who initiated the action
     * @return bool
     * Return the result
     */
    public function get_daily_summaries($id_users, $transactionBy = transactionBy_by_user)
    {
        $fitrockr_user = $this->get_fitrockr_user($id_users);
        if (!$fitrockr_user || !isset($fitrockr_user['id_fitrockr'])) {
            // fitrockr user does not exists
            return false;
        }
        $get_params = array(
            "startDate" => (isset($this->fitrockr_settings['fitrockr_start_date']) && $this->fitrockr_settings['fitrockr_start_date'] != '' ? $this->fitrockr_settings['fitrockr_start_date'] : date("Y-m-d")),
            "endDate" => date("Y-m-d") //current date
        );
        $url = str_replace('FITROCKR_USER_ID', $fitrockr_user['id_fitrockr'], FITROCKR_URL_DAILY_SUMMARIES) . http_build_query($get_params);
        $data = array(
            "request_type" => "GET",
            "URL" => $url,
            "header" => array(
                "Content-Type: application/json",
                "X-API-Key: " . $this->fitrockr_settings['fitrockr_api_key'],
                "X-Tenant: " . $this->fitrockr_settings['fitrockr_api_tenant']
            )
        );
        $res = $this->execute_curl_call($data);
        if ($res) {
            if (isset($res['status'])) {
                // if status is set there is some error
                return false;
            }
            $selected_user = $this->get_user($this->fetch_user($id_users));
            foreach ($res as $key => $value) {
                $res[$key]['code'] = $selected_user['code'];
                $res[$key]['id_users'] = $fitrockr_user['id_users'];
                $date = date($value['date']['year'] . '-' . $value['date']['month'] . '-' . $value['date']['day']);
                $res[$key]['date'] = $date;
            }
            return $this->save_fitrockr_data(FITROCKR_DAILY_SUMMARIES, $transactionBy, $fitrockr_user['id_fitrockr'], $res);
        }
        return false;
    }

    /**
     * Create fitrockr user in fitrockr system and assign the fitrockr id to selfhelp for making the link between these users
     */
    public function create_fitrockr_user()
    {
        if ($this->get_fitrockr_user()) {
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
                    "email" => $selected_user['code'] . '@unibe.ch',
                    "yearOfBirth" => isset($_POST['year']['value']) ? $_POST['year']['value'] : 1900,
                    "height" => isset($_POST['height']['value'])?$_POST['height']['value'] : 9999,
                    "heightUOM" => null,
                    "weight" => isset($_POST['weight']['value'])?$_POST['weight']['value'] : 9999,
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

    /**
     * Get activities for a user. Execute curl request and prepare the data in a format to be saved as uploadTable
     * @param int $id_user
     * Selfhelp user id
     * @param string $transactionBy
     * Who initiated the action
     * @return bool
     * Return the result
     */
    public function get_activities($id_users, $transactionBy = transactionBy_by_user)
    {
        $fitrockr_user = $this->get_fitrockr_user($id_users);
        if (!$fitrockr_user || !isset($fitrockr_user['id_fitrockr'])) {
            // fitrockr user does not exists
            return false;
        }
        $get_params = array(
            "startDate" => (isset($this->fitrockr_settings['fitrockr_start_date']) && $this->fitrockr_settings['fitrockr_start_date'] != '' ? $this->fitrockr_settings['fitrockr_start_date'] : date("Y-m-d")),
            "endDate" => date("Y-m-d") //current date
        );
        $url = str_replace('FITROCKR_USER_ID', $fitrockr_user['id_fitrockr'], FITROCKR_URL_ACTIVITIES) . http_build_query($get_params);
        $data = array(
            "request_type" => "GET",
            "URL" => $url,
            "header" => array(
                "Content-Type: application/json",
                "X-API-Key: " . $this->fitrockr_settings['fitrockr_api_key'],
                "X-Tenant: " . $this->fitrockr_settings['fitrockr_api_tenant']
            )
        );
        $res = $this->execute_curl_call($data);
        if ($res) {
            if (isset($res['status'])) {
                // if status is set there is some error
                return false;
            }
            $selected_user = $this->get_user($this->fetch_user($id_users));
            $dates_with_activity = array();
            $fitrockr_activity_duration = isset($this->fitrockr_settings['fitrockr_activity_duration']) && $this->fitrockr_settings['fitrockr_activity_duration'] != '' ? $this->fitrockr_settings['fitrockr_activity_duration'] : 0;
            foreach ($res as $key => $value) {
                $res[$key]['code'] = $selected_user['code'];
                $res[$key]['id_users'] = $fitrockr_user['id_users'];
                $startDate = date($value['startDate']['date']['year'] . '-' . $value['startDate']['date']['month'] . '-' . $value['startDate']['date']['day'] .
                    ' ' . $value['startDate']['time']['hour'] . ':' . $value['startDate']['time']['minute'] . ':' . $value['startDate']['time']['second']);
                $endDate = date($value['endDate']['date']['year'] . '-' . $value['endDate']['date']['month'] . '-' . $value['endDate']['date']['day'] .
                    ' ' . $value['endDate']['time']['hour'] . ':' . $value['endDate']['time']['minute'] . ':' . $value['endDate']['time']['second']);
                $res[$key]['startDate'] = $startDate;
                $res[$key]['endDate'] = $endDate;
                $date = date($value['startDate']['date']['year'] . '-' . $value['startDate']['date']['month'] . '-' . $value['startDate']['date']['day']);
                $date = date('Y-m-d H:i:s', strtotime($startDate . ' -' . ($this->fitrockr_settings['fitrockr_activity_buffer_time'] ? $this->fitrockr_settings['fitrockr_activity_buffer_time'] : 0) . ' hours'));
                $res[$key]['date_time'] = $date;
                $res[$key]['date'] = (new DateTime($date))->format('Y-m-d');
                $activity = array(
                    "code" => $selected_user['code'],
                    "id_users" => $fitrockr_user['id_users'],
                    "userId" => $fitrockr_user['id_fitrockr'],
                    "date" => $res[$key]['date'],
                    "duration" => $res[$key]['duration'],
                    "activity_level" => (($res[$key]['duration'] >= ($fitrockr_activity_duration * 60)) ? 2 : 1)
                );
                if (isset($dates_with_activity[$activity['date']]) && $dates_with_activity[$activity['date']] == $activity['date']) {
                    // the date has more than 1 activity; we need the one with the highest duration
                    if ($activity['duration'] > $dates_with_activity[$activity['date']]['duration']) {
                        $dates_with_activity[$activity['date']]['duration'] = $activity['duration']; // update it with the higher duration
                    }
                } else {
                    $dates_with_activity[$activity['date']] = $activity;
                }
            }

            $calced_activities = array();
            if (count($res) > 0) {
                $first_entry_date = $res[count($res) - 1]['date'];
                $days = (new DateTime(date("Y-m-d")))->diff(new DateTime($first_entry_date))->days;
                for ($i = 0; $i < $days + 1; $i++) {
                    $check_date = date('Y-m-d', strtotime($first_entry_date . ' +' . $i . ' day'));
                    if (isset($dates_with_activity[$check_date])) {
                        $act = $dates_with_activity[$check_date];
                        $act['day'] = $i + 1;
                        $calced_activities[] = $act;
                    } else {
                        $calced_activities[] = array(
                            "code" => $selected_user['code'],
                            "id_users" => $fitrockr_user['id_users'],
                            "userId" => $fitrockr_user['id_fitrockr'],
                            "date" => $check_date,
                            "activity_level" => 0,
                            "day" => $i + 1
                        );
                    }
                }
            }

            $save_fitrockr_activities_result = $this->save_fitrockr_data(FITROCKR_ACTIVITIES, $transactionBy, $fitrockr_user['id_fitrockr'], $res);
            return $save_fitrockr_activities_result && $this->save_fitrockr_data(FITROCKR_ACTIVITIES_SUMMARY, $transactionBy, $fitrockr_user['id_fitrockr'], $calced_activities);
        }
        return false;
    }

    /**
     * Pull all fitrockr data for all the users
     * @param string $transactionBy
     * Who initiated the action
     */
    public function pull_data_all_users($transactionBy)
    {
        if (isset($this->fitrockr_settings['fitrockr_pull_data']) && $this->fitrockr_settings['fitrockr_pull_data']) {
            $fitrockr_users = $this->fetch_fitrockr_users();
            foreach ($fitrockr_users as $key => $user) {
                $this->get_daily_summaries($user['id_users'], $transactionBy);
                $this->get_activities($user['id_users'], $transactionBy);
            }
        } else {
            $this->transaction->add_transaction(
                transactionTypes_insert,
                $transactionBy,
                null,
                $this->transaction::TABLE_uploadTables,
                null,
                "Disabled",
                'Fitrockr - pulling data is disabled'
            );
        }
    }
}
