<?php

declare(strict_types=1);

namespace TestAssignmentBlog\Core;

use Smarty\Exception;
use Smarty\Smarty;

class View
{
    private Smarty $smarty;

    public function __construct()
    {
        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir(__DIR__ . '/../../templates');
        $this->smarty->setCompileDir(__DIR__ . '/../../templates_c');
        $this->smarty->setCacheDir(__DIR__ . '/../../cache');
    }

    /**
     * @throws Exception
     */
    public function render(string $template, array $data = []): string
    {
        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        $this->smarty->assign('content_template', $template);
        return $this->smarty->fetch('layout.tpl');
    }

    /**
     * @throws Exception
     */
    public function display(string $template, array $data = []): void
    {
        echo $this->render($template, $data);
    }
}
