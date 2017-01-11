<?php

require_once('bZimbra/domain.php');

function main() {
    $GLOBALS['log']->fatal("[bZimbra] Entering bZimbra synchronization.");
    Domain::sync_all_domains();
    $GLOBALS['log']->fatal("[bZimbra] bZimbra synchronization finished.");
    return true;
}

?>
