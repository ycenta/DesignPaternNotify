<?php

class Cryptocurrency{

    public  $id;
    public  $base;
    public  $name;

    public $subscribedusers;
 
    public function __construct($id, $base,$name)
    {
        $this->id = $id;
        $this->base = $base;
        $this->name = $name;
    }

    public function subscribe(User $user)
    {
        $this->subscribedusers[$user->id] = $user;
    }

    public function unsubscribe(User $user)
    {
        unset($this->subscribedusers[$user->id]);
    }

    public function notify()
    {
        foreach ($this->subscribedusers as $user){
            $user->update($this);
        }
    }



}