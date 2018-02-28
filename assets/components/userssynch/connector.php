<?php
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
}
else {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var UsersSynch $UsersSynch */
$UsersSynch = $modx->getService('userssynch', 'UsersSynch', $modx->getOption('userssynch_core_path', null,
        $modx->getOption('core_path') . 'components/userssynch/') . 'model/userssynch/'
);
$modx->lexicon->load('userssynch:default');

// handle request
$corePath = $modx->getOption('userssynch_core_path', null, $modx->getOption('core_path') . 'components/userssynch/');
$path = $modx->getOption('processorsPath', $UsersSynch->config, $corePath . 'processors/');
$modx->getRequest();

/** @var modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));