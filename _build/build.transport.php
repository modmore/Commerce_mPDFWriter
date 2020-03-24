<?php

/**
 * @param string $filename The name of the file.
 * @return string The file's content
 * @by splittingred
 */
function getSnippetContent($filename = '') {
    $o = file_get_contents($filename);
    $o = str_replace('<?php','',$o);
    $o = str_replace('?>','',$o);
    $o = trim($o);
    return $o;
}

/**
 * Empties a folder. Used in our case for removing unneeded fonts to reduce package size.
 * Based on a PR by @jako to the Git Package Management (GPM) tool for MODX.
 * https://github.com/theboxer/Git-Package-Management/pull/112
 * @param $path
 * @param string $filemask
 */
function emptyFolder($path, $filemask = '*') {
    $inverse = false;
    if (strpos($filemask, '!') === 0) {
        $filemask = substr($filemask, 1);
        $inverse = true;
    }
    $files = glob($path . '/' . $filemask, GLOB_BRACE);
    if ($inverse) {
        $allFiles = glob($path . '/*');
        $files = array_diff($allFiles, $files);
    }
    foreach ($files as $file) {
        if (is_dir($file)) {
            emptyFolder($file, '*');
            rmdir($file);
        } else {
            unlink($file);
        }
    }
    return;
}

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

if (!defined('MOREPROVIDER_BUILD')) {
    /* define version */
    define('PKG_NAME', 'Commerce_mPDFWriter');
    define('PKG_NAMESPACE', 'commerce_mpdfwriter');
    define('PKG_VERSION', '1.1.2');
    define('PKG_RELEASE', 'pl');

    /* load modx */
    require_once dirname(dirname(__FILE__)) . '/config.core.php';
    require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
    $modx= new modX();
    $modx->initialize('mgr');
    $modx->setLogLevel(modX::LOG_LEVEL_INFO);
    $modx->setLogTarget('ECHO');

    flush();
    $targetDirectory = dirname(dirname(__FILE__)) . '/_packages/';
}
else {
    $targetDirectory = MOREPROVIDER_BUILD_TARGET;
}

$root = dirname(dirname(__FILE__)).'/';
$sources= array (
    'root' => $root,
    'build' => $root .'_build/',
    'events' => $root . '_build/events/',
    'resolvers' => $root . '_build/resolvers/',
    'validators' => $root . '_build/validators/',
    'data' => $root . '_build/data/',
    'plugins' => $root.'_build/elements/plugins/',
    'snippets' => $root.'_build/elements/snippets/',
    'source_core' => $root.'core/components/'.PKG_NAMESPACE,
    'source_assets' => $root.'assets/components/'.PKG_NAMESPACE,
    'lexicon' => $root . 'core/components/'.PKG_NAMESPACE.'/lexicon/',
    'docs' => $root.'core/components/'.PKG_NAMESPACE.'/docs/',
    'model' => $root.'core/components/'.PKG_NAMESPACE.'/model/',
);
unset($root);

$modx->loadClass('transport.modPackageBuilder','',false, true);
$builder = new modPackageBuilder($modx);
$builder->directory = $targetDirectory;
$builder->createPackage(PKG_NAMESPACE,PKG_VERSION,PKG_RELEASE);
$builder->registerNamespace(PKG_NAMESPACE,false,true,'{core_path}components/'.PKG_NAMESPACE.'/', '{assets_path}components/'.PKG_NAMESPACE.'/');

$modx->log(modX::LOG_LEVEL_INFO,'Packaged in namespace.'); flush();

/* Specify directories to empty (mainly large fonts) */
$emptyFolders = [
    $sources['source_core'].'/vendor/mpdf/mpdf/tmp' => "*",
    $sources['source_core'].'/vendor/mpdf/mpdf/ttfonts' => "!{DejaVu*,Free*}",
    $sources['source_core'].'/vendor/mpdf/mpdf/ttfontdata' => "*"
];
foreach ($emptyFolders as $emptyFolder => $emptyFiles) {
    emptyFolder($emptyFolder, $emptyFiles);
}
$modx->log(modX::LOG_LEVEL_INFO,'Removed unused fonts from mPDF.'); flush();

$builder->package->put(
    [
        'source' => $sources['source_core'],
        'target' => "return MODX_CORE_PATH . 'components/';",
    ],
    [
        'vehicle_class' => 'xPDOFileVehicle',
        'validate' => [
            [
                'type' => 'php',
                'source' => $sources['validators'] . 'requirements.script.php'
            ]
        ],
        'resolve' => array(
            array(
                'type' => 'php',
                'source' => $sources['resolvers'] . 'loadmodules.resolver.php',
            )
        )
    ]
);

$modx->log(modX::LOG_LEVEL_INFO,'Packaged in resolvers.'); flush();

/* now pack in the license file, readme and setup options */
$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
));
$modx->log(modX::LOG_LEVEL_INFO,'Packaged in package attributes.'); flush();

$modx->log(modX::LOG_LEVEL_INFO,'Packing...'); flush();
$builder->pack();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO,"\n<br />Package Built.<br />\nExecution time: {$totalTime}\n");
