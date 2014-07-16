<?php

namespace Bit3\Contao\Theme\RevealJs\Basic;

class Hooks
{
	public function loadLayout($dc)
	{
		$layout = \Database::getInstance()
			->prepare('SELECT * FROM tl_layout WHERE id=?')
			->execute($dc->id);

		if ($layout->useRevealJs) {
			Loader::load();
		}
	}

	public function saveLayout($dc)
	{
		$layout = \Database::getInstance()
			->prepare('SELECT * FROM tl_layout WHERE id=?')
			->execute($dc->id);

		if ($layout->useRevealJs) {
			$update = array();

			if ($layout->sections) {
				$update['sections'] = '';
			}
			if (count(deserialize($layout->framework, true))) {
				$update['framework'] = serialize(array());
			}
			if (count(deserialize($layout->newsfeeds, true))) {
				$update['newsfeeds'] = serialize(array());
			}
			if (count(deserialize($layout->calendarfeeds, true))) {
				$update['calendarfeeds'] = serialize(array());
			}
			if ($layout->template == 'fe_page') {
				$update['template'] = 'fe_reveal';
			}
			if ($layout->addJQuery) {
				$update['addJQuery'] = '';
			}
			if ($layout->addMooTools) {
				$update['addMooTools'] = '';
			}
			if ($layout->static) {
				$update['static'] = '';
			}

			if (count($update)) {
				\Database::getInstance()
					->prepare('UPDATE tl_layout %s WHERE id=?')
					->set($update)
					->execute($dc->id);
			}
		}
	}

	public function getPageLayout(\PageModel $page, \LayoutModel $layout, \PageRegular $pageRegular)
	{
		if ($layout->useRevealJs) {
			Loader::load();

			$basePath = $GLOBALS['TL_CONFIG']['revealJsPath'] . '/' . $GLOBALS['TL_CONFIG']['revealJsVersion'];
			$cssPath  = $basePath . '/css/';
			$jsPath   = $basePath . '/js/';

			if (!is_array($GLOBALS['TL_CSS'])) {
				$GLOBALS['TL_CSS'] = (array) $GLOBALS['TL_CSS'];
			}

			if ($layout->revealJsTheme) {
				array_unshift($GLOBALS['TL_CSS'], $cssPath . 'theme/' . $layout->revealJsTheme . '.css');
			}

			array_unshift(
				$GLOBALS['TL_CSS'],
				$cssPath . 'reveal' . ($GLOBALS['TL_CONFIG']['revealJsUseMinified'] ? '.min' : '') . '.css'
			);

			if (!is_array($GLOBALS['TL_JAVASCRIPT'])) {
				$GLOBALS['TL_JAVASCRIPT'] = (array) $GLOBALS['TL_JAVASCRIPT'];
			}

			array_unshift(
				$GLOBALS['TL_JAVASCRIPT'],
				$jsPath . 'reveal' . ($GLOBALS['TL_CONFIG']['revealJsUseMinified'] ? '.min' : '') . '.js'
			);

			if (!is_array($GLOBALS['TL_BODY'])) {
				$GLOBALS['TL_BODY'] = (array) $GLOBALS['TL_BODY'];
			}

			$options = array(
				'controls'             => (bool) $layout->revealJsControls,
				'progress'             => (bool) $layout->revealJsProgress,
				'slideNumber'          => (bool) $layout->revealJsSlideNumber,
				'history'              => (bool) $layout->revealJsHistory,
				'keyboard'             => (bool) $layout->revealJsKeyboard,
				'overview'             => (bool) $layout->revealJsOverview,
				'center'               => (bool) $layout->revealJsCenter,
				'touch'                => (bool) $layout->revealJsTouch,
				'loop'                 => (bool) $layout->revealJsLoop,
				'rtl'                  => (bool) $layout->revealJsRtl,
				'fragments'            => (bool) $layout->revealJsFragments,
				'embedded'             => (bool) $layout->revealJsEmbedded,
				'autoSlide'            => (int) $layout->revealJsAutoSlide,
				'autoSlideStoppable'   => (bool) $layout->revealJsAutoSlideStoppable,
				'mouseWheel'           => (bool) $layout->revealJsMouseWheel,
				'hideAddressBar'       => (bool) $layout->revealJsHideAddressBar,
				'previewLinks'         => (bool) $layout->revealJsPreviewLinks,
				'transition'           => (string) $layout->revealJsTransition,
				'transitionSpeed'      => (string) $layout->revealJsTransitionSpeed,
				'backgroundTransition' => (string) $layout->revealJsBackgroundTransition,
				'viewDistance'         => (int) $layout->revealJsViewDistance,
			);

			$size  = deserialize($layout->revealJsSize, true);
			$scale = deserialize($layout->revealJsScale, true);

			if (strlen($size[0])) {
				$options['width'] = (int) $size[0];
			}
			if (strlen($size[1])) {
				$options['height'] = (int) $size[1];
			}
			if (strlen($layout->revealJsMargin)) {
				$options['margin'] = (double) $layout->revealJsMargin;
			}
			if (strlen($scale[0])) {
				$options['minScale'] = (double) $scale[0];
			}
			if (strlen($scale[1])) {
				$options['maxScale'] = (double) $scale[1];
			}

			$options = json_encode($options);

			$GLOBALS['TL_BODY'][] = <<<EOF
<script>
Reveal.initialize({$options});
</script>
EOF;
		}
	}
}
