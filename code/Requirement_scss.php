<?php


/**
 * 
 * Defines a object that takes a path to a raw SASS file
 * and compiles it down into CSS or inclusion in the
 * {@link Requirements} chain
 *
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 * @package meta_languages
 */
class Requirement_scss extends MetaLanguage {



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
		$new_file = basename($this->uncompiledFile,".scss").".css";
		if(!$this->config()->compiled_path) {
			return SSViewer::get_theme_folder()."/css/".$new_file;
		}
		return $this->config()->compiled_path."/".$new_file;

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
	 * Compiles the $uncompiledFile into CSS
	 */
	public function compile() {
		if(MetaLanguages::within_modification_tolerance($this->uncompiledFile, $this->getCompiledPath())) {
			return;
		}		
		$path = $this->getCompiledPath();
		$parser = new SassParser();				
		$sass = $parser->toCss($this->uncompiledFile);
		if(file_exists($path) && !is_writable($this->getCompiledPath())) {
			user_error("SCSS compiling error: $path is not writable.", E_USER_ERROR);
		}
		elseif(!is_writable(BASE_PATH."/".$path)) {
			user_error("SCSS compiling error: $path is not writable.", E_USER_ERROR);	
		}
		$file = fopen($path,"w");
		fwrite($file, $sass);
		fclose($file);
	}

	
}
