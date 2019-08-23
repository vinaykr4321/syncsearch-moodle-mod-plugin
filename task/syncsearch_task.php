<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


namespace mod_syncsearch\task;
use core\task\scheduled_task;

defined('MOODLE_INTERNAL') || die();

/**
 * Class containing the scheduled task for lti module.
 *
 * @package    mod_lti
 * @copyright  2018 Stephen Vickers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class syncsearch_task extends scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('modulename', 'mod_syncsearch');
    }

    /**
     * Run lti cron.
     */
    public function execute() {
        global $DB;

        $datasend = [];
        $pagedata = $DB->get_records_sql("select pg.content, cm.course, cm.id from mdl_page as pg right join mdl_course_modules as cm on pg.id = cm.`instance` where cm.module = 15 and pg.content like '%still-oasis%'");
        if (!empty($pagedata)) {
            foreach ($pagedata as $row) {
                $content = str_replace("iframe", "none", $row->content);
                $arr = explode('data-uploadid="',$content);
                $id = strtok($arr[1],'"');
                $datasend[] = ['course'=>$row->course, 'activity'=>$row->id,'datauploadid'=>$id];
            }
        }
        
        if (!empty($datasend)) {
            foreach ($datasend as $datas) {
                shell_exec("curl -X PUT -F 'upload[course]={$datas['course']}' -F 'upload[activity]={$datas['activity']}' -F 'key=HJArYbtHQvpvRcxQFJHjsZkABTBbljekVSTuRUFZPIAZaeXLKs' http://still-oasis-17398.herokuapp.com/uploads/{$datas['datauploadid']}.json");
            }	
        }
    }
}
