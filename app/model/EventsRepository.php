<?php

namespace App\Model;

use Nette;

class EventsRepository
{
    use Nette\SmartObject;

    /** @var Nette\Database\Context */
    private $database;


    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


    /** @return Nette\Database\Table\Selection */
    public function findAll()
    {
        return $this->database->table('events');
    }


    /** @return Nette\Database\Table\ActiveRow */
    public function findById($id)
    {
        return $this->findAll()->get($id);
    }


    /** @return Nette\Database\Table\ActiveRow */
    public function addAttending($id)
    {
        $event = $this->findById($id);
        return $event->update(array('attending' => $event->attending + 1));
    }


    /** @return Nette\Database\Table\ActiveRow */
    public function findUpcoming()
    {
        return $this->findAll()->where("timeend > ?", time())->order('timestart');
    }


    /** @return Nette\Database\Table\ActiveRow */
    public function findPast()
    {
        return $this->findAll()->where("timeend < ?", time())->order('timestart DESC');
    }


    /** @return Nette\Database\Table\ActiveRow */
    public function insert($values)
    {
        return $this->findAll()->insert($values);
    }
}
