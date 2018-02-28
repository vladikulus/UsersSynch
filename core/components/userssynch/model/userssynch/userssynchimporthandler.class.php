<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 05.12.2016
 * Time: 17:20
 */
class userssynchImportHandler {
    public function __construct(UsersSynch $userssynch, array $config = array()) {
        $this->userssynch = & $userssynch;
        $this->modx = & $userssynch->modx;

        $this->config = array_merge(array(
            'json_response' => false
            ,'temp_dir' => $this->modx->getOption('userssynch_assets_path', $config, $this->modx->getOption('assets_path').'components/userssynch/1c_temp/')
            ,'sync_direction' => $this->modx->getOption('userssynch_sync_direction', $config, 1)
            ,'catalog_context' => $this->modx->getOption('msklad_catalog_context', $config, 'web')
            ,'time_limit' => $this->modx->getOption('msklad_time_limit', $config, 60)
        ), $config);

        $this->catalog = & $this->config['catalog'];

        if (empty($this->catalog) || !is_array($this->catalog)) {
            $this->catalog = array();
        }

        $this->config['start_time'] = microtime(true);
        $this->config['max_exec_time'] = min($this->config['time_limit'], @ini_get('max_execution_time'));
        if(empty($this->config['max_exec_time'])) $this->config['max_exec_time'] = 60;
        $this->modx->user = $this->modx->getObject('modUser', 1);

        $this->options = array(
            'usersImportStart' => (!isset($_SESSION['importTotalUsers']))? 1 : 0
            ,'usersImportFinish' => isset($_SESSION['usersImportFinish'])? $_SESSION['usersImportFinish'] : 0
            ,'importLastUser' => isset($_SESSION['importLastUser'])? $_SESSION['importLastUser'] : 0
            ,'importTotalUsers' => isset($_SESSION['importTotalUsers'])? $_SESSION['importTotalUsers'] : 0
        );
    }

    public function initialize($ctx = 'web') {
        return true;
    }

    public function checkauth() {
        return 'success'.PHP_EOL.session_name().PHP_EOL.session_id();
    }

    public function init() {

        $tmp_files = glob($this->config['temp_dir'].'*.*');
        if(is_array($tmp_files)){
            foreach($tmp_files as $v){
                unlink($v);
            }
        }

        unset($_SESSION['usersImportStart']
            ,$_SESSION['usersImportFinish']
            ,$_SESSION['importLastUser']
            ,$_SESSION['importTotalUsers']
            ,$_SESSION['users_mapping']
            ,$_SESSION['price_mapping']);

        //$_SESSION['feature_mapping'] = array();

        return 'zip=no'.PHP_EOL.'file_limit=1000000'.PHP_EOL;
    }

    public function file($filename='',$file) {
        if($filename) {
            $filename = basename($filename);

            $f = fopen($this->config['temp_dir'].$filename, 'ab');
            fwrite($f, $file);
            fclose($f);
            return 'success'.PHP_EOL;
        }

        $this->modx->log(modX::LOG_LEVEL_ERROR, '[usersSynch] Ошибка импорта пользователей, передано пустое имя файла (переменная filename)');
        return 'failure'.PHP_EOL.'Please see errors in MODX log'.PHP_EOL;
    }

