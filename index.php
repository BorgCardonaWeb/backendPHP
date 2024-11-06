<?php
// index.php

header('Content-Type: application/json');


// Asegúrate de incluir el autoload de Composer para cargar las dependencias
require_once __DIR__ . '/vendor/autoload.php'; // Agregado para cargar las clases de PHP Dotenv
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/routes/api.php';
