<?php
/**
 * onSubmit event accordingly the test tasks
 */

namespace AllClasses\Events;

use AllClasses\Comment;
use AllClasses\Store;

class onSubmit extends Event
{
    public $url = 'public/images/smiles/emoji';

    public function callback($data)
    {
        $replacements = array(
            ':)' => '1.jpg',
            ':(' => '2.jpg'
        );

        $search = array();
        $replace = array();

        foreach($replacements as $key => $val){
            $search[] = $key;
            $replace[] = '<img src="' . $this->url . $val . '" alt="' . $key . '" />';
        }
        
        $text =  str_replace($search, $replace, $data['text']);

        $comment = new Comment(Store::getInstance());
        $comment->edit(['id' => $data['id'], 'text' => $text]);
    }
}