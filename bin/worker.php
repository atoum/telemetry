<?php

$app = include __DIR__ . '/../src/bootstrap.php';

$app['worker']->consume();
