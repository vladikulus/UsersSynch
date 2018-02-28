<?php
ini_set('display_errors', 1);
ini_set('error_reporting', 1);

if (empty($_REQUEST['type'])) {
    die('Access denied');
}
else {
    $type = $_REQUEST['type'];
    $mode = $_REQUEST['mode'];
}

define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/index.php';

$modx->getService('error','error.modError');
$modx->getRequest();
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;

$UsersSynch = $modx->getService('userssynch', 'UsersSynch', $modx->getOption('userssynch_core_path', null,
        $modx->getOption('core_path') . 'components/userssynch/') . 'model/userssynch/'
);

if ($modx->error->hasError()) {die('Error');}
$UsersSynch->initialize('web', array('json_response' => true));

if(!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])){
    /*
     * Add support on FastCGI mode
     * RewriteCond %{HTTP:Authorization} !^$
     * RewriteRule ^(.*)$ $1?http_auth=%{HTTP:Authorization} [QSA]
     */
    if(isset($_GET['http_auth'])){
        $d = base64_decode(substr($_GET['http_auth'],6) );
        list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $d);
    }
}
$user = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

$syncuser = $modx->getOption('msklad_1c_sync_login');
$syncpass = $modx->getOption('msklad_1c_sync_pass');

if ( ($user != $syncuser || $password != $syncpass) ) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[userssynch] Ошибка авторизации импорта, проверьте правильность логина и пароля.');
    echo "failure\n";
    exit;
}



switch ($type) {

    case 'import':
        switch ($mode) {
            case 'checkauth':
                $response = $UsersSynch->import->checkauth();
                break;
            case 'init':
                $response = $UsersSynch->import->init();
                break;
            case 'file':
                $response = $UsersSynch->import->file(@$_REQUEST['filename'], @file_get_contents("php://input"));
                break;
            case 'import':
                $response = $UsersSynch->import->import(@$_REQUEST['filename'], @file_get_contents("php://input"));
                break;
            default:
        }
        break;

    //Заказы
    case 'export':
        switch ($mode) {
            case 'checkauth':
                $response = $UsersSynch->export->checkauth();
                break;
            case 'init':
                $response = $UsersSynch->export->init();
                break;
            case 'query':
                //header ( "Content-type: text/xml; charset=windows-1251" );
//                print "\xEF\xBB\xBF";
                $response = $UsersSynch->export->query();
                break;
            case 'success':
                $response = $UsersSynch->sale->success();
                break;
            case 'file':
                $response = $UsersSynch->sale->file(@$_REQUEST['filename'], @file_get_contents("php://input"));
                break;
            default:
        }
        break;
    default:
}

@session_write_close();
exit($response);