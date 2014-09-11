<?php

namespace Bit3\Contao\Theme\RevealJs\Basic;

class Hooks
{
    public function loadLayoutDca($dc)
    {
        $layout = \Database::getInstance()
                           ->prepare('SELECT * FROM tl_layout WHERE id=?')
                           ->execute($dc->id);

        if ($layout->useRevealJs) {
            Loader::load();

            // {title_legend},name;
            // {header_legend},rows;
            // {column_legend},cols;
            // {sections_legend:hide},sections,sPosition;
            // {webfonts_legend:hide},webfonts;
            // {style_legend},framework,stylesheet,external;
            // {feed_legend:hide},newsfeeds,calendarfeeds;
            // {modules_legend},modules;
            // {expert_legend:hide},template,doctype,viewport,titleTag,cssClass,onload,head;
            // {jquery_legend},addJQuery;
            // {mootools_legend},addMooTools;
            // {script_legend:hide},analytics,script;
            // {static_legend},static'

            \MetaPalettes::appendBefore(
                'tl_layout',
                'default',
                'expert',
                array(
                    'revealJs' => array(
                        'revealJsPrint',
                        'revealJsTheme',
                        'revealJsSize',
                        'revealJsMargin',
                        'revealJsScale',
                        'revealJsControls',
                        'revealJsProgress',
                        'revealJsSlideNumber',
                        'revealJsHistory',
                        'revealJsKeyboard',
                        'revealJsOverview',
                        'revealJsCenter',
                        'revealJsTouch',
                        'revealJsLoop',
                        'revealJsRtl',
                        'revealJsFragments',
                        'revealJsEmbedded',
                        'revealJsAutoSlide',
                        'revealJsAutoSlideStoppable',
                        'revealJsMouseWheel',
                        'revealJsHideAddressBar',
                        'revealJsPreviewLinks',
                        'revealJsTransition',
                        'revealJsTransitionSpeed',
                        'revealJsBackgroundTransition',
                        'revealJsViewDistance'
                    )
                )
            );

            \MetaPalettes::removeFields(
                'tl_layout',
                'default',
                array('sections', 'sPosition', 'static')
            );
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

    public function loadArticleDca($dc)
    {
        $layout  = null;
        $article = \ArticleModel::findByPk($dc->id);
        $page    = \PageModel::findWithDetails($article->pid);

        while (!$layout && $page) {
            if ($page->includeLayout) {
                $layout = \LayoutModel::findByPk($page->layout);
            } else {
                $page = \PageModel::findWithDetails($page->pid);
            }
        }

        if ($layout && $layout->useRevealJs) {
            \MetaPalettes::appendFields(
                'tl_article',
                'default',
                'template',
                array(
                    'revealVerticalSlide'
                )
            );
        }
    }

    public function getArticleLabel($row, $label)
    {
        $page   = \PageModel::findWithDetails($row['pid']);
        $layout = $page->getRelated('layout');

        $callback = $GLOBALS['TL_DCA']['tl_article']['list']['label']['reveal_original_label_callback'];
        if (is_array($callback)) {
            $callback[0] = \System::importStatic($callback[0]);
        }
        $label = call_user_func($callback, $row, $label);

        if ($layout->useRevealJs) {
            if ($row['revealVerticalSlide'] == 'start') {
                $label = '&boxhd; ' . $label;
            } else {
                if ($row['revealVerticalSlide'] == 'stop') {
                    $label = '&boxhu; ' . $label;
                } else {
                    $predecessors = \ArticleModel::findBy(
                        array('pid = ?', 'sorting < ?', 'revealVerticalSlide != ?'),
                        array($row['pid'], $row['sorting'], ''),
                        array('order' => 'sorting DESC', 'limit' => 1)
                    );

                    if ($predecessors && $predecessors->revealVerticalSlide == 'start') {
                        $successor = \ArticleModel::findOneBy(
                            array('pid = ?', 'sorting > ?'),
                            array($row['pid'], $row['sorting']),
                            array('order' => 'sorting', 'limit' => 1)
                        );

                        if ($successor && $successor->revealVerticalSlide == 'start') {
                            $label = '&boxhu; ' . $label;
                        } else {
                            $label = '&boxv; ' . $label;
                        }
                    }
                }
            }

            $predecessors = \ArticleModel::findBy(
                array('pid = ?', 'sorting < ?'),
                array($row['pid'], $row['sorting']),
                array('order' => 'sorting')
            );

            if ($predecessors) {
                $slide = $predecessors->count();
                $page  = -1;

                $inVertical = false;
                foreach ($predecessors as $predecessor) {
                    if ($predecessor->revealVerticalSlide == 'start') {
                        $inVertical = true;
                    }

                    if ($inVertical && $predecessor->revealVerticalSlide != 'start') {
                        $page += .001;
                    } else {
                        $page = (int) ($page + 1);
                    }

                    if ($predecessor->revealVerticalSlide == 'stop') {
                        $inVertical = false;
                    }
                }

                if ($inVertical && $row['revealVerticalSlide'] != 'start') {
                    $page += .001;
                } else {
                    $page = (int) ($page + 1);
                }

                if ($inVertical) {
                    $pageMain = (int) $page;
                    $pageSub  = (int) (($page - $pageMain) * 1000);

                    $page = sprintf('%d-%d', $pageMain, $pageSub);
                } else {
                    $page = (int) $page;
                }

            } else {
                $slide = 0;
                $page  = 0;
            }

            $label .= ' ' . sprintf($GLOBALS['TL_LANG']['tl_article']['revealSlideNumber'], $slide, $page);
        }
        
        return $label;
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

            if ($layout->revealJsPrint == 'pdf') {
                array_unshift(
                    $GLOBALS['TL_CSS'],
                    $cssPath . 'print/pdf.css'
                );
            } else {
                if ($layout->revealJsPrint == 'paper') {
                    array_unshift(
                        $GLOBALS['TL_CSS'],
                        $cssPath . 'print/paper.css'
                    );
                }
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

            $GLOBALS['TL_BODY'][]                   = <<<EOF
<script>
Reveal.initialize({$options});
</script>
EOF;
            $GLOBALS['TL_HOOKS']['parseTemplate'][] = array('Bit3\Contao\Theme\RevealJs\Basic\Hooks', 'parseTemplate');
        }
    }

    public function parseTemplate(\Template $template)
    {
        if (substr($template->getName(), 0, 3) == 'ce_' && $template->imgSize) {
            $template->imgSize = '';
        }
    }
}
