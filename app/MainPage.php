<?php

namespace App;

class MainPage
{
    private $view = 'main';
    private $tree;

    public function __construct(DB $db)
    {
        $this->tree = $db->getData();
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
        $template_file = __DIR__ . "/view/{$template_name}.html";
        if (!file_exists($template_file)) {
            throw new \RuntimeException('Template does not exist');
        }

        $template_content = file_get_contents($template_file);

        echo strtr($template_content, $vars);
    }
}
