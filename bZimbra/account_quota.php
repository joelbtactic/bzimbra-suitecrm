<?php

require_once('bZimbra/zimbra_api.php');
require_once('bZimbra/utils.php');
require_once('bZimbra/bean_utils.php');

abstract class AccountQuota {

    // These two method are not used, instead, the sync_all_quotas_of_demain method is used
    // static public function sync_all_quotas() {
    //     foreach (ZimbraAPI::get_zimbra_servers() as $servername => $server_properties) {
    //         $GLOBALS['log']->fatal("[bZimbra] Syncing accounts quotas of '".$servername
    //                 ."' server...");
    //         self::sync_all_quotas_of_server($server_properties);
    //     }
    // }

    // static public function sync_all_quotas_of_server($server_properties) {
    //     $api_instance = ZimbraAPI::get_api_instance($server_properties);
    //     $quotas = $api_instance->getQuotaUsage()->account;
    //     foreach ($quotas as $quota) {
    //         self::sync_quota((array) $quota);
    //     }
    //     $GLOBALS['log']->fatal("[bZimbra] --> ".count($quotas)." Zimbra quotas synced.");
    // }

    static public function sync_all_quotas_of_domain($api_instance, $domain) {
        $offset = 0;
        $limit = 10;

        do {
            $quotas = $api_instance->getQuotaUsage($domain, true, offset: $offset, limit: $limit);

            foreach ($quotas->getAccountQuotas() as $quota) {
                self::sync_quota($quota);
                unset($quota);
            }
            $offset += $limit;
        } while (count($quotas->getAccountQuotas()) >= $limit);

        $GLOBALS['log']->fatal("[bZimbra] --> ".$quotas->getSearchTotal()
                ." Zimbra quotas synced from domain '".$domain."'.");
        unset($quotas);
        gc_collect_cycles();
    }

    static public function sync_quota($quota) {
        $keys_values = array();
        $keys_values['name'] = $quota->getName();
        $bean = retrieve_record_bean('btc_Zimbra_Accounts', $keys_values);
        $bean->name = $quota->getName();
        set_quota_limit_attr($quota, $bean,'quota');
        set_quota_used_attr($quota, $bean,'usado');
        $bean->save();
    }

}

?>
