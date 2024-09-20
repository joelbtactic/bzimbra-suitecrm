<?php

require_once('bZimbra/domain.php');
require_once('bZimbra/account.php');
require_once('bZimbra/account_quota.php');
require_once('bZimbra/domain_quota.php');

function main() {
    $GLOBALS['log']->fatal("[bZimbra] Entering bZimbra synchronization.");
    Domain::sync_all_domains();
//    ZimbraAccount::sync_all_accounts();
//    AccountQuota::sync_all_quotas();
    DomainQuota::sync_all_domains();
    $GLOBALS['log']->fatal("[bZimbra] bZimbra synchronization finished.");
    return true;
}

?>
