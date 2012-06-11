<?php

$dir = basename(dirname(__FILE__));
if($dir != "meta_languages") {
	user_error("The meta_languages module must be in a directory named 'meta_languages'",E_USER_ERROR);
}