<?php

require_once('bZimbra/domain.php');
require_once('bZimbra/account.php');

function main() {
    $GLOBALS['log']->fatal("[bZimbra] Entering bZimbra synchronization.");
    Domain::sync_all_domains();
    Account::sync_all_accounts();
    $GLOBALS['log']->fatal("[bZimbra] bZimbra synchronization finished.");
    return true;
}

?>
