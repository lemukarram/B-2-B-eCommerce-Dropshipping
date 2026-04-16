<?php

declare(strict_types=1);

namespace Core;

use RuntimeException;

/**
 * Template renderer using output buffering.
 *
 * Controllers call View::render() with a template path relative to src/views/,
 * a data array, and an optional layout name.
 *
 * The layout file (src/views/layouts/{layout}.php) must echo $content
 * where it wants the template body inserted.
 *
 * Usage:
 *   View::render('admin/products/index', ['products' => $products], 'admin');
 *   View::render('auth/login', [], 'guest');
 */
class View
{
    public static function render(string $template, array $data = [], string $layout = 'guest'): void
    {
        $templateFile = VIEW_PATH . '/' . ltrim($template, '/') . '.php';
        $layoutFile   = VIEW_PATH . '/layouts/' . $layout . '.php';

        if (!is_file($templateFile)) {
            throw new RuntimeException("View not found: {$templateFile}");
        }
        if (!is_file($layoutFile)) {
            throw new RuntimeException("Layout not found: {$layoutFile}");
        }

        // Make data keys available as variables — EXTR_SKIP never overwrites $content
        extract($data, EXTR_SKIP);

        ob_start();
        require $templateFile;
        $content = ob_get_clean();

        require $layoutFile;
    }

    /**
     * Render a partial without a layout (for AJAX partials, email fragments, etc.)
     */
    public static function partial(string $template, array $data = []): string
    {
        $file = VIEW_PATH . '/' . ltrim($template, '/') . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("Partial not found: {$file}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        return ob_get_clean() ?: '';
    }

    /**
     * XSS-safe output helper. Use e($var) in every view.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
