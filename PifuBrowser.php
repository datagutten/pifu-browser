<?php
class PifuBrowser
{
    public $twig;
    function __construct()
    {
        $loader = new Twig\Loader\FilesystemLoader(array('templates', 'templates'), __DIR__);
        $this->twig = new Twig\Environment($loader, array('strict_variables' => true));
        $this->twig->addFunction(new Twig\TwigFunction('asset', function ($asset) {
            //return sprintf(__DIR__.'/%s', ltrim($asset, '/'));
            return $asset;
        }));
    }

    /**
     * Renders a template.
     *
     * @param string $name    The template name
     * @param array  $context An array of parameters to pass to the template
     *
     * @return string The rendered template
     */
    public function render($name, $context)
    {
        try {
            return $this->twig->render($name, $context);
        }
        catch (\Twig\Error\Error $e) {

            //$trace = sprintf('<pre>%s</pre>', $e->getTraceAsString());
            $msg = "Error rendering template:\n" . $e->getMessage();
            try {
                die($this->twig->render('error.twig', array(
                        'title'=>'Rendering error',
                        'error'=>$msg)
                ));
            }
            catch (\Twig\Error\Error $e_e)
            {
                $msg = sprintf("Original error: %s\n<pre>%s</pre>\nError rendering error template: %s\n<pre>%s</pre>",
                    $e->getMessage(), $e->getTraceAsString(), $e_e->getMessage(), $e_e->getTraceAsString());
                die($msg);
            }
        }
    }
}