<?php

require_once('bZimbra/zimbra_api.php');
require_once('bZimbra/utils.php');
require_once('bZimbra/bean_utils.php');

abstract class DomainQuota {

    static public function sync_all_domains() {
        foreach (ZimbraAPI::get_zimbra_servers() as $servername => $server_properties) {
            $GLOBALS['log']->fatal("[bZimbra] Syncing domains quotas of '".$servername
                    ."' server...");
            $api_instance = ZimbraAPI::get_api_instance($server_properties);
            self::sync_all_domains_of_server($api_instance);
        }
    }

    static public function sync_all_domains_of_server($api_instance) {
        $domains = $api_instance->computeAggregateQuotaUsage()->domain;
        foreach ($domains as $domain) {
            self::sync_domain((array) $domain);
        }
        $GLOBALS['log']->fatal("[bZimbra] --> ".count($domains)." Zimbra domains quotas synced.");
    }

    static public function sync_domain($domain) {
        $keys_values = array();
        $keys_values['name'] = $domain['name'];
        $bean = retrieve_record_bean('btc_bMail', $keys_values);
        $bean->name = $domain['name'];
        set_mb_attribute($domain, $bean, 'used', 'usado');
        $bean->save();
    }

}

