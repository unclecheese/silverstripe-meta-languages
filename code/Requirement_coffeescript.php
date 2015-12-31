<?php


/**
 * 
 * Defines a object that takes a path to a raw CoffeeScript file
 * and compiles it down into JavaScript or inclusion in the
 * {@link Requirements} chain
 *
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 * @package meta_languages
 */
class Requirement_coffeescript extends MetaLanguage
{
    

    /**
     * @var string The path to the compiled JS file
     */
    public static $compiled_path;


    
    /**
     * @var string The path to the "coffee" executable
     */
    public static $coffee_exec = "coffee";




    /**
     * Returns the path for the compiled JS file. Falls back on
     * the project dir if {@link self::$compiled_path} is undefined.
     *
     * @return string
     */
    public function getCompiledPath()
    {
        $new_file = basename($this->uncompiledFile, ".coffee").".js";
        if (!$this->config()->compiled_path) {
            return project()."/javascript/".$new_file;
        }
        return $this->config()->compiled_path."/".$new_file;
    }




    /**
     * Defines that the Requirements::javascript() method should be called
     * for meta-languages of this type.
     *
     * @return string
     */
    public function getBaseRequirement()
    {
        return "javascript";
    }



    /**
     * Compiles the $uncompiledFile into JS
     */
    public function compile()
    {
        if (!class_exists("CoffeeScript\\Compiler")) {
            user_error("CoffeeScript requires the PHP CoffeeScript compiler to run. You can install with \"composer require coffeescript/coffeescript\"", E_USER_ERROR);
        }
        if (MetaLanguages::within_modification_tolerance($this->uncompiledFile, $this->getCompiledPath())) {
            return;
        }
        $file = BASE_PATH.'/'.$this->uncompiledFile;
        try {
            $coffee = file_get_contents($file);
            $js = CoffeeScript\Compiler::compile($coffee, array('filename' => $file));
            $js_file = fopen(BASE_PATH.'/'.$this->getCompiledPath(), "w");
            fwrite($js_file, $js);
            fclose($js_file);
        } catch (Exception $e) {
            user_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
