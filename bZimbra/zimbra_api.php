<?php

require_once 'bZimbra/zimbra-api/vendor/autoload.php';
use Zimbra\Admin\AdminApi;

abstract class ZimbraAPI {

    private static $zimbra_servers_config_file = 'bZimbra/config/zimbra_servers.ini';

    static public function get_zimbra_servers() {
        if (file_exists(self::$zimbra_servers_config_file)) {
            return parse_ini_file(self::$zimbra_servers_config_file, true);
        } else {
            $GLOBALS['log']->fatal("[bZimbra] Impossible to access '"
                    .$zimbra_servers_config_file."'.");
        }
    }

    static public function get_api_instance($server_access) {
        $api = new AdminApi($server_access['api_url']);
        $api->auth($server_access['user'], $server_access['password']);
        return $api;
    }

}

?>

