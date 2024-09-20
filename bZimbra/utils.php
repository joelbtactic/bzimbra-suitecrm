<?php

function get_atributes_as_array($object_info) {
    $atributes = array();
    foreach ($object_info as $atribute) {
        $atributes[$atribute->getKey()] = $atribute->getValue();
    }
    return $atributes;
}

function bytes($bytes, $force_unit = NULL, $show_unit = true, $format = NULL, $si = TRUE) {
    // Format string
    $format = ($format === NULL) ? ($show_unit ? '%01.3f %s' : '%01.3f') : (string) $format;

    // IEC prefixes (binary)
    if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE) {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $mod   = 1024;
    } else { // SI prefixes (decimal)
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
        $mod   = 1000;
    }

    // Determine unit to use
    if (($power = array_search((string) $force_unit, $units)) === FALSE) {
        $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
    }

    return $show_unit ? sprintf($format, $bytes / pow($mod, $power), $units[$power])
        : sprintf($format, $bytes / pow($mod, $power));
}

function bytes_to_megabytes($bytes) {
    return bytes($bytes, 'MiB', false);
}

function bytes_to_gibibytes($bytes) {
    return bytes($bytes, 'GiB', false);
}

function set_attribute($record_info, $bean, $info_var, $bean_var) {
    if(isset($record_info[$info_var])) $bean->$bean_var = $record_info[$info_var];
}

function set_mb_attribute($record_info, $bean, $info_var, $bean_var) {
    if(isset($record_info[$info_var])) {
        $bean->$bean_var = bytes_to_megabytes($record_info[$info_var]);
    }
}

function set_quota_limit_attr($quota, $bean, $bean_var){
    $quota_limit = $quota->getQuotaLimit();
    if (isset($quota_limit)){
        $bean->$bean_var = bytes_to_megabytes($quota_limit);
    }
}

function set_quota_used_attr($quota, $bean, $bean_var){
    $quota_used = $quota->getQuotaUsed();
    if (isset($quota_used)){
        $bean->$bean_var = bytes_to_megabytes($quota_used);
    }
}

function check_mx($domain) {
    require('bZimbra/config/valid_mx_servers.php');
    getmxrr($domain, $mx_records);
    if (isset($mx_records[0])) {
        foreach ($mx_records as $mx_record) {
            if (!in_array($mx_record, $valid_mx)) {
                return false;
            }
        }
        return true;
    } else {
        return false;
    }
}

function check_txt($domain) {
    require('bZimbra/config/valid_txt_servers.php');
    $txt = dns_get_record($domain, DNS_TXT);
    if (count($txt) != 1 || !isset($txt[0]['txt'])) return false;
    return preg_match($txt_records, $txt[0]['txt']);
}

?>
