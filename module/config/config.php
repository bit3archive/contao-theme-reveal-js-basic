<?php

$GLOBALS['TL_CONFIG']['revealJsVersion']     = '2.6.2';
$GLOBALS['TL_CONFIG']['revealJsPath']        = 'assets/reveal-js';
$GLOBALS['TL_CONFIG']['revealJsUseMinified'] = true;

$GLOBALS['TL_HOOKS']['getPageLayout'][] = array('Bit3\Contao\Theme\RevealJs\Basic\Hooks', 'getPageLayout');
