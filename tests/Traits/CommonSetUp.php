<?php

require_once __DIR__ . '/TestCaseSetUp.php';
require_once __DIR__ . '/CustomAssertions.php';
require_once __DIR__ . '/InitCartInstance.php';

trait CommonSetUp
{
    use TestCaseSetUp, CustomAssertions, InitCartInstance;
}
