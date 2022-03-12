<?php

namespace Jackiedo\Cart\Contracts;

interface ActionHandler
{
    /**
     * Control action rules.
     *
     * @param \Jackiedo\Cart\Action $action The action
     *
     * @return array
     */
    public function actionHandler($action);
}
