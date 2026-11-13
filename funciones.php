<?php

function escapar(string $valor): string {
return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

// evita que se hagan sql injections pq convierte los caracteres de html en letras normales