<?php
/**
 * Messages module
 *
 * @package modules
 * @copyright (C) 2002-2007 The copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage messages
 * @link http://xaraya.com/index.php/release/6.html
 * @author Carl P. Corliss <rabbitt@xaraya.com>
 */
sys::import('modules.messages.xarincludes.defines');

/**
 * Get the number of messages sent or received by a user
 *
 * @author mikespub
 * @access public
 * @param array<mixed> $args
 * with
 *     integer    $author      the id of the author you want to count messages for, or
 *     integer    $recipient   the id of the recipient you want to count messages for
 *     bool       $unread      (optional) count unread rather than total
 *     bool       $drafts      (optional) count drafts
 * @return integer  the number of messages
 */
function messages_userapi_get_count(array $args = [], $context = null)
{
    extract($args);

    if ((!isset($author) || empty($author)) && (!isset($recipient) || empty($recipient))) {
        $msg = xarML(
            'Invalid #(1) for #(2) function #(3)() in module #(4)',
            'author/recipient',
            'userapi',
            'get_count',
            'messages'
        );
        throw new BadParameterException(null, $msg);
    }

    $dbconn = xarDB::getConn();
    $xartable = xarDB::getTables();

    $sql = "SELECT  COUNT(id) as numitems
              FROM  $xartable[messages]
             WHERE  ";

    $bindvars = [];
    if (isset($recipient)) {
        $sql .= "to_delete=? AND to_id=? AND from_status!=?";
        $bindvars[] = MESSAGES_NOTDELETED;
        $bindvars[] = (int) $recipient;
        $bindvars[] = MESSAGES_STATUS_DRAFT;
        if (isset($unread)) {
            $sql .= " AND to_status=?";
            $bindvars[] = MESSAGES_STATUS_UNREAD;
        }
    } elseif (isset($author)) {
        $sql .= " from_delete=? AND from_id=?";
        $bindvars[] = MESSAGES_NOTDELETED;
        $bindvars[] = (int) $author;
        if (isset($unread)) {
            $sql .= " AND from_status=?";
            $bindvars[] = MESSAGES_NOTDELETED;
        } elseif (isset($drafts)) {
            $sql .= " AND from_status=?";
            $bindvars[] = MESSAGES_STATUS_DRAFT;
        } else {
            $sql .= " AND from_status!=?";
            $bindvars[] = MESSAGES_STATUS_DRAFT;
        }
    }


    $result = & $dbconn->Execute($sql, $bindvars);

    if (!$result) {
        return 0;
    }

    if ($result->EOF) {
        return 0;
    }

    [$numitems] = $result->fields;

    $result->Close();

    return $numitems;
}
