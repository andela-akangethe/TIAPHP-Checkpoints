<?php

namespace Alex;

/**
 * This is a class which determines the githubu user level based
 * on the number of repositories they posses.
 *
 * @author Alex Kangethe
 */

class EvangelistStatus
{
    /**
     * A constructor
     *
     * It is called every time the an object is instansiated
     * before the object can be manupilated by getStatus method
     *
     * @param string user. The string is the github username
     */
    public function __construct($user)
    {
        $this->userRepository = $user;
    }
    
    /**
     * This method gets the number of repositories belonging to a github user
     *
     * In this method the number of repositories the github user posses are
     * determined and the user is ranked based on the number of repositories.
     *
     * @throws Exception if the github user does not exist
     *
     * @return The level of the github user
     */
    public function getStatus()
    {
        $options  = array('http' => array('user_agent'=> 'andela-akangethe'));
        $context  = stream_context_create($options);
        $userResult = file_get_contents(
            "https://api.github.com/users/{$this->userRepository}/repos",
            false,
            $context
        );
       
        if ($userResult === false) {
            throw new Exception("Sorry no such user exists on github");
        } else {
            $totalRepository = count(json_decode($userResult));

            if ($totalRepository < 5) {
                $level = 'Hey pull up your socks and help the community';
            } elseif ($totalRepository >= 5 && $totalRepository < 11) {
                $level = 'Damn It!!! Please make the world better, Oh Ye Prodigal Evangelist';
            } elseif ($totalRepository < 21) {
                $level = 'Keep Up The Good Work, I crown you Associate Evangelist';
            } else {
                $level = 'Awesome, I crown you Most Senior Evangelist.';
            }

            return $level;
        }
    }
}
