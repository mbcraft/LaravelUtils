## LaravelUtils

This package contains various helper classes for Laravel :

- artisan commands
- middleware for enchacing blade template syntax
- controller helper classes for default actions
- localization helpers generator
- font icons helpers generator
- route helpers generator
- database migrations helpers

# How to install with composer

In your composer.json file, add the following :

`

"require": {
	......
        "mbcraft/laravelutils": "dev-master"     <-- add this
    },

`

The run 'php composer.phar update' to download the package.


After downloading the package adding it as a dependency in your composer.json file, add the
following lines to your 'app/Console/Kernel.php' file :
`

    protected $commands = [

        ...

        \Mbcraft\Laravel\Lang\Commands\RegenerateLangHelpers::class,

        \Mbcraft\Laravel\Lang\Commands\HideLang::class,

        \Mbcraft\Laravel\Lang\Commands\ShowLang::class,

        \Mbcraft\Laravel\Http\Commands\RegenerateRoutesHelpers::class,

        \Mbcraft\Laravel\Misc\Commands\LogClear::class,

        \Mbcraft\Laravel\Icons\Commands\RegenerateIconHelpers::class,

        \Mbcraft\Laravel\Misc\Commands\ResourcePathCheck::class

    ];

`

and the followings to your app/Http/Kernel.php middleware classes :

`

    protected $middleware = [

	...

        \Mbcraft\Laravel\Http\Middleware\ConfigureCarbon::class,

        \Mbcraft\Laravel\Http\Middleware\BladeExtendedSyntax::class,

        \Mbcraft\Laravel\Http\Middleware\BladeAssetsSyntax::class,

        \Mbcraft\Laravel\Http\Middleware\BladeWidgetsSyntax::class,

        \Mbcraft\Laravel\Http\Middleware\LocalizationAliases::class

    ];

`
You should also create a writable folder inside your laravel installation 'storage/' folder called 'generated_classes/'.
Also add this folder to your composer.json project file in the classloading section in order to enable loading of generated classes :

`

    "autoload": {

        "classmap": [

            "database"

        ],

        "psr-4": {

            "App\\": "app/",

	    "": [...,"storage/generated_classes/"]   <-- this path must be added to the default namespace

        }

    }, 

`

This should complete the installation of this package. Run a 


`
./artisan
`

in order to check if the new artisan commands are now available.

Here is a brief explanation of the features provided by this package :

# New Laravel Artisan commands :

The following commands are added to artisan :

[i18n]

- lang:hide -> hides a language folder
- lang:show -> shows a language folder
- lang:regenerate_helpers -> regenerates the language helper classes

Use these commands to work using only one language folder at a time. Keep in mind
that the generated helpers will inherit all values from the root language files with the same name, so:


messages.php

customers/messages.php   


... will generate two classes : 'App\Lang\LMessages.php' and 'App\Lang\Customers\LMessages.php' and the second one will inherit all the values from the previous one, so inside code you will simply use the more specific one but will also be able to access all the values from the generic one.

[log]

- log:clear -> clears the laravel log inside the 'storage/logs/laravel.log'


[icons]

- icons:regenerate_helpers -> this will generate helper class for the font icons. 

The 'font awesome' font icons class definitions are bundled with this package and are accessed simply with :

FA::<icon method>

so, for example :


<?= FA::webApplication_Clock_o("fa-fw") ?>


will output this text :


<i class='fa fa-clock-o fa-fw'></i>


You can also pass a second parameter to the method as a tooltip text for the icon. The first parameter is a list of space separated additional css classes to add to the default ones.

Actually only font-awesome helpers is bundled with this package (only class definition, not actual font awesome files). Keep in mind to include the required stylesheet (not provided). The names of the icons are taken from fontawesome.org cheatsheet.

[routing]


- route:regenerate_helpers -> generate helpers for the routing methods. Call route:cache before this one, since the helper is generated from the cache.


