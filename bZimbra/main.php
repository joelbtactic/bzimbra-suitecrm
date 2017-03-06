<?php

require_once('bZimbra/domain.php');
require_once('bZimbra/account.php');
require_once('bZimbra/quota.php');

function main() {
    $GLOBALS['log']->fatal("[bZimbra] Entering bZimbra synchronization.");
    Domain::sync_all_domains();
    Account::sync_all_accounts();
    Quota::sync_all_quotas();
    $GLOBALS['log']->fatal("[bZimbra] bZimbra synchronization finished.");
    return true;
}

?>
