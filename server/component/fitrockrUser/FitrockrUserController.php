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
        if (isset($_POST["fitrockr_user_id"])) {
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
        }
    }

    /* Public Methods *********************************************************/
}
?>
