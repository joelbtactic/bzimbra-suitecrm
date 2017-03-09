<?php

require_once('bZimbra/zimbra_api.php');
require_once('bZimbra/utils.php');
require_once('bZimbra/bean_utils.php');
require_once('bZimbra/account.php');
require_once('bZimbra/quota.php');

abstract class Domain {

    static public function sync_all_domains() {
        foreach (ZimbraAPI::get_zimbra_servers() as $servername => $server_properties) {
            $GLOBALS['log']->fatal("[bZimbra] Syncing domains of '".$servername
                    ."' server...");
            $api_instance = ZimbraAPI::get_api_instance($server_properties);
            self::sync_all_domains_of_server($api_instance);
        }
    }

    static public function sync_all_domains_of_server($api_instance) {
        $domains = $api_instance->getAllDomains()->domain;
        foreach ($domains as $domain) {
            self::sync_domain($domain);
            Account::sync_all_accounts_of_domain($api_instance, $domain->name);
            Quota::sync_all_quotas_of_domain($api_instance, $domain->name);
        }
        $GLOBALS['log']->fatal("[bZimbra] --> ".count($domains)." Zimbra domains synced.");
    }

    static public function sync_domain($domain) {
        $keys_values = array();
        $keys_values['name'] = $domain->name;
        $bean = retrieve_record_bean('btc_bMail', $keys_values);
        $bean->name = $domain->name;
        $domain_atributes = get_atributes_as_array($domain->a);
        set_mb_attribute($domain_atributes, $bean,
                'zimbraMailDomainQuota', 'capacidad');
        set_mb_attribute($domain_atributes, $bean,
                'zimbraDomainAggregateQuota', 'capacidad_agregado');
        set_attribute($domain_atributes, $bean,
                'zimbraDomainAggregateQuotaWarnPercent', 'warn_percent');
        set_attribute($domain_atributes, $bean,
                'zimbraDomainAggregateQuotaWarnEmailRecipient', 'email_quota_warn');
        set_attribute($domain_atributes, $bean,
                'zimbraDomainAggregateQuotaPolicy', 'aggregate_quota_policy');
        set_attribute($domain_atributes, $bean,
                'zimbraPulicServiceHostname', 'nombre_servidor_publico');
        set_attribute($domain_atributes, $bean,
                'zimbraPublicServicePort', 'puerto_servicio_publico');
        set_attribute($domain_atributes, $bean,
                'zimbraPublicServiceProtocol', 'protocolo_servicio_publico');
        set_attribute($domain_atributes, $bean, 'description', 'description');
        //self::set_attribute($domain_atributes, $bean, 'zimbraDomainStatus', '');
        set_attribute($domain_atributes, $bean,
                'zimbraDomainDefaultCOSId', 'id_clase_servicio_por_defecto');
        $bean->save();
        self::relate_zimbra_domain_with_domain($bean, $domain->name);
    }

    static private function relate_zimbra_domain_with_domain($zimbra_bean, $domain) {
        $keys_values = array();
        $keys_values['name'] = $domain;
        $domain_bean = retrieve_record_bean('btc_Dominios', $keys_values);
        if (!empty($domain_bean->id)) {
            $domain_bean->load_relationship('btc_dominios_btc_bmail');
            $domain_bean->btc_dominios_btc_bmail->add($zimbra_bean);
        }
    }

}

