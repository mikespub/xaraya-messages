<?php
/**
 * Handle messages_user_settings object
 *
 * @package modules
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Messages module
 * @link http://xaraya.com/index.php/release/6.html
 */
/**
 * Provides extra processing to roles user account function for user_settings
 *
 * @param $args['id'] id of the user to get settings for (default current user)
 * @param $args['phase'] phase to process (valid phases are showform, checkinput, and updateitem)
 * NOTE: If you provide this function in your module, you must include return values for all phases
 * @param $args['object'] user_settings object (default messages_user_settings)
 * @return mixed
 * @return array on showform
 * @return bool on checkinput, invalid = false, valid = true
 * @return bool on updateitem, error = false, success = true
 */
function messages_userapi_usermenu($args)
{
    // not logged in?
    if (!xarUser::isLoggedIn()) {
        // redirect user to their account page after login
        $redirecturl = xarController::URL('roles', 'user', 'account');
        xarResponse::redirect(xarController::URL($defaultloginmodname, 'user', 'showloginform', ['redirecturl' => $redirecturl]));
    }

    // edit account is disabled?
    if ((bool)xarModVars::get('messages', 'enable_user_menu') == false) {
        // show the user their profile display
        xarResponse::redirect(xarController::URL('roles', 'user', 'account'));
    }

    // Get arguments from argument array
    extract($args);

    $data = [];

    if (empty($phase)) {
        $phase = 'showform';
    }

    if (empty($id) || !is_numeric($id)) {
        $id = xarUser::getVar('id');
    }

    if (!isset($object)) {
        $object = xarMod::apiFunc('base', 'admin', 'getusersettings', ['module' => 'messages', 'itemid' => $id]);
    }
    // only get the fields we need
    $fieldlist = [];
    $settings = explode(',', xarModVars::get('roles', 'duvsettings'));
    $fieldlist[] = 'user_sendemail';
    $fieldlist[] = 'enable_autoreply';
    $fieldlist[] = 'autoreply';
    $fieldlist[] = 'user_send_redirect';

    $object->setFieldList(join(',', $fieldlist));
    switch (strtolower($phase)) {
        /**
         * The showform phase is called whenever the usermenu form is displayed
         * This data is passed to /roles/xartemplates/objects/showform-usermenu.xt
         * Use this phase to perform any extra operations on your data
         * (such as setting fieldlist, template, layout, etc, see below for examples)
        **/
        case 'showform':
        default:
            // optionally specify the module template and layout to use
            $object->tplmodule = 'messages'; // roles/xartemplates/objects/
            $object->template = 'usermenu'; // showform-usermenu.xt
            //$object->layout = 'messages_user_settings';
            $object->getItem(['itemid' => $id]);

            // pass all relevant data back to the calling function
            // the object to be displayed
            $data['object'] = $object;
            // any extra data needed can be added to the $formdata array. This will be
            // available in your showform- template as #$formdata#. Use this if
            // your form needs data not available from the object itself.
            $data['formdata'] = [
                'settings' => $settings,
            ];
            // if you want to provide your own update function, you can specify
            // the form action url to be used. When the form is POSTed your function
            // will be used. (see roles user usermenu for an example).
            $data['formaction'] = xarController::URL('roles', 'user', 'usermenu');
            // not necessary, but for completeness pass back any fields you changed
            $data['tplmodule'] = 'messages';
            $data['template'] = 'usermenu';
            //$data['layout'] = 'roles_user_settings';
            // pass the module name in when setting the authkey, this avoids clashes
            // when the output contained within another modules display (eg in xarpages)
            $data['authid'] = xarSec::genAuthKey('messages');
            // finally return data to the calling function
            return $data;
            break;

            /**
             * The checkinput phase allows you to perform validations. Use this
             * when a standard $object->checkInput() call just isn't enough.
            **/
        case 'checkinput':
            $isvalid = $object->checkInput();

            //$user_sendemail = $object->properties['user_sendemail']->value;
            //xarModItemVars::set('messages', "user_sendemail", $user_sendemail ,$id);

            /*if (!empty($object->properties['userhome']) && (bool)xarModVars::get('roles','allowuserhomeedit')) {
               $home = $object->properties['userhome']->getValue();
               if ((bool)xarModVars::get('roles','allowuserhomeedit')) { // users can edit user home
                    // Check if external urls are allowed in home page
                    $allowexternalurl = (bool)xarModVars::get('roles','allowexternalurl');
                    $url_parts = parse_url($home);
                    if (!$allowexternalurl) {
                        if ((preg_match("%^http://%", $home, $matches)) &&
                        ($url_parts['host'] != $_SERVER["SERVER_NAME"]) &&
                        ($url_parts['host'] != $_SERVER["HTTP_HOST"])) {
                            $msg  = xarML('External URLs such as #(1) are not permitted as your home page.', $home);
                            $object->properties['userhome']->invalid .= $msg;
                            $isvalid = false;
                        }
                    }
                }
            }*/
            // instead of returning here, if the data is valid,
            // we could fall through to the updateitem phase

            // return the bool result to the calling function
            return $isvalid == true ? true : false;
            break;

            /**
             * The updateitem phase allows you to update user settings. Use this
             * when a standard $object->updateItem() call just isn't enough.
            **/
        case 'updateitem':
            // if you added the module name when you generated the authkey,
            // be sure to use it here when confirming :)
            if (!xarSec::confirmAuthKey('messages')) {
                return;
            }
            // data is already validated, go ahead and update the item
            $object->updateItem();
            // you could just return directly from here...
            /*
            // be sure to check for a returnurl
            if (!xarVar::fetch('returnurl', 'pre:trim:str:1', $returnurl, '', xarVar::NOT_REQUIRED)) return;
            // the default returnurl should be roles user account with a moduleload of current module
            if (empty($returnurl))
                $returnurl = xarController::URL('roles', 'user', 'account', array('moduleload' => 'roles'));
            return xarResponse::redirect($returnurl);
            */
            // let the calling function know the update was a success
            return true;
            break;
    }
    // if we got here, we don't know what went wrong, just return false
    return false;
}
