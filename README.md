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
By default, CoffeeScript compiles to project-dir/javascript, and SASS compiles to themes/my-theme/css, but those paths can be overridden.
```php
<?php
MetaLanguages::set_scss_target_dir("themes/my-theme/css/compiled");
MetaLanguages::set_coffeescript_target_dir("mysite/javascript/compiled");
```

## Specifying a path to the "coffee" executable
The /usr/local/bin path is forced into the shell environment by default, but if you need more control over it:
```php
<?php
MetaLanguages::set_coffee_exec("/my/path/to/coffee");
```

## To-do
Compiling SASS is exceedingly difficult from within PHP because it is a Ruby gem, so environmental issues are numerous. This module uses a thirdparty PHP compiler for SASS, which is known to have some bugs.