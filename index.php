<?php
/**
 * Owner: Taras Kostyuk
 * Test task
 */

include('config/config.php');
include('app/class.php');

$params = new AllClasses\Params();
$validator = new AllClasses\Validator($params);

$comment = new AllClasses\Comment(AllClasses\Store::getInstance());

if($validator->validate()) {
    $comment->add($params->get('text'));
    header('Location: /', true, 301);
    exit();
}

$error = $validator->getError();

$commentsText = $comment->getAll();

ob_start();

include('templates/layout.html');

echo ob_get_clean();