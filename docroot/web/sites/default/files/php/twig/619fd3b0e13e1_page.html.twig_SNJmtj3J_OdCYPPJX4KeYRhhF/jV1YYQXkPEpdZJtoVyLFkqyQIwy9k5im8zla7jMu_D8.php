<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* profiles/xnavi/themes/zfdw_b4/templates/layout/page.html.twig */
class __TwigTemplate_05cfb7a168cc4261a8bce37ec3d85ea77e09537c5ffad5deb4e1ae891c8076d0 extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 46
        $context["nav_classes"] = ((("navbar navbar-expand-lg" . (((        // line 47
($context["b4_navbar_schema"] ?? null) != "none")) ? ((" navbar-" . $this->sandbox->ensureToStringAllowed(($context["b4_navbar_schema"] ?? null), 47, $this->source))) : (" "))) . (((        // line 48
($context["b4_navbar_schema"] ?? null) != "none")) ? ((((($context["b4_navbar_schema"] ?? null) == "dark")) ? (" text-light") : (" text-dark"))) : (" "))) . (((        // line 49
($context["b4_navbar_bg_schema"] ?? null) != "none")) ? ((" bg-" . $this->sandbox->ensureToStringAllowed(($context["b4_navbar_bg_schema"] ?? null), 49, $this->source))) : (" ")));
        // line 51
        echo "
";
        // line 53
        $context["footer_classes"] = (((" " . (((        // line 54
($context["b4_footer_schema"] ?? null) != "none")) ? ((" footer-" . $this->sandbox->ensureToStringAllowed(($context["b4_footer_schema"] ?? null), 54, $this->source))) : (" "))) . (((        // line 55
($context["b4_footer_schema"] ?? null) != "none")) ? ((((($context["b4_footer_schema"] ?? null) == "dark")) ? (" text-light") : (" text-dark"))) : (" "))) . (((        // line 56
($context["b4_footer_bg_schema"] ?? null) != "none")) ? ((" bg-" . $this->sandbox->ensureToStringAllowed(($context["b4_footer_bg_schema"] ?? null), 56, $this->source))) : (" ")));
        // line 58
        echo "
<!-- Uni Branding -->
";
        // line 75
        echo "

<!--Main Navigation -->
";
        // line 78
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "nav_main", [], "any", false, false, true, 78)) {
            // line 79
            echo "<nav class=\"";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["nav_classes"] ?? null), 79, $this->source), "html", null, true);
            echo "\">
  <div class=\"";
            // line 80
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["b4_top_container"] ?? null), 80, $this->source), "html", null, true);
            echo " row mx-auto\">
    <div class=\"col-3 col-md-auto p-0 text-right\">
      <button class=\"navbar-toggler collapsed\" type=\"button\" data-toggle=\"collapse\"
                data-target=\"#navbarSupportedContent\" aria-controls=\"navbarSupportedContent\"
                aria-expanded=\"false\" aria-label=\"Toggle navigation\">
          <span class=\"navbar-toggler-icon\"></span>
        </button>
    </div>
    <div class=\"collapse navbar-collapse col-12 col-md-auto p-0 justify-content-end\" id=\"navbarSupportedContent\">
      ";
            // line 89
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "nav_main", [], "any", false, false, true, 89), 89, $this->source), "html", null, true);
            echo "
    </div>
  </div>
</nav>
";
        }
        // line 94
        echo "<div class=\"brand-border\">
  <ul class=\"zsb-container\">
    <li class=\"home\"><a href=\"";
        // line 96
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["base_path"] ?? null), 96, $this->source), "html", null, true);
        echo "\"><span class=\"sr-only\">Zur Homepage</span><i class=\"fas fa-home\" aria-hidden=\"true\"></i></a></li>
  </ul>
</div>
<header>
  ";
        // line 100
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "header", [], "any", false, false, true, 100), 100, $this->source), "html", null, true);
        echo "
</header>

