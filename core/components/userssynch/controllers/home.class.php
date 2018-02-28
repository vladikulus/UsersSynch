<?php

/**
 * The home manager controller for UsersSynch.
 *
 */
class UsersSynchHomeManagerController extends modExtraManagerController
{
    /** @var UsersSynch $UsersSynch */
    public $UsersSynch;


    /**
     *
     */
    public function initialize()
    {
        $path = $this->modx->getOption('userssynch_core_path', null,
                $this->modx->getOption('core_path') . 'components/userssynch/') . 'model/userssynch/';
        $this->UsersSynch = $this->modx->getService('userssynch', 'UsersSynch', $path);
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return array('userssynch:default');
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('userssynch');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->UsersSynch->config['cssUrl'] . 'mgr/main.css');
        $this->addCss($this->UsersSynch->config['cssUrl'] . 'mgr/bootstrap.buttons.css');
        $this->addJavascript($this->UsersSynch->config['jsUrl'] . 'mgr/userssynch.js');
        $this->addJavascript($this->UsersSynch->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->UsersSynch->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->UsersSynch->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->UsersSynch->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->UsersSynch->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->UsersSynch->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        UsersSynch.config = ' . json_encode($this->UsersSynch->config) . ';
        UsersSynch.config.connector_url = "' . $this->UsersSynch->config['connectorUrl'] . '";
        Ext.onReady(function() {
            MODx.load({ xtype: "userssynch-page-home"});
        });
        </script>
        ');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->UsersSynch->config['templatesPath'] . 'home.tpl';
    }
}