<?php
namespace Core;

/**
 * Base View Class
 */
class View
{
    /**
     * Render a view file
     * 
     * @param string $view Path to the view file (e.g., 'public/home')
     * @param array $data Data to pass to the view
     */
    public static function render($view, $data = [])
    {
        extract($data);

        $viewFile = BASE_PATH . "/App/Views/{$view}.php";

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("View {$view} not found.");
        }
    }

    /**
     * Render a view file and return contents as string
     */
    public static function renderToString($view, $data = [])
    {
        extract($data);
        $viewFile = BASE_PATH . "/App/Views/{$view}.php";

        if (!file_exists($viewFile)) {
            return "View {$view} not found.";
        }

        ob_start();
        require $viewFile;
        return ob_get_clean();
    }

    /**
     * Render a view with a layout
     */
    public static function renderWithLayout($view, $layout, $data = [])
    {
        extract($data);

        $viewFile = BASE_PATH . "/App/Views/{$view}.php";

        if (!file_exists($viewFile)) {
            die("View {$view} not found.");
        }

        // Capture view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render layout
        $layoutFile = BASE_PATH . "/App/Views/layouts/{$layout}.php";
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            die("Layout {$layout} not found.");
        }
    }
}
