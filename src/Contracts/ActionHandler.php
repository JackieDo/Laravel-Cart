<?php

namespace Jackiedo\Cart\Contracts;

use Jackiedo\Cart\Action;

interface ActionHandler
{
    /**
     * Control action rules.
     *
     * @param Action $action The action
     *
     * @return array
     */
    public function actionHandler($action);
}
