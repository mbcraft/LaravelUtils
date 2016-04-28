==== Instructions for icons sets ====

Each "icon set" must be in an interface file, in namespace 'Mbcraft\Utils\FontAwesome\Icons'.

Each interface must declare 2 constants :

- ICON_PREFIX : a string containing the prefix classes.
- ICON_LIST : a list of all icon names (without any common prefix)
    as copied from the font awesome web site.

Each set must be added to the FontAwesomeCmdLineClassGenerator.php class.
(see class construct);