<?php


/**
 * Defines the base class for a meta-language
 *
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 * @package meta_languages
 */
abstract class MetaLanguage implements MetaLanguageInterface
{
    

    /**
     * @var string The path to the raw, unprocessed file
     */
    protected $uncompiledFile;

    

    /**	
     * @param string a path to a raw, unprocessed file to be compiled
     */
    public function __construct($path)
    {
        $this->uncompiledFile = $path;
    }



    /**
     * A shortcut to getting the config, since this class is not a descendant of {@link Object}
     *
     * @return Config
     */
    public function config()
    {
        return Config::inst()->forClass(get_class($this));
    }
}
