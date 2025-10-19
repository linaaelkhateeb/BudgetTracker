<?php
define('BASE_URL', '/budgettracker/');

function url(string $path): string {
  return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
