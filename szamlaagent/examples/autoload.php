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
        $baseDir = __DIR__ . '/../src/' . $apiName .DIRECTORY_SEPARATOR;
        $path    = explode('\\', $class);
        $path[0] = strtolower($path[0]);

        if ($path[0] == $apiName) {
            for ($i = 0; $i < count($path)-1; $i++) {
                $nPath[] = $path[$i];
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