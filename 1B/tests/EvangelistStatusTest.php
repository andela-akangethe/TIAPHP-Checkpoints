<?php

use Alex\EvangelistStatus;

class EvangelistStatusTest extends PHPUnit_Framework_TestCase
{
    public function testMostSeniorEvangelist()
    {
        $status = new EvangelistStatus('ProfNandaa');
        $this->assertEquals($status->getStatus(), 'Awesome, I crown you Most Senior Evangelist.');
    }

    public function testAssociateEvangelist()
    {
        $status = new EvangelistStatus('andela-akangethe');
        $this->assertEquals($status->getStatus(), 'Keep Up The Good Work, I crown you Associate Evangelist');
    }

    public function testJuniorEvangelist()
    {
        $status = new EvangelistStatus('andela-sachungo');
        $this->assertEquals($status->getStatus(), 'Damn It!!! Please make the world better, Oh Ye Prodigal Evangelist');
    }

    public function testtoddlerEvangelist()
    {
        $status = new EvangelistStatus('TemmyFowotade');
        $this->assertEquals($status->getStatus(), 'Hey pull up your socks and help the community');
    }
}
