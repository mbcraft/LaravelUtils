## LaravelUtils

This package contains various additions for Laravel :

- new artisan commands
- middleware for enhancing blade template syntax
- controller behaviour traits for default actions
- localization helpers generator
- font icons helpers generator
- route helpers generator
- database migrations helpers

# How to install with composer

If you are using composer, install the package with 

    php composer.phar require mbcraft/laravelutils
    
    php composer.phar install


After downloading the package adding it as a dependency in your composer.json file, add the
following lines to your 'app/Console/Kernel.php' file :


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



... the followings to your app/Http/Kernel.php middleware classes :



    protected $middleware = [

	...

        \Mbcraft\Laravel\Http\Middleware\ConfigureCarbon::class,

        \Mbcraft\Laravel\Http\Middleware\BladeExtendedSyntax::class,

        \Mbcraft\Laravel\Http\Middleware\BladeAssetsSyntax::class,

        \Mbcraft\Laravel\Http\Middleware\BladeWidgetsSyntax::class,

        // \Mbcraft\Laravel\Http\Middleware\BladePolicy2Syntax::class,

        \Mbcraft\Laravel\Http\Middleware\LocalizationAliases::class

    ];


You should also create a writable folder inside your laravel installation 'storage/' folder called 'generated_classes/'.
Also add this folder to your composer.json project file in the classloading section in order to enable loading of generated classes :



    "autoload": {

        "classmap": [

            "database"

        ],

        "psr-4": {

            "App\\": "app/",

	    "": [...,"storage/generated_classes/"]   <-- this path must be added to the default namespace

        }

    }, 



This should complete the installation of this package. Run



    ./artisan


in order to check if the new artisan commands are now available.

Here is a brief explanation of the features provided by this package :

# New Laravel Artisan commands :

The following commands are available for artisan :

## I18n

- lang:overview -> shows the list of available and hidden localizations
- lang:hide -> hides a language folder
- lang:show -> shows a language folder
- lang:clean -> removes missing keys comparing two languages
- lang:regenerate_helpers -> regenerates the language helper classes

Use these commands to work using only one language folder at a time. Keep in mind
that the generated helpers will inherit all values from the root language files with the same name, so:


messages.php

customers/messages.php   


... will generate two classes : 'App\Lang\LMessages.php' and 'App\Lang\Customers\LMessages.php' and the second one will inherit all the values from the previous one, so inside code you will simply use the more specific one but will also be able to access all the values from the generic one.


Remember to add the following lines to your app/Console/Kernel.php :

    protected $commands = [
            ...
            \Mbcraft\Laravel\Lang\Commands\HideLang::class,
            \Mbcraft\Laravel\Lang\Commands\ShowLang::class,
            \Mbcraft\Laravel\Lang\Commands\CleanLang::class,
            \Mbcraft\Laravel\Lang\Commands\OverviewLang::class,
            ...
            ];


## Log

- log:clear -> clears the laravel log inside the 'storage/logs/laravel.log'

Remember to add the following lines to your app/Console/Kernel.php :

    protected $commands = [
            ...
            \Mbcraft\Laravel\Misc\Commands\LogClear::class,
            ...
            ];


## Font icons

- icons:regenerate_helpers -> this will generate helper class for the font icons. 

The 'font awesome' font icons class definitions are bundled with this package and are accessed simply with :

FA::<icon method>

so, for example :


    <?= FA::webApplication_Clock_o("fa-fw") ?>


will output this text :


    <i class='fa fa-clock-o fa-fw'></i>


You can also pass a second parameter to the method as a tooltip text for the icon. The first parameter is a list of space separated additional css classes to add to the default ones.

Actually only font-awesome helpers is bundled with this package (only class definition, not actual font awesome files). Keep in mind to include the required stylesheet (not provided). The names of the icons are taken from fontawesome.org cheatsheet.

Remember to add the following lines to your app/Console/Kernel.php :

    protected $commands = [
            ...
            \Mbcraft\Laravel\Icons\Commands\RegenerateIconHelpers::class,
            ...
            ];


## Routing


- route:regenerate_helpers -> generate helpers for the routing methods. Call route:cache before this one, since the helper is generated from the cache.


This command will generate a 'Routes' and 'FormButtonJSRoutes' classes containing all routing methods defined inside your app/Http/routes.php class.
The 'Routes' class static methods are used for links to be placed inside links href attribues, while the 'FormButtonJSRoutes' are to be used inside button's onclick event handler and will actually output javascript code (no external js libraries required).
I suggest using a '.do' suffix on route names defined inside app/Http/routes.php as a convention in order to identify post routes (with '.do' suffix) and get routes. The route method name will actually end with '_do'.

