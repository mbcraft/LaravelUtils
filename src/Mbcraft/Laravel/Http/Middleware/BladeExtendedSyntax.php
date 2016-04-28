<?php

namespace Mbcraft\Laravel\Http\Middleware;

use Closure;
use Blade;
use Illuminate\Database\Eloquent\Collection;

/**
 * This middleware adds blade support for :
 *
 * @ifdef : renders the content only if the named variable(s) is(are) defined.
 * @index : defines and index variable. If already defined, increases it by 1.
 * @if_has_elements : renders the nested body if and only if the variable is defined and has elements (count>0)
 * @safe_count : renders a counter, or nothing if the variable is not set.
 * @if_column : renders the content if the column is listed in "show_columns" (if defined) 
 * or not listed in "hide_columns" (if defined)
 * @if_action : renders the action if the action is listed in "show_actions" (if defined)
 * or not listed in "hide_actions" (if defined)
 * @menuitem  : opens a menu item. Needs route as parameter.
 * @endmenuitem : closes a menu item.
 * 
 */
class BladeExtendedSyntax extends __AbstractBladeSyntax
{
    /**
     * Renders the content only if the variable named as a parameter are defined.
     * 
     * usage : 
     * 
     * @ifdef("my_var")   // @ifdef("my_var1","my_var2","last_var")  <-- multi check is also supported
     * ...
     * ...
     * @endif
     */
    private function setupIfdef() {
        Blade::directive('ifdef', function($expression) {
            $all_params_st = $this->stringParamAsString($expression);
            $params = eval("return array(".$all_params_st.");");
            $result = '<?php ';
            $result .= 'if (';
            
            for ($i=0;$i<count($params);$i++) {
                $result .= "isset($".$params[$i].")";
                if ($i<count($params)-1) {
                    $result .=" && ";
                }
            }
            $result .= '): ?>';
            return $result;
        });
    }
    
    /**
     * Renders the content only if all the variable named as a parameter are not defined.
     * 
     * usage : 
     * 
     * @ifndef("my_var")   // @ifndef("my_var1","my_var2","last_var")  <-- multi check is also supported
     * ...
     * ...
     * @endif
     */
    private function setupIfndef() {
        Blade::directive('ifndef', function($expression) {
            $all_params_st = $this->stringParamAsString($expression);
            $params = eval("return array(".$all_params_st.");");
            $result = '<?php ';
            $result .= 'if (';
            
            for ($i=0;$i<count($params);$i++) {
                $result .= "!isset($".$params[$i].")";
                if ($i<count($params)-1) {
                    $result .=" && ";
                }
            }
            $result .= '): ?>';
            return $result;
        });
    }
    
    /**
     * Creates an index to be used inside foreach loops.
     * 
     * usage : @index("ix_name")  .... then simply use {{ $ix_name }}
     */
    private function setupIndex() {
        Blade::directive('index', function($expression) {
            return '<?php '
            .'if (isset('.$this->stringParamAsVar($expression).'))'
            . '{ '.$this->stringParamAsVar($expression).'++; }'
            . ' else {'.$this->stringParamAsVar($expression).'=0;} ?>';
        });
    }

    /**
     * Creates a directive to check if the variable has elements (exists and has
     * at least one element)
     * 
     * usage : @if_has_elements("my_list")
     * 
     * ...
     * 
     * @endif
     * 
     * @return type
     */
    private function setupIfHasElements() {
        Blade::directive('if_has_elements', function($expression) {
            return '<?php \n'
            .'$my_var = $this->stringParamAsString($expression);\n'
            .'$__array_with_items = isset($$my_var) && is_array($$my_var) && count($$my_var)>0;\n'
            .'$__collection_with_items = isset($$my_var) && $$my_var instanceof Illuminate\Database\Eloquent\Collection && count($$my_var->items)>0;\n'
            .'if ($__array_with_items || $__collection_with_items): \n'
            .'  unset($__array_with_items);\n'
            .'  unset($__collection_with_items);\n';
            
        });
    }
    
    /**
     * Directive for showing the count of the elements, or nothing if the
     * variable is not set at all.
     * 
     * usage : @safe_count("var_name")
     * 
     */
    private function setupSafeCount() {
        Blade::directive('safe_count', function($expression) {
            return '<?= '
            .'isset('.$this->stringParamAsVar($expression).') ? '
            . 'count('.$this->stringParamAsVar($expression).') : '
            . ' "" ?>';
        });
    }
    /**
     * Used to show only specific columns, if $show_columns['column_name'] is set,
     * to hide column if $hide_columns['column_name'] is set, or to show
     * the column if both variables are unset.
     * 
     * usage : 
     *      @if_column("col_name") 
     *      ....
     *      ....
     *      ....    
     *      @endif
     * 
     * 
     */
    private function setupIfColumn() {
        
        Blade::directive('if_column', function($expression) {
            $col = $this->stringParamAsString($expression); //gets col
            return '<?php '
            .' if ( (isset($show_columns) && array_search('.$col.',$show_columns)!==FALSE) || '
            . '(isset($hide_columns) && array_search('.$col.',$hide_columns)===FALSE) || '
            .' (!isset($show_columns) && !isset($hide_columns)) ): ?>';

        });
    }
    
    /**
     * Used to show only specific actions, if $show_actions['action_name'] is set,
     * to hide actions if $hide_actions['action_name'] is set, or to show
     * the action if both variables are unset.
     * 
     * usage : 
     *      @if_action("act_name") 
     *      ....
     *      ....
     *      ....    
     *      @endif
     * 
     * 
     */
    private function setupIfAction() {
        
        Blade::directive('if_action', function($expression) {
            $act = $this->stringParamAsString($expression); //gets act
            return '<?php '
            .' if ( (isset($show_actions) && array_search('.$act.',$show_actions)!==FALSE) || '
            . '(isset($hide_actions) && array_search('.$act.',$hide_actions)===FALSE) || '
            .' (!isset($show_actions) && !isset($hide_actions)) ): ?>';

        });
    }
    
    /**
     * 
     * Used to define menu items as li elements. Needs to be terminated with a @endmenuitem
     * 
     * @return html
     */
    private function setupMenuItem() {
        Blade::directive('menuitem',function($expression) {
            $route = $this->stringParamAsString($expression); //gets route
            return '<li <?= Request::is('.$route.') ? \'class=active\' : \'\' ?>>
                        <a href="<?= URL::to('.$route.') ?>">';
        });
    }
    
    /**
     * Used to terminate the menu item.
     * 
     * @return html
     */
    private function setupEndMenuItem() {
        Blade::directive('endmenuitem',function($expression) {
            return '</a>
            </li>';
        });
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //setup for ifdef
        $this->setupIfdef();
        
        //setup for ifndef
        $this->setupIfndef();
        
        //counter for foreach
        $this->setupIndex();
        
        //if has elements or skip
        $this->setupIfHasElements();
        
        //safe count or nothing
        $this->setupSafeCount();
        
        //contitional columns
        $this->setupIfColumn();
        
        //conditional actions
        $this->setupIfAction();
        
        //menu builder helpers
        $this->setupMenuItem();
        $this->setupEndMenuItem();
        
        \Log::debug("Blade extended syntax (cycles, columns, menu, safe getters) setup Completed!");
        //next middleware
        return $next($request);
    }
}
