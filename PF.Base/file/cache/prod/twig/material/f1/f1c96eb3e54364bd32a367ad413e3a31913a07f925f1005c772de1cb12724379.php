<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* @Theme/layout.html */
class __TwigTemplate_948728e7a8e03bf9bccb973889832e7359d16252269064ee8fca87e2f518fab5 extends Core\View\Base
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<!DOCTYPE html>
<html ";
        // line 2
        echo ($context["html"] ?? null);
        echo ">
\t<head>
\t\t<title>";
        // line 4
        echo ($context["title"] ?? null);
        echo "</title>
\t\t";
        // line 5
        echo ($context["header"] ?? null);
        echo "
\t\t<link href=\"https://fonts.googleapis.com/css?family=Roboto:300,400,700\" rel=\"stylesheet\">
\t</head>
\t<body ";
        // line 8
        echo ($context["body"] ?? null);
        echo ">
\t\t<div class=\"landing-background row_image\"></div>
\t\t<div id=\"pf-loading-message\">
\t\t\t<span class=\"l-1\"></span>
\t\t\t<span class=\"l-2\"></span>
\t\t\t<span class=\"l-3\"></span>
\t\t\t<span class=\"l-4\"></span>
\t\t\t<span class=\"l-5\"></span>
\t\t\t<span class=\"l-6\"></span>
\t\t</div>
\t\t<div id=\"section-header\">
\t\t\t<div class=\"sticky-bar\">
\t\t\t\t<div class=\"container sticky-bar-inner h-6 ";
        // line 20
        if ((call_user_func_array($this->env->getFunction('setting')->getCallable(), ["user.hide_main_menu"]) == true)) {
            echo "setting-hide-menu";
        }
        echo "\">
\t\t\t\t\t<div class=\"mr-2 site-logo-block\">
\t\t\t\t\t\t";
        // line 22
        echo ($context["logo"] ?? null);
        echo "
\t\t\t\t\t</div>

\t\t\t\t\t<!-- Button collapse main nav when on device -->
\t\t\t\t\t";
        // line 26
        if (( !call_user_func_array($this->env->getFunction('setting')->getCallable(), ["user.hide_main_menu"]) || (call_user_func_array($this->env->getFunction('is_user')->getCallable(), []) == true))) {
            // line 27
            echo "\t\t\t\t\t\t<button type=\"button\" class=\"btn-nav-toggle collapsed mr-2 js-btn-collapse-main-nav\"></button>
\t\t\t\t\t";
        }
        // line 29
        echo "
\t\t\t\t\t";
        // line 30
        if (call_user_func_array($this->env->getFunction('user_group_setting')->getCallable(), ["search.can_use_global_search"])) {
            // line 31
            echo "\t\t\t\t\t\t<div id=\"search-panel\" class=\"search-panel mr-7\">
\t\t\t\t\t\t\t<div class=\"js_temp_friend_search_form\"></div>
\t\t\t\t\t\t\t<form method=\"get\" action=\"";
            // line 33
            echo call_user_func_array($this->env->getFunction('url')->getCallable(), ["search"]);
            echo "\" class=\"header_search_form\" id=\"header_search_form\">
\t\t\t\t\t\t\t\t<div class=\"form-group has-feedback\">
\t\t\t\t\t\t\t\t\t<span class=\"ico ico-arrow-left btn-globalsearch-return\"></span>
\t\t\t\t\t\t\t\t\t<input type=\"text\" name=\"q\" placeholder=\"";
            // line 36
            echo call_user_func_array($this->env->getFunction('_p')->getCallable(), ["search"]);
            echo "...\" autocomplete=\"off\" class=\"form-control input-sm in_focus\" id=\"header_sub_menu_search_input\" />
\t\t\t\t\t\t\t\t\t<span class=\"ico ico-search-o form-control-feedback\" data-action=\"submit_search_form\"></span>
\t\t\t\t\t\t\t\t\t<span class=\"ico ico-search-o form-control-feedback btn-mask-action\"></span>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t</form>
\t\t\t\t\t\t</div>
\t\t\t\t\t";
        }
        // line 43
        echo "
\t\t\t\t\t<!-- Main Navigation -->
\t\t\t\t\t";
        // line 45
        if (( !call_user_func_array($this->env->getFunction('setting')->getCallable(), ["user.hide_main_menu"]) || (call_user_func_array($this->env->getFunction('is_user')->getCallable(), []) == true))) {
            // line 46
            echo "\t\t\t\t\t\t<div class=\"fixed-main-navigation\">
\t\t\t\t\t\t\t<div class=\"dropdown\">
\t\t\t\t\t\t\t\t<span data-toggle=\"dropdown\"><i class=\"ico ico-navbar\"></i></span>
\t\t\t\t\t\t\t\t<ul class=\"dropdown-menu site_menu\">
\t\t\t\t\t\t\t\t\t";
            // line 50
            echo ($context["menu_list"] ?? null);
            echo "
\t\t\t\t\t\t\t\t</ul>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t<span class=\"js-btn-collapse-main-nav link\"></span>
\t\t\t\t\t\t</div>
\t\t\t\t\t";
        }
        // line 56
        echo "
