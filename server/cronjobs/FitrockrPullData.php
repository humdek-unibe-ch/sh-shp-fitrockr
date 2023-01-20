<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../../../service/Services.php";
require_once __DIR__ . "/../../../../service/PageDb.php";
require_once __DIR__ . "/../../../../service/Transaction.php";
require_once __DIR__ . "/../component/fitrockrAPI/FitrockrAPIModel.php";
require_once __DIR__ . "/../service/globals.php";

/**
 * SETUP
 * Make the script executable:  chmod +x
 * Cronjob (Pull Fitrockr user data every hour and execute them if there any) 0 * * * * php /home/user/selfhelp/server/plugins/fitrockr/server/cronjobs/class FitrockrPullData.php 
 */

/**
 * ScheduledJobsQueue class. It is scheduled on a cronjob and it is executed on given time. It checks for mails
 * that should be send within the time and schedule events for them.
 * TEST:
 * php --define apc.enable_cli=1 FitrockrPullData.php
 */
class FitrockrPullData
{

    /**
     * The db instance which grants access to the DB.
     */
    private $db = null;

    /**
     * The transaction instance which logs to the DB.
     */
    private $transaction = null;

    /**
     * Fitrockr API model
     */
    private $api;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->db = new PageDb(DBSERVER, DBNAME, DBUSER, DBPW);
        $this->transaction = new Transaction($this->db);
        $this->api = new FitrockrAPIModel(new Services(false), array("uid" => null));
    }

    /**
     * Check the mailing queue and send the mails if there are mails in the queue which should be sent
     */
    public function pull_data_all_users()
    {
        $debug_start_time = microtime(true);
        $this->api->pull_data_all_users(transactionBy_by_cron_job);
        $this->transaction->add_transaction(
            transactionTypes_insert,
            transactionBy_by_cron_job,
            null,
            $this->transaction::TABLE_uploadTables,
            null,
            "",
            'Fitrockr cronjob executed for: ' . (microtime(true) - $debug_start_time)
        );
    }
}

$fitrockrPullData = new FitrockrPullData();
$fitrockrPullData->pull_data_all_users();

?>
