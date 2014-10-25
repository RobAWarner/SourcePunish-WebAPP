<?php
if(!defined('IN_SP')) die('Access Denied!');

$GLOBALS['config']['system']['printdebug'] = true;
$GLOBALS['config']['sql']['host'] = 'localhost';
$GLOBALS['config']['sql']['username'] = 'user1';
$GLOBALS['config']['sql']['password'] = 'password';
$GLOBALS['config']['sql']['database'] = 'somedb';
$GLOBALS['config']['sql']['prefix'] = 'sp_';
$GLOBALS['config']['admins']['useexisting'] = true;
$GLOBALS['config']['admins']['differentdb'] = false;
$GLOBALS['config']['admins']['table'] = 'sm_admins';
$GLOBALS['config']['admins']['host'] = '';
$GLOBALS['config']['admins']['username'] = '';
$GLOBALS['config']['admins']['password'] = '';
$GLOBALS['config']['admins']['database'] = '';
?>