<main role=\"main\">
  <a id=\"main-content\" tabindex=\"-1\"></a>";
        // line 105
        echo "
  ";
        // line 107
        $context["sidebar_first_classes"] = (((twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 107) && twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 107))) ? ("col-12 col-sm-6 col-lg-3") : ("col-12 col-lg-3"));
        // line 109
        echo "
  ";
        // line 111
        $context["sidebar_second_classes"] = (((twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 111) && twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 111))) ? ("col-12 col-sm-6 col-lg-3") : ("col-12 col-lg-3"));
        // line 113
        echo "
  ";
        // line 115
        $context["content_classes"] = (((twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 115) && twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 115))) ? ("col-12 col-lg-6") : ((((twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 115) || twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 115))) ? ("col-12 col-lg-9") : ("col-12"))));
        // line 117
        echo "

  <div class=\"";
        // line 119
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["b4_top_container"] ?? null), 119, $this->source), "html", null, true);
        echo "\">
    ";
        // line 120
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumb", [], "any", false, false, true, 120)) {
            // line 121
            echo "      ";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumb", [], "any", false, false, true, 121), 121, $this->source), "html", null, true);
            echo "
    ";
        }
        // line 123
        echo "    <div class=\"row no-gutters\">
      ";
        // line 124
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 124)) {
            // line 125
            echo "        <div class=\"order-2 order-lg-1 ";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["sidebar_first_classes"] ?? null), 125, $this->source), "html", null, true);
            echo "\">
          ";
            // line 126
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 126), 126, $this->source), "html", null, true);
            echo "
        </div>
      ";
        }
        // line 129
        echo "      <div class=\"order-1 order-lg-2 ";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["content_classes"] ?? null), 129, $this->source), "html", null, true);
        echo "\">
        ";
        // line 130
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "content", [], "any", false, false, true, 130), 130, $this->source), "html", null, true);
        echo "
      </div>
      ";
        // line 132
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 132)) {
            // line 133
            echo "        <div class=\"order-3 ";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["sidebar_second_classes"] ?? null), 133, $this->source), "html", null, true);
            echo "\">
          ";
            // line 134
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 134), 134, $this->source), "html", null, true);
            echo "
        </div>
      ";
        }
        // line 137
        echo "    </div>
  </div>

</main>

";
        // line 142
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "footer", [], "any", false, false, true, 142)) {
            // line 143
            echo "<footer class=\"mt-auto zfdw-b4-footer ";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["footer_classes"] ?? null), 143, $this->source), "html", null, true);
            echo "\">
  <div class=\"";
            // line 144
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["b4_top_container"] ?? null), 144, $this->source), "html", null, true);
            echo "\">
    ";
            // line 145
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "footer", [], "any", false, false, true, 145), 145, $this->source), "html", null, true);
            echo "
  </div>
</footer>
";
        }
    }

    public function getTemplateName()
    {
        return "profiles/xnavi/themes/zfdw_b4/templates/layout/page.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  194 => 145,  190 => 144,  185 => 143,  183 => 142,  176 => 137,  170 => 134,  165 => 133,  163 => 132,  158 => 130,  153 => 129,  147 => 126,  142 => 125,  140 => 124,  137 => 123,  131 => 121,  129 => 120,  125 => 119,  121 => 117,  119 => 115,  116 => 113,  114 => 111,  111 => 109,  109 => 107,  106 => 105,  99 => 100,  92 => 96,  88 => 94,  80 => 89,  68 => 80,  63 => 79,  61 => 78,  56 => 75,  52 => 58,  50 => 56,  49 => 55,  48 => 54,  47 => 53,  44 => 51,  42 => 49,  41 => 48,  40 => 47,  39 => 46,);
    }

    public function getSourceContext()
    {
        return new Source("", "profiles/xnavi/themes/zfdw_b4/templates/layout/page.html.twig", "/app/web/profiles/xnavi/themes/zfdw_b4/templates/layout/page.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("set" => 46, "if" => 78);
        static $filters = array("escape" => 79);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['escape'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
