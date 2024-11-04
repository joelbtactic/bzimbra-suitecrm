<?php

require_once('bZimbra/zimbra_api.php');
require_once('bZimbra/utils.php');
require_once('bZimbra/bean_utils.php');
require_once 'bZimbra/zimbra-api/vendor/autoload.php';
use Zimbra\Admin\Struct\DomainSelector;
use Zimbra\Common\Enum\DomainBy;

abstract class ZimbraAccount {

    // These two method are not used, instead, the sync_all_account_of_demain method is used
    // static public function sync_all_accounts() {
    //     foreach (ZimbraAPI::get_zimbra_servers() as $servername => $server_properties) {
    //         $GLOBALS['log']->fatal("[bZimbra] Syncing accounts of '".$servername
    //                 ."' server...");
    //         self::sync_all_accounts_of_server($server_properties);
    //     }
    // }

    // static public function sync_all_accounts_of_server($server_properties) {
    //     $api_instance = ZimbraAPI::get_api_instance($server_properties);
    //     $accounts = $api_instance->getAllAccounts()->account;
    //     foreach ($accounts as $account) {
    //         self::sync_account($account);
    //     }
    //     $GLOBALS['log']->fatal("[bZimbra] --> ".count($accounts)." Zimbra accounts synced.");
    // }

    static public function sync_all_accounts_of_domain($api_instance, $domain) {
        $offset = 0;
        $limit = 10;

        do {
            $account_search = $api_instance->searchDirectory(types: 'accounts', domain: $domain, limit: $limit, offset: $offset);

            foreach ($account_search->getAccounts() as $account) {
                self::sync_account($account);
                unset($account);
            }
            $offset += $limit;
        } while (count($account_search->getAccounts()) >= $limit);

        $GLOBALS['log']->fatal("[bZimbra] --> ".$account_search->getSearchTotal()
                ." Zimbra accounts synced from domain '".$domain."'.");
        unset($account_search);
        gc_collect_cycles();
    }

    static public function sync_account($account) {
        $keys_values = array();
        $keys_values['name'] = $account->getName();
        $bean = retrieve_record_bean('btc_Zimbra_Accounts', $keys_values);
        $bean->name = $account->getName();
        $account_atributes = get_atributes_as_array($account->getAttrList());
        set_attribute($account_atributes, $bean, 'givenName', 'given_name');
        set_attribute($account_atributes, $bean, 'initials', 'initials');
        set_attribute($account_atributes, $bean, 'sn', 'sn');
        set_attribute($account_atributes, $bean, 'zimbraAccountStatus', 'zimbra_account_status');
        set_attribute($account_atributes, $bean, 'zimbraCOSId', 'zimbra_cos_id');
        set_attribute($account_atributes, $bean, 'zimbraIsAdminAccount', 'zimbra_is_admin_account');
        set_attribute($account_atributes, $bean, 'zimbraDelegatedAdminAccount', 'zimbra_delegated_admin_account');
        set_attribute($account_atributes, $bean, 'zimbraMailTransport', 'zimbra_mail_transport');
        set_attribute($account_atributes, $bean, 'zimbraMailHost', 'zimbra_mail_host');
        set_attribute($account_atributes, $bean, 'zimbraPasswordLockoutEnabled', 'zimbra_password_lockoutenabled');
        set_attribute($account_atributes, $bean, 'zimbraMailAlias', 'zimbra_mail_alias');
        set_attribute($account_atributes, $bean, 'zimbraLastLogonTimestamp', 'zimbra_last_logon_timestamp');
        $bean->save();
        self::relate_account_with_zimbra_domain($bean);
    }

    static private function relate_account_with_zimbra_domain($account_bean) {
        $keys_values = array();
        preg_match('/@(.*)/', $account_bean->name, $matches);
        $keys_values['name'] = $matches[1];
        $domain_bean = retrieve_record_bean('btc_bMail', $keys_values);
        if (!empty($domain_bean->id)) {
            $domain_bean->load_relationship('btc_bmail_btc_zimbra_accounts');
            $domain_bean->btc_bmail_btc_zimbra_accounts->add($account_bean);
        }
    }

}

?>
