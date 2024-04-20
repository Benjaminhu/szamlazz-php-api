<?php
    spl_autoload_register( 'invoiceAgentAutoloader' );

    /**
     * Számla Agent PHP API autoloader
     *
     * @param  string $class
     *
     * @return void
     * @throws ErrorException
     */
    function invoiceAgentAutoloader( $class ) {
        $apiName = 'szamlaagent';
        $prefix  = $apiName . '\\';
        $baseDir = __DIR__ . '/../src/' . strtolower($apiName) .DIRECTORY_SEPARATOR;
        $path    = explode('\\', $class);

        if (strtolower($path[0]) == $apiName) {
            for ($i = 0; $i < count($path)-1; $i++) {
                $nPath[] = strtolower($path[$i]);
            }
            array_push($nPath, end($path));

            $rootPath = str_replace($prefix, $baseDir, implode('\\', $nPath));
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $rootPath) . '.php';

            $file = realpath($fileName);
            if (file_exists($file)) {
                require_once $file;
            } else {
                throw new \ErrorException("Cannot load this class: {$class}, " . $file);
            }
        }
    }