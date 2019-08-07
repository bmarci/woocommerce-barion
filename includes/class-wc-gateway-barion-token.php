<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WC_Gateway_Barion_Token A helper class to provide secure tokens.
 */
class WC_Gateway_Barion_Token
{

    private function __construct()
    {
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function generate_token()
    {
        return hash('sha256', random_bytes(32));
    }
}
