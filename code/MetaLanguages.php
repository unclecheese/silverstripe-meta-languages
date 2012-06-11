<?php

/**
 * This class allows the direct inclusion of dependencies written
 * in meta-languages, e.g. coffeescript, SASS. It is designed
 * to automatically compile the code into their natural JS/CSS
 * state with each page load. Special care is given to limiting
 * this compiling process to only certain environments, e.g. dev, test.
 * The compiling can be forced on or off, as well, allowing the user
 * to use his own logic for determining a "compilable" environment.
 *
 * @todo Support for LESS
 * @todo The true SASS compiler cannot be run from within PHP very easily
 * 		 because it is a Ruby gem, and raises all sorts of environment
 *		 issues when running shell_exec(). Instead, this class uses
 *		 a thirdparty PHP class that compiles SASS which is known to
 *		 have some bugs. 
 * @author Uncle Cheese <unclecheese@leftandmain.com>
 * @package meta_languages
 */
class MetaLanguages {
	

	/**
	 * @var bool If false, compiling is turned off unconditionally, without
	 *			 regard to the environment
	 */
	protected static $enable_compiling = true;



	/**
	 * @var string The target directory of compiled SASS, relative to the SS root
	 * 			   e.g. themes/my-theme/css
	 */
	protected static $scss_target_dir;



	/**
	 * @var string The target directory of compiled CoffeeScript, relative to the SS root
	 * 			   e.g. mysite/javascript/
	 */
	protected static $coffeescript_target_dir;



	/**
	 * @var array A list of environments and/or hostnames where code should be compiled
	 * 			  at runtime.
	 */
	protected static $environments = array (
		'test',
		'dev'
	);



	/**
	 * @var string Path to the "coffee" executable script.
	 */
	protected static $coffee_exec = "coffee";



	/**
	 * Determines whether this environment should compile meta-languages
	 *
	 * @return bool
	 */
	protected static function should_compile() {
		if(!self::$enable_compiling) {
			return false;
		}
		return 	(in_array(Director::get_environment_type(), self::$environments)) || 
				(in_array($_SERVER['HTTP_HOST'], self::$environments));
	}



	/**
	 * Compiles SASS to the {@link $scss_target_dir}
	 *
	 * @param string The path to the raw SASS, relative to SS root
	 * @return string The path to the compiled CSS file
	 */
	protected static function compile_scss($path) {
		$target = self::get_compiled_scss_path($path);		
		$parser = new SassParser();				
		$sass = $parser->toCss($path);
		if(file_exists($target) && !is_writable($target)) {
			user_error("SCSS compiling error: $path is not writable.", E_USER_ERROR);
		}
		elseif(!is_writable(dirname($path))) {
			user_error("SCSS compiling error: $path is not writable.", E_USER_ERROR);	
		}
		$file = fopen($target,"w");
		fwrite($file, $sass);
		fclose($file);
		return $target;
	}



	/**
	 * Compiles CoffeeScript to the {@link $scss_target_dir}
	 *
	 * @param string The path to the raw CoffeeSCript, relative to SS root
	 * @return string The path to the compiled JS file
	 */
	protected static function compile_coffeescript($path) {
		putenv("PATH=/usr/local/bin");
		chdir(BASE_PATH);
		$target_dir = self::get_coffeescript_target_dir();
		$target_file = self::get_compiled_coffeescript_path($path);
		$exec = sprintf("%s -c -o %s %s",
					self::$coffee_exec,
					$target_dir,
					$path
				);
		$output = self::run_command($exec);				
		if(!file_exists($target_file)) {
			user_error("Could not compile CoffeeScript. Ran command '$exec', Code: " .$output['code'] . " Output: ".$output['output'], E_USER_ERROR);
		}
		return $target_file;
	}



	/**
	 * A utility method that removes a given file extension and replaces
	 * it with a new one.
	 *
	 * @param string $filename The path to a file whose extension will be replaced
	 * @param string $newExt The new extension for the file
	 * @return The path to the new filename
	 */
	protected static function replace_file_extension($filename, $newExt) {
		$ext = strrchr($filename, '.');  
		if($ext !== false)  {
			$filename = substr($filename, 0, -strlen($ext));  
			return "$filename.$newExt";
		}
		return $filename;
	}



	/**
	 * Requires a SASS file. This is a call that would typically
	 * go in a controller to include a dependency.
	 *
	 * @param string $path The path to the raw, uncompiled file
	 */
	public static function require_scss($path) {
		if(self::should_compile()) {
			$compiled = self::compile_scss($path);			
			Requirements::css($compiled);
		}
		Requirements::css(self::get_compiled_scss_path($path));
	}



