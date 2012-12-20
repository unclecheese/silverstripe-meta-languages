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
	public static $enable_compiling = true;




	/**
	 * @var array A list of environments and/or hostnames where code should be compiled
	 * 			  at runtime.
	 */
	protected static $environments = array (
		'test',
		'dev'
	);



	/**
	 * @var int The number of seconds difference between an uncompiled
	 * 			file and a complied file that should trigger a compile.
	 *			This value is used to control unnecessarily compling on
	 *			every request, and limit them to only times when raw
	 *			file has been modified. It should represent the maximum
	 *			amount of time that a compliling script should take to run
	 * @see {@link MetaLanguages::within_modification_tolerance()}
	 *
	 */
	public static $modification_tolerance = 5;



	/**
	 * Determines whether this environment should compile meta-languages
	 *
	 * @return bool
	 */
	protected static function should_compile() {
		if(!Config::inst()->forClass("MetaLanguages")->enable_compiling) {
			return false;
		}
		return 	(in_array(Director::get_environment_type(), self::$environments)) || 
				(in_array($_SERVER['HTTP_HOST'], self::$environments));
	}



	public static function __callStatic($method, $args) {
		if(substr($method, 0, 8) == "require_") {
			$dependency = substr($method, 8);
			$class ="Requirement_".$dependency;
			if(!class_exists($class)) {
				user_error("MetaLanguages::$method -- $class doesn't exist.", E_USER_ERROR);
			}
			$req = Object::create($class, $args[0]);
			if(self::should_compile()) {
				$req->compile();
			}
			call_user_func(
				"Requirements::{$req->getBaseRequirement()}",
				$req->getCompiledPath()
			);
		}
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
	 * Runs a shell command and uses proc_open to keep a careful watch
	 * on the output. More reliable than shell_exec().
	 *
	 * @param string $cmd The command to run
	 * @return array An array containing the termination code, and text output
	 *				 from the process.
	 */
	public static function run_command($cmd) {
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



	/**
	 * Compares the modified times of two files and determines if compiling should
	 * happen, based on {@link self::$modification_tolerance}. This is to limit running
	 * compiling on every request, and only run compiling scripts when changes have been
	 * made to the uncomplied file(s).
	 *
	 * @param string $file1 The path to the first file
	 * @param string $file2 The path to the second file
	 * @return bool True if the two files are close enough in modification time
	 *					 that they don't need to be compiled.
	 *
	 */
	public static function within_modification_tolerance($file1, $file2) {
		return abs(filemtime(BASE_PATH."/".$file1) - filemtime(BASE_PATH."/".$file2)) <= Config::inst()->forClass("MetaLanguages")->modification_tolerance;
	}


}