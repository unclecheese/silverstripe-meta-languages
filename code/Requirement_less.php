<?php


/**
 * 
 * Defines a object that takes a path to a raw LESS file
 * and compiles it down into CSS or inclusion in the
 * {@link Requirements} chain
 *
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 * @package meta_languages
 */
class Requirement_less extends MetaLanguage {



	/**
	 * @var string The path to the compiled JS file
	 */
	public static $compiled_path;



	/**
	 * Returns the path for the compiled CSS file. Falls back on
	 * the theme_dir/css if {@link self::$compiled_path} is undefined.
	 *
	 * @return string
	 */
	public function getCompiledPath() {
		$new_file = basename($this->uncompiledFile,".less").".css";
		if(!self::$compiled_path) {
			return SSViewer::get_theme_folder()."/css/".$new_file;
		}
		return self::$compiled_path."/".$new_file;

	}




	/**
	 * Defines that the Requirements::css() method should be called
	 * for meta-languages of this type.
	 *
	 * @return string
	 */
	public function getBaseRequirement() {
		return "css";
	}




	/**
	 * Compiles the $uncompiledFile into JS
	 */
	public function compile() {
		$path = $this->getCompiledPath();
		if(file_exists($path) && !is_writable($this->getCompiledPath())) {
			user_error("SCSS compiling error: $path is not writable.", E_USER_ERROR);
		}
		elseif(!is_writable(dirname($path))) {
			user_error("SCSS compiling error: $path is not writable.", E_USER_ERROR);	
		}
		lessc::ccompile(BASE_PATH."/".$this->uncompiledFile, BASE_PATH."/".$path);
	}

	
}