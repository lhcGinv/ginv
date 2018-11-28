<?php

namespace script;
use api\v1\demo as demoC;

class demo
{
    public function run() {
        $demo = new demoC;
        $demo->index();
    }
}