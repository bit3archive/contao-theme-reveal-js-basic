<?php

namespace Bit3\Contao\Theme\RevealJs\Basic;

class Loader
{
    static public function load()
    {
        \TemplateLoader::addFiles(
            array
            (
                'fe_reveal'   => 'system/modules/reveal-js/templates',
                'mod_article' => 'system/modules/reveal-js/templates',
            )
        );
    }
}
