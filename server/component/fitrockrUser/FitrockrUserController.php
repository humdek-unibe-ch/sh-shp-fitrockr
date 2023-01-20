<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseController.php";
/**
 * The controller class of the FotrockrsUser component.
 */
class FitrockrUserController extends BaseController
{
    /* Private Properties *****************************************************/


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->proccess_data();
    }

    /* Private Methods *********************************************************/

    private function proccess_data()
    {
        if (isset($_POST["fitrockr_user_id"]) && $this->model->get_mode() == FITROCKR_USER_UPDATE) {
            if ($this->model->save_fitrockr_user(
                array(
                    "fitrockr_user_id" => $_POST["fitrockr_user_id"]
                )
            )) {
                $this->success = true;
                $this->success_msgs[] = "The user was updated!";
            } else {
                $this->fail = true;
                $this->error_msgs[] = "Failed to save the user.";
            }
        } else if ($this->model->get_mode() == FITROCKR_USER_PULL_DATE) {
            if (isset($_POST[FITROCKR_DAILY_SUMMARIES]) || isset($_POST[FITROCKR_ACTIVITIES])) {
                $api = new FitrockrAPIModel($this->model->get_services(), array("uid" => $this->model->get_uid()));
            } else {
                $this->fail = true;
                $this->error_msgs[] = "Please select which data should be pulled!";
            }
            if (isset($_POST[FITROCKR_DAILY_SUMMARIES])) {
                if ($api->get_daily_summaries($this->model->get_uid())) {
                    $this->success = true;
                    $this->success_msgs[] = "The daily summaries were pulled and updated!";
                } else {
                    $this->fail = true;
                    $this->error_msgs[] = "Error while updating the daily summaries!";
                }
            }
            if (isset($_POST[FITROCKR_ACTIVITIES])) {
                if ($api->get_activities($this->model->get_uid())) {
                    $this->success = true;
                    $this->success_msgs[] = "The user activities were pulled and updated!";
                } else {
                    $this->fail = true;
                    $this->error_msgs[] = "Error while updating the user activities!";
                }
            }
        }
    }

    /* Public Methods *********************************************************/
}
?>