	/**
	 * Require a CoffeeScript file. This is a call taht would typically
	 * go in a controller to include a dependency.
	 *
	 * @param string $path The path to the raw, uncompiled file
	 */
	public static function require_coffeescript($path) {
		if(self::should_compile()) {
			$compiled = self::compile_coffeescript($path);
			Requirements::css($compiled);
		}		
		Requirements::javascript(self::get_compiled_coffeescript_path($path));
	}


	/**
	 * Given a path to an uncompiled SASS file, get its destination
	 * as compiled CSS. Purely hypothetical -- does no processing.
	 *
	 * @param string The path to the uncompiled file
	 * @return The path to the compiled file
	 */
	public static function get_compiled_scss_path($path) {
		$target = self::get_scss_target_dir()."/".basename($path);
		return self::replace_file_extension($target,"css");
	}



	/**
	 * Given a path to an uncompiled CoffeeScript file, get its destination
	 * as compiled JS. Purely hypothetical -- does no processing.
	 *
	 * @param string The path to the uncompiled file
	 * @return The path to the compiled file
	 */
	public static function get_compiled_coffeescript_path($path) {
		$target = self::get_coffeescript_target_dir()."/".basename($path);
		return self::replace_file_extension($target,"js");
	}



	/**
	 * Sets the enironments that are eligible for compiling.
	 * @example
	 * 	<code>
	 *		MetaLanguages::set_compile_environments(array(
     *			'dev',
	 *			'localhost:8888',
	 *			'staging.mydomain.com'
	 *		));
	 * </code>
	 */	 
	public static function set_compile_environments($env) {
		self::$environments = $env;
	}



	/**
	 * Sets the destination directory for all compiled SASS.
	 * If not defined, it will fall back on your theme CSS dir.
	 *
	 * @param string $dir The target directory, relative to SS root
	 */
	public static function set_scss_target_dir($dir) {
		self::$scss_target_dir = $dir;
	}



	/**
	 * Sets the destination directory for all compiled CoffeeScript.
	 * If not defined, it will fall back on your project/javascript dir.
	 *
	 * @param string $dir The target directory, relative to SS root
	 */
	public static function set_coffeescript_target_dir($dir) {
		self::$coffeescript_target_dir = $dir;
	}



	/**
	 * Gets the destination directory for compiled SASS. If no override
	 * directory is defined, it resolves to the theme CSS dir
	 *
	 * @return string The target CSS directory
	 */
	public static function get_scss_target_dir() {
		return self::$scss_target_dir ? self::$scss_target_dir : SSViewer::get_theme_folder()."/css";
	}



	/**
	 * Gets the destination directory for compiled CoffeeScript. If no override
	 * directory is defined, it resolves to the project/javascript directory
	 *
	 * @return string The target Javascript directory
	 */
	public static function get_coffeescript_target_dir() {
		return self::$coffeescript_target_dir ? self::$coffeescript_target_dir : project()."/javascript";
	}



	/**
	 * Sets an executable path to the CoffeeScript compiler.
	 * @example MetaLanguages::set_coffee_exec("/usr/local/bin/node /usr/local/bin/coffee");
	 *
	 * @param string $path The path to the executable
	 */
	public static function set_coffee_exec($path) {
		self::$coffee_exec = $path;
	}



	/**
	 * Runs a shell command and uses proc_open to keep a careful watch
	 * on the output. More reliable than shell_exec().
	 *
	 * @param string $cmd The command to run
	 * @return array An array containing the termination code, and text output
	 *				 from the process.
	 */
	protected static function run_command($cmd) {
	   $descriptorspec = array(
	       0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
	       1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
	       2 => array("pipe", "w") // stderr is a file to write to
	   );
	
	   $pipes= array();	   
	   $process = proc_open($cmd, $descriptorspec, $pipes);
	   $output= "";
	
	   if (!is_resource($process)) return false;
	
	   #close child's input immediately
	   fclose($pipes[0]);
	
	   stream_set_blocking($pipes[1],false);
	   stream_set_blocking($pipes[2],false);
	
	   $todo= array($pipes[1],$pipes[2]);
	
	   while( true ) {
	       $read= array();
	       if( !feof($pipes[1]) ) $read[]= $pipes[1];
	       if( !feof($pipes[2]) ) $read[]= $pipes[2];
	
	       if (!$read) break;
		   $num = 2;
	       $ready= stream_select($read, $write, $ex, $num);
	
	       if ($ready === false) {
	           break; #should never happen - something died
	       }
	
	       foreach ($read as $r) {
	           $s= fread($r,1024);
	           $output.= $s;
	       }
	   }
	
	   fclose($pipes[1]);
	   fclose($pipes[2]);
	
	   $termination_code = proc_close($process);	   
	   return array(
	   	'output' => $output,
	   	'code' => $termination_code
	   );
		
	}


}