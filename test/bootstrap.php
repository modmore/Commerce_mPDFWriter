<?php
require_once dirname(dirname(__FILE__)) . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('web');
$modx->getService('error','error.modError', '', '');
$modx->setLogTarget('ECHO');
$modx->setLogLevel(xPDO::LOG_LEVEL_ERROR);
$path = $modx->getOption('commerce.core_path', null, $modx->getOption('core_path') . 'components/commerce/');
$commerce = $modx->getService('commerce', 'Commerce', $path . 'model/commerce/', array('mode' => 'unit_test'));

require_once dirname(__DIR__) . '/core/components/commerce_mpdfwriter/vendor/autoload.php';

