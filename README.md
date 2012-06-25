# Meta-Languages for SilverStripe
This module allows the direct inclusion of uncompiled meta-language dependencies such as CoffeeScript, LESS, and SASS into your project and compiles them at runtime.

## Basic Usage
Just require the dependency in your controller, and use it just like Requirements.

```php
<?php
MetaLanguages::require_scss('themes/my-theme/css/sass/my-sass.scss');
MetaLanguages::require_coffeescript('mysite/javascript/coffee/my-coffee.coffee');
```

## Limiting the compiling
By default, files are only compiled in "test" and "dev" environments. To limit compiling to specific environments:
```php
<?php
MetaLanguages::set_compile_environments(array(
  'dev',
  'localhost:8888',
  'staging.example.com'
));
```

## Specifying target directories
By default, CoffeeScript compiles to project-dir/javascript, and SASS/LESS compiles to themes/my-theme/css, but those paths can be overridden.
```php
<?php
Requirement_coffeescript::$compiled_path = "mysite/coffee";
Requirement_scss::$compiled_path = "mysite/sass";
```

## Specifying a path to the "coffee" executable
The /usr/local/bin path is forced into the shell environment by default, but if you need more control over it:
```php
<?php
Requirement_coffeescript::$coffee_exec = "/my/path/to/coffee;
```

## Change the modification time tolerance that triggers a compile. Compiling doesn't happen unless the "last edited" time difference between the raw and uncompiled file is greater than a specific number of seconds (defaults to 5)
```php
<?php
MetaLanguages::$modification_tolerance = 10;
```

## To-do
Compiling SASS is exceedingly difficult from within PHP because it is a Ruby gem, so environmental issues are numerous. This module uses a thirdparty PHP compiler for SASS, which is known to have some bugs.