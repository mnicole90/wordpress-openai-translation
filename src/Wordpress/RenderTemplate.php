<?php
declare(strict_types=1);

namespace Translation\Wordpress;

trait RenderTemplate
{
    private string $file;

    public function render(string $name, array $args = []): void
    {
        extract($args);
        $file = plugin_dir_path($this->file) . 'views/' . $name . '.php';
        ob_start();
        include_once $file;
        echo ob_get_clean();
    }
}
