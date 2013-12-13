<?php
/**
 * FilterInput is a class for filtering input from any data source
 */
class Core_FilterInput {

    function clean($source, $type='string') {
        // Handle the type constraint
        switch (strtoupper($type)) {
            case 'INT' :
            case 'INTEGER' :
                // Only use the first integer value
                preg_match('/-?[0-9]+/', (string) $source, $matches);
                $result = @ (int) $matches[0];
                break;

            case 'FLOAT' :
            case 'DOUBLE' :
                // Only use the first floating point value
                preg_match('/-?[0-9]+(\.[0-9]+)?/', (string) $source, $matches);
                $result = @ (float) $matches[0];
                break;

            case 'BOOL' :
            case 'BOOLEAN' :
                $result = (bool) $source;
                break;

            case 'WORD' :
                $result = (string) preg_replace('/[^A-Z_]/i', '', $source);
                break;

            case 'ALNUM' :
                $result = (string) preg_replace('/[^A-Z0-9]/i', '', $source);
                break;

            case 'CMD' :
                $result = (string) preg_replace('/[^A-Z0-9_\.-]/i', '', $source);
                $result = ltrim($result, '.');
                break;

            case 'BASE64' :
                $result = (string) preg_replace('/[^A-Z0-9\/+=]/i', '', $source);
                break;

            case 'STRING' :
                $result = (string) $source;
                break;

            case 'ARRAY' :
                $result = (array) $source;
                break;

            case 'PATH' :
                $pattern = '/^[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';
                preg_match($pattern, (string) $source, $matches);
                $result = @ (string) $matches[0];
                break;

            case 'USERNAME' :
                $result = (string) preg_replace('/[\x00-\x1F\x7F<>"\'%&]/', '', $source);
                break;



            default :
                $result = (string) $source;
                break;
        }
        return $result;
    }

}
