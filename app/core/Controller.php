<?php
class Controller {
    // Base controller logic can go here

    public function view($view, $data = []) {
        require_once __DIR__ . '/View.php';
        View::render($view, $data);
    }
} 