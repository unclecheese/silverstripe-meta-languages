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
class Requirement_coffeescript extends MetaLanguage {
	

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
	public function getCompiledPath() {
		$new_file = basename($this->uncompiledFile,".coffee").".js";
		if(!self::$compiled_path) {
			return project()."/javascript/".$new_file;
		}
		return self::$compiled_path."/".$new_file;

	}




	/**
	 * Defines that the Requirements::javascript() method should be called
	 * for meta-languages of this type.
	 *
	 * @return string
	 */
	public function getBaseRequirement() {
		return "javascript";
	}



	/**
	 * Compiles the $uncompiledFile into JS
	 */
	public function compile() {
		if(MetaLanguages::within_modification_tolerance($this->uncompiledFile, $this->getCompiledPath())) {
			return;
		}
		putenv("PATH=/usr/local/bin");
		chdir(BASE_PATH);
		$target_dir = dirname($this->uncompiledFile);
		$target_file = $this->getCompiledPath();
		$exec = sprintf("%s -c -o %s %s",
					self::$coffee_exec,				
					dirname($target_file),
					$target_dir
				);
		$output = MetaLanguages::run_command($exec);
		if(!empty($output['output'])) {
			user_error("Error compiling CoffeeScript: " . $output['output'], E_USER_ERROR);
		}	
		if(!file_exists($target_file)) {
			user_error("Could not compile CoffeeScript. Ran command '$exec', Code: " .$output['code'] . " Output: ".$output['output'], E_USER_ERROR);
		}		
	}


}