<?php

require_once('bZimbra/zimbra_api.php');
require_once('bZimbra/utils.php');
require_once('bZimbra/bean_utils.php');

abstract class Quota {

    static public function sync_all_quotas() {
        foreach (ZimbraAPI::get_zimbra_servers() as $servername => $server_properties) {
            $GLOBALS['log']->fatal("[bZimbra] Syncing accounts quotas of '".$servername
                    ."' server...");
            self::sync_all_quotas_of_server($server_properties);
        }
    }

    static public function sync_all_quotas_of_server($server_properties) {
        $api_instance = ZimbraAPI::get_api_instance($server_properties);
        $quotas = $api_instance->getQuotaUsage()->account;
        foreach ($quotas as $quota) {
            self::sync_quota((array) $quota);
        }
        $GLOBALS['log']->fatal("[bZimbra] --> ".count($quotas)." Zimbra quotas synced.");
    }

    static public function sync_all_quotas_of_domain($api_instance, $domain) {
        $quotas = $api_instance->getQuotaUsage($domain, true);
        if (!isset($quotas->account)) {
             $GLOBALS['log']->fatal("[bZimbra] --> Domain '".$domain."' has no "
                     ."accounts quotas to sync. Maybe it's an alias.");
             return;
        }
        foreach ($quotas->account as $quota) {
            self::sync_quota((array) $quota);
        }
        $GLOBALS['log']->fatal("[bZimbra] --> ".count($quotas->account)
                ." Zimbra quotas synced from domain '".$domain."'.");
    }

    static public function sync_quota($quota) {
        $keys_values = array();
        $keys_values['name'] = $quota['name'];
        $bean = retrieve_record_bean('btc_Zimbra_Accounts', $keys_values);
        $bean->name = $quota['name'];
        set_mb_attribute($quota, $bean, 'limit', 'quota');
        set_mb_attribute($quota, $bean, 'used', 'usado');
        $bean->save();
    }

}

?>
