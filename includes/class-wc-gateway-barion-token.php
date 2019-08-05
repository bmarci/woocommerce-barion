<?php

/**
 * Class WC_Gateway_Barion_Token A helper class to provide secure tokens.
 */
class WC_Gateway_Barion_Token {

    /**
     * @return string
     * @throws Exception
     */
    public static function generateToken()
    {
        return hash('sha256', random_bytes(32));
    }
}