\t\t\t\t\t";
        // line 57
        if ((call_user_func_array($this->env->getFunction('is_user')->getCallable(), []) == true)) {
            // line 58
            echo "\t\t\t\t\t\t<div id=\"user_sticky_bar\" class=\"user-sticky-bar\"></div>
\t\t\t\t\t";
        } else {
            // line 60
            echo "\t\t\t\t\t\t";
            echo ($context["sticky_bar"] ?? null);
            echo "
\t\t\t\t\t";
        }
        // line 62
        echo "\t\t\t\t</div>
\t\t\t</div>
\t\t\t<nav class=\"navbar main-navigation collapse navbar-collapse\" id=\"main-navigation-collapse\">
\t\t\t\t<div class=\"container\">
\t\t\t\t\t<!-- Collect the nav links, forms, and other content for toggling -->
\t\t\t\t\t";
        // line 67
        echo ($context["menu"] ?? null);
        echo "
\t\t\t\t</div><!-- /.container-fluid -->
\t\t\t</nav>

\t\t\t";
        // line 71
        echo ($context["location_6"] ?? null);
        echo "

\t\t</div>

\t\t<div id=\"main\" class=\"";
        // line 75
        echo ($context["main_class"] ?? null);
        echo "\">
\t\t\t<div class=\"container\">
\t\t\t\t<div class=\"row\">
\t\t\t\t\t<div class=\"col-md-12 col-sm-12\">
\t\t\t\t\t\t";
        // line 79
        echo ($context["location_11"] ?? null);
        echo "
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t\t<div class=\"layout-main\">
\t\t\t\t\t<div class=\"layout-left \" id=\"left\">
                        ";
        // line 84
        if ((call_user_func_array($this->env->getFunction('get_controller_name')->getCallable(), []) == "index-visitor")) {
            // line 85
            echo "\t\t\t\t\t\t\t<div id=\"index-visitor-error\">
                            ";
            // line 86
            echo ($context["errors"] ?? null);
            echo "
\t\t\t\t\t\t\t</div>
                        ";
        }
        // line 89
        echo "\t\t\t\t\t\t";
        echo ($context["left"] ?? null);
        echo "
\t\t\t\t\t</div>
\t\t\t\t\t<div class=\"layout-middle\" id=\"content-holder\">
\t\t\t\t\t\t<div id=\"content-stage\" class=\"bg-tran\">
\t\t\t\t\t\t\t<div id=\"top\">
\t\t\t\t\t\t\t\t";
        // line 94
        echo ($context["main_top"] ?? null);
        echo "
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t<div class=\"\">
\t\t\t\t\t\t\t\t";
        // line 97
        echo ($context["breadcrumb"] ?? null);
        echo "
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t<div id=\"content\">
\t\t\t\t\t\t\t\t";
        // line 100
        echo ($context["errors"] ?? null);
        echo "
\t\t\t\t\t\t\t\t";
        // line 101
        echo ($context["content"] ?? null);
        echo "
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>
\t\t\t\t\t</div>
\t\t\t\t\t<div class=\"layout-right\" id=\"right\">
\t\t\t\t\t\t";
        // line 106
        echo ($context["right"] ?? null);
        echo "
\t\t\t\t\t</div>
\t\t\t\t\t<div class=\"btn-scrolltop\" style=\"display: none\">
\t\t\t\t\t\t<span class=\"btn btn-round btn-gradient btn-primary s-5\" onclick=\"page_scroll2top();\">
\t\t\t\t\t\t\t<i class=\"ico ico-goup\"></i>
\t\t\t\t\t\t</span>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t\t<div class=\"row\">
\t\t\t\t\t<div class=\"col-md-12 col-sm-12\">
\t\t\t\t\t\t";
        // line 116
        echo ($context["location_8"] ?? null);
        echo "
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</div>
\t\t</div>

\t\t<div id=\"bottom_placeholder\">
\t\t\t";
        // line 123
        echo ($context["location_12"] ?? null);
        echo "
\t\t</div>

\t\t<footer id=\"section-footer\">
\t\t\t<div class=\"container\">
\t\t\t\t";
        // line 128
        echo ($context["footer"] ?? null);
        echo "
\t\t\t\t";
        // line 129
        echo ($context["location_5"] ?? null);
        echo "
\t\t\t</div>
\t\t</footer>
\t\t";
        // line 132
        echo ($context["js"] ?? null);
        echo "
\t\t<div class=\"nav-mask-modal\"></div>
\t</body>
</html>";
    }

    public function getTemplateName()
    {
        return "@Theme/layout.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  265 => 132,  259 => 129,  255 => 128,  247 => 123,  237 => 116,  224 => 106,  216 => 101,  212 => 100,  206 => 97,  200 => 94,  191 => 89,  185 => 86,  182 => 85,  180 => 84,  172 => 79,  165 => 75,  158 => 71,  151 => 67,  144 => 62,  138 => 60,  134 => 58,  132 => 57,  129 => 56,  120 => 50,  114 => 46,  112 => 45,  108 => 43,  98 => 36,  92 => 33,  88 => 31,  86 => 30,  83 => 29,  79 => 27,  77 => 26,  70 => 22,  63 => 20,  48 => 8,  42 => 5,  38 => 4,  33 => 2,  30 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "@Theme/layout.html", "C:\\xampp\\htdocs\\phpfox\\PF.Site\\flavors\\material\\html/layout.html");
    }
}