This command will generate a 'Routes' and 'FormButtonJSRoutes' classes containing all routing methods defined inside your app/Http/routes.php class.
The 'Routes' class static methods are used for links to be placed inside links href attribues, while the 'FormButtonJSRoutes' are to be used inside button's onclick event handler and will actually output javascript code (no external js libraries required).
I suggest using a '.do' suffix on route names defined inside app/Http/routes.php as a convention in order to identify post routes (with '.do' suffix) and get routes. The route method name will actually end with '_do'.

 
[resources (js and css)]


- resources:check_path -> this will check all the views found inside the view path of your project, for all the resources required with


* @require_local_js (blade extension, see the 'Laravel Blade Extensions' section)
* @require_local_css (blade extension, see the 'Laravel Blade Extensions' section)


... and for each view will tell if any path is broken.

# Laravel Blade Extensions

## Resources (assets)

The following blade extensions are added :

- @require_local_js(<local_resource_path>) -> adds the resource to the js resource list needed
- @require_local_css(<local_resource_path>) -> adds the resource to the css resource list needed
- @require_remote_js(<remote_resource_path>) -> adds the resource to the js resource list needed
- @require_remote_css(<remote_resource_path>) -> adds the resource to the css resource list needed

You can safely call more than once this "require" calls and the resource will actually be loaded only once.

## Logic

- @ifdef("my_var") ... @endif -> multicheck is also supported, eg: @ifdef("my_var1","my_var2","last_var"). Shows the content only if all the variables are set.
- @ifndef("my_var") ... @endif -> multicheck is also supported. Shows the content only if all the variables are NOT set.
- @index("name") -> creates an index equal to 0 named $name . This index is incremented by one each time the @index("name") definition is done. To be used inside @foreach loops. 
- @if_has_elements($elements) ... @endif -> renders the content only if the array or the laravel collection exists and has at least one element.
- @safe_count("name")   -> returns count($name) only if $name isset, otherwise returns an empty string.
- @if_column("column_name") ... @endif -> will show the content only if the column is set inside the $show_columns array OR is not set inside the $hide_columns array. The behaviour of this command is somewhat complex to explain, just use it and remember that, if defined, $show_columns and $hide_columns containing column names will change the behaviour as you expect.
- @if_action("action_name") ... @endif -> this will work exactly like the @if_column command, but the show and hide array variables are called $show_actions and $hide_actions.
- @menuitem(route) ... @endmenuitem -> this will create a <li><a>...</a></li> element, useful for menus. The route is added as link. Also, if the route of the page matches the parameter, a "class='active'" is added to the anchor tag.

## Widgets

- @begin_widget("category/name",$params) -> used to render a widget opening part
- @end_widget("category/name",$params) -> used to render a widget closing part
- @widget("category/name") -> renders a full widget

Widgets are searched inside the view path inside the folders :


_widgets/category/name_


Opening widgets ends with '__begin.blade.php' and ending widgets ends with '__end.blade.php'.
The setup.blade.php inside the widget category is loaded whenever a widget of that category is used. This is to be used with the '@require...' commands explained before.


# Database helper classes

## ResetTablesSeeder trait class

The trait 'Mbcraft\Laravel\Database\ResetTablesSeederTrait' is provided to help resetting the table content for re-seeding. To use it, simply use it in your laravel 'database/seeds/DatabaseSeeder.php' class.
Then add a 'protected $reset_tables' array containing the names of all the tables of which you want to empty, in reverse dependency order, eg:

`

protected $reset_tables = [
      
        
                "configs",

                "invoices",
                "customers",

		...
        
                "SENTINEL_throttle",      // if you are using the cartalyst/sentinel package,
                "SENTINEL_reminders",     // this is the correct order for
                "SENTINEL_persistences",  // emptying all the tables
                "SENTINEL_activations",
                "SENTINEL_role_users",
                "SENTINEL_roles",
                "SENTINEL_users",  
    ];

`

## SoftDeletesCascade trait class

This trait will enable your "softDeletes" models to trigger a cascade behaviour as it usually happens when the real deletes occurs in databases.
Simply add this trait to your model class, then add a 'softCascades' array, containing the list of methods that reaches objects to be soft deleted when this one it soft deleted, eg:

`

protected $softCascades = ["tickets"];  // this can be in a customer class

`

# Entity controllers


... to be continued ...



====

-Marco Bagnaresi
