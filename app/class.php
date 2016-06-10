<?php
/**
 * Encapsulation all classes and eventmanager.
 */

namespace AllClasses;

include_once ('eventManager.php');
include_once ("events/event.php");
include_once ("events/onSubmit.php");

use EventManager\EventManager;

/**
 * Class Store which inititalize store to db connection
 * 
 * @package AllClasses
 */
class Store
{
    private static $objInstance;

    public static function getInstance()
    {
        if (!self::$objInstance) {
            self::$objInstance = new \PDO(DB_DSN, DB_USER, DB_PASS);
            self::$objInstance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return self::$objInstance;
    }

    private function __clone() {}

    private function __construct() {}
}


/**
 * Class Comment which realize store logic to and from DB
 * 
 * @package AllClasses
 */
class Comment
{
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function add($text)
    {
        $smtp = $this->db->prepare("INSERT INTO comments (text) VALUES (:text)");
        $smtp->bindValue(':text', $text, \PDO::PARAM_STR);
        $smtp->execute();

        $id = $this->db->lastInsertId();

        EventManager::getInstance()->emit('onSubmit', ['id' => $id, 'text' => $text]);
    }

    public function getAll()
    {
        $comments = [];

        foreach ($this->db->query("SELECT text FROM comments ORDER BY id DESC") as $row) {
            $comments[] = $row['text'];
        }

        return $comments;
    }

    public function edit($params)
    {
        $smtp = $this->db->prepare("UPDATE comments SET text = :text WHERE id = :id");
        $smtp->bindValue(':id', $params['id'], \PDO::PARAM_INT);
        $smtp->bindValue(':text', $params['text'], \PDO::PARAM_STR);
        $smtp->execute();
    }
}

/**
 * Class Observer which realize get logic from DB
 *
 * @package AllClasses
 */

class Observers
{
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $events = [];

        foreach ($this->db->query("SELECT name FROM observers ORDER BY id ASC") as $row) {
            $events[] = $row['name'];
        }

        return $events;
    }
}

/**
 * Class Params which preserv params in object
 * 
 * @package AllClasses
 */
class Params
{
    private $params = [];
    
    public function __construct()
    {
        if(isset($_REQUEST)) {
            $this->params = $_REQUEST;
        }
    }
    
    public function get($element_name) 
    {
        if(isset($this->params[$element_name])) {
            return $this->params[$element_name];
        }
        
        return [];
    }

    public function isPost()
    {
        return empty($_POST) ? false : true;
    }
}

/**
 * Class Validator which response from validation of the request
 * 
 * @package AllClasses
 */
class Validator
{
    public $error = '';

    public function __construct(Params $params)
    {
        $this->pre_validation_params = $params;
    }

    public function validate()
    {
        if($this->pre_validation_params->isPost()) {
            if($this->pre_validation_params->get('token') != 'additional_security_shell_from_csrf_atack_with_appropriate_system_on_backend') {
                $this->error = 'CSRF attack'; // we can use multilanguage text in other class which we can generate appropriate text for each of case in the test application I dont use this but mentioned that we can do it
                return false;
            }

            return true;
        }

        return false;
    }
    
    public function getError()
    {
        return $this->error;
    }
}

$eventManager = EventManager::getInstance();