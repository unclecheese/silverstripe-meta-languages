<?php


/**
 * Defines the required methods for a meta-language subclass
 *
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 * @package meta_languages
 */
interface MetaLanguageInterface
{
    

    /**
     * Required method for compiling raw meta-language
     */
    public function compile();


    
    /**
     * Required method for determining the path to a file after it is compiled
     */
    public function getCompiledPath();



    /**
     * Required method for determining which Requirements::xxx() method
     * to call for inclusion in the controller
     */
    public function getBaseRequirement();
}
