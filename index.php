<?php
// index.php

header('Content-Type: application/json');

require_once __DIR__ . '/vendor/autoload.php'; 
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/routes/api.php';
