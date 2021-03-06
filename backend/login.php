<?php
/**
 * Created by PhpStorm.
 * User: Sunny
 * Date: 2017-11-21
 * Time: 2:25 PM
 */

session_start();

$GLOBALS['db'] = Util::db_connect();

if (array_key_exists('uid', $_SESSION)) {
    $GLOBALS['user'] = new User($_SESSION['uid']);
    $GLOBALS['userLvl'] = $GLOBALS['user']->getType();
} else {
    $GLOBALS['userLvl'] = Constants::LVL_NONE;
}

function isLoggedIn()
{
    return array_key_exists('user', $GLOBALS);
}

function logout()
{
    unset($GLOBALS['user']);
    unset($_SESSION['uid']);
}