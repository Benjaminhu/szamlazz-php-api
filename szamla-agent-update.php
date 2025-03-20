<?php

$linkPage = 'https://docs.szamlazz.hu/php';

$zipBaseDir = './szamlaagent';

echo 'Hivatalos dokumentacio letoltese es `PHPApiAgent-X.Y.Z.zip` letoltes link kiszedese...' . PHP_EOL;
$content = file_get_contents($linkPage);
$matches = [];

preg_match('!"(?P<php_api_zip>[^"]+PHPApiAgent[^"]+zip)"!i', $content, $matches);

// minimal check
if (!array_key_exists('php_api_zip', $matches) || empty($matches['php_api_zip'])) {
    die(PHP_EOL . 'Nem talalhato letoltheto .zip hivatkozas az oldalon!' . PHP_EOL);
}

$urlPathData = parse_url($matches['php_api_zip']);
$fileName = pathinfo($urlPathData['path'], PATHINFO_BASENAME);

echo sprintf('URL to filename: `%s` -> `%s`', $matches['php_api_zip'], $fileName) . PHP_EOL;

$libFullUrl = $matches['php_api_zip'];
if (!array_key_exists('host', $urlPathData)) {
    $linkPagePathData = parse_url($linkPage);
    $libFullUrl = sprintf('%s://%s%s',
        $linkPagePathData['scheme'],
        $linkPagePathData['host'],
        $matches['php_api_zip']
    );
}

file_put_contents($fileName, file_get_contents($libFullUrl));
echo sprintf('Saved to local: `%s`', $fileName) . PHP_EOL;

// meglevo `szamlaagent` torlese
function delTreeRecursive($dir): bool
{
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $item = sprintf('%s/%s', $dir, $file);
        if (is_dir($item)) {
            delTreeRecursive($item);
        } else {
            unlink($item);
        }
    }

    return rmdir($dir);
}
if (is_dir($zipBaseDir)) {
    delTreeRecursive($zipBaseDir);
    echo sprintf('Regi `%s` konyvtar torlese', $zipBaseDir) . PHP_EOL;
}

echo sprintf('Kicsomagolas: `%s`', $fileName) . PHP_EOL;
$zip = new ZipArchive;
$res = $zip->open($fileName);
if (TRUE !== $res) {
    die(PHP_EOL . 'ZIP megnyitasi hiba!' . PHP_EOL);
}
$directoryInTheZip = sprintf('./%s', basename($zip->statIndex(0)['name']));
$zip->extractTo('./');
$zip->close();

if ($directoryInTheZip !== $zipBaseDir) {
    rename($directoryInTheZip, $zipBaseDir);
}

// `PHPApiAgent-X.Y.Z.zip` torlese
unlink($fileName);

echo 'PSR-4 konyvtarnev javitasok:' . PHP_EOL;
$prefixDir = sprintf('%s/src/', $zipBaseDir);
$fixDirNames = [
    'szamlaagent' => 'SzamlaAgent',

    'SzamlaAgent/waybill' => 'SzamlaAgent/Waybill',
    'SzamlaAgent/response' => 'SzamlaAgent/Response',
    'SzamlaAgent/ledger' => 'SzamlaAgent/Ledger',
    'SzamlaAgent/item' => 'SzamlaAgent/Item',
    'SzamlaAgent/header' => 'SzamlaAgent/Header',

    'SzamlaAgent/document' => 'SzamlaAgent/Document',
    'SzamlaAgent/Document/invoice' => 'SzamlaAgent/Document/Invoice',
    'SzamlaAgent/Document/receipt' => 'SzamlaAgent/Document/Receipt',

    'SzamlaAgent/creditnote' => 'SzamlaAgent/CreditNote',
];
foreach ($fixDirNames as $from => $to) {
    $oldDir = sprintf('%s%s', $prefixDir, $from);
    if (is_dir($oldDir)) {
        $newDir = sprintf('%s%s', $prefixDir, $to);
        echo sprintf('%s -> %s', $oldDir, $newDir) . PHP_EOL;
        rename($oldDir, $newDir);
    }
}

echo 'DONE' . PHP_EOL;
