<?php

class userssynchExportHandler {
    public $modx;
    protected $export;

    function __construct(UsersSynch $userssynch, array $config = array()) {
        $this->userssynch = & $userssynch;
        $this->modx = & $userssynch->modx;

        $this->config = $config;

        $this->export = & $this->config['export'];

        if (empty($this->export) || !is_array($this->export)) {
            $this->export = array();
        }
    }

    public function initialize($ctx = 'web') {
        return true;
    }

    public function checkauth() {
        return 'success'.PHP_EOL.session_name().PHP_EOL.session_id();
    }

    public function init() {
        return 'zip=no'.PHP_EOL.'file_limit=1000000'.PHP_EOL;
    }

    public function query() {
        $no_spaces = '<?xml version="1.0" standalone="yes"?>
        <КоммерческаяИнформация ВерсияСхемы="2.04" ДатаФормирования="' . date('Y-m-d'). '"></КоммерческаяИнформация>';

        $xml = new SimpleXMLElement($no_spaces);


        $result = $this->modx->query("SELECT `id`, `username`, `active`, `createdon`, `subscribe`, `uuid_1c` FROM `modx_users` AS `modUser` LEFT JOIN `modx_userssynch_users` AS `usersSynchUsersData` ON `modUser`.`id` = `usersSynchUsersData`.`user_id` WHERE `sudo` != 1 ORDER BY id ASC");
        $usersData = $result->fetchAll(PDO::FETCH_ASSOC);
        if(is_array($usersData) && count($usersData)>0){
            foreach($usersData as $userData){
                $doc = $xml->addChild("Контрагент");
                $doc->addChild("Ид", $userData['id']);
                $doc->addChild("Логин", $userData['username']);
                $doc->addChild("Активный", $userData['active']);
                $doc->addChild("ДатаСоздания", $userData['createdon']);
                $doc->addChild("Подписка", $userData['subscribe']);
                $doc->addChild("ИдКонтрагента", $userData['uuid_1c']);
            }
        }

        $out =  $xml->asXML ();
        $out = iconv("UTF-8", "cp1251", $out);
        $out = str_replace('<?xml version="1.0" standalone="yes"?>','<?xml version="1.0" encoding="windows-1251" standalone="yes"?>',$out);
        return $out;
    }

}