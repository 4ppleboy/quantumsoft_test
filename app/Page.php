<?php

namespace App;

class Page
{
    private $view = 'page.html';
    private $tree;

    public function __construct(array $tree)
    {
        $this->tree = $tree;
    }

    public function build(): void
    {
        $name = getenv('APP_NAME');

        $this->render($this->view, [
            '%name%' => $name,
            '%db_tree%' => json_encode($this->tree)
        ]);
    }

    public function render(string $template_name, array $vars = []): void
    {
        $template_file = __DIR__ . "/view/{$template_name}";
        if (!file_exists($template_file)) {
            throw new \RuntimeException('Template does not exist');
        }

        $template_content = file_get_contents($template_file);

        echo strtr($template_content, $vars);
    }
}
