<?php
/**
 * Messages Module
 *
 * @package modules
 * @copyright (C) copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Messages Module
 * @link http://xaraya.com/index.php/release/6.html
 * @author XarayaGeek
 */
/**
 * Get the list of groups this user can send to
 * @return array		$sendtogroups the IDs of groups this user can send to
 */

sys::import('modules.messages.xarincludes.defines');

    function messages_userapi_get_sendtogroups($args)
    {
        extract($args);

        if (!isset($currentuser)) {
            $currentuser = xarUser::getVar('id');
        }

        // First we get all the parents of the current user
        sys::import('xaraya.structures.query');
        $xartable = xarDB::getTables();
        $q = new Query('SELECT');
        $q->addtable($xartable['roles'], 'r');
        $q->addtable($xartable['rolemembers'], 'rm');
        $q->join('r.id', 'rm.role_id');

        $q->addfield('rm.parent_id');
        $q->eq('id', $currentuser);

        if (!$q->run()) {
            return;
        }
        $parents =  $q->output();

        // Find the groups these parents can send to
        $sendtogroups = [];
        foreach ($parents as $parent) {
            $allowedgroups = unserialize(xarModItemVars::get('messages', "allowedsendmessages", $parent['parent_id']));
            if (!empty($allowedgroups)) {
                foreach ($allowedgroups as $allowedgroup) {
                    $sendtogroups[$allowedgroup] = $allowedgroup;
                }
            }
        }

        return $sendtogroups;
    }
