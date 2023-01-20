<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../../component/BaseView.php";

/**
 * The view class of the asset select component.
 */
class FitrockrUserView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The component params
     */
    private $params;

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller, $params)
    {
        $this->params = $params;
        parent::__construct($model, $controller);
        $this->output_content();
    }

    /* Private Methods ********************************************************/

    private function output_view_mode()
    {
        $fitrockr_user = $this->model->get_fitrockr_user();
        $fitrockr_user_id_field = array(
            "title" => "Fitrockr ID",
            "help" => 'Fitrockr ID code which is used for linking accounts between Selfhelp and Fitrockr',
            "display" => 0,
            "css" => 'border-0',
            "children" => array(new BaseStyleComponent("rawText", array(
                "text" => (isset($fitrockr_user['id_fitrockr']) ? $fitrockr_user['id_fitrockr'] : '')
            )))
        );

        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Fitrockr User",
            "type" => "info",
            "url_edit" => $this->model->get_services()->get_router()->get_link_url("userUpdate", array("uid" => $this->params['uid'], "mode" => FITROCKR_USER_UPDATE)),
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => "",
                    "url" => '#',
                    "type" => "info",
                    "children" => array(new BaseStyleComponent("descriptionItem", $fitrockr_user_id_field))
                )),
            )
        ));
        $card->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        if (isset($this->params['mode']) && in_array($this->params['mode'], array(FITROCKR_USER_UPDATE, FITROCKR_USER_PULL_DATE))) {
            require __DIR__ . "/tpl_update_fitrockr_user.php";
        } else {
            $this->output_view_mode();
        }
    }

    public function output_content_mobile()
    {
        echo 'mobile';
    }

    /**
     * Render the alert message.
     */
    protected function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }

    /**
     * Render edit fitrockr user
     */
    public function output_update_fitrockr_user()
    {        
        $fitrockr_user = $this->model->get_fitrockr_user();
        $fitrockr_user_id_field = array(
            "title" => "Fitrockr ID",
            "help" => 'Fitrockr ID code which is used for linking accounts between Selfhelp and Fitrockr',
            "display" => 0,
            "css" => 'border-0',
            "children" => array(new BaseStyleComponent("input", array(
                "name" => "fitrockr_user_id",
                "value" => (isset($fitrockr_user['id_fitrockr']) ? $fitrockr_user['id_fitrockr'] : ''),
                "css" => "mb-3"
            )))
        );

        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Fitrockr User Data",
            "type" => "warning",
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => "Save",
                    "url" => $this->model->get_services()->get_router()->get_link_url("userUpdate", array("uid" => $this->params['uid'], "mode" => FITROCKR_USER_UPDATE)),
                    "url_cancel" => $this->model->get_link_url("userSelect", array("uid" => $this->params['uid'])),
                    "label_cancel" => "Back to the User",
                    "type" => "warning",
                    "children" => array(new BaseStyleComponent("descriptionItem", $fitrockr_user_id_field))
                )),
            )
        ));
        $card->output_content();

        $cardPullData = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Pull Fitrockr Data",
            "type" => "info",
            "children" => array(
                new BaseStyleComponent(
                    "form",
                    array(
                        "label" => "Pull Data",
                        "url" => $this->model->get_services()->get_router()->get_link_url("userUpdate", array("uid" => $this->params['uid'], "mode" => FITROCKR_USER_PULL_DATE)),
                        "type" => "info",
                        "name" => FITROCKR_USER_PULL_DATE,
                        "children" => array(
                            new BaseStyleComponent("descriptionItem", array(
                                "title" => "Daily Summaries",
                                "help" => 'If selected it will pull the daily summaries for the selected user',
                                "display" => 0,
                                "css" => 'border-0',
                                "children" => array(new BaseStyleComponent("input", array(
                                    "type_input" => "checkbox",
                                    "name" => FITROCKR_DAILY_SUMMARIES,
                                )))
                            )),
                            new BaseStyleComponent("descriptionItem", array(
                                "title" => "Activities",
                                "help" => 'If selected it will pull the activities for the selected user',
                                "display" => 0,
                                "css" => 'border-0',
                                "children" => array(new BaseStyleComponent("input", array(
                                    "type_input" => "checkbox",
                                    "name" => FITROCKR_ACTIVITIES
                                )))
                            ))


                        )
                    ),
                )
            )
        ));
        $cardPullData->output_content();
    }

    public function output_user_info(){
        $selected_user = $this->model->get_selected_user();
        echo "Code: <span><code> &nbsp;" . $selected_user['code'] . ' &nbsp;</span></code> Email: &nbsp;<span><code>' . $selected_user['email'] . '</code></span>';
    }
}
?>
