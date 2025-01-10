<?php

/**
 * @package modules\messages
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Messages\AdminGui;

use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarServer;
use xarModVars;
use xarVar;
use xarTpl;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * messages admin main function
 */
class MainMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Messages Module
     * @package modules
     * @subpackage messages module
     * @copyright (C) copyright-placeholder
     * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
     * @link http://xaraya.com/index.php/release/6.html
     * @author XarayaGeek
     * @author Ryan Walker
     * @author Marc Lutolf <mfl@netspan.ch>
     */
    public function __invoke(array $args = [])
    {
        if (!xarSecurity::check('AdminMessages')) {
            return;
        }

        $refererinfo =  xarController::$request->getInfo(xarServer::getVar('HTTP_REFERER'));
        $info =  xarController::$request->getInfo();
        $samemodule = $info[0] == $refererinfo[0];

        $data = [];

        if (((bool) xarModVars::get('modules', 'disableoverview') == false) || $samemodule) {
            if (!xarVar::fetch('tab', 'str', $data['tab'], '', xarVar::NOT_REQUIRED)) {
                return;
            }
            $data['context'] = $this->getContext();
            return xarTpl::module('messages', 'admin', 'overview', $data);
        } else {
            xarController::redirect(xarController::URL('messages', 'admin', 'modifyconfig'), null, $this->getContext());
            return true;
        }
    }
}