Remember to add the following lines to your app/Console/Kernel.php :

    protected $commands = [
            ...
            \Mbcraft\Laravel\Http\Commands\RegenerateRoutesHelpers::class,
            ...
            ];

 
## Resources (js and css)


- resources:check_path -> this will check all the views found inside the view path of your project, for all the resources required with


* @require_local_js (blade extension, see the 'Laravel Blade Extensions' section)
* @require_local_css (blade extension, see the 'Laravel Blade Extensions' section)


... and for each view will tell if any path is broken.

Remember to add the following lines to your app/Console/Kernel.php :

    protected $commands = [
            ...
            \Mbcraft\Laravel\Misc\Commands\ResourcePathCheck::class
            ...
            ];


# Laravel Blade Extensions



## Policy 
    
Some people reports that @can blade extension does not work correctly.
The @ican and @icannot directive does mostly the same, except that :
- you close it with the @endcan directive
- you can pass no other parameters than the check name, and the User Policy class is chosen among the others.
- you can use any number of parameters, it will work as you expect. You never need to pass the user parameter.
Actually this middleware requires Sentinel v2 : the currently authenticated user is fetched using _Sentinel::getUser()_.
You can change this behaviour registering your own middleware class overriding BladePolicy2Syntax
 and the protected variable GET_AUTH_USER inside it.
    
This directive actually uses the policy() helper function which was reported to work.

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

## Automatic Carbon configuration

The Date/Time library nesbot/carbon is configured automatically with the laravel time zone.

## Localization aliases

The localization functions **lang** and **lang_choice**, aliases of *trans* and *trans_choice* are added to the available functions.


----

Remember to add the following lines to your app/Http/Kernel.php

    protected $middleware = [
        ...
        \Mbcraft\Laravel\Http\Middleware\ConfigureCarbon::class,
        \Mbcraft\Laravel\Http\Middleware\BladeExtendedSyntax::class,
        \Mbcraft\Laravel\Http\Middleware\BladeAssetsSyntax::class,
        \Mbcraft\Laravel\Http\Middleware\BladeWidgetsSyntax::class,
        \Mbcraft\Laravel\Http\Middleware\LocalizationAliases::class
        ...
    ];

# Database helper classes

## ResetTablesSeeder trait class

The trait **Mbcraft\Laravel\Database\ResetTablesSeederTrait** is provided to help resetting the table content for re-seeding. To use it, simply use it in your laravel 'database/seeds/DatabaseSeeder.php' class.
Then add a 'protected $reset_tables' array containing the names of all the tables of which you want to empty, in reverse dependency order, eg:


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



## SoftDeletesCascade trait class

This trait will enable your "softDeletes" models to trigger a cascade behaviour as it usually happens when the real deletes occurs in databases.
Simply add this trait to your model class, then add a 'softCascades' array, containing the list of methods that reaches objects to be soft deleted when this one it soft deleted, eg:



    protected $softCascades = ["tickets"];  // this can be in a customer class


# Entity controllers

Some traits have been developed to help creating entity controllers.
The available traits are for these behaviours : index, create, edit, show, delete and restore.

To use them, create a new controller and make it inherit EntityController :



    <?php
    
    use Mbcraft\Laravel\Http\Controllers\EntityController;
    
    //these are for the traits!!
    // use Mbcraft\Laravel\Http\Controllers\Behaviours\ImportedIndex;
    use Mbcraft\Laravel\Http\Controllers\Behaviours\Index;
    // use Mbcraft\Laravel\Http\Controllers\Behaviours\Create;
    use Mbcraft\Laravel\Http\Controllers\Behaviours\Edit;
    // use Mbcraft\Laravel\Http\Controllers\Behaviours\Delete; 
    // use Mbcraft\Laravel\Http\Controllers\Behaviours\Restore; 
    use Mbcraft\Laravel\Http\Controllers\Behaviours\Show;
    
    
    class InvoicesController extends EntityController {
    
    
    use Index, Edit, Show;
    
    ...



In the example a controller that lets the user list, edit and show the entities.
You should also define some constants that tells the controller what model class to use and if route and views needs some prefix.


    ...
    const MODEL_CLASS = "App\Models\Invoice";
    const VIEW_PREFIX = "admin.";
    const ROUTE_PREFIX = "admin.";
    ...


And that's all. 
By default the views named 'admin.invoces.index', 'admin.invoices.edit', 'admin.invoices.show' will be used as views for the actions.
The routes for this action will be mostly named as the corresponding view.
The model class must implement the Mbcraft\Laravel\Models\INameable interface.


====

-Marco Bagnaresi