    public function import($filename='') {
        @set_time_limit($this->config['time_limit']);

        $this->config['start_time'] = microtime(true);

        if($filename) {
            $filename = basename($filename);

            if (strpos($filename, 'customer') === 0) {
                $out ='';

                if (!file_exists($this->config['temp_dir'].$filename)) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, '[UsersSynch] Ошибка импорта пользователей, не существует файл '.$this->config['temp_dir'].$filename);
                    return 'failure'.PHP_EOL.'Please see errors in MODX log'.PHP_EOL;
                }

                //Check import step
                $step = $this->checkImportCatalogStep();
                switch ($step){

                    case 'importUsers':
                        $out = $this->importUsers($filename);
                        break;

                    case 'prepareUsers':
                        $out = $this->prepareUsers();
                        break;

                    case 'finish':
                        $this->clearCache();
                        $out = 'success'.PHP_EOL.' Выгружено пользователей:'.$this->options['importTotalUsers'];
                        break;

                }

                return $out;
            }
        }
        $this->modx->log(modX::LOG_LEVEL_ERROR, '[usersSynch] Ошибка импорта пользователей, передано пустое имя файла (переменная filename)');
        return 'failure'.PHP_EOL.'Please see errors in MODX log'.PHP_EOL;
    }

    private function checkImportCatalogStep(){
        $start = $this->options['usersImportStart'];
        $finish = $this->options['usersImportFinish'];

        $importLastUser = $this->options['importLastUser'];
        $importTotalUsers = $this->options['importTotalUsers'];

        if($start) return 'importUsers';
        if($finish) return 'finish';

        if($importLastUser < $importTotalUsers) return 'prepareUsers';

        return 'finish';
    }

    private function importUsers($filename){
        //clear temp users table
        $this->modx->exec("TRUNCATE TABLE {$this->modx->getTableName('usersSynchUsersTemp')}");

        //read xml file
        $preader = new XMLReader;
        $preader->open($this->config['temp_dir'].$filename);

        while ($preader->read() && $preader->name !== 'ТипЦены');
        while($preader->name === 'ТипЦены'){
            $xml = new SimpleXMLElement($preader->readOuterXML());
            $priceId = addslashes((string) $xml->Ид);
            if(isset($xml->Наименование)) {
                $name = 0;
                switch ($xml->Наименование) {
                    case "1-я категория":
                        $name = 1;
                        break;
                    case "2-я категория":
                        $name = 2;
                        break;
                    case "3-я категория":
                        $name = 3;
                        break;
                }
                $_SESSION['price_mapping'][$priceId] = addslashes((string) $name);
            }

            $preader->next('ТипЦены');
        }
        $preader->close();

        //search users
        $reader = new XMLReader;
        $reader->open($this->config['temp_dir'].$filename);
        while ($reader->read() && $reader->name !== 'Контрагенты');
        if($reader->name == 'Контрагенты'){
            $xml = new SimpleXMLElement($reader->readOuterXML());
            $reader->close();

            $this->importUser($xml);
        }

        $totalUsers = intval($this->getTotalUsers());
        $this->options['importTotalUsers'] = $_SESSION['importTotalUsers'] = $totalUsers;

        return 'progress'.PHP_EOL.'Пользователи выгружены в временную таблицу'.PHP_EOL;
    }

    private function importUser($xml) {
        //$this->modx->log(1, print_r($xml, 1));
        if(isset($xml->Контрагент)){
            foreach ($xml->Контрагент as $xml_user){
                $user_name = isset($xml_user->Наименование)? addslashes((string) $xml_user->Наименование): '';
                $user_uuid = isset($xml_user->Ид)? addslashes((string) $xml_user->Ид) : '';
                $user_inn = isset($xml_user->ИНН)? addslashes((string) $xml_user->ИНН): '';
                $user_phone = isset($xml_user->Телефон)? addslashes((string) $xml_user->Телефон) : '';

                $priceType = 0;
                $priceTypeId = isset($xml_user->ИдТипаЦены)? addslashes((string) $xml_user->ИдТипаЦены) : '';
                if(isset($_SESSION['price_mapping'][$priceTypeId])) {
                    $priceType = $_SESSION['price_mapping'][$priceTypeId];
                }

                $sql = "INSERT " . "INTO ".$this->modx->getTableName('usersSynchUsersTemp')." (`fullname`, `uuid`,
                    `inn`, `phone`, `price_group`) VALUES
                        ('{$user_name}', '{$user_uuid}', '{$user_inn}', '{$user_phone}', '{$priceType}');";

                $stmt = $this->modx->prepare($sql);
                $stmt->execute();
            }
        }

    }

    private function getTotalUsers(){
        $c = $this->modx->newQuery('usersSynchUsersTemp');
        $total = $this->modx->getCount('usersSynchUsersTemp', $c);
        if($total) return $total;

        return false;
    }

    private function prepareUsers(){
        $lastUser = $this->options['importLastUser'];
        $importTotalUsers = $this->options['importTotalUsers'];

        //get 500 users from temp table to import
        $q = $this->modx->newQuery('usersSynchUsersTemp');
        $q->select($this->modx->getSelectColumns('usersSynchUsersTemp', 'usersSynchUsersTemp', '', array('id','fullname','uuid', 'phone', 'inn', 'form_type', 'price_group', 'subscribe')));
        $q->sortby('id','ASC');
        $q->limit(500,$lastUser);

        if ($q->prepare() && $q->stmt->execute()){
            $usersData = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

            if(is_array($usersData) && count($usersData)>0){
                foreach($usersData as $userData){
                    $this->prepareUser($userData);

                    ++$lastUser;
                    $_SESSION['importLastUser'] = $lastUser;

                    //if exec time more max time, break cycle
                    $exec_time = microtime(true) - $this->config['start_time'];
                    if($exec_time+1>=$this->config['max_exec_time']){
                        break;
                    }
                }
            }
        }

        return 'progress'.PHP_EOL.'Импортировано пользователей '.$lastUser.' из '.$importTotalUsers.PHP_EOL;
    }

    private function prepareUser($userData) {

        //Checking prepare or not this category
        if(!isset($_SESSION['users_mapping'][$userData['uuid']])){
            $userId = 0;

            //clear modx errors
            $this->modx->error->message = null;
            $this->modx->error->errors = array();

            //get usersSynchUsersData object
            $uData = $this->modx->getObject('usersSynchUsersData', array('uuid_1c' => $userData['uuid']) );
            if($uData){
                $userId = $uData->get('user_id');

                //if isset modUser object, then update
                if($user = $this->modx->getObject('modUser', array('class_key' => 'modUser','id' => $userId) ) ){
                    $userData['id'] = $userId;
                    $this->updateUser($userData);
                }
                //else create new modUser object
                else{

                    $response = $this->createUser($userData);
                    //add user id/uuid
                    if($response){
                        //update new usersSynchUsersData object
                        $userId = $response->response['object']['id'];
                        $userData->set('user_id',$userId);
                        $userData->set('uuid_1c',$userData['uuid']);
                        $userData->save();
                    }
                }
            }
            //usersSynchUsersData object not found
            else{
                //create new modUser object
                $response = $this->createUser($userData);
                //add user id/uuid
                if($response){
                    //create new usersSynchUsersData object
                    $userId = $response->response['object']['id'];
                    $newUserData = $this->modx->newObject('usersSynchUsersData');
                    $newUserData->set('user_id',$userId);
                    $newUserData->set('uuid_1c',$userData['uuid']);
                    $newUserData->save();
                }
            }

            $_SESSION['users_mapping'][strval($userData['uuid'])] = $userId;
        }
    }

    private function createUser($userData){

        /**/
        $groups = array();
        $groups['Group1']['usergroup'] = '3'; // ID of group
        $groups['Group1']['role'] = '1'; // ID of role
        $fields = $userData;
        $fields['active'] = true;
        $fields['passwordgenmethod'] = 'g';
        $fields['passwordnotifymethod'] = 'e';

        $fields['email'] = "";
        $fields['username'] = "";

        if($userData['phone'] != ''){
            $fields['email'] = $userData['phone'] . "@email.ru";
            $fields['username'] = $userData['phone'];
        }
        $fields['groups'] = $groups;

        $response = $this->modx->runProcessor('security/user/create', $fields);

        if (!$response->isError()) {
            return $response;
        }
        else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[usersSynch] Ошибка создания пользователя '.print_r($response->getResponse(),1));
            return false;
        }
    }

    private function updateUser($userData){

        $fields = $userData;

        $fields['email'] = "";
        $fields['username'] = "";
        if($userData['phone'] != ''){
            $fields['email'] = $userData['phone'] . "@email.ru";
            $fields['username'] = $userData['phone'];
        }

        $response = $this->modx->runProcessor('security/user/update', $fields);

        if (!$response->isError()) {
            return $response;
        }
        else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[usersSynch] Ошибка обновления пользователя '.print_r($response->getResponse(),1));
            return false;
        }
    }

    public function clearCache() {
        $this->modx->cacheManager->refresh();
        return true;
    }

}