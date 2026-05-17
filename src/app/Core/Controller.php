<?php

declare(strict_types=1);

namespace TestAssignmentBlog\Core;

abstract class Controller
{
    protected View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }
}
