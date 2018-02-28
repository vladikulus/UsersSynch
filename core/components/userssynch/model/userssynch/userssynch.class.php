<?php

class UsersSynch
{
    /** @var modX $modx */
    public $modx;

    protected $request;
    public $initialized = array();
    public $import;
    public $export;
    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = array()) {

        $this->modx =& $modx;

        $corePath = $this->modx->getOption('userssynch_core_path', $config, $this->modx->getOption('core_path') . 'components/userssynch/');
        $assetsUrl = $this->modx->getOption('userssynch_assets_url', $config, $this->modx->getOption('assets_url') . 'components/userssynch/');
        $connectorUrl = $assetsUrl . 'connector.php';

        $this->config = array_merge(array(
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'imagesUrl' => $assetsUrl . 'images/',
            'connectorUrl' => $connectorUrl,

            'corePath' => $corePath,
            'assetsPath' => $this->modx->getOption('assets_path').'components/userssynch/',
            'modelPath' => $corePath . 'model/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'templatesPath' => $corePath . 'elements/templates/',
            'chunkSuffix' => '.chunk.tpl',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'processorsPath' => $corePath . 'processors/',

            'commercMlLink'=> MODX_SITE_URL.'assets/components/userssynch/1c_exchange.php'
        ), $config);

        $this->modx->addPackage('userssynch', $this->config['modelPath']);
        $this->modx->lexicon->load('userssynch:default');
    }

    public function initialize($ctx = 'web', $scriptProperties = array()) {
        $this->config = array_merge($this->config, $scriptProperties);
        $this->config['ctx'] = $ctx;
        if (!empty($this->initialized[$ctx])) {
            return true;
        }
        switch ($ctx) {
            case 'mgr': break;
            case 'web':
                require_once dirname(__FILE__) . '/userssynchimporthandler.class.php';
                $import_class = 'userssynchImportHandler';

                $this->import = new $import_class($this, $this->config);
                if ($this->import->initialize($ctx) !== true) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not initialize UsersSynch import handler class: "'.$import_class.'"');
                    return false;
                }

                require_once dirname(__FILE__) . '/userssynchexporthandler.class.php';
                $export_class = 'userssynchExportHandler';

                $this->export = new $export_class($this, $this->config);
                if ($this->export->initialize($ctx) !== true) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not initialize UsersSynch export handler class: "'.$export_class.'"');
                    return false;
                }
                break;
        }
    }

